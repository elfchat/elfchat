<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

use ElfChat\Application;
use ElfChat\Entity\Message;
use ElfChat\Entity\User;
use ElfChat\Repository\MessageRepository;
use ElfChat\Server\Protocol;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketServer extends AbstractServer implements ServerInterface, MessageComponentInterface
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

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->em = $this->app->entityManager();
        $this->clients = array();
    }

    /**
     * @param ConnectionInterface $conn
     */
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

            $this->clients[$user->id] = $conn;

            $users = array();
            foreach ($this->clients as $conn) {
                $users[] = $conn->user->export();
            }

            $this->sendToUser($user->id, Protocol::data(Protocol::SYNCHRONIZE, $users));
        } else {
            $conn->close();
        }
    }

    /**
     * @param ConnectionInterface $from
     * @param string $data
     */
    public function onMessage(ConnectionInterface $from, $data)
    {
        $data = json_decode($data);

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($data) || count($data) < 0) {
            return;
        }

        $message = $this->onReceiveData($from->user, $data);
        if (null !== $message) {
            $this->em->detach($message);
        }
    }

    /**
     * When we send to user private message, we need to send this message to author.
     *
     * @param \ElfChat\Entity\User $user
     * @param $forId
     * @param $text
     * @return Message
     */
    protected function privateMessage(User $user, $forId, $text)
    {
        $message = parent::privateMessage($user, $forId, $text);
        if ($forId !== $user->id) {
            $this->sendToUser($user->id, Protocol::message($message));
        }
        return $message;
    }


    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        if (null !== $conn->user) {
            $this->sendExclude($conn->user->id, Protocol::userLeave($conn->user));
            $this->detach($conn);
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
        $this->detach($conn);
    }

    /**
     * @param $data
     */
    public function send($data)
    {
        foreach ($this->clients as $id => $conn) {
            $conn->send(json_encode($data));
        }
    }

    /**
     * @param $userId
     * @param $data
     */
    public function sendExclude($userId, $data)
    {
        foreach ($this->clients as $id => $conn) {
            if ($id === $userId) {
                continue;
            }

            $conn->send(json_encode($data));
        }
    }

    /**
     * @param $userId
     * @param $data
     */
    public function sendToUser($userId, $data)
    {
        if (isset($this->clients[$userId])) {
            $conn = $this->clients[$userId];
            $conn->send(json_encode($data));
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
     * @param $userId
     */
    public function kill($userId)
    {
        $conn = $this->getClient($userId);

        if (null !== $conn) {
            $conn->close();
        }
    }

    public function log($text, $level = 'default')
    {
        $this->send(Protocol::log($text, $level));
    }

    public function updateUser()
    {
        // Update user in WebSocketServer\Controller::updateUser
    }
}