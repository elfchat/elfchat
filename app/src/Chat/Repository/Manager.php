<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Repository;

use Doctrine\ORM\EntityManager;

class Manager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $entity
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function get($entity)
    {
        return $this->em->getRepository("Chat\\Entity\\" . $entity);
    }

    /**
     * @return UserRepository
     */
    public function users()
    {
        return $this->get('User');
    }

    public function bans()
    {
        return $this->get('Ban');
    }
} 