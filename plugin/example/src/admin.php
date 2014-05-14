<?php
/**
 * Example Plugin Controller
 *
 * @var $app \ElfChat\Application
 * @var $plugin \Silex\ControllerCollection
 */

$plugin->match('/plugins/example', function () use ($app) {
    $form = $app->form($app->config())
        ->add('example:setting', 'text', array('label' => 'Some settings'))
        ->getForm();

    $form->handleRequest($app->request());
    if ($form->isValid()) {
        $config = $form->getData();
        $config->save();

        $app->session()->getFlashBag()->set('success', 'Configuration saved');
    }

    return $app->render('@example/config.twig', array(
        'form' => $form->createView(),
    ));
})->bind('plugin_example_config');