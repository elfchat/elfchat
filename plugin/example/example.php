<?php
/**
 * Example Plugin Controller
 *
 * @var $app \ElfChat\Application
 * @var $plugin \Silex\ControllerCollection
 */
$plugin = $app['controllers_factory'];

$plugin->get('', function () use ($app) {
    $my = new \Example\MyOwnClass();

    return $app->render('@example/hello.twig', array(
        'my' => $my,
    ));
});

return $plugin;