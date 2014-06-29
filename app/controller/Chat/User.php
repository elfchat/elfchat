<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Chat;

use ElfChat\Controller;
use ElfChat\Entity\Avatar;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Silicone\Route;

/**
 * @Route("/user")
 */
class User extends Controller
{
    /**
     * @Route("/avatar", name="user_avatar")
     */
    public function avatar()
    {
        $em = $this->app->entityManager();
        $user = $this->app->user();

        $form = $this->app->form(new Avatar(), array('validation_groups' => array('avatar')))
            ->add('file', 'file')
            ->getForm();

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $avatar = $form->getData();
            $user->avatar = $avatar;
            $em->persist($avatar);
            $em->flush();

            return $this->render('user/avatar/crop.twig');
        }


        return $this->render('user/avatar/change.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/avatar/crop", methods="POST", name="user_avatar_crop")
     */
    public function crop()
    {
        $avatar = $this->app->user()->avatar;

        if (null !== $avatar) {
            $imagine = new Imagine();

            $image = $imagine->open($avatar->getAbsolutePath());
            $start = new Point(
                (int)$this->request->request->get('x', 0),
                (int)$this->request->request->get('y', 0)
            );
            $size = new Box(
                (int)$this->request->request->get('w', 50),
                (int)$this->request->request->get('h', 50)
            );
            $image->crop($start, $size);
            $image->save($avatar->getAbsolutePath());
        }

        return $this->app->json(true);
    }

    /**
     * @Route("/update", methods="get", name="user_update")
     */
    public function update()
    {
        $this->app->server()->updateUser();

        return $this->app->redirect($this->app->url('user_avatar'));
    }

    /**
     * @Route("/remove")
     */
    public function remove()
    {
        $em = $this->app->entityManager();
        $em->remove($this->app->user()->avatar);
        $this->app->user()->avatar = null;
        $em->flush();
        return 'removed!';
    }
}