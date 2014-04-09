<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Form;

use ElfChat\Entity\Ban;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'user', array(
            'label' => 'User ID',
            'required' => false,
        ));
        $builder->add('ip', 'text', array(
            'label' => 'IP',
            'required' => false,
        ));
        $builder->add('howLong', 'choice', array(
            'label' => 'On how long',
            'choices' => Ban::howLongChoices(),
        ));
        $builder->add('reason', 'textarea', array(
            'label' => 'Reason',
            'required' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ElfChat\Entity\Ban',
        ));
    }


    public function getName()
    {
        return 'ban';
    }
} 