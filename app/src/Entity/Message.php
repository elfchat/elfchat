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
 * @property mixed $data
 *
 * @ORM\Entity
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
     * @ORM\Column(type="json_array")
     */
    protected $data;

    public function __construct()
    {
        $this->room = 'main';
    }

    public function export()
    {
        return array(
            'id' => $this->id,
            'user' => array(
                'id' => $this->user->id
            ),
            'for' => null,
            'datetime' => $this->datetime->format(\DateTime::ISO8601),
            'room' => $this->room,
            'data' => $this->data,
        );
    }

    public function exportWithUser()
    {
        $export = $this->export();
        $export['user'] = $this->user->export();
        $export['for'] = empty($this->for) ? null : $this->for->export();
        return $export;
    }

    /**
     * @param int $userId
     * @return Message[]
     */
    public static function getLastMessages($userId)
    {
        $dql = "
        SELECT m, u, for, a
        FROM ElfChat\Entity\Message m
        JOIN m.user u
        LEFT JOIN m.for for
        LEFT JOIN u.avatar a
        WHERE m.room = :room AND (m.for IS NULL OR (m.for IS NOT NULL AND (u.id = :userId OR for.id = :userId)))
        ORDER BY m.id DESC
        ";
        $query = self::entityManager()->createQuery($dql);
        $query->setMaxResults(10);
        $query->setParameter('room', 'main');
        $query->setParameter('userId', $userId);
        return $query->getResult();
    }
}
