<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat;

use Chat\EventListener\PasswordEncoderSubscriber;
use Chat\Exception\NoUserException;
use Chat\Repository\Manager;
use Silicone;
use Chat\Repository\UserRepository;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Chat\Config\Reader;
use Chat\Entity\File;
use Chat\Entity\User;
use Chat\EventListener\FileSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;

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

        $app['version'] = '6.0.0 BETA1';

        $app['config_file'] = $this->getOpenDir() . '/config.php';

        $config = new Config();
        $reader = new Reader($config);
        $reader->read($app['config_file']);

        $app['config'] = function () use ($config) {
            return $config;
        };

        $app['debug'] = $config->debug;
        $app['locale'] = $config->locale;

        $app['router.resource'] = array(
            $app->getRootDir() . '/src/Chat/Controller/',
            $app->getRootDir() . '/src/Chat/Moderator/',
            $app->getRootDir() . '/src/Admin/Controller/',
        );

        switch ($config->database) {
            case 'mysql':
                $app['doctrine.connection'] = $config->mysql;
                break;

            case 'sqlite':
                $app['doctrine.connection'] = $config->sqlite;
                break;

            case 'postgres':
                $app['doctrine.connection'] = $config->postgres;
                break;
        }

        $app['doctrine.paths'] = array(
            $app->getRootDir() . '/src/Chat/Entity',
        );


        $app['session.storage.options'] = array(
            'name' => 'ELFCHAT',
        );

        $app['twig.options'] = array(
            'cache' => $app->getCacheDir() . '/twig/',
            'auto_reload' => true,
        );
        $app['twig.path'] = array(
            $app->getRootDir() . '/views/',
        );

        $app['security.user_class'] = 'Chat\Entity\User';
        $app['security.firewalls'] = array(
            'default' => array(
                'pattern' => '^/',
                'anonymous' => true,
                'form' => array(
                    'login_path' => '/login',
                    'check_path' => '/login_check'
                ),
                'logout' => array(
                    'logout_path' => '/logout'
                ),
                'users' => $app->raw('security.users'),
                'remember_me' => array(
                    'key' => 'remember_me',
                    'lifetime' => 31536000, # 365 days in seconds
                    'path' => '/',
                    'name' => 'ELFCHAT_REMEMBER_ME',
                ),
            ),
        );
        $app['security.role_hierarchy'] = array(
            'ROLE_GUEST' => array(),
            'ROLE_USER' => array('ROLE_GUEST'),
            'ROLE_MODERATOR' => array('ROLE_USER', 'ROLE_GUEST'),
            'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_MODERATOR'),
        );
        $app['security.access_rules'] = array(
            array('^/admin', 'ROLE_ADMIN'),
            array('^/moderator', 'ROLE_MODERATOR'),
            array('^/profile', 'ROLE_USER'),

            // Next rule must be at the end of list,
            // otherwise access rules will not work.
            array('^/', 'IS_AUTHENTICATED_ANONYMOUSLY'),
        );

        $app['chat.upload_path'] = realpath($this->getRootDir() . '/../upload');
        $app['chat.upload_url'] = '/upload';

        $app['password.encoder'] = $app->share(function () use ($app) {
            return new PasswordEncoderSubscriber($app['security.encoder.digest']);
        });

        // Form types and transformers.

        $app['user_transformer'] = $app->share(function () use ($app) {
            return new \Chat\Form\Transformer\UserTransformer($app['em']);
        });

        $app['user_type'] = function () use ($app) {
            return new \Chat\Form\UserType($app['user_transformer']);
        };

        $app['chosen_type'] = function () use ($app) {
            return new \Chat\Form\ChosenType();
        };
    }

    protected function registerProviders()
    {
        $app = $this;
        parent::registerProviders();

        File::setUploadPath($app['chat.upload_path']);

        $app['dispatcher'] = $app->extend('dispatcher',
            function (EventDispatcherInterface $dispatcher) use ($app) {
                $dispatcher->addSubscriber(new FileSubscriber($app['chat.upload_url']));
                return $dispatcher;
            });

        // Override form factory.
        $app['form.factory'] = $app->share(function () use ($app) {
            return Forms::createFormFactoryBuilder()
                ->addExtensions($app['form.extensions'])
                ->addTypeExtensions($app['form.type.extensions'])
                ->addTypeGuessers($app['form.type.guessers'])
                ->addTypes(array(
                    $app['user_type'],
                    $app['chosen_type'],
                ))
                ->getFormFactory();
        });

        $this->repository = new Manager($this->entityManager());

        // Monolog
        $app['monolog.name'] = 'ELFCHAT';
        $app['monolog.logfile'] = $app->getLogDir() . '/error_log.txt';
        $app['monolog.level'] = function () {
            return \Monolog\Logger::NOTICE;
        };
    }


    /**
     * @return Request
     */
    public function request()
    {
        return $this['request'];
    }

    /**
     * @return User
     * @throws Exception\NoUserException
     */
    public function user()
    {
        $user = parent::user();

        if (null !== $user) {
            return $user;
        }

        throw new NoUserException();
    }

    /**
     * @return Config
     */
    public function config()
    {
        return $this['config'];
    }

    /**
     * @return EntityManager
     */
    public function entityManager()
    {
        return $this['em'];
    }

    /**
     * @return Manager
     */
    public function repository()
    {
        return $this->repository;
    }

    /**
     * @return Cache
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