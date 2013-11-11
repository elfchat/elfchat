<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller;

use ElfChat\Entity\Guest;
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
        $em = $this->app->entityManager();

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

                    return $this->app->redirect($this->app->url('chat'));
                }
            }

            $error = $this->app->trans('Bad credentials');
        }

        $guestForm = $this->app->form()
            ->add('guestname')
            ->getForm();

        $guestForm->handleRequest($this->request);

        if($guestForm->isValid()) {
            $data = $guestForm->getData();

            $guest = new Guest();
            $guest->setName($data['guestname']);

            $em->persist($guest);
            $em->flush($guest);

            $session->set('user', array($guest->getId(), $guest->getRole()));

            return $this->app->redirect($this->app->url('chat'));
        }

        $response = $this->render('users/login.twig', array(
            'error' => isset($error) ? $error : null,
            'form' => $form->createView(),
            'guestForm' => $guestForm->createView(),
        ));
        return $response;
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        $this->app->session()->remove('user');
        return $this->app->redirect($this->app->url('chat'));
    }
} 