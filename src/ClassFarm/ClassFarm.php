<?php

namespace Wolo\ClassFarm;

use RuntimeException;
use Wolo\Hash;

/**
 * Create and use classes globally
 */
class ClassFarm
{
    public static array $barn = [];

    /**
     * Construct class Store constructed class object in memory
     * @param  string  $name
     * @param  callable|class-string  $constructor
     * @param ...$arguments  - will be used when $constructor is class-string
     * @return mixed
     */
    public static function barn(string $name, callable|string $constructor, ...$arguments): mixed
    {
        $hash = Hash::crc32b($name, $constructor, $arguments);
        if (!static::has($hash)) {
            static::put($hash, $constructor, $arguments);
        }

        return static::get($hash);
    }

    /**
     * @param  string  $name
     * @param  callable|class-string  $constructor
     * @param  array  $arguments
     * @return void
     */
    public static function put(string $name, callable|string $constructor, array $arguments = []): void
    {
        static::$barn[$name] = [
            'constructed' => false,
            'constructor' => $constructor,
            'constructorArguments' => $arguments
        ];
    }

    /**
     * Does farmer exists
     *
     * @param  string  $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, static::$barn);
    }

    /**
     * Is farmer constructed
     *
     * @param  string  $name
     * @return bool
     */
    public static function constructed(string $name): bool
    {
        return static::$barn[$name]['constructed'];
    }

    /**
     * Delete farmer
     *
     * @param  string  $name
     * @return void
     */
    public static function forget(string $name): void
    {
        unset(static::$barn[$name]);
    }

    public static function get(string $name): mixed
    {
        if (!static::has($name)) {
            throw new RuntimeException("ClassFarm farmer('$name') does not exist");
        }
        if (!static::constructed($name)) {
            $constructor = static::$barn[$name]['constructor'];
            static::$barn[$name]['constructed'] = true;
            if (is_string($constructor)) {
                static::$barn[$name]['classObject'] = new $constructor(...static::$barn[$name]['constructorArguments']);
            }
            else {
                static::$barn[$name]['classObject'] = $constructor();
            }
        }

        return static::$barn[$name]['classObject'];
    }

    //region deprecated

    /**
     * @see static::barn()
     * @deprecated
     */
    public static function instance(string $name, callable|string $constructor, ...$arguments): mixed
    {
        return static::barn($name, $constructor, ...$arguments);
    }

    /**
     * @see static::forget()
     * @deprecated
     */
    public static function remove(string $name): void
    {
        static::forget($name);
    }

    /**
     * @see static::has()
     * @deprecated
     */
    public static function exists(string $name): bool
    {
        return static::has($name);
    }

    /**
     * @see static::put()
     * @deprecated
     */
    public static function set(string $name, callable|string $constructor): void
    {
        static::put($name, $constructor);
    }
    //endregion
}