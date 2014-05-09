<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

use ElfChat\Entity\Ajax\Queue;
use ElfChat\Entity\User;

class AjaxServer extends AbstractServer implements ServerInterface
{
    private $user;

    /**
     * We use AjaxServer only on HTTP request, and every HTTP request has a specified session
     * with specified user. So we can use user in our server implementation.
     * @param \ElfChat\Entity\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function send($data)
    {
        $queue = new Queue();
        $queue->user = $this->user;
        $queue->data = $data;
        $queue->save();
    }

    public function sendExclude($userId, $data)
    {
        $queue = new Queue();
        $queue->user = $this->user;
        $queue->exclude = User::reference($userId);
        $queue->data = $data;
        $queue->save();
    }

    public function sendToUser($userId, $data)
    {
        $queue = new Queue();
        $queue->user = $this->user;
        $queue->for = User::reference($userId);
        $queue->data = $data;
        $queue->save();
    }

    public function kill($userId)
    {
        $this->send(Protocol::userLeave(User::find($userId)));
    }

    public function log($text, $level = 'default')
    {
        $queue = new Queue();
        $queue->data = Protocol::log($text, $level);
        $queue->save();
    }

    public function updateUser()
    {
        $this->send(Protocol::userUpdate($this->user));
    }
}