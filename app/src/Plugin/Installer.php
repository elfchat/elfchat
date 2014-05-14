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

    /**
     * @param $pluginFile string
     */
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }


    /**
     * @param Plugin[] $plugins
     */
    public function install($plugins)
    {
        if(!file_exists($this->pluginFile)) {
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
            $content .= "    '{$plugin->getPluginFile()}',\n";
        }

        $content .= ");\n";

        // Autoload

        $content .= '
$loader = ElfChat\Config\LoaderRegistry::getLoader();
';

        foreach ($plugins as $plugin) {
            foreach ($plugin->autoload as $namespace => $path) {

                $content .= "\$loader->addPsr4('" . addslashes($namespace) . "', '$path');\n";
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

        file_put_contents($this->pluginFile, $content);
    }


}