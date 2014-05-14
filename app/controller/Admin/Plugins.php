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
     * @return Plugin[]
     */
    private function getPlugins()
    {
        $installed = isset($this->app['plugins']) ? $this->app['plugins'] : array();

        $finder = new Finder();
        $finder->files()
            ->depth(1)
            ->name('plugin.json')
            ->in($this->app->getPluginDir());

        $plugins = array();
        foreach ($finder as $file) {
            $plugin = new Plugin($file);
            $plugin->installed = isset($installed[$plugin->name]);
            $plugins[$plugin->name] = $plugin;
        }

        return $plugins;
    }

    /**
     * @Route("", name="admin_plugins")
     */
    public function index()
    {

        return $this->render('admin/plugin/list.twig', array(
            'plugins' => $this->getPlugins(),
        ));
    }

    /**
     * @Route("/install/{name}", name="admin_plugin_install")
     */
    public function install($name)
    {
        $plugins = $this->getPlugins();

        if (isset($plugins[$name])) {
            $plugins[$name]->installed = true;
        }

        $this->installPlugins($plugins);

        return $this->app->redirect($this->app->url('admin_plugins'));
    }

    /**
     * @Route("/uninstall/{name}", name="admin_plugin_uninstall")
     */
    public function uninstall($name)
    {
        $plugins = $this->getPlugins();

        if (isset($plugins[$name])) {
            $plugins[$name]->installed = false;
        }

        $this->installPlugins($plugins);

        return $this->app->redirect($this->app->url('admin_plugins'));
    }

    private function installPlugins($plugins)
    {
        $installer = new Installer($this->app->getOpenDir() . '/plugins.php');
        $installer->install(array_filter($plugins, function (Plugin $plugin) {
            return $plugin->installed;
        }));
    }
} 