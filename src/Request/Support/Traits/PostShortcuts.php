<?php

namespace Wolo\Request\Support\Traits;

use Wolo\Request\Http;

/**
 * Managing $_POST variables
 * @mixin Http
 */
trait PostShortcuts
{
    public static function allPOST(): array
    {
        return static::post()->all();
    }

    public static function getPOST(string $key = null, mixed $default = null): mixed
    {
        return static::post()->get($key, $default);
    }

    public static function setPOST(string $key, mixed $value): void
    {
        static::post()->set($key, $value);
    }

    public static function deletePOST(string|int|array $keys): void
    {
        static::post()->delete($keys);
    }

    /**
     * @see static::deletePOST() for replacement
     * @deprecated
     */
    public static function unsetPOST(string|int|array $keys): void
    {
        static::deletePOST($keys);
    }

    /**
     * @see static::has() for replacement
     * @deprecated
     */
    public static function existsPOST(string $key): bool
    {
        return static::hasPOST($key);
    }

    public static function hasPOST(string $key): bool
    {
        return static::post()->has($key);
    }

    public static function flushPOST(): void
    {
        static::post()->flush();
    }
}