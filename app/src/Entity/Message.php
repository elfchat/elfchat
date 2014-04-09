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
 * @property \ElfChat\Entity\User $user
 * @property \ElfChat\Entity\User $for
 * @property \DateTime $datetime
 * @property string $room
 * @property string $text
 *
 * @ORM\Entity(repositoryClass="ElfChat\Repository\MessageRepository")
 * @ORM\Table("elfchat_message")
 */
class Message extends Entity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ElfChat\Entity\User")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="ElfChat\Entity\User")
     */
    protected $for;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $datetime;

    /**
     * @ORM\Column
     */
    protected $room;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    public function __construct()
    {
        $this->room = 'main';
    }

    public function export()
    {
        return array(
            'id' => $this->id,
            'user' => array(
                'id' => $this->user->getId()
            ),
            'for' => null,
            'datetime' => $this->datetime->format(\DateTime::ISO8601),
            'room' => $this->room,
            'text' => $this->text,
        );
    }

    public function exportWithUser()
    {
        $export = $this->export();
        $export['user'] = $this->user->export();
        $export['for'] = empty($this->for) ? null : $this->for->export();
        return $export;
    }

    public function setText($text)
    {
        $this->text = mb_substr($text, 0, 1000, 'UTF-8');
    }
}
