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
use Symfony\Component\HttpFoundation\Request;

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
            ->sort(function ($a, $b) {
                return ($a->getMTime() < $b->getMTime());
            })
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
     * @Route("/install", name="admin_plugin_install")
     */
    public function install(Request $request)
    {
        $plugins = $this->getPlugins();

        $name = $request->get('name');
        if (isset($plugins[$name])) {
            $plugins[$name]->installed = true;
        }

        $this->installPlugins($plugins);

        return $this->app->redirect($this->app->url('admin_plugins'));
    }

    /**
     * @Route("/uninstall", name="admin_plugin_uninstall")
     */
    public function uninstall(Request $request)
    {
        $plugins = $this->getPlugins();

        $name = $request->get('name');
        if (isset($plugins[$name])) {
            $plugins[$name]->installed = false;
        }

        $this->installPlugins($plugins);

        return $this->app->redirect($this->app->url('admin_plugins'));
    }

    /**
     * @Route("/update", name="admin_plugin_update")
     */
    public function update(Request $request)
    {
        $this->installPlugins($this->getPlugins());

        return $this->app->redirect($request->get('next', $this->app->url('admin_plugins')));
    }

    private function installPlugins($plugins)
    {
        $installer = new Installer($this->app->getOpenDir() . '/plugins.php', $this->app['plugin_view_path']);
        $installer->install(array_filter($plugins, function (Plugin $plugin) {
            return $plugin->installed;
        }));
    }
} 