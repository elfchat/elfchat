<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity\Ajax;

use Doctrine\ORM\Mapping as ORM;
use ElfChat\Entity\Entity;

/**
 * @property int $id
 * @property \ElfChat\Entity\User $user
 * @property \ElfChat\Entity\User $for
 * @property \ElfChat\Entity\User $exclude
 * @property mixed $data
 *
 * @ORM\Entity()
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
     * @ORM\ManyToOne(targetEntity="ElfChat\Entity\User")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="ElfChat\Entity\User")
     */
    protected $for;

    /**
     * @ORM\ManyToOne(targetEntity="ElfChat\Entity\User")
     */
    protected $exclude;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $data;

    public static function poll($last, $userId, $limit = 10)
    {
        $dql = '
        SELECT q
        FROM ElfChat\Entity\Ajax\Queue q
        WHERE (q.for IS NULL OR (q.for IS NOT NULL AND (q.user = :userId OR q.for = :userId)))
        AND (q.exclude IS NULL OR (q.exclude IS NOT NULL AND q.exclude != :userId))
        AND q.id > :last
        ORDER BY q.id DESC
        ';

        $query = self::entityManager()->createQuery($dql);
        $query->setMaxResults($limit);
        $query->setParameter('last', $last);
        $query->setParameter('userId', $userId);
        return $query->getResult();
    }

    public static function deleteOld($last, $limit = 10)
    {
        $dql = 'DELETE ElfChat\Entity\Ajax\Queue q WHERE q.id NOT IN(:ids)';
        $query = self::entityManager()->createQuery($dql);
        $query->setParameter('ids', range($last, $last - $limit));
        return $query->getResult();
    }
}