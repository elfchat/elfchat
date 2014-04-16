<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Chat;

use ElfChat\Controller;
use ElfChat\Entity\Message;
use ElfChat\Repository\MessageRepository;
use Silicone\Route;
use Silicone\Translator\Translator;

class Chat extends Controller
{
    /**
     * @Route("/", name="chat")
     */
    public function index()
    {
        $em = $this->app->entityManager();

        if (!$this->app->isAuthenticated()) {
            return $this->render('chat/index.twig');
        } else {
            $user = $this->app->user();

            // Recent messages
            $recent = array();

            $repository = $this->app->repository()->messages();
            foreach ($repository->getLastMessages($user->id) as $message) {
                $recent[] = $message->exportWithUser();
            }

            // Reverse array of messages
            $recent = array_reverse($recent);

            // Language
            $lang = array();
            $translator = $this->app['translator'];
            if ($translator instanceof Translator) {
                $lang = $translator->getCatalogue()->all('browser');
            }

            return $this->render('chat/chat.twig', array(
                'user' => $user->export(),
                'recent' => $recent,
                'lang' => $lang,
            ));
        }
    }
}