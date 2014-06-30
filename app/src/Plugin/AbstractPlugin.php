<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

abstract class AbstractPlugin
{
    private $configFile;

    public $require = array();

    public function __construct(\SplFileInfo $pluginFile)
    {
        $this->configFile = $pluginFile;
        $this->load();
    }

    public function checkRequirements()
    {
        $installed = array(
            'php' => PHP_VERSION,
            'elfchat' => ELFCHAT_VERSION,
        );

        foreach ($this->require as $what => $version) {
            if (!isset($installed[$what])) {
                return false;
            }

            $currentVersion = $installed[$what];
            $currentVersion = str_replace('__VERSION__', $version, $currentVersion);

            $versionRegex = preg_quote($version);
            $versionRegex = str_replace('\*', '.+?', $versionRegex);
            $versionRegex = "/^$versionRegex$/";

            if (!preg_match($versionRegex, $currentVersion)) {
                return false;
            }
        }

        return true;
    }

    private function getContents()
    {
        $level = error_reporting(0);
        $content = file_get_contents($this->configFile->getPathname());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        return $content;
    }

    private function load()
    {
        if (!$this->configFile->isReadable()) {
            throw new \RuntimeException("Plugin config \"" . $this->configFile->getPathname() . "\" does not readable.");
        }

        $json = json_decode($this->getContents(), true);

        $this->parse($json);
    }

    abstract public function parse(array $json);

    public function getConfigFileName()
    {
        return $this->configFile->getPathname();
    }

    public function getDir()
    {
        return $this->configFile->getPath();
    }
} 