<?php
/**
 * Example Plugin Controller
 *
 * @var $app \ElfChat\Application
 * @var $plugin \Silex\ControllerCollection
 */

$plugin->get('', function () use ($app) {
    return $app->render('plugin:example:hello.twig');
});