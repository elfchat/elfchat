<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\EventListener;

use Doctrine\ORM\EntityRepository;
use ElfChat\Security\Authentication\Provider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $provider;

    protected $repository;

    protected $roleHierarchy;

    protected $accessRules;

    public function __construct(Provider $provider, EntityRepository $repository, array $roleHierarchy, array $accessRules)
    {
        $this->provider = $provider;
        $this->accessRules = $accessRules;
        $this->repository = $repository;
        $this->roleHierarchy = $roleHierarchy;
    }


    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('onKernelRequest', 120),
            ),
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        list($id, $role) = $event->getRequest()->getSession()->get('user', array(null, 'ROLE_GUEST'));

        $this->provider->setRole($role);

        if(null !== $id) {
            $user = $this->repository->find($id);

            if(null !== $user) {
                $this->provider->setUser($user);
            }
        }
    }
}