<?php

namespace Wolo\Globals;

use Closure;

/**
 * TempController
 * @method static string name() Get collection name
 * @method static bool exists(string $key) Does key exists
 * @method static bool has(string $key) Does key exists
 * @method static void set(string $key, mixed $value) Set new item
 * @method static void add(mixed $value) Append new item
 * @method static void append(mixed $value) Append new item
 * @method static mixed get(string $key, mixed $default = null) Get item, if not found $returnOnNotFound will be returned
 * @method static bool delete(string $key) delete item
 * @method static array all() get all values
 * @method static array tree() get all items and sub collections
 * @method static void each(callable $callback) Call $callback for every item in current collection<br /> $callback($itemValue, $itemName)
 * @method static void eachTree(callable $callback) Call $callback for every collection, sub collection and every item<br />$callback($itemValue, $itemName, $collectionName)
 * @method static void eachCollection(callable $callback) Call $callback for every collection<br />$callback($Collection, $collectionName)
 * @method static array collections() get this all collections
 * @method static mixed once(mixed ...$keys, callable $callback) Execute $callback once by hash-sum of $parameters
 * @method static bool flush() - Flush current data and collections
 */
class Globals
{
    private static array $collections = [];

    /**
     * @param string $key - collection name
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