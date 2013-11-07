<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller;

use Buzz\Browser;
use Buzz\Client\Curl;
use ElfChat\Entity\Message;
use ElfChat\Repository\MessageRepository;
use ElfChat\Secure\Stringify;
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

        if (!$this->app->isGranted('ROLE_USER')) {
            return $this->render('chat/index.twig');
        } else {
            $user = $this->app->user();

            // Render chat

            // Chat secure data
            $config = $this->app->config();
            $auth = array(
                'user' => $user->export(),
            );

            // TODO: Remote this if then in production.
            if (false) {
                $auth['ip'] = $this->request->getClientIp();
            }


            $auth = $this->secure($auth);

            // Recent messages
            $recent = array();

            $repository = $this->app->repository()->messages();
            foreach ($repository->getLastMessages('main') as $message) {
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
                'save' => $auth,
                'recent' => $recent,
                'lang' => $lang,
            ));
        }
    }

    /**
     * @Route("/send", name="send", methods={"POST"})
     */
    public function send()
    {
        // Get necessary variables.
        $config = $this->app->config();
        $em = $this->app->entityManager();
        $user = $this->app->user();
        $text = $this->request->request->get('text', '');
        $room = $this->request->request->get('room', '');

        // Create message
        $message = new Message();
        $message->user = $user;
        $message->datetime = new \DateTime();
        $message->room = $room;
        $message->text = $text;

        // And save it any way
        $em->persist($message);
        $em->flush();

        // Encode message
        $content = $this->secure($message->export());
        $encode = base64_encode(json_encode($content));

        // Send to node.js server.
        $browser = new Browser(new Curl());
        $res = $browser->post($config->server . '/send', array(), array('encode' => $encode));
        $json = json_decode($res->getContent(), true);

        // If an error, delete message from database.
        if (!isset($json['error']) || $json['error'] !== false) {
            $em->remove($message);
            $em->flush();
        }

        $this->app->cache()->delete(MessageRepository::lastMessageCache);

        // Send response to client
        return $this->app->json($json);
    }

    /**
     * Secure data and add hash of data with key.
     * Add domain to data.
     *
     * @param array $data
     * @return array
     */
    private function secure(array $data)
    {
        $config = $this->app->config();
        $stringify = new Stringify();

        // Add domain.
        $data['_domain'] = $config->domain;

        // Generate hash and remove key.
        $data['_key'] = $config->key;
        $hash = sha1($stringify->stringify($data));
        unset($data['_key']);

        // Add hash.
        $data['_hash'] = $hash;

        return $data;
    }
}