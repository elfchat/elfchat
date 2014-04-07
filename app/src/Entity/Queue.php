<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ElfChat\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property int $id
 * @property \DateTime $datetime
 * @property string $type
 * @property mixed $data
 *
 * @ORM\Entity
 * @ORM\Table("elfchat_queue")
 */
class Queue extends Entity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $datetime;

    /**
     * @ORM\Column
     */
    protected $type;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $data;

    public function __construct()
    {
        $this->datetime = new \DateTime();
    }
}
