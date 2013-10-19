<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Admin\Form;

use Admin\Form\Config\MysqlType;
use Admin\Form\Config\PostgresType;
use Admin\Form\Config\SqliteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('domain');
        $builder->add('key');
        $builder->add('database', 'choice', array(
            'choices' => array('sqlite' => 'SQLite', 'mysql' => 'MySQL', 'postgres' => 'PostgreSQL'),
            'required' => true,
        ));
        $builder->add('sqlite', new SqliteType());
        $builder->add('mysql', new MysqlType());
        $builder->add('postgres', new PostgresType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'config',
            'required' => false,
        ));
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'config';
    }

}