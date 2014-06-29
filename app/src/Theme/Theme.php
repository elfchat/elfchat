<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Theme;

use ElfChat\Plugin\Plugin;

class Theme extends Plugin
{
    const CONFIG_NAME = 'theme.json';

    public $thumbnail;

    private $themeViewsPath;

    protected function parse(array $json)
    {
        if(isset($json['views']) && is_string($json['views'])) {
            $this->themeViewsPath = $this->getPluginDir() . '/' . $json['views'];

            unset($json['views']);
        }

        parent::parse($json);
    }

    /**
     * @return string
     */
    public function getThemeViewsPath()
    {
        return $this->themeViewsPath;
    }
}