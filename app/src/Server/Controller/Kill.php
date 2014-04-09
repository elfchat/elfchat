<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\Controller;

use ElfChat\Server;
use Guzzle\Http\Message\RequestInterface;

class Kill extends Controller
{
    private $chat;

    public function __construct(Server $chat)
    {
        $this->chat = $chat;
    }

    public function action(RequestInterface $request)
    {
        $userId = $request->getUrl(true)->getQuery()->get('userId');
        $this->chat->kill($userId);
        return $this->json(true);
    }
}