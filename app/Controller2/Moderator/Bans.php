<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Moderator;

use Buzz\Browser;
use Buzz\Client\Curl;
use ElfChat\Controller;
use ElfChat\Entity\Ban;
use ElfChat\Entity\User;
use ElfChat\Form\BanType;
use Silicone\Route;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @Route("/moderator/ban")
 */
class Bans extends Controller
{
    /**
     * @Route("", name="moderator_bans")
     */
    public function bans()
    {
        $bans = $this->app->repository()->bans()->findAll();
        return $this->render('moderator/ban/index.twig', array(
            'bans' => $bans,
            'howLongChoices' => Ban::howLongChoices(),
        ));
    }

    /**
     * @Route("/add", name="add_ban")
     */
    public function addBan()
    {
        $ban = new Ban();

        $user = null;
        if ($id = $this->request->get('id')) {
            if ($user = $this->app->repository()->users()->find($id)) {
                $ban->user = $user;
            }
        }

        $form = $this->app->formType(new BanType(), $ban);

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $em = $this->app->entityManager();
            $ban = $form->getData();

            $ban->created = time();
            $ban->author = $this->app->user();

            $em->persist($ban);
            $em->flush();

            if (null !== $ban->user) {
                $this->app->server()->kill($ban->user->id);

                $log = $this->app->trans('User %user% was banned for %time%.', array(
                    '%user%' => $ban->user->name,
                    '%time%' => $this->app->trans($ban->getHowLongString()),
                ));

                if ($ban->reason != '') {
                    $log .= ' ' . $this->app->trans('Reason: %reason%', array(
                            '%reason%' => $ban->reason,
                        ));
                }

                $this->app->server()->log($log, 'danger');
            }

            return $this->app->redirect($this->app->url('moderator_bans'));
        }

        return $this->render('moderator/ban/add.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    /**
     * @Route("/remove/{id}", name="remove_ban", requirements={"id": "\d+"})
     */
    public function removeBan($id)
    {
        if ($ban = $this->app->repository()->bans()->find($id)) {
            $this->app->entityManager()->remove($ban);
            $this->app->entityManager()->flush();
            $this->app->session()->getFlashBag()->add('success', $this->app->trans('Ban was deleted.'));
        }

        return $this->app->redirect($this->app->url('moderator_bans'));
    }

    /**
     * @Route("/users", name="query_users")
     */
    public function users()
    {
        $users = $this->app->repository()->users()->queryNames($this->request->get('query'));
        $users = array_map(function (User $user) {
            return $user->export();
        }, $users);
        return $this->app->json($users);
    }
}