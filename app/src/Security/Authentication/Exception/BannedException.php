<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Security\Authentication\Exception;

use ElfChat\Entity\Ban;

class BannedException extends \Exception
{
    private $ban;

    public function __construct(Ban $ban, $code)
    {
        parent::__construct('', $code);
        $this->ban = $ban;
    }

    /**
     * @return \ElfChat\Entity\Ban
     */
    public function getBan()
    {
        return $this->ban;
    }
}