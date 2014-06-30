<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

class Plugin extends AbstractPlugin
{
    const CONFIG_NAME = 'plugin.json';

    public $installed = false;

    public $name;

    public $title;

    public $description;

    public $author = array('name' => 'NoName', 'email' => 'no_email');

    public $autoload = array();

    public $file;

    public $controllers = array();

    public $views = array();

    public $configurationRoute;

    public $hooks = array();

    public function parse(array $json)
    {
        $this->name = isset($json['name']) ? $json['name'] : 'vendor/name';
        $this->title = isset($json['title']) ? $json['title'] : 'No title';
        $this->description = isset($json['description']) ? $json['description'] : '';
        $this->configurationRoute = isset($json['configuration_route']) ? $json['configuration_route'] : '';

        if (isset($json['author'])) {
            $this->author = array(
                'name' => isset($json['author']['name']) ? $json['author']['name'] : 'NoName',
                'email' => isset($json['author']['email']) ? $json['author']['email'] : 'no_email',
            );
        }

        if (isset($json['require'])) {
            foreach ($json['require'] as $what => $version) {
                $this->require[$what] = $version;
            }
        }

        if (isset($json['autoload'])) {
            foreach ($json['autoload'] as $namespace => $path) {
                $this->autoload[$namespace] = $this->getDir() . '/' . $path;
            }
        }

        $this->file = isset($json['file']) ? $this->getDir() . '/' . $json['file'] : null;

        if (isset($json['controllers'])) {
            foreach ($json['controllers'] as $mount => $path) {
                $this->controllers[$mount] = $this->getDir() . '/' . $path;
            }
        }

        if (isset($json['views'])) {
            foreach ($json['views'] as $namespace => $path) {
                $this->views[$namespace] = $this->getDir() . '/' . $path;
            }
        }

        if (isset($json['hooks'])) {
            $this->hooks = $json['hooks'];
        }
    }

    public function hasConfigurationRoute()
    {
        return is_string($this->configurationRoute) && strlen($this->configurationRoute) > 0;
    }

}