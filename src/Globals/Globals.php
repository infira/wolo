<?php

namespace Wolo\Globals;

use Wolo\ClassFarm\ClassFarm;

/**
 * A utility to store data in php runtimememory
 * @method static string name()
 * @method static bool has(string|int $key)
 * @method static GlobalsCollection put(string|int $key, mixed $value)
 * @method static GlobalsCollection add(mixed $value)
 * @method static GlobalsCollection append(mixed $value)
 * @method static GlobalsCollection push(mixed ...$value)
 * @method static mixed get(string|int $key, mixed $default = null)
 * @method static GlobalsCollection forget(string|int|array $keys)
 * @method static array all()
 * @method static array tree()
 * @method static void each(callable $callback)
 * @method static void eachTree(callable $callback)
 * @method static void eachCollection(callable $callback)
 * @method static array collections()
 * @method static mixed once(mixed ...$keys, callable $callback) - Execute $callback once by hash-sum of $parameters
 * @method static bool flush()
 * @see GlobalsCollection
 */
class Globals
{
    /**
     * @param  string  $name  - collection name
     * @return GlobalsCollection
     */
    public static function of(string $name): GlobalsCollection
    {
        return ClassFarm::barn("GlobalsCollection.$name", GlobalsCollection::class, $name);
    }

    public static function __callStatic(string $method, array $args)
    {
        return self::of('general')->$method(...$args);
    }

    //region deprecated

    /**
     * @see GlobalsCollection::has()
     * @deprecated
     */
    public static function exists(string $key): bool
    {
        return self::of('general')->has($key);
    }

    /**
     * @see GlobalsCollection::put()
     * @deprecated
     */
    public static function set(string|int $key, mixed $value): GlobalsCollection
    {
        return self::of('general')->put($key, $value);
    }

    /**
     * @see GlobalsCollection::forget()
     * @deprecated
     */
    public static function delete(string|int|array $keys): GlobalsCollection
    {
        return self::of('general')->forget($keys);
    }
    //endregion
}