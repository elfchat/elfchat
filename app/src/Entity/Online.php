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
 * @property int $time
 *
 * @ORM\Entity()
 * @ORM\Table("elfchat_online", indexes={
 *     @ORM\index(name="user_idx", columns={"user_id"}),
 *     @ORM\index(name="time_idx", columns={"time"})
 * })
 */
class Online extends Entity
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
     * @ORM\Column(type="integer")
     */
    protected $time;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $connected;

    public function __construct()
    {
        $this->connected = new \DateTime();
    }

    public function updateTime()
    {
        $this->time = time();
    }

    /**
     * @param int $timeout Timeout in seconds.
     * @return Online[]
     */
    public static function users($timeout = 30)
    {
        $dql = '
        SELECT o FROM ElfChat\Entity\Online o JOIN o.user u WHERE :now < o.time + :timeout
        ORDER BY o.connected DESC
        ';
        $query = self::entityManager()->createQuery($dql);
        $query->setParameter('now', time());
        $query->setParameter('timeout', $timeout);
        return $query->getResult();
    }


    /**
     * @param int $timeout Timeout in seconds.
     * @return Online[]
     */
    public static function offlineUsers($timeout = 30)
    {
        $dql = '
        SELECT o FROM ElfChat\Entity\Online o JOIN o.user u WHERE :now > o.time + :timeout
        ';
        $query = self::entityManager()->createQuery($dql);
        $query->setParameter('now', time());
        $query->setParameter('timeout', $timeout);
        return $query->getResult();
    }

    /**
     * @param $userId
     * @return Online
     */
    public static function findUser($userId)
    {
        return self::entityManager()->getRepository('ElfChat\Entity\Online')->findOneBy(array(
            'user' => $userId,
        ));
    }
}