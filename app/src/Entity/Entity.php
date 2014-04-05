<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity;

class Entity
{
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        return method_exists($this, $methodName) ? $this->{$methodName}() : $this->{$name};
    }

    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        return method_exists($this, $methodName) ? $this->{$methodName}($value) : $this->{$name} = $value;
    }

    public function __isset($name)
    {
        return property_exists($this, $name);
    }
} 