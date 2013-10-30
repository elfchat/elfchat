<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @var \Chat\Application $app */

\Chat\Entity\File::setUploadPath($app['chat.upload_path']);

$app['dispatcher'] = $app->extend('dispatcher',
    function (\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) use ($app) {
        $dispatcher->addSubscriber(new \Chat\EventListener\FileSubscriber($app['chat.upload_url']));
        return $dispatcher;
    });

// Override form factory.
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
 * Monolog
 */

$app['monolog.name'] = 'ELFCHAT';
$app['monolog.logfile'] = $app->getLogDir() . '/error_log.txt';
$app['monolog.level'] = function () {
    return \Monolog\Logger::NOTICE;
};

//$app['doctrine.common.cache'] = $app->share(function () use ($app) {
//    return new \Doctrine\Common\Cache\FilesystemCache($app->getCacheDir() . '/common');
//});