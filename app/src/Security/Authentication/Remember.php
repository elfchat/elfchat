<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Security\Authentication;

class Remember
{
    const REMEMBER_ME = 'ELFCHAT_REMEMBER';

    private $key;

    private $it;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encode($it)
    {
        return serialize(array($it, $this->hash($it)));
    }

    public function check($check)
    {
        list($it, $hash) = unserialize($check);

        if ($this->hash($it) === $hash) {
            $this->it = $it;
            return true;
        }

        return false;
    }

    private function hash($it)
    {
        return sha1(serialize($it) . $this->key);
    }

    public function getIt()
    {
        return $this->it;
    }
}