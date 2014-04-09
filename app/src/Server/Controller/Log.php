<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\Controller;

use ElfChat\Server;
use Guzzle\Http\Message\RequestInterface;

class Log extends Controller
{
    private $chat;

    public function __construct(Server $chat)
    {
        $this->chat = $chat;
    }


    public function action(RequestInterface $request)
    {
        $text = $request->getUrl(true)->getQuery()->get('text');
        $level = $request->getUrl(true)->getQuery()->get('level');
        $this->chat->log($text, $level ?: 'default');
        return $this->json(true);
    }
}