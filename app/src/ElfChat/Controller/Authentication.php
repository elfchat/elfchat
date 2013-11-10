<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller;

use Silicone\Route;

class Authentication extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function check()
    {
        $session = $this->app->session();
        $users = $this->app->repository()->users();

        $form = $this->app->form()
            ->add('username')
            ->add('password', 'password')
            ->add('remember_me', 'checkbox', array('required' => false))
            ->getForm();

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $user = $users->findOneByName($data['username']);

            if(null !== $user) {
                if(password_verify($data['password'], $user->getPassword())) {
                    $session->set('user', array($user->getId(), $user->getRole()));
                } else {
                    $error = $this->app->trans('Bad credentials');
                }
            }
        }

        $guestForm = $this->app->form()
            ->add('guestname')
            ->getForm();

        $guestForm->handleRequest($this->request);

        if($guestForm->isValid()) {
            var_dump($guestForm->getData());
        }

        $response = $this->render('users/login.twig', array(
            'error' => isset($error) ? $error : null,
            'form' => $form->createView(),
            'guestForm' => $guestForm->createView(),
        ));
        return $response;
    }

    /**
     * @Route("/logout")
     */
    public function logout()
    {

    }
} 