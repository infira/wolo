<?php

namespace Wolo\Profiler;

//require_once '../../vendor/autoload.php';
use Wolo\ClassFarm\ClassFarm;

/**
 * @method static void measure(string $name, callable $measurable, mixed ...$measurableArguments)
 * @method static void start(string $name)
 * @method static void stop(string $name)
 * @method static float elapsedTime()
 * @method static string dump()
 * @method static void print()
 */
class Prof
{
    protected static bool $halted = false;

    /**
     * Halt all hal
     *
     * @return void
     */
    public static function halt(): void
    {
        static::$halted = true;
    }

    /**
     * Continue measuring
     *
     * @return void
     */
    public static function continue(): void
    {
        static::$halted = false;
    }

    /** Is measuring halted */
    public static function isHalted(): bool
    {
        return static::$halted;
    }

    /**
     * @param  string  $name
     * @return Profiler
     */
    public static function of(string $name = 'globalProfiler'): Profiler
    {
        return ClassFarm::barn("WoloProfiler.$name", Profiler::class);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return self::of('globalProfiler')->$name(...$arguments);
    }
}