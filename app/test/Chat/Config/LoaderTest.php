<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Chat\Config;

use Chat\Config;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $config = new Config();
        $loader = new Reader($config);
    }
}