<?php

namespace Wolo\Request\Support;

abstract class InstanceShortcuts
{
    abstract protected static function instance(): RequestVariableCollection;

    public static function all(): array
    {
        return static::instance()->all();
    }

    public static function get(string $key = null, mixed $default = null): mixed
    {
        return static::instance()->get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        static::instance()->set($key, $value);
    }

    public static function delete(string|int|array $keys): void
    {
        static::instance()->delete($keys);
    }

    /**
     * @see static::delete() for replacement
     * @deprecated
     */
    public static function unset(string|int|array $keys): void
    {
        static::delete($keys);
    }

    /**
     * @see static::has() for replacement
     * @deprecated
     */
    public static function exists(string $key): bool
    {
        return static::has($key);
    }

    public static function has(string $key): bool
    {
        return static::instance()->has($key);
    }

    public static function flush(): void
    {
        static::instance()->flush();
    }
}