<?php
/**
 * ElfChat Configuration
 *
 * @var $app \ElfChat\Application
 */

$app['version'] = ELFCHAT_VERSION . ' ' . ELFCHAT_EDITION;

// Configuration

$app['config.file'] = $app->getOpenDir() . '/config.php';
$config = new ElfChat\Config\Config(include $app->getRootDir() . '/config/default.php');
$config->load($app['config.file']);

$app['config'] = function () use ($config) {
    return $config;
};

$app['debug'] = $config->get('debug', false);
$app['locale'] = $config->get('locale', 'en');


// Router
$app->register(new Silicone\Provider\RouterServiceProvider());
$app['router.resource'] = array(
    $app->getRootDir() . '/controller/Chat/',
    $app->getRootDir() . '/controller/Admin/',
    $app->getRootDir() . '/controller/Moderator/',
);
if ($app->isOpen()) {
    $app['router.cache_dir'] = $app->getCacheDir();
}

// Assets
$app['assets.base_path'] = '/web/';

// Controllers
$app['resolver'] = $app->share(function () use ($app) {
    return new Silicone\Controller\ControllerResolver($app, $app['logger']);
});
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// Doctrine Common
$app->register(new Silicone\Provider\DoctrineCommonServiceProvider());
$app['doctrine.common.cache'] = $app->share(function () use ($app) {
    if (true === $app['debug'] || !$app->isOpen()) {
        return new \Doctrine\Common\Cache\ArrayCache();
    }

    switch ($app->config()->get('cache')) {

        case 'filesystem':
            return new \Doctrine\Common\Cache\FilesystemCache($app->getCacheDir() . '/doctrine');
            break;

        default:
            if (extension_loaded('apc')) {
                return new \Doctrine\Common\Cache\ApcCache();
            } else {
                return new \Doctrine\Common\Cache\FilesystemCache($app->getCacheDir() . '/doctrine');
            }
    }
});

// Doctrine ORM
$app->register(new Silicone\Provider\DoctrineOrmServiceProvider());
$app['doctrine.options'] = array(
    'debug' => $app['debug'],
    'proxy_namespace' => 'Proxy',
    'proxy_dir' => $app->isOpen() ? $app->getCacheDir() . '/proxy/' : sys_get_temp_dir(),
);

switch ($config->get('database', 'sqlite')) {
    case 'mysql':
        $app['doctrine.connection'] = $config->get('mysql');
        break;

    case 'sqlite':
        $app['doctrine.connection'] = $config->get('sqlite');
        break;

    case 'postgres':
        $app['doctrine.connection'] = $config->get('postgres');
        break;
}

$app['doctrine.paths'] = array(
    $app->getRootDir() . '/src/Entity',
);

// ElfChat Entities
ElfChat\Entity\Entity::setEntityManagerFactory(function () use ($app) {
    return $app['em'];
});


// Session
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.options' => array(
        'name' => 'ELFCHAT',
    )
));

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => $app->isOpen() ? array(
            'cache' => $app->getCacheDir() . '/twig/',
            'auto_reload' => true,
        ) : array(),
    'twig.path' => $app->getRootDir() . '/views/',
    'twig.form.templates' => array('form_layout.twig'),
));
$app->register(new Silicone\Provider\TwigServiceProviderExtension());
$app['twig'] = $app->share($app->extend('twig', function (\Twig_Environment $twig, $app) {
    $twig->addExtension(new ElfChat\Twig\ViewExtension());
    return $twig;
}));

// Translation
$app->register(new Silicone\Provider\TranslationServiceProvider());
$app['translator.resource'] = $app->getRootDir() . '/lang/';

// Validator
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silicone\Provider\ValidatorServiceProviderExtension());
$app['validator.unique'] = function () use ($app) {
    return new ElfChat\Validator\Constraints\UniqueValidator($app['em']);
};

// Form
$app->register(new Silex\Provider\FormServiceProvider());
$app['form.factory'] = $app->share(function () use ($app) {
    return \Symfony\Component\Form\Forms::createFormFactoryBuilder()
        ->addExtensions($app['form.extensions'])
        ->addTypeExtensions($app['form.type.extensions'])
        ->addTypeGuessers($app['form.type.guessers'])
        ->addTypes(array(
            $app['user_type'],
            $app['chosen_type'],
        ))
        ->getFormFactory();
});

/**
 * Uploads
 */

ElfChat\Entity\File::setUploadPath(realpath($this->getRootDir() . '/../upload'));
ElfChat\Entity\File::setBaseUrl($config->get('baseurl') . '/upload');

/**
 * Form types and transformers.
 */

$app['user_transformer'] = $app->share(function () use ($app) {
    return new ElfChat\Form\Transformer\UserTransformer($app['em']);
});

$app['user_type'] = function () use ($app) {
    return new ElfChat\Form\UserType($app['user_transformer']);
};

$app['chosen_type'] = function () use ($app) {
    return new ElfChat\Form\ChosenType();
};

/**
 * Validators
 */

