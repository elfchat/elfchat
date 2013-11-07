<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Admin\Form;

use ElfChat\Entity\User;
use ElfChat\EventListener\PasswordEncoderSubscriber;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserFormType extends AbstractType
{
    private $passwordEncoderSubscriber;

    public function __construct(PasswordEncoderSubscriber $passwordEncoderSubscriber)
    {
        $this->passwordEncoderSubscriber = $passwordEncoderSubscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => 'user.name'))
            ->add('email', 'email', array('label' => 'user.email'))
            ->add('plainPassword', 'password', array(
                'mapped' => false,
                'required' => false,
                'label' => 'user.password',
            ))
            ->add('role', 'choice', array(
                'choices' => array(
                    'ROLE_USER' => 'user.role.user',
                    'ROLE_MODERATOR' => 'user.role.moderator',
                    'ROLE_ADMIN' => 'user.role.admin'
                ),
                'expanded' => false,
                'multiple' => false,
                'label' => 'user.roles'
            ))
            ->add('removeAvatar', 'checkbox', array(
                'mapped' => false,
                'required' => false,
                'label' => 'Remove avatar',
            ))
        ;

        $builder->addEventSubscriber($this->passwordEncoderSubscriber);

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
            'translation_domain' => 'admin'
        ));
    }

    public function getName()
    {
        return 'user_edit';
    }
}