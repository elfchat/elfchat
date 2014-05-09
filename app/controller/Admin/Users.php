<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Admin;

use ElfChat\Entity\User;
use ElfChat\Form\UserEditFormType;
use ElfChat\Controller;
use Silicone\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin/users")
 */
class Users extends Controller
{
    /**
     * @Route("", name="admin_users")
     */
    public function index()
    {
        $users = User::findAllUsers();
        return $this->render('admin/users/list.twig', array(
            'users' => $users,
        ));
    }

    /**
     * @Route("/edit/{id}", name="admin_users_edit")
     */
    public function edit($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundHttpException($this->app->trans('User not found'));
        }

        $form = $this->app->formType(new UserEditFormType(), $user);

        $form->handleRequest($this->request);
        if ($form->isValid()) {
            /** @var $user \ElfChat\Entity\User */
            $user = $form->getData();

            $this->app->entityManager()->persist($user);
            $this->app->entityManager()->flush();

            $this->app->session()->getFlashBag()->add(
                'success',
                $this->app->trans('User "%name%" edited successful.', array('%name%' => $user->name))
            );
        }

        return $this->render('admin/users/edit.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="admin_users_delete")
     */
    public function remove($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundHttpException($this->app->trans('User not found'));
        }

        $form = $this->app->form()
            ->add('delete', 'submit')
            ->getForm();

        $form->handleRequest($this->request);

        if ($form->isValid()) {

            if ($form->get('delete')->isClicked()) {
                $this->app->entityManager()->remove($user);
                $this->app->entityManager()->flush();

                $message = $this->app->trans('User "%name%" was deleted.', array('%name%' => $user->name));
                $this->app->session()->getFlashBag()->add('success', $message);

                return $this->app->redirect($this->app->url('admin_users'));
            }
        }


        return $this->render('admin/users/delete.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }
}