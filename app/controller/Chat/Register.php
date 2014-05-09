<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Chat;

use ElfChat\Controller;
use Silicone\Route;
use ElfChat\Entity\User;
use ElfChat\Form\RegistrationFormType;

/**
 * @Route("/register")
 */
class Register extends Controller
{
    /**
     * @Route("", name="register")
     */
    public function index()
    {
        $em = $this->app->entityManager();

        $form = $this->app->formType(new RegistrationFormType());

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $user->save();

            $this->app->session()->set('user', array($user->id, $user->role));
            return $this->app->redirect($this->app->url('chat'));
        }

        $response = $this->render('users/register.twig', array(
            'form' => $form->createView(),
        ));

        return $response;
    }

    /**
     * @Route("/success", name="register_success")
     */
    public function success()
    {
        return $this->render('users/register/success.twig');
    }
}
