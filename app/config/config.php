<?php
/** @var $app \ElfChat\Application */

$app['version'] = '6.0.0 BETA 1';

$app['config_file'] = $app->getOpenDir() . '/config.php';

$config = new \ElfChat\Config();
$reader = new \ElfChat\Config\Reader($config);
$reader->read($app['config_file']);

$app['config'] = function () use ($config) {
    return $config;
};

$app['debug'] = $config->debug;
$app['locale'] = $config->locale;

// Router
$app->register(new Silicone\Provider\RouterServiceProvider());
$app['router.resource'] = array(
    $app->getRootDir() . '/src/Chat/Controller/',
    $app->getRootDir() . '/src/Chat/Moderator/',
    $app->getRootDir() . '/src/Admin/Controller/',
);
$app['router.cache_dir'] = $app->getCacheDir();

// Assets
$app['assets.base_path'] = '/web/';

// Http Cache
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => $app->getCacheDir() . '/http/',
));

// Controllers
$app['resolver'] = $app->share(function () use ($app) {
    return new Silicone\Controller\ControllerResolver($app, $app['logger']);
});
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// Doctrine Common
$app->register(new Silicone\Provider\DoctrineCommonServiceProvider());

// Doctrine ORM
$app->register(new Silicone\Provider\DoctrineOrmServiceProvider());
$app['doctrine.options'] = array(
    'debug' => $app['debug'],
    'proxy_namespace' => 'Proxy',
    'proxy_dir' => $app->getCacheDir() . '/proxy/',
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

// Monolog
$app->register(new Silex\Provider\MonologServiceProvider());
$app['monolog.name'] = 'ELFCHAT';
$app['monolog.logfile'] = $app->getLogDir() . '/error_log.txt';
$app['monolog.level'] = function () {
    return \Monolog\Logger::NOTICE;
};

// Session
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.options' => array(
        'name' => 'ELFCHAT',
    )
));

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => $app->getCacheDir() . '/twig/',
        'auto_reload' => true,
    ),
    'twig.path' => $app->getRootDir() . '/views/',
));
$app->register(new Silicone\Provider\TwigServiceProviderExtension());
$app['twig'] = $app->share($app->extend('twig', function(\Twig_Environment $twig, $app) {
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
    return new Validator\UniqueValidator($app['em']);
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

$app['chat.upload_path'] = realpath($this->getRootDir() . '/../upload');
$app['chat.upload_url'] = '/upload';

ElfChat\Entity\File::setUploadPath($app['chat.upload_path']);
$app['dispatcher'] = $app->extend('dispatcher',
    function (Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) use ($app) {
        $dispatcher->addSubscriber(new ElfChat\EventListener\FileSubscriber($app['chat.upload_url']));
        return $dispatcher;
    });

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

$app['validator.unique'] = function () use($app) {
    return new ElfChat\Validator\Constraints\UniqueValidator($app['em']);
};

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

    // WebProfiler
    $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => $app->getCacheDir() . '/profiler',
        'profiler.mount_prefix' => '/_profiler',
    ));
    $app->register(new Silicone\Provider\WebProfilerServiceProvider());
}

/**
 *
 */
$app->get('/logout', function () {
    return 'You are JPEG!';
})->bind('logout');

