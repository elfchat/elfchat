<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

use ElfChat\Entity\Queue;
use ElfChat\Entity\User;

class AjaxServer extends AbstractServer implements ServerInterface
{
    private $user;

    /**
     * We use AjaxServer only on HTTP request, and every HTTP request has a specified session
     * with specified user. So we can use user in our server implementation.
     * @param User $user
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
        // TODO: Implement kill() method.
    }

    public function log($text, $level = 'default')
    {
        $queue = new Queue();
        $queue->data = Protocol::log($this, $level);
        $queue->save();
    }

} 