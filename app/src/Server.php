<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat;

use ElfChat\Entity\User;
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
     * @var ConnectionInterface[]
     */
    private $clients;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->clients = array();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $userData = $conn->Session->get('user');

        if (count($userData) == 2 && is_int($userData[0])) {
            list($userId, $userRole) = $userData;

            $user = $this->app->repository()->users()->find($userId);
            $conn->user = $user;

            $this->send(Protocol::userJoin($user));

            $this->clients[$user->getId()] = $conn;

            $users = array();
            foreach ($this->clients as $conn) {
                $users[] = $conn->user->export();
            }

            $this->sendPrivate($user->id, Protocol::data(Protocol::SYNCHRONIZE, $users));
        }
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        /*$message = trim($message);

        if (empty($message)) {
            return;
        }

        $message = [
            3,
            [
                'text' => $message,
                'user' => $from->user,
            ]
        ];

        foreach ($this->clients as $client) {
            $client->send(json_encode($message));
        }*/
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->detach($conn);
        $this->send(Protocol::userLeave($conn->user));
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
        /** @var $user User */
        $user = $conn->user;
        unset($this->clients[$user->getId()]);
    }
}