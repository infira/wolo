<?php

namespace Wolo\ClassFarm;

use RuntimeException;
use stdClass;

/**
 * Create and use classes globally
 */
class ClassFarm
{
    public static array $farm = [];

    public static function of(string $class, ...$arguments)
    {
        if (!static::exists($class)) {
            static::set($class, static fn() => new $class(...$arguments));
        }

        return static::get($class);
    }

    public static function set(string $name, callable|string $constructor): void
    {
        $instance = new stdClass();
        $instance->constructed = false;
        $instance->constructor = $constructor;
        static::$farm[$name] = $instance;
    }

    /**
     * Does farmer exists
     *
     * @param string $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return array_key_exists($name, static::$farm);
    }

    /**
     * Is farmer constructed
     *
     * @param string $name
     * @return bool
     */
    public static function constructed(string $name): bool
    {
        return static::$farm[$name]->constructed;
    }

    /**
     * Delete farmer
     *
     * @param string $name
     * @return void
     */
    public static function remove(string $name): void
    {
        unset(static::$farm[$name]);
    }

    public static function get(string $name): ?object
    {
        if (!static::exists($name)) {
            throw new RuntimeException("ClassFarm farmer('$name') does not exist");
        }
        if (!static::constructed($name)) {
            $constructor = static::$farm[$name]->constructor;
            if (is_string($constructor)) {
                static::$farm[$name]->classObject = new $constructor();
            }
            else {
                static::$farm[$name]->classObject = $constructor();
            }
            static::$farm[$name]->constructed = true;
        }

        return static::$farm[$name]->classObject;
    }

    public static function instance(string $name, callable|string $constructor): ?object
    {
        if (!static::exists($name)) {
            static::set($name, $constructor);
        }

        return static::get($name);
    }
}