<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin;

use ElfChat\Plugin\InstallScript\Collector;
use ElfChat\Plugin\InstallScript\View;

class Hook
{
    private static $collector;

    public static function view($file)
    {
        return new View(self::$collector, $file);
    }

    public static function setCollector(Collector $collector)
    {
        self::$collector = $collector;
    }
} 