<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Form;

use Chat\EventListener\PasswordEncoderSubscriber;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends AbstractType
{
    private $passwordEncoderSubscriber;

    public function __construct(PasswordEncoderSubscriber $passwordEncoderSubscriber)
    {
        $this->passwordEncoderSubscriber = $passwordEncoderSubscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => 'form.name'))
            ->add('email', 'email', array('label' => 'form.email'))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'user.password.mismatch',
            ))
        ;

        $builder->addEventSubscriber($this->passwordEncoderSubscriber);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Chat\Entity\User',
            'validation_groups' => array('Registration'),
            'translation_domain' => 'users'
        ));
    }

    public function getName()
    {
        return 'user_registration';
    }
}