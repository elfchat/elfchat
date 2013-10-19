<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Admin\Form\Config;

use Admin\Form\ConfigType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostgresType extends ConfigType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('host');
        $builder->add('dbname');
        $builder->add('user');
        $builder->add('password');
    }

    public function getName()
    {
        return 'postgres';
    }


}