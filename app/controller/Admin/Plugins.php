<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Admin;

use ElfChat\Controller;
use ElfChat\Plugin\Installer;
use ElfChat\Plugin\Plugin;
use Silicone\Route;
use Symfony\Component\Finder\Finder;

/**
 * @Route("/admin/plugins")
 */
class Plugins extends Controller
{
    /**
     * @Route("", name="admin_plugins")
     */
    public function index()
    {
        $finder = new Finder();
        $finder->files()
            ->depth(1)
            ->name('plugin.json')
            ->in($this->app->getPluginDir());

        $plugins = array();
        foreach ($finder as $file) {
            $plugins[] = new Plugin($file);
        }

        $installer = new Installer($this->app->getOpenDir() . '/plugins.php');
        $installer->install($plugins);

        return $this->render('admin/plugin/list.twig', array(
            'plugins' => $plugins,
        ));
    }
} 