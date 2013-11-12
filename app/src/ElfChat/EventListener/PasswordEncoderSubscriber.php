<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\EventListener;

use ElfChat\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PasswordEncoderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => 'onSubmit',
        );
    }

    public function onSubmit(FormEvent $event)
    {
        /** @var $user User */
        $user = $event->getData();
        $form = $event->getForm();

        if ($form->has('plainPassword')) {
            $password = $form->get('plainPassword')->getData();

            if(empty($password)) {
                return;
            }
        } else {
            $password = $user->getPassword();
        }

        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
    }
}