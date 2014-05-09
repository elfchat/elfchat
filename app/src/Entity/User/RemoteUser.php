<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use ElfChat\Entity\User;

/**
 * @property string $remoteSource
 * @property int $remoteId
 *
 * @ORM\Entity
 */
class RemoteUser extends User
{
    /**
     * @ORM\Column(nullable=true)
     */
    protected $remoteSource;

    /**
     * @ORM\Column(type="integer")
     */
    protected $remoteId;

    public static function findRemote($from, $id)
    {
        $dql = '
        SELECT u
        FROM ElfChat\Entity\User\RemoteUser u
        WHERE u.remoteSource = :from AND u.remoteId = :id
        ';
        $query = self::entityManager()->createQuery($dql);
        $query->setParameter(':from', $from);
        $query->setParameter(':id', $id);
        $query->setMaxResults(1);

        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}