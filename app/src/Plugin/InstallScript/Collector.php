<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin\InstallScript;

class Collector 
{
    public $views = array();

    public function append($view, $block, $file)
    {
        $this->views[$view][$block]['append'][] = $file;
    }

    public function prepend($view, $block, $file)
    {
        $this->views[$view][$block]['prepend'][] = $file;
    }

    public function replace($view, $block, $file)
    {
        $this->views[$view][$block]['replace'][] = $file;
    }
}