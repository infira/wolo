<?php

namespace Wolo\Globals;

use Closure;

/**
 * TempController
 * @method static string name()
 * @method static bool exists(string $key)
 * @method static bool has(string $key)
 * @method static void set(string $key, mixed $value)
 * @method static void add(mixed $value)
 * @method static void append(mixed $value)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool delete(string $key)
 * @method static array all()
 * @method static array tree()
 * @method static void each(callable $callback)
 * @method static void eachTree(callable $callback)
 * @method static void eachCollection(callable $callback)
 * @method static array collections()
 * @method static mixed once(mixed ...$keys)
 * @method static bool flush()
 * @see GlobalsCollection
 */
class Globals
{
    private static array $collections = [];

    /**
     * @param  string  $key  - collection name
     * @return GlobalsCollection
     */
    public static function of(string $key): GlobalsCollection
    {
        if (!isset(self::$collections[$key])) {
            self::$collections[$key] = new GlobalsCollection($key);
        }

        return self::$collections[$key];
    }

    public static function __callStatic(string $method, array $args)
    {
        return self::of('general')->$method(...$args);
    }
}