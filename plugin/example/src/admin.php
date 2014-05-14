<?php
/**
 * Example Plugin Controller
 *
 * @var $app \ElfChat\Application
 * @var $plugin \Silex\ControllerCollection
 */

$plugin->match('/plugins/example', function () use ($app) {
    $form = $app->form()
        ->add('setting', 'text', array('label' => 'Some settings'))
        ->getForm();

    $form->handleRequest($app->request());
    if ($form->isValid()) {
        $app->session()->getFlashBag()->set('success', $app->trans('Configuration saved'));
    }

    return $app->render('@example/config.twig', array(
        'form' => $form->createView(),
    ));
})->bind('plugin_example_config');