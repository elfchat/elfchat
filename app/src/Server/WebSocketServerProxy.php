<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

use Buzz\Browser;
use Buzz\Client\Curl;

class WebSocketServerProxy implements ServerInterface
{
    private $browser;

    private $serverBaseUrl;

    private $cookie;

    public function __construct($baseUrl, $cookie)
    {
        $this->browser = new Browser(new Curl());
        $this->serverBaseUrl = $baseUrl;
        $this->cookie = $cookie;
    }

    private function get($url, $params = array())
    {
        return $this->browser->get($this->serverBaseUrl . $url . '?' . http_build_query($params), array('Cookie' => $this->cookie));
    }

    public function send($data)
    {
        throw new \RuntimeException('Send method of WebSocketServerProxy does not implemented.');
    }

    public function sendExclude($userId, $data)
    {
        throw new \RuntimeException('Send method of WebSocketServerProxy does not implemented.');
    }

    public function sendToUser($userId, $data)
    {
        throw new \RuntimeException('Send method of WebSocketServerProxy does not implemented.');
    }

    public function kill($userId)
    {
        $this->get('/kill', array('userId' => $userId));
    }

    public function log($text, $level = 'default')
    {
        $this->get('/log', array('text' => $text, 'level' => $level));
    }

} 