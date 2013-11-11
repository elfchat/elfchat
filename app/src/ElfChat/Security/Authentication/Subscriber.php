<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Security\Authentication;

use Doctrine\ORM\EntityRepository;
use ElfChat\Exception\AccessDenied;
use ElfChat\Security\Authentication\Provider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class Subscriber implements EventSubscriberInterface
{
    protected $provider;

    protected $repository;

    protected $remember;

    public function __construct(Provider $provider, EntityRepository $repository, Remember $remember)
    {
        $this->provider = $provider;
        $this->repository = $repository;
        $this->remember = $remember;
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
        $request = $event->getRequest();
        $session = $request->getSession();

        list($id, $role) = $session->get('user', array(null, 'ROLE_GUEST'));

        if(null === $id && $request->cookies->has(Remember::REMEMBER_ME)) {
            if( $this->remember->check($request->cookies->get(Remember::REMEMBER_ME)))
            {
                list($id, $role) = $this->remember->getIt();
                $session->set('user', array($id, $role));
            }
        }

        $this->provider->setRole($role);

        if(!$this->provider->isAllowed($request->getPathInfo())) {
            throw new AccessDenied("Access denied to " . $request->getPathInfo());
        }

        if(null !== $id) {
            $user = $this->repository->find($id);

            if(null !== $user) {
                $this->provider->setUser($user);
                $this->provider->setAuthenticated(true);
            }
        }
    }
}