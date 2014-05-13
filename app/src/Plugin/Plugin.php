<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

use Symfony\Component\Finder\SplFileInfo;

class Plugin
{
    const CONFIG_NAME = 'plugin.json';

    private $configFile;

    public $autoload = array();

    public $controllers = array();

    public $views = array();

    public function __construct(SplFileInfo $pluginFile)
    {
        $this->configFile = $pluginFile;
        $this->load();
    }

    private function load()
    {
        if (!$this->configFile->isReadable()) {
            throw new \RuntimeException("Plugin config \"" . $this->configFile->getPathname() . "\" does not readable.");
        }

        $json = json_decode($this->configFile->getContents(), true);

        if (isset($json['autoload'])) {
            foreach ($json['autoload'] as $namespace => $path) {
                $this->autoload[$namespace] = $this->getPluginDir() . '/' . $path;
            }
        }

        if (isset($json['controllers'])) {
            foreach ($json['controllers'] as $mount => $path) {
                $this->controllers[$mount] = $this->getPluginDir() . '/' . $path;
            }
        }

        if (isset($json['views'])) {
            foreach ($json['views'] as $namespace => $path) {
                $this->views[$namespace] = $this->getPluginDir() . '/' . $path;
            }
        }
    }

    public function getPluginFile()
    {
        return $this->configFile->getPathname();
    }

    public function getPluginDir()
    {
        return $this->configFile->getPath();
    }
}