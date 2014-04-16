<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\Controller;

use ElfChat\Server\WebSocketServer;
use Guzzle\Http\Message\RequestInterface;

class Kill extends Controller
{
    public function isAllowed($role)
    {
        return $role === 'ROLE_MODERATOR';
    }

    public function action(RequestInterface $request)
    {
        $userId = $request->getUrl(true)->getQuery()->get('userId');
        $this->chat->kill($userId);
        return $this->json(true);
    }
}