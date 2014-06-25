<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

class Installer
{
    private $pluginFile;

    private $pluginViewPath;

    /**
     * @param $pluginFile string
     * @param $pluginViewPath string
     */
    public function __construct($pluginFile, $pluginViewPath)
    {
        $this->pluginFile = $pluginFile;
        $this->pluginViewPath = $pluginViewPath;
    }


    /**
     * @param Plugin[] $plugins
     */
    public function install($plugins)
    {
        if (!file_exists($this->pluginFile)) {
            file_put_contents($this->pluginFile, '');
        }

        if (!is_writable($this->pluginFile)) {
            throw new \RuntimeException("Plugin file does not writeable.");
        }

        $content = "<?php\n";

        // Loader instance
        $content .= '
/** @var $app \ElfChat\Application */
';

        // Plugins list
        $content .= '
$app[\'plugins\'] = array(
';

        foreach ($plugins as $plugin) {
            $content .= "    '{$plugin->name}' => '{$plugin->getPluginFile()}',\n";
        }

        $content .= ");\n";

        // Nothing more
        if (empty($plugins)) {
            goto write;
        }

        // Autoload

        $content .= '
$loader = ElfChat\Config\LoaderRegistry::getLoader();
';

        foreach ($plugins as $plugin) {
            foreach ($plugin->autoload as $namespace => $path) {

                $content .= "\$loader->addPsr4('" . addslashes($namespace) . "', '$path');\n";
            }
        }

        // Plugin file

        foreach ($plugins as $plugin) {
            if (null !== $plugin->file) {
                $content .= "require_once \"$plugin->file\";\n";
            }
        }

        // Controllers

        $content .= '
$includeController = function ($__file) use ($app) {
    $plugin = $app[\'controllers_factory\'];
    $obtain = include $__file;
    return $obtain == 1 ? $plugin : $obtain;
};

';

        foreach ($plugins as $plugin) {
            foreach ($plugin->controllers as $mount => $path) {

                $content .= "\$app->mount('" . addslashes($mount) . "', \$includeController('$path'));\n";
            }
        }

        // Views

        $content .= "
\$app['twig.loader.filesystem'] = \$app->share(\$app->extend('twig.loader.filesystem', function (\\Twig_Loader_Filesystem \$loader) {
";

        foreach ($plugins as $plugin) {
            foreach ($plugin->views as $namespace => $path) {
                $content .= "    \$loader->addPath('$path', '$namespace');\n";
            }
        }

        $content .= "
    return \$loader;
}));
";

        $this->createPluginViews($plugins);

        write:
        file_put_contents($this->pluginFile, $content);
    }

    /**
     * @param Plugin[] $plugins
     */
    private function createPluginViews($plugins)
    {
        // Drop old plugin hooks.
        $this->deleteDirectory($this->pluginViewPath);

        $collector = new InstallScript\Collector();
        Hook::setCollector($collector);

        foreach ($plugins as $plugin) {
            if (!empty($plugin->installScript)) {
                include $plugin->installScript;
            }
        }

        foreach ($collector->views as $view => $blocks) {
            $content = "{% extends 'theme:$view' %}\n";

            foreach ($blocks as $block => $where) {
                $content .= "{% block $block %}\n";
                // Prepend
                if (isset($where['prepend'])) {
                    foreach ($where['prepend'] as $file) {
                        $content .= "{% include '$file' %}\n";
                    }
                }

                // Replace
                if (isset($where['replace'])) {
                    foreach ($where['replace'] as $file) {
                        $content .= "{% include '$file' %}\n";
                    }
                } else {
                    $content .= "{{ parent() }}\n";
                }

                // Append
                if (isset($where['append'])) {
                    foreach ($where['append'] as $file) {
                        $content .= "{% include '$file' %}\n";
                    }
                }
                $content .= "{% endblock %}\n";
            }

            $viewFile = $this->pluginViewPath . '/' . $view;
            $viewDir = dirname($viewFile);
            if (!is_dir($viewDir)) {
                mkdir($viewDir, 0777, true);
            }

            file_put_contents($viewFile, $content);
        }
    }

    /**
     * @param $dir string
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }
}