<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("elfchat_session")
 */
class Session extends Entity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $data;

    /**
     * @ORM\Column(type="integer")
     */
    protected $time;
} 