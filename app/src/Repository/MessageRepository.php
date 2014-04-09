<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Repository;

use ElfChat\Entity\Message;
use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{
    /**
     * @param int $userId
     * @return Message[]
     */
    public function getLastMessages($userId)
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
        $query = $this->_em->createQuery($dql);
        $query->setMaxResults(10);
        $query->setParameter('room', 'main');
        $query->setParameter('userId', $userId);
        return $query->getResult();
    }
}