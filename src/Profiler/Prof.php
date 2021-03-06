<?php

namespace Wolo\Profiler;

//require_once '../../vendor/autoload.php';

use Exception;

/**
 * @method static void void(string $name)
 * @method static void measure(string $name, callable $measurable, mixed ...$measurableArguments)
 * @method static void start(string $name)
 * @method static void stop(string $name)
 * @method static float elapsedTime()
 * @method static string dump()
 * @method static void print()
 */
class Prof
{
	/**
	 * @param string $name
	 * @return Profiler
	 */
	public static function of(string $name = "globalProfiler"): Profiler
	{
		return \Wolo\Globals\Globals::once(function ()
		{
			return new Profiler();
		}, $name);
	}
	
	public static function __callStatic(string $name, array $arguments)
	{
		return self::of()->$name(...$arguments);
	}
}

Prof::measure('test', function ()
{
	sleep(3);
});

Prof::print();