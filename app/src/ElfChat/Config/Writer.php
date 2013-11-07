<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Config;

class Writer
{
    private $config;

    private $class;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->class = new \ReflectionClass(get_class($this->config));
    }

    public function write($file)
    {
        if (is_writeable($file)) {
            $array = array();
            foreach ($this->class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $array[$property->getName()] = $property->getValue($this->config);
            }
            $export = var_export($array, true);
            $code = "<?php return $export;";
            file_put_contents($file, $code);
        } else {
            throw new \RuntimeException("The configuration file \"$file\" does not writeable.");
        }
    }
}