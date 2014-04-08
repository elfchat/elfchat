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

            $this->sendPrivate($user->id, Protocol::data(Protocol::SYNCHRONIZE, $users));
        } else {
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $data)
    {
        $em = $this->app->entityManager();
        /** @var $user User */
        $user = $from->user;

        // Create message
        $message = new Message();
        $message->user = $user;
        $message->datetime = new \DateTime();
        $message->text = $data;

        // And save it any way
        $em->persist($message);
        $em->flush();

        $this->send(Protocol::message($message));
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
            if($id === $userId) {
                continue;
            }

            $conn->send($data);
        }
    }

    public function sendPrivate($userId, $data)
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
}