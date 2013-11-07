<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller;

use ElfChat\Entity\Avatar;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Silicone\Route;

/**
 * @Route("/profile")
 */
class Profile extends Controller
{
    /**
     * @Route("/avatar", name="profile_avatar")
     */
    public function avatar()
    {
        $em = $this->app->entityManager();
        $user = $this->app->user();

        $form = $this->app->form(new Avatar(), array('validation_groups' => array('avatar')))
            ->add('file')
            ->getForm();

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $avatar = $form->getData();
            $user->setAvatar($avatar);
            $em->persist($avatar);
            $em->flush();

            return $this->render('profile/avatar/crop.twig');
        }


        return $this->render('profile/avatar.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/crop", methods="POST", name="profile_avatar_crop")
     */
    public function crop()
    {
        $avatar = $this->app->user()->getAvatar();

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
     * @Route("/remove")
     */
    public function remove()
    {
        $em = $this->app->entityManager();
        $em->remove($this->app->user()->getAvatar());
        $this->app->user()->setAvatar(null);
        $em->flush();
        return 'removed!';
    }
}