<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ElfChat\Entity\Statistic;

use Doctrine\ORM\Mapping as ORM;
use ElfChat\Entity\Entity;

/**
 * @property int $id
 * @property int $memoryUsage
 *
 * @ORM\Entity
 * @ORM\Table("elfchat_statistic_memory")
 */
class Memory extends Entity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $memoryUsage;
}
