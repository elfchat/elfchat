<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Admin;

use ElfChat\Controller;
use Silicone\Route;

/**
 * @Route("/admin/config")
 */
class Configuration extends Controller
{
    /**
     * @Route("", name="admin_config")
     */
    public function index()
    {
        $config = $this->app->config();

        $form = $this->app->form($config)
            ->add('debug', 'checkbox', array('label' => 'Debug', 'required' => false))
            ->add('locale', 'choice', array(
                'choices' => array(
                    'ru' => 'Russian',
                    'en' => 'English',
                ),
                'label' => 'Language',
            ))
            ->add('remember_me:token')
            ->add('mysql:host', 'text', array('label' => 'Host'))
            ->add('mysql:user', 'text', array('label' => 'Database user'))
            ->add('mysql:password', 'text', array('label' => 'Password', 'required' => false))
            ->add('mysql:dbname', 'text', array('label' => 'Database name'))
            ->add('domain')
            ->add('server')
            ->add('key')
            ->getForm();

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $config = $form->getData();
            $config->save();

            $this->app->session()->getFlashBag()->set('success', 'Configuration saved');
        }


        return $this->render('admin/config/form.twig', array(
            'form' => $form->createView(),
        ));
    }
}