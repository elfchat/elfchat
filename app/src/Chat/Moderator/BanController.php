<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Moderator;

use Chat\Controller\Controller;
use Chat\Entity\Ban;
use Chat\Entity\User;
use Chat\Form\BanType;
use Silicone\Route;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @Route("/moderator/ban")
 */
class BanController extends Controller
{
    /**
     * @Route("", name="moderator_bans")
     */
    public function bans()
    {
        $bans = $this->app->repository()->bans()->findAll();
        return $this->render('moderator/ban/index.twig', array(
            'bans' => $bans,
            'howLongChoices' => BanType::howLongChoices(),
        ));
    }

    /**
     * @Route("/add", name="moderator_add_ban")
     */
    public function addBan()
    {
        $ban = new Ban();

        $user = null;
        if ($id = $this->request->get('id')) {
            if ($user = $this->app->repository()->users()->find($id)) {
                $ban->setUser($user);
            }
        }

        $form = $this->app->formType(new BanType(), $ban);

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $em = $this->app->entityManager();
            $ban = $form->getData();
            $ban->setCreated(new \DateTime());
            $ban->setAuthor($this->app->user());
            $em->persist($ban);
            $em->flush();

            return $this->app->redirect($this->app->url('moderator_bans'));
        }

        return $this->render('moderator/ban/add.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    /**
     * @Route("/remove/{id}", name="moderator_remove_ban", requirements={"id": "\d+"})
     */
    public function removeBan($id)
    {
        if($ban = $this->app->repository()->bans()->find($id)) {
            $this->app->entityManager()->remove($ban);
            $this->app->entityManager()->flush();
            $this->app->session()->getFlashBag()->add('success', $this->app->trans('Ban was deleted.'));
        }

        return $this->app->redirect($this->app->url('moderator_bans'));
    }
}