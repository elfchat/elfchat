<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat;

use ElfChat\Entity\Message;
use ElfChat\Entity\User;
use ElfChat\Repository\MessageRepository;
use ElfChat\Server\Protocol;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Server implements MessageComponentInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var ConnectionInterface[]
     */
    private $clients;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->em = $this->app->entityManager();
        $this->clients = array();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $users = $this->app->repository()->users();

        $userData = $conn->Session->get('user');

        if (count($userData) == 2 && is_int($userData[0])) {
            list($userId, $userRole) = $userData;

            $user = $users->find($userId);

            if (null === $user) {
                $conn->close();
                return;
            }

            $this->em->refresh($user);
            $conn->user = $user;

            $this->send(Protocol::userJoin($user));

            $this->clients[$user->getId()] = $conn;

            $users = array();
            foreach ($this->clients as $conn) {
                $users[] = $conn->user->export();
            }

            $this->sendToUser($user->id, Protocol::data(Protocol::SYNCHRONIZE, $users));
        } else {
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $data)
    {
        $data = json_decode($data);

        if (null === json_last_error_msg() || !is_array($data) || count($data) < 0) {
            return;
        }

        if ($data[0] === Protocol::MESSAGE && count($data) === 2) {
            $this->onPublicMessage($from, $data[1]);
        } else if ($data[0] === Protocol::PRIVATE_MESSAGE && count($data) === 3) {
            $this->onPrivateMessage($from, $data[1], $data[2]);
        }
    }

    private function onPublicMessage(ConnectionInterface $from, $text)
    {
        $em = $this->app->entityManager();
        /** @var $user User */
        $user = $from->user;

        // Create message
        $message = new Message();
        $message->user = $user;
        $message->datetime = new \DateTime();
        $message->text = $text;

        // And save it
        $em->persist($message);
        $em->flush();

        $this->send(Protocol::message($message));

        $em->detach($message);
    }

    private function onPrivateMessage(ConnectionInterface $from, $userId, $text)
    {
        $em = $this->app->entityManager();
        /** @var $user User */
        $user = $from->user;

        // Create message
        $message = new Message();
        $message->user = $user;
        $message->for = $em->getPartialReference('ElfChat\Entity\User', $userId);
        $message->datetime = new \DateTime();
        $message->text = $text;

        // And save it
        $em->persist($message);
        $em->flush();

        $data = Protocol::message($message);
        $this->sendToUser($user->id, $data);
        if ($userId != $user->id) {
            $this->sendToUser($userId, $data);
        }

        $em->detach($message);
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->sendExclude($conn->user->id, Protocol::userLeave($conn->user));
        $this->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
        $this->detach($conn);
    }

    public function send($data)
    {
        foreach ($this->clients as $id => $conn) {
            $conn->send($data);
        }
    }

    public function sendExclude($userId, $data)
    {
        foreach ($this->clients as $id => $conn) {
            if ($id === $userId) {
                continue;
            }

            $conn->send($data);
        }
    }

    public function sendToUser($userId, $data)
    {
        if (isset($this->clients[$userId])) {
            $conn = $this->clients[$userId];
            $conn->send($data);
        }
    }

    /**
     * Remove user from connected clients by ConnectionInterface.
     *
     * @param ConnectionInterface $conn
     */
    private function detach(ConnectionInterface $conn)
    {
        $user = $conn->user;
        unset($this->clients[$user->id]);
    }

    /**
     * @param $userId
     * @return null|ConnectionInterface
     */
    public function getClient($userId)
    {
        if (isset($this->clients[$userId])) {
            return $conn = $this->clients[$userId];
        } else {
            return null;
        }
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    public function kill($userId)
    {
        $conn = $this->getClient($userId);

        if(null !== $conn) {
            $conn->close();
            //$this->onClose($conn);
        }
    }

    public function log($text, $level = 'default')
    {
        $this->send(Protocol::data(Protocol::LOG, array(
            'text' => $text,
            'level' => $level,
        )));
    }
}