<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use ElfChat\Entity\User;

/**
 * @ORM\Entity
 */
class GuestUser extends User
{
    public function __construct()
    {
        $this->role = 'ROLE_GUEST';
    }
} 