$app['validator.unique'] = function () use ($app) {
    return new ElfChat\Validator\Constraints\UniqueValidator($app['em']);
};


/**
 * Security
 */

$app['security.role_hierarchy'] = array(
    'ROLE_GUEST' => array(),
    'ROLE_USER' => array('ROLE_GUEST'),
    'ROLE_MODERATOR' => array('ROLE_USER', 'ROLE_GUEST'),
    'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_MODERATOR', 'ROLE_GUEST'),
);

$app['security.access_rules'] = array(
    array('^/admin', 'ROLE_ADMIN'),
    array('^/install', 'ROLE_ADMIN'),
    array('^/moderator', 'ROLE_MODERATOR'),
    array('^/profile', 'ROLE_USER'),
    array('^/ajax', 'ROLE_GUEST'),

    // Next rule must be at the end of list,
    // otherwise access rules will not work.
    array('^/', 'ROLE_GUEST'),
);

$app['security.provider'] = $app->share(function () use ($app) {
    return new ElfChat\Security\Authentication\Provider(
        $app['security.role_hierarchy'],
        $app['security.access_rules']
    );
});

$app['security.middleware'] = $app->share(function () use ($app) {
    return new ElfChat\Security\Authentication\SecurityMiddleware(
        $app['security.provider'],
        $app['em'],
        $app['security.remember']
    );
});

$app['security.remember'] = $app->share(function () use ($app) {
    return new ElfChat\Security\Authentication\Remember($app->config()->get('remember_me.token'));
});

// Things to do not use then directory "open" does not writeable.
if ($app->isOpen()) {

    // Http Cache
    $app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
        'http_cache.cache_dir' => $app->getCacheDir() . '/http/',
    ));

    // Monolog
    $app->register(new Silex\Provider\MonologServiceProvider());
    $app['monolog.name'] = 'ELFCHAT';
    $app['monolog.logfile'] = $app->getLogDir() . '/error_log.txt';
    $app['monolog.level'] = function () {
        return \Monolog\Logger::NOTICE;
    };
}


// Things that block install script.
if ($app->isInstalled()) {

    /**
     * Middleware
     */

    $app->before(function ($request) use ($app) {
        return $app['security.middleware']->onRequest($request);
    });


    /**
     *  Session Handler
     */

    if ($config->get('server.type') === 'websocket') {
        $app['session.storage.handler'] = $app->share(function () use ($app) {
            return new \ElfChat\Server\WebSocketServer\Session\DbalSessionHandler(
                $app->entityManager()->getConnection(),
                array(
                    'db_table' => 'elfchat_session',
                    'db_id_col' => 'id',
                    'db_data_col' => 'data',
                    'db_time_col' => 'time',
                ));
        });
    }


    /**
     * Server
     */
    $app['server'] = $app->share(function () use ($app) {
        return $app->config()->get('server.type') === 'ajax' ?
            new ElfChat\Server\AjaxServer($app->user()) :
            new ElfChat\Server\WebSocketServerProxy(
                'http://' . $app->config()->get('server.host') . ':' . $app->config()->get('server.port'),
                $app->request()->headers->get('Cookie')
            );
    });

}


/**
 * Debug
 */

if ($app['debug']) {
    // Console
    $app['console'] = $app->protect(function (\Symfony\Component\Console\Application $console) use ($app) {
        $console->add(new Silicone\Doctrine\Console\DatabaseCreateCommand($app));
        $console->add(new Silicone\Doctrine\Console\DatabaseDropCommand($app));
        $console->add(new Silicone\Doctrine\Console\SchemaCreateCommand($app));
        $console->add(new Silicone\Doctrine\Console\SchemaDropCommand($app));
        $console->add(new Silicone\Doctrine\Console\SchemaUpdateCommand($app));
        $console->add(new Silicone\Console\CacheClearCommand($app));
    });

    if (php_sapi_name() !== 'cli') {
        // WebProfiler
        if ($app->isOpen()) {
            $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
                'profiler.cache_dir' => $app->getCacheDir() . '/profiler',
                'profiler.mount_prefix' => '/_profiler',
            ));
            $app->register(new Silicone\Provider\WebProfilerServiceProvider());
        }

        // Whoops
        $app->register(new Whoops\Provider\Silex\WhoopsServiceProvider);
    }

    /**
     * Monolog
     */
    $app['monolog.level'] = function () {
        return \Monolog\Logger::INFO;
    };
} else {
    /**
     * Errors
     */

    $app->error(function (\Exception $e, $code) use ($app) {
        if ($code === 404) {
            $response = $app->render('error/not_fount.twig');
        } else if ($e instanceof ElfChat\Security\Authentication\Exception\AccessDeniedException) {
            $response = $app->render('error/access_denied.twig');
        } else if ($e instanceof ElfChat\Security\Authentication\Exception\BannedException) {
            $response = $app->render('error/banned.twig', array(
                'ban' => $e->getBan(),
            ));
        } else {
            $response = $app->render('error/error.twig');
        }

        $response->setStatusCode($code);
        return $response;
    });
}