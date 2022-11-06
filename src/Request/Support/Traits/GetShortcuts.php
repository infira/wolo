<?php

namespace Wolo\Request\Support\Traits;

use Wolo\Request\Http;

/**
 * Managing $_GET variables
 * @mixin Http
 */
trait GetShortcuts
{
    public static function allGET(): array
    {
        return static::url()->all();
    }

    public static function getGET(string $key = null, mixed $default = null): mixed
    {
        return static::url()->get($key, $default);
    }

    public static function setGET(string $key, mixed $value): void
    {
        static::url()->set($key, $value);
    }

    public static function deleteGET(string|int|array $keys): void
    {
        static::url()->delete($keys);
    }

    /**
     * @see static::deleteGET() for replacement
     * @deprecated
     */
    public static function unsetGET(string|int|array $keys): void
    {
        static::deleteGET($keys);
    }

    /**
     * @see static::has() for replacement
     * @deprecated
     */
    public static function existsGET(string $key): bool
    {
        return static::hasGET($key);
    }

    public static function hasGET(string $key): bool
    {
        return static::url()->has($key);
    }

    public static function flushGET(): void
    {
        static::url()->flush();
    }
}