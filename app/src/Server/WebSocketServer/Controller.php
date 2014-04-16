<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\WebSocketServer;

use ElfChat\Server\Protocol;
use ElfChat\Server\WebSocketServer;
use ElfChat\Server\WebSocketServer\Controller\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    const LIMIT_MEMORY_USAGE_STAT = 1000;

    private $memoryUsage = array();

    private $server;

    public function __construct(WebSocketServer $server)
    {
        $this->server = $server;
    }

    protected function json($data, $callback = null)
    {
        $json = json_encode($data);

        if (empty($callback)) {
            return new Response($json, 200, array(
                'Content-Type' => 'application/json; charset=UTF-8',
                'Access-Control-Allow-Origin' => '*'
            ));
        } else {
            return new Response("$callback($json);", 200, array(
                'Content-Type' => 'application/javascript; charset=UTF-8',
                'Access-Control-Allow-Origin' => '*'
            ));
        }
    }

    protected function html($data)
    {
        return new Response($data, 200, array(
            'Content-Type' => 'text/html; charset=UTF-8',
            'Access-Control-Allow-Origin' => '*'
        ));
    }

    public function kill(Request $request)
    {
        $userId = $request->get('userId');
        $this->server->kill($userId);
        return $this->json(true, $request->get('callback'));
    }

    public function log(Request $request)
    {
        $text = $request->get('text');
        $level = $request->get('level', 'default');

        $this->server->log($text, $level);
        return $this->json(true, $request->get('callback'));
    }

    public function updateUser(Request $request)
    {
        $conn = $this->server->getClient($request->getUserId());

        if (null === $conn) {
            return $this->json(false, $request->get('callback'));
        } else {
            /** @var $user \ElfChat\Entity\User */
            $user = $conn->user;
            $user->refresh();
            $this->server->send(Protocol::userUpdate($user));

            return $this->json(true, $request->get('callback'));
        }
    }

    public function memoryUsage(Request $request)
    {
        return $this->json($this->memoryUsage, $request->get('callback'));
    }

    public function gatherMemoryUsage()
    {
        $this->memoryUsage[] = memory_get_usage(true) / 1048576; // in megabytes

        if (count($this->memoryUsage) > self::LIMIT_MEMORY_USAGE_STAT) {
            array_shift($this->memoryUsage);
        }
    }
} 