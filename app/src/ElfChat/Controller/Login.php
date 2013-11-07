<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller;

use ElfChat\Entity\Guest;
use Silicone\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @Route("/login")
 */
class Login extends Controller
{
    /**
     * @Route("", name="login")
     */
    public function index(Request $request)
    {
        $response = $this->render('users/login.twig', array(
            'error' => $this->app['security.last_error']($request),
            'last_username' => $this->app['session']->get('_security.last_username'),
        ));
        return $response;
    }

    /**
     * @Route("/guest", name="login_guest")
     */
    public function guest(Request $request)
    {
        $em = $this->app->entityManager();

        $guest = new Guest();
        $guest->setUsername($this->request->request->get('_username', 'Guest'));
        $em->persist($guest);
        $em->flush();

        $this->app->session()->set('_guest', serialize($guest));

        return $this->app->redirect('login_check_guest');
    }
}
