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
    /**
     * Configure application
     */
    protected function configure()
    {
        $app = $this;

        // Configuration
        require $app->getRootDir() . '/config/config.php';

        // Plugins
        if (is_readable($pluginsFile = $app->getOpenDir() . '/plugins.php')) {
            require $pluginsFile;
        }
    }

    /**
     * Main run method with HTTP Cache.
     *
     * @param Request $request
     */
    public function run(Request $request = null)
    {
        if ($this['debug'] || !$this->isOpen()) {
            parent::run($request);
        } else {
            $this['http_cache']->run($request);
        }
    }

    /**
     * Is open directory writeable?
     * @return bool
     */
    public function isOpen()
    {
        return is_writable($this->getOpenDir());
    }

    /**
     * Is application installed correctly?
     * @return bool
     */
    public function isInstalled()
    {
        return $this->config()->get('installed', false);
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

    public function getPluginDir()
    {
        static $dir;
        if (empty($dir)) {
            $dir = dirname(dirname(__DIR__)) . '/plugin';
        }
        return $dir;
    }

    public function getThemeDir()
    {
        static $dir;
        if (empty($dir)) {
            $dir = dirname(dirname(__DIR__)) . '/theme';
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
     * @throws \ElfChat\Entity\User\NoUserException
     */
    public function user()
    {
        $user = $this['security.provider']->getUser();

        if (null !== $user) {
            return $user;
        }

        throw new Entity\User\NoUserException();
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
     * @return \Doctrine\Common\Cache\Cache
     */
    public function cache()
    {
        return $this['doctrine.common.cache'];
    }

    /**
     * @return \ElfChat\Server\ServerInterface
     */
    public function server()
    {
        return $this['server'];
    }

    /**
     * @param $role
     * @return bool
     */
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
        return $this->isGranted(ROLE_MODERATOR);
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        return $this->isGranted(ROLE_USER);
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->isGranted(ROLE_GUEST);
    }

    /**
     * @return bool
     */
    public function isAnonymous()
    {
        return $this->isGranted(ROLE_ANONYMOUS);
    }

    // TODO: Move next code to Silicone

    /**
     * Get cache directory.
     * @return string
     */
    public function getCacheDir()
    {
        static $dir;
        if (empty($dir)) {
            $dir = $this->getOpenDir() . '/cache/';

            if (!is_dir($dir) && is_writable(dirname($dir))) {
                mkdir($dir, 0755, true);
            }
        }
        return $dir;
    }

    /**
     * Get log directory.
     * @return string
     */
    public function getLogDir()
    {
        return $this->getOpenDir();
    }
}