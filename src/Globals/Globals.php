<?php

namespace Wolo\Globals;

use Closure;
use Wolo\Globals\GlobalsCollection;

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
 * @method static mixed magic(Closure $callback) Execute closure once per $key existence
 * @method static mixed once(mixed|callable ...$keys) Execute $callback once, if $keys is not provided then cache ID will be generated using callable footprint
 * @method static mixed onceForce(string|array $key, bool $forceExec, callable $callback) Execute $callback once per $key existence or force it to call
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