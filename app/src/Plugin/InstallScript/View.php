<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Plugin\InstallScript;

class View 
{
    private $collector;
    private $file;

    public function __construct(Collector $collector, $file)
    {
        $this->collector = $collector;
        $this->file = $file;
    }

    public function block($name)
    {
        return new Block($this->collector, $this->file, $name);
    }
}