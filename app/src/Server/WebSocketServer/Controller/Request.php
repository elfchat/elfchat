<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\WebSocketServer\Controller;

use Guzzle\Http\Message\RequestInterface as GuzzleRequest;

class Request
{
    private $guzzleRequest;

    private $userId;

    public function __construct(GuzzleRequest $guzzleRequest, $userId)
    {
        $this->guzzleRequest = $guzzleRequest;
        $this->userId = $userId;
    }

    public function get($name, $default = null)
    {
        return $this->guzzleRequest->getUrl(true)->getQuery()->get($name) ? : $default;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}