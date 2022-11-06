<?php

namespace Wolo\Request\Support\Traits;

use Wolo\Request\Http;

/**
 * Managing $_SERVER variables
 * @mixin Http
 */
trait ServerShortcuts
{
    public static function allSERVER(): array
    {
        return static::server()->all();
    }

    public static function getSERVER(string $key = null, mixed $default = null): mixed
    {
        return static::server()->get($key, $default);
    }

    /**
     * @see static::hasSERVER() for replacement
     * @deprecated
     */
    public static function existsSERVER(string $key): bool
    {
        return static::hasSERVER($key);
    }

    public static function hasSERVER(string $key): bool
    {
        return static::server()->has($key);
    }
}