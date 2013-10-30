<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Unique extends Constraint
{
    public $column = '';
    public $message = '%column% already used';

    public function validatedBy()
    {
        return 'validator.unique';
    }

    public function getRequiredOptions()
    {
        return array('column');
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}