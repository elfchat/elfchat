<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Form;

use ElfChat\EventListener\PasswordEncoderSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('password', 'repeated', array(
                'type' => 'password',
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat password'),
                'invalid_message' => 'Password mismatch',
            ))
        ;

        $builder->addEventSubscriber(new PasswordEncoderSubscriber);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ElfChat\Entity\User',
            'validation_groups' => array('registration'),
        ));
    }

    public function getName()
    {
        return 'registration';
    }
}