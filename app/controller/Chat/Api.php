<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Chat;

use ElfChat\Controller;
use ElfChat\Entity\User\RemoteUser;
use Silicone\Route;

/**
 * @Route("/api")
 */
class Api extends Controller
{

    /**
     * @Route("/test")
     */
    public function test()
    {
        $user = array(
            'from' => 'test',
            'id' => 1,
            'name' => 'Remote Test',
        );

        return $this->app->redirect($this->app->url('api_login', array(
            'data' => $data = base64_encode(json_encode($user)),
            'hash' => sha1(sha1($data) . sha1($this->app->config()->get('integration_key'))),
        )));
    }

    /**
     * @Route("/login/{hash}/{data}", name="api_login")
     */
    public function login($hash, $data)
    {
        $em = $this->app->entityManager();
        $session = $this->app->session();

        if ($this->validate($data, $hash)) {
            $data = $this->decode($data);

            $user = RemoteUser::findRemote($data['from'], $data['id']);

            if (null === $user) {
                $user = new RemoteUser();

                $user->remoteSource = $data['from'];
                $user->remoteId = $data['id'];
                $user->name = $data['name'];

                $em->persist($user);
                $em->flush($user);
            }

            $session->set('user', array($user->id, $user->role));

            return $this->app->redirect($this->app->url('chat'));
        } else {
            throw new \RuntimeException('Can not validate user login data.');
        }
    }

    private function decode($data)
    {
        return json_decode(base64_decode($data), true);
    }

    private function validate($data, $hash)
    {
        return sha1(sha1($data) . sha1($this->app->config()->get('integration_key'))) === $hash;
    }
} 