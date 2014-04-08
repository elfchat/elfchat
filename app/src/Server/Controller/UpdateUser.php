<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\Controller;

use ElfChat\Server;
use Guzzle\Http\Message\RequestInterface;

class UpdateUser extends Controller
{
    private $chat;

    public function __construct(Server $chat)
    {
        $this->chat = $chat;
    }

    public function action(RequestInterface $request)
    {
        $conn = $this->chat->getClient($this->userId);

        if(null === $conn) {
            return $this->jsonp(false);
        }

        $this->chat->getEntityManager()->refresh($conn->user);
        $this->chat->send(Server\Protocol::userUpdate($conn->user));

        return $this->jsonp(true);
    }
}