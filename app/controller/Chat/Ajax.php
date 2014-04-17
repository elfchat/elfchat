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
        // Make use "online"
        $online = Online::findUser($this->app->user()->id);

        // If user does not online, "connect" user to chat.
        // First check if online entity exist, if not when create new one.
        // If entity exist but in timeout, when send "user join" again.

        if (empty($online)) {
            $online = new Online();
            $online->user = $this->app->user();
            $this->app->server()->send($userJoin = Protocol::userJoin($this->app->user()));
        } else if ($online->isTimeout()) {
            $this->app->server()->send($userJoin = Protocol::userJoin($this->app->user()));
        }

        $online->updateTime();
        $online->save();

        // Time out other users.
        foreach (Online::offlineUsers() as $online) {
            $this->app->server()->send(Protocol::userLeave($online->user));
            $online->remove();
        }
        Online::flush();

        // Message queue workflow.
        // On first load user send last=0, and we need to send correct last queue id for him.
        // If its first load poll only 1 entity - grab id of it and clear queue (message duplicate issue).
        // Otherwise pull 10 entities, clear queue only if where are no messages pulled.

        $last = (int)$this->request->get('last', 0);
        $firstLoad = $last === 0;

        $queue = Queue::poll($last, $this->app->user()->id, $firstLoad ? 1 : 10);

        if (!empty($queue)) {
            $last = $queue[0]->id;

            if ($firstLoad) {
                $queue = array();
            }
        } else {
            // Clear queue in one on hundred times.
            if (1 === rand(1, 100)) {
                Queue::deleteOld($last);
            }
        }

        // Sort in correct direction and convert to json format.
        $queue = array_reverse(array_map(function ($q) {
            return $q->data;
        }, $queue));

        if (isset($userJoin)) {
            $queue = array($userJoin);
        }

        return $this->app->json(array('last' => (int)$last, 'queue' => $queue));
    }

    /**
     * @Route("/send", name="ajax_send", methods="post")
     */
    public function onSend()
    {
        $data = json_decode($this->request->request->get('data'));

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($data) || count($data) < 0) {
            return $this->app->json(false);
        }

        $server = $this->app->server();

        if ($server instanceof AjaxServer) {
            $message = $server->onReceiveData($this->app->user(), $data);

            return $this->app->json($message !== null);
        }

        return $this->app->json(false);
    }

    /**
     * @Route("/synchronize", name="ajax_synchronize", methods="post")
     */
    public function synchronize()
    {
        $users = array();
        foreach (Online::users() as $online) {
            $users[] = $online->user->export();
        }

        return $this->app->json(Protocol::synchronize($users));
    }
}