<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Repository;

use ElfChat\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @method User find($id)
 */
class UserRepository extends EntityRepository
{
    /**
     * @return \ElfChat\Entity\User[]
     */
    public function findAllUsers()
    {
        $dql = "SELECT u, a FROM ElfChat\Entity\User u LEFT JOIN u.avatar a WHERE u INSTANCE OF ElfChat\Entity\User";
        $query = $this->_em->createQuery($dql);
        return $query->getResult();
    }

    /**
     * @param $a
     * @return array
     */
    public function queryNames($a)
    {
        $dql = "SELECT u FROM ElfChat\Entity\User u WHERE u.name LIKE ?1";
        $query = $this->_em->createQuery($dql);
        $query->setParameter(1, $a . '%');
        $query->setMaxResults(10);
        return $query->getResult();
    }

    /**
     * @param $name
     * @return \ElfChat\Entity\User
     */
    public function findOneByName($name)
    {
        $dql = "SELECT u FROM ElfChat\Entity\User u WHERE u.name = ?1 AND u INSTANCE OF ElfChat\Entity\User";
        $query = $this->_em->createQuery($dql);
        $query->setParameter(1, $name);
        return $query->getSingleResult();
    }
}