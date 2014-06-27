<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Admin;

use ElfChat\Controller;
use ElfChat\Plugin\Plugin;
use ElfChat\Plugin\PluginManager;
use Silicone\Application;
use Silicone\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/plugins")
 */
class Plugins extends Controller
{
    /**
     * @var PluginManager
     */
    private $plugins;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->plugins = $app['plugin_manager'];
    }

    /**
     * @Route("", name="admin_plugins")
     */
    public function index()
    {
        return $this->render('admin/plugin/list.twig', array(
            'plugins' => $this->plugins->getPlugins(),
        ));
    }

    /**
     * @Route("/install", name="admin_plugin_install")
     */
    public function install(Request $request)
    {
        $this->plugins->forPlugin($request->get('name'), true);

        return $this->app->redirect($this->app->url('admin_plugins'));
    }

    /**
     * @Route("/uninstall", name="admin_plugin_uninstall")
     */
    public function uninstall(Request $request)
    {
        $this->plugins->forPlugin($request->get('name'), false);

        return $this->app->redirect($this->app->url('admin_plugins'));
    }

    /**
     * @Route("/update", name="admin_plugin_update")
     */
    public function update(Request $request)
    {
        $this->plugins->install();

        return $this->app->redirect($request->get('next', $this->app->url('admin_plugins')));
    }
} 