<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Twig;

class LevelLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LevelLoader
     */
    private $ll;

    public function setUp()
    {
        $this->ll = new LevelLoader([
            'plugin' => __DIR__ . '/fixture/twig/plugin',
            'theme' => __DIR__ . '/fixture/twig/theme',
            'base' => __DIR__ . '/fixture/twig/base',
        ]);
    }

    public function testBaseLoad()
    {
        $source = $this->ll->getSource('onlyInBase.twig');
        $this->assertEquals('onlyInBase', $source);
    }

    public function testBaseOverrideByTheme()
    {
        $source = $this->ll->getSource('baseAndTheme.twig');
        $this->assertEquals('theme', $source);
    }

    public function testThemeLoad()
    {
        $source = $this->ll->getSource('onlyInTheme.twig');
        $this->assertEquals('onlyInTheme', $source);
    }

    public function testThemeOverrideByPlugin()
    {
        $source = $this->ll->getSource('themeAndPlugin.twig');
        $this->assertEquals('plugin', $source);
    }

    public function testGoDownDesired()
    {
        $source = $this->ll->getSource('base:themeAndPlugin.twig');
        $this->assertEquals('base', $source);

        $source = $this->ll->getSource('theme:themeAndPlugin.twig');
        $this->assertEquals('theme', $source);

        $source = $this->ll->getSource('plugin:themeAndPlugin.twig');
        $this->assertEquals('plugin', $source);
    }

    public function testGoDownDesiredWithoutTheme()
    {
        $source = $this->ll->getSource('baseAndPlugin.twig');
        $this->assertEquals('plugin', $source);

        $source = $this->ll->getSource('theme:baseAndPlugin.twig');
        $this->assertEquals('base', $source);
    }

    public function testOverFunctions()
    {
        $exist = $source = $this->ll->exists('themeAndPlugin.twig');
        $this->assertTrue($exist);

        $isFresh = $this->ll->isFresh('onlyInBase.twig', filemtime(__DIR__ . '/fixture/twig/base/onlyInBase.twig'));
        $this->assertTrue($isFresh);

        $cacheKey = $this->ll->getCacheKey('onlyInBase.twig');
        $this->assertEquals(realpath(__DIR__ . '/fixture/twig/base/onlyInBase.twig'), $cacheKey);
    }
}
 