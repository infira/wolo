<?php

namespace Wolo\Request\Support\Traits;

use Wolo\Request\Http;

/**
 * Managing $_FILE variables
 * @mixin Http
 */
trait FileShortcuts
{
    public static function allFILE(): array
    {
        return static::file()->all();
    }

    public static function getFILE(string $key = null, mixed $default = null): mixed
    {
        return static::file()->get($key, $default);
    }

    public static function setFILE(string $key, mixed $value): void
    {
        static::file()->set($key, $value);
    }

    public static function deleteFILE(string|int|array $keys): void
    {
        static::file()->delete($keys);
    }

    /**
     * @see static::deleteFILE() for replacement
     * @deprecated
     */
    public static function unsetFILE(string|int|array $keys): void
    {
        static::deleteFILE($keys);
    }

    /**
     * @see static::has() for replacement
     * @deprecated
     */
    public static function existsFILE(string $key): bool
    {
        return static::hasFILE($key);
    }

    public static function hasFILE(string $key): bool
    {
        return static::file()->has($key);
    }

    public static function flushFILE(): void
    {
        static::file()->flush();
    }
}