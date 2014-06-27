<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

use Symfony\Component\Filesystem\Filesystem;

class Installer
{
    private $pluginFile;

    private $pluginViewDir;

    /**
     * @param $pluginFile string
     * @param $pluginViewDir string
     */
    public function __construct($pluginFile, $pluginViewDir)
    {
        $this->pluginFile = $pluginFile;
        $this->pluginViewDir = $pluginViewDir;
    }


    /**
     * @param Plugin[] $plugins
     */
    public function install($plugins)
    {
        $fs = new Filesystem();

        if (!file_exists($this->pluginFile)) {
            file_put_contents($this->pluginFile, '');
        }

        // Drop old plugin hooks.
        $fs->remove($this->pluginViewDir);

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
$app[\'installed_plugins\'] = array(
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
        $fs = new Filesystem();

        foreach ($plugins as $plugin) {
            if (!empty($plugin->hooks)) {


                foreach ($plugin->hooks as $view => $blocks) {
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

                    $viewFile = $this->pluginViewDir . '/' . $view;
                    $viewDir = dirname($viewFile);
                    $fs->mkdir($viewDir);

                    file_put_contents($viewFile, $content);
                }
            }
        }
    }
}