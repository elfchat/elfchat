<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\Controller;

use ElfChat\Server\Controller\Controller;
use Guzzle\Http\Message\RequestInterface;

class MemoryUsage extends Controller
{
    const LIMIT = 1000;

    private $memory = array();

    public function action(RequestInterface $request)
    {
        return $this->jsonp($this->memory);
    }

    public function gather()
    {
        $this->memory[] = memory_get_usage(true) / 1048576; // in megabytes

        if(count($this->memory) > self::LIMIT) {
            array_shift($this->memory);
        }
    }
} 