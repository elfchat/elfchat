<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

class Plugin
{
    const CONFIG_NAME = 'plugin.json';

    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function loadConfig()
    {
        $configFilePath = $this->path . '/' . self::CONFIG_NAME;

        if (!is_readable($configFilePath)) {
            throw new \RuntimeException("Plugin config \"$configFilePath\" does not readable.");
        }

        $json = json_decode(file_get_contents($configFilePath), true);


    }
} 