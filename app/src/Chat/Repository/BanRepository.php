<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Repository;

use Doctrine\ORM\EntityRepository;

class BanRepository extends EntityRepository
{
    public function findAll()
    {
        $dql = " SELECT b FROM Chat\Entity\Ban b ORDER BY b.created DESC";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}