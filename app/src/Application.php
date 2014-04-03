<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat;

use Silicone;
use Symfony\Component\HttpFoundation\Request;

class Application extends Silicone\Application
{
    protected $repository;

    /**
     * Configure application
     */
    protected function configure()
    {
        $app = $this;
        require_once $app->getRootDir() . '/config/config.php';
    }

    /**
     * Main run method with HTTP Cache.
     *
     * @param Request $request
     */
    public function run(Request $request = null)
    {
        if ($this['debug']) {
            parent::run($request);
        } else {
            $this['http_cache']->run($request);
        }
    }

    /**
     * Get root directory.
     * @return string
     */
    public function getRootDir()
    {
        static $dir;
        if (empty($dir)) {
            $dir = dirname(__DIR__);
        }
        return $dir;
    }


    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function request()
    {
        return $this['request'];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function session()
    {
        return $this['session'];
    }

    /**
     * @return \ElfChat\Entity\User
     * @throws Exception\NoUserException
     */
    public function user()
    {
        $user = $this['security.provider']->getUser();

        if (null !== $user) {
            return $user;
        }

        throw new Exception\NoUserException();
    }

    /**
     * @return \ElfChat\Config\Config
     */
    public function config()
    {
        return $this['config'];
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function entityManager()
    {
        return $this['em'];
    }

    /**
     * @return \ElfChat\Repository\Manager
     */
    public function repository()
    {
        if (null === $this->repository) {
            $this->repository = new \ElfChat\Repository\Manager($this->entityManager());
        }

        return $this->repository;
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    public function cache()
    {
        return $this['doctrine.common.cache'];
    }

    public function isGranted($role)
    {
        return $this['security.provider']->isGranted($role);
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this['security.provider']->isAuthenticated();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    /**
     * @return bool
     */
    public function isModerator()
    {
        return $this->isGranted('ROLE_MODERATOR');
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        return $this->isGranted('ROLE_USER');
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->isGranted('ROLE_GUEST');
    }
}