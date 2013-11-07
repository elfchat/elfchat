<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Repository;

use ElfChat\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findAll()
    {
        $dql = "SELECT u, a FROM ElfChat\Entity\User u LEFT JOIN u.avatar a";
        $query = $this->_em->createQuery($dql);
        return $query->getResult();
    }

    public function queryNames($a)
    {
        $dql = "SELECT u FROM ElfChat\Entity\User u WHERE u.username LIKE ?1";
        $query = $this->_em->createQuery($dql);
        $query->setParameter(1, $a . '%');
        $query->setMaxResults(10);
        return $query->getResult();
    }
}