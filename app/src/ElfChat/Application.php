<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat;

use Silicone;

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
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function request()
    {
        return $this['request'];
    }

    /**
     * @return \ElfChat\Entity\User
     * @throws Exception\NoUserException
     */
    public function user()
    {
        $user = $this->repository()->users()->find(1);

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

    public function isGranted()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        // TODO: auth
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        // TODO: auth
    }

    /**
     * @return bool
     */
    public function isModerator()
    {
        // TODO: auth
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        // TODO: auth
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        // TODO: auth
    }
}