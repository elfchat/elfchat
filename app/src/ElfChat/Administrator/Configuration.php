<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Administrator;

use Admin\Form\ConfigType;
use ElfChat\Config\Writer;
use Silicone\Controller;
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
        $form = $this->app->formType(new ConfigType(), $config);

        if ('POST' == $this->request->getMethod()) {
            $form->bind($this->request);

            if ($form->isValid()) {
                $config = $form->getData();

                $writer = new Writer($config);
                $writer->write($this->app['config_file']);

                $this->app->getSession()->getFlashBag()->set('success', 'config_saved');
            }
        }

        return $this->render('admin/config/form.twig', array(
            'form' => $form->createView(),
        ));
    }
}