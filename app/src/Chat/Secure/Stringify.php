<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Secure;

class Stringify
{
    public function stringify(array $data)
    {
        return $this->loop($data);
    }

    private function loop($data)
    {
        $stack = array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $stack[] = $key . ':' . $this->loop($value);
            }
            return '[' . implode(',', $stack) . ']';
        } else {
            return $data;
        }
    }
}