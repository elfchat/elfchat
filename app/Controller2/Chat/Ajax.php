<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Chat;

use ElfChat\Controller;
use ElfChat\Entity\Online;
use ElfChat\Entity\Queue;
use ElfChat\Server\AjaxServer;
use ElfChat\Server\Protocol;
use Silicone\Route;

/**
 * @Route("/ajax")
 */
class Ajax extends Controller
{
    /**
     * @Route("/poll", name="ajax_poll")
     */
    public function poll()
    {
        // Online users list workflow.

        $online = Online::findUser($this->app->user()->id);

        // If user does not online, "connect" user to chat.
        if (empty($online)) {
            $online = new Online();
            $online->user = $this->app->user();

            $this->app->server()->send(Protocol::userJoin($this->app->user()));
        }

        $online->updateTime();
        $online->save();

        // Message queue workflow.

        $last = (int)$this->request->get('last', 0);
        $queue = Queue::poll($last, $this->app->user()->id, $last === 0 ? 1 : 10);

        if (!empty($queue)) {
            $last = $queue[0]->id;
        } else {
            // Clear queue in one on hundred times.
            if (1 === rand(1, 100)) {
                Queue::deleteOld($last);
            }
        }

        // Sort in correct direction and for convert to json format.
        $queue = array_reverse(array_map(function ($q) {
            return $q->data;
        }, $queue));

        return $this->app->json(['last' => (int)$last, 'queue' => $queue]);
    }

    /**
     * @Route("/send", name="ajax_send", methods="post")
     */
    public function onSend()
    {
        $data = json_decode($this->request->request->get('data'));

        if (null === json_last_error_msg() || !is_array($data) || count($data) < 0) {
            return $this->app->json(false);
        }

        $server = $this->app->server();

        if($server instanceof AjaxServer) {
            $message = $server->onReceiveData($this->app->user(), $data);

            return $this->app->json($message !== null);
        }

        return $this->app->json(false);
    }
} 