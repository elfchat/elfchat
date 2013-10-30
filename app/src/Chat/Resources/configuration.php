<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @var \Chat\Application $app */

$app['version'] = '6.0.0 BETA1';

$app['config_file'] = $app->getOpenDir() . '/config.php';

$config = new \Chat\Config();
$reader = new \Chat\Config\Reader($config);
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
    return new \Chat\EventListener\PasswordEncoderSubscriber($app['security.encoder.digest']);
});


/**
 * Form types and transformers.
 */

$app['user_transformer'] = $app->share(function () use ($app) {
    return new \Chat\Form\Transformer\UserTransformer($app['em']);
});

$app['user_type'] = function () use ($app) {
    return new \Chat\Form\UserType($app['user_transformer']);
};

$app['chosen_type'] = function () use ($app) {
    return new \Chat\Form\ChosenType();
};

/**
 * Validators
 */

$app['validator.unique'] = function () use($app) {
    return new \Chat\Validator\Constraints\UniqueValidator($app['em']);
};

 