<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

use Symfony\Component\Finder\Finder;

class PluginManager
{
    private $plugins;

    private $pluginDir;

    private $pluginConfigFile;

    private $pluginViewDir;

    private $installed;

    public function __construct($pluginDir, $pluginConfigFile, $pluginViewDir, array $installed)
    {
        $this->pluginDir = $pluginDir;
        $this->pluginConfigFile = $pluginConfigFile;
        $this->pluginViewDir = $pluginViewDir;
        $this->installed = $installed;
    }

    public function getPlugins()
    {
        if (null === $this->plugins) {
            $finder = new Finder();
            $finder->files()
                ->depth(1)
                ->name('plugin.json')
                ->sort(function ($a, $b) {
                    return ($a->getMTime() < $b->getMTime());
                })
                ->in($this->pluginDir);

            $this->plugins = array();
            foreach ($finder as $file) {
                $plugin = new Plugin($file);
                $plugin->installed = isset($this->installed[$plugin->name]);
                $this->plugins[$plugin->name] = $plugin;
            }
        }

        return $this->plugins;
    }

    public function install()
    {
        $installer = new Installer($this->pluginConfigFile, $this->pluginViewDir);
        $installer->install(array_filter($this->getPlugins(), function (Plugin $plugin) {
            return $plugin->installed;
        }));
    }

    public function forPlugin($name, $install = true)
    {
        $this->getPlugins();
        if (isset($this->plugins[$name])) {
            $this->plugins[$name]->installed = $install;
        }
        $this->install();
    }
} 