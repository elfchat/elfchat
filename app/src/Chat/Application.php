<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat;

use Silicone;

class Application extends Silicone\Application
{
    protected $repository;

    /**
     * Get root directory.
     * @return string
     */
    public function getRootDir()
    {
        static $dir;
        if (empty($dir)) {
            $dir = dirname(dirname(__DIR__));
        }
        return $dir;
    }

    /**
     * Configure application
     */
    public function configure()
    {
        $app = $this;
        require_once __DIR__ . '/Resources/configuration.php';
    }

    /**
     * Register providers
     */
    protected function registerProviders()
    {
        parent::registerProviders();

        $app = $this;
        require_once __DIR__ . '/Resources/providers.php';
    }


    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function request()
    {
        return $this['request'];
    }

    /**
     * @return \Chat\Entity\User
     * @throws Exception\NoUserException
     */
    public function user()
    {
        $user = parent::user();

        if (null !== $user) {
            return $user;
        }

        throw new Exception\NoUserException();
    }

    /**
     * @return Config
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
     * @return \Chat\Repository\Manager
     */
    public function repository()
    {
        if (null === $this->repository) {
            $this->repository = new \Chat\Repository\Manager($this->entityManager());
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

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->isGranted('IS_AUTHENTICATED_FULLY');
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