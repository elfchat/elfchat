<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr;
use Doctrine\Tests\Models\Company\CompanyRaffle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($class, Constraint $constraint)
    {
        $qb = $this->em->createQueryBuilder();
        $className = get_class($class);
        $column = $constraint->column;

        $query = $qb
            ->select('c')
            ->from($className, 'c')
            ->where($qb->expr()->eq('c.' . $column, '?1'))
            ->andWhere('c INSTANCE OF ' . $className)
            ->getQuery();

        $query->setParameter(1, $class->{'get' . ucfirst($column)}());
        $query->setMaxResults(1);

        $count = count($query->getResult());


        if ($count > 0) {
            $this->context->addViolation($constraint->message, array('%column%' => ucfirst($column)));
        }
    }
}