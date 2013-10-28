<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'user', array(
            'required' => false,
        ));
        $builder->add('ip', 'text', array(
            'required' => false,
        ));
        $builder->add('howLong', 'choice', array(
            'choices' => static::howLongChoices(),
        ));
        $builder->add('reason', 'textarea', array(
            'required' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Chat\Entity\Ban',
        ));
    }


    public function getName()
    {
        return 'ban';
    }

    static public function howLongChoices()
    {
        return array(
            60 => 'One min',
            60 * 5 => '5 min',
            60 * 15 => '15 min',
            60 * 60 => 'One hour',
            60 * 60 * 12 => '12 hours',
            60 * 60 * 24 => 'One day',
            60 * 60 * 24 * 2 => '2 days',
            60 * 60 * 24 * 7 => '7 days',
            60 * 60 * 24 * 14 => '14 days',
            60 * 60 * 24 * 31 => '31 days',
            -1 => 'Forever',
        );
    }
} 