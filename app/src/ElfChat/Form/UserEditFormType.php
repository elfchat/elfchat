<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Form;

use ElfChat\Entity\User;
use ElfChat\EventListener\PasswordEncoderSubscriber;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null)
            ->add('email', 'email')
            ->add('plainPassword', 'password', array(
                'mapped' => false,
                'required' => false,
                'label' => 'Password',
            ))
            ->add('role', 'choice', array(
                'choices' => array(
                    'ROLE_GUEST' => 'Guest',
                    'ROLE_USER' => 'User',
                    'ROLE_MODERATOR' => 'Moderator',
                    'ROLE_ADMIN' => 'Admin'
                ),
                'expanded' => false,
                'multiple' => false,
                'label' => 'Role'
            ))
            ->add('removeAvatar', 'checkbox', array(
                'mapped' => false,
                'required' => false,
                'label' => 'Remove avatar',
            ))
        ;

        $builder->addEventSubscriber(new PasswordEncoderSubscriber);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var $user User */
            $user = $event->getData();
            $form = $event->getForm();

            if($form->get('removeAvatar')->getData()) {
                $user->setAvatar(null);
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ElfChat\Entity\User',
            'validation_groups' => array('Edit'),
        ));
    }

    public function getName()
    {
        return 'user_edit';
    }
}