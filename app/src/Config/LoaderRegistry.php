<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Config;

class LoaderRegistry
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    private static $loader;

    /**
     * @param \Composer\Autoload\ClassLoader $loader
     */
    public static function setLoader($loader)
    {
        self::$loader = $loader;
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        return self::$loader;
    }
}