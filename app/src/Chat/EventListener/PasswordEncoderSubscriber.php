<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\EventListener;

use Chat\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PasswordEncoderSubscriber implements EventSubscriberInterface
{
    private $encoder;

    public function __construct(PasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

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

        $salt = uniqid(time(), true);
        $user->setPassword($this->encoder->encodePassword($password, $salt));
        $user->setSalt($salt);
    }
}