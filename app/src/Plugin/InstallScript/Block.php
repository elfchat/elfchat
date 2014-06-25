<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin\InstallScript;

class Block 
{
    private $collector;
    private $view;
    private $block;

    public function __construct(Collector $collector, $view, $block)
    {
        $this->collector = $collector;
        $this->view = $view;
        $this->block = $block;
    }

    public function append($file)
    {
        $this->collector->append($this->view, $this->block, $file);
    }

    public function prepend($file)
    {
        $this->collector->prepend($this->view, $this->block, $file);
    }

    public function replace($file)
    {
        $this->collector->replace($this->view, $this->block, $file);
    }
} 