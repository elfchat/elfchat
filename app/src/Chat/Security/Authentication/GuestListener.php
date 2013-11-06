<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Security\Authentication;

use Chat\Entity\Guest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class GuestListener implements ListenerInterface
{
    protected $securityContext;

    protected $authenticationManager;

    protected $httpUtils;

    protected $session;

    protected $options;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, HttpUtils $httpUtils, Session $session, $options = array())
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->httpUtils = $httpUtils;
        $this->session = $session;
        $this->options = array_merge(array(
            'check_path' => '/login_check_guest',
        ), $options);
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->session->has('_guest')) {
            $guest = unserialize($this->session->get('_guest'));

            $token = new GuestToken();
            $token->setUser($guest);

            try {
                $authToken = $this->authenticationManager->authenticate($token);
                $this->securityContext->setToken($authToken);

                return;
            } catch (AuthenticationException $failed) {
                throw $failed;
            }
        }
    }
}