<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Form;

use Symfony\Component\Form\AbstractType;

class ChosenType extends AbstractType
{
    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'chosen';
    }
} 