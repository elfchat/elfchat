<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr;
use Doctrine\Tests\Models\Company\CompanyRaffle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserNameValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($name, Constraint $constraint)
    {
        $query = $this->em->createQuery('
        SELECT COUNT(u.id) FROM ElfChat\Entity\User u
        WHERE (u.name = :name AND u INSTANCE OF ElfChat\Entity\User)
        OR (u.name = :name AND u INSTANCE OF ElfChat\Entity\User\RemoteUser)
        OR (u.name = :name AND u INSTANCE OF ElfChat\Entity\User\GuestUser AND u.registered > :day)');

        $query->setParameter('name', $name);
        $now = new \DateTime();
        $oneDaysAgo = $now->sub(new \DateInterval("P1D"));
        $query->setParameter('day', $oneDaysAgo);
        $query->setMaxResults(1);

        if ($query->getSingleScalarResult() > 0) {
            $this->context->addViolation($constraint->message);
        }
    }
}