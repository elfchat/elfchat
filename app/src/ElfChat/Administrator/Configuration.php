<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Administrator;

use ElfChat\Controller\Controller;
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
            ->add('locale', 'choice', array(
                'choices' => array(
                    'ru' => 'Russian',
                    'en' => 'English',
                )
            ))
            ->add('remember_me:token')
            ->add('')
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