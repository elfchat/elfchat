<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Theme;

use ElfChat\Plugin\AbstractPlugin;

class Theme extends AbstractPlugin
{
    const CONFIG_NAME = 'theme.json';

    public $name;

    public $title;

    public $thumbnail;

    public $author = array('name' => 'NoName', 'email' => 'no_email');

    private $views;

    private $assets;

    private $webPath;

    public function parse(array $json)
    {
        $this->name = isset($json['name']) ? $json['name'] : 'vendor/name';
        $this->title = isset($json['title']) ? $json['title'] : 'No title';
        $this->thumbnail = isset($json['thumbnail']) ? $json['thumbnail'] : null;

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

        if(isset($json['views']) && is_string($json['views'])) {
            $this->views = $this->getDir() . '/' . $json['views'];

        }

        if(isset($json['assets']) && is_string($json['assets'])) {
            $this->assets = $this->getDir() . '/' . $json['assets'];
        }
    }

    /**
     * @return string
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @return string
     */
    public function getAssets()
    {
        return $this->assets;
    }

    public function getWebPath()
    {
        return $this->webPath;
    }

    /**
     * @param string $webPath
     */
    public function setWebPath($webPath)
    {
        $this->webPath = $webPath;
    }
}