<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Config;

class Config extends DotNotation
{
    private $file;

    public function __construct(array $values = array())
    {
        parent::__construct($values);
    }

    public function load($fromFile)
    {
        $this->file = $fromFile;
        if (is_readable($fromFile)) {
            $loaded = include $fromFile;
            $this->values = $this->arrayMergeRecursiveDistinct($this->values, $loaded);
        }
    }

    public function save($toFile = null)
    {
        $toFile = null === $toFile ? $this->file : $toFile;

        $export = var_export($this->values, true);
        $code = "<?php return $export;";
        file_put_contents($toFile, $code);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
} 