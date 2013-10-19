<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Config;

class Reader
{
    private $config;

    private $class;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->class = new \ReflectionClass(get_class($this->config));
    }

    public function read($file)
    {
        if (is_readable($file)) {
            $array = include $file;
            foreach ($this->class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (isset($array[$property->getName()])) {
                    $value = $array[$property->getName()];
                    if ($value !== null) {
                        $this->config->{$property->getName()} = $value;
                    }
                }
            }
        } else {
            throw new \RuntimeException("The configuration file \"$file\" does not readable.");
        }
    }
}