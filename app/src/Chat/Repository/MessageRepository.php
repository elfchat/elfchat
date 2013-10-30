<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Repository;

use Chat\Entity\Message;
use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{
    const lastMessageCache = 'last_message';

    /**
     * @param $room
     * @return Message[]
     */
    public function getLastMessages($room)
    {
        $dql = "
        SELECT m, u
        FROM Chat\Entity\Message m
        JOIN m.user u
        WHERE m.room = :room
        ORDER BY m.id DESC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setMaxResults(10);
        $query->setParameter('room', $room);
        $query->useResultCache(true, 3600, self::lastMessageCache);
        return $query->getResult();
    }
}