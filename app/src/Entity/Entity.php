<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity;

use Doctrine\ORM\EntityManager;

class Entity
{
    /**
     * @var \Closure
     */
    static private $entityManagerFactory;

    /**
     * @var EntityManager
     */
    static private $entityManager;

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

    final public function persist()
    {
        self::entityManager()->persist($this);
    }

    final public function remove()
    {
        self::entityManager()->remove($this);
    }

    final public function refresh()
    {
        self::entityManager()->refresh($this);
    }

    final public function save()
    {
        $this->persist();
        self::flush($this);
    }

    final public function delete()
    {
        $this->remove();
        self::flush($this);
    }

    final public static function flush($entity = null)
    {
        self::entityManager()->flush($entity);
    }

    public static function reference($id)
    {
        return self::entityManager()->getPartialReference(get_called_class(), $id);
    }

    public static function find($id)
    {
        return self::entityManager()->find(get_called_class(), $id);
    }

    final public static function entityManager()
    {
        if (self::$entityManager === null) {
            self::$entityManager = self::$entityManagerFactory->__invoke();
        }

        return self::$entityManager;
    }

    final public static function setEntityManagerFactory(\Closure $factory)
    {
        self::$entityManagerFactory = $factory;
    }
}