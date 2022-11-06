<?php

namespace Wolo;

use ReflectionException;
use ReflectionFunction;

use ReflectionParameter;

use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_VERSION_ID;

class Closure
{
    private static function getPHPBuiltInTypes(): array
    {
        // PHP 8.1
        if(PHP_VERSION_ID >= 80100) {
            return ['array', 'callable', 'string', 'int', 'bool', 'float', 'iterable', 'void', 'object', 'mixed', 'false', 'null', 'never'];
        }

        // PHP 8
        if(PHP_MAJOR_VERSION === 8) {
            return ['array', 'callable', 'string', 'int', 'bool', 'float', 'iterable', 'void', 'object', 'mixed', 'false', 'null'];
        }

        // PHP 7
        return match (PHP_MINOR_VERSION) {
            0 => ['array', 'callable', 'string', 'int', 'bool', 'float'],
            1 => ['array', 'callable', 'string', 'int', 'bool', 'float', 'iterable', 'void'],
            default => ['array', 'callable', 'string', 'int', 'bool', 'float', 'iterable', 'void', 'object'],
        };
    }

    /**
     * A tool to make callback type injectable
     * @throws ReflectionException
     */
    public static function makeInjectable(callable $callback): \Closure
    {
        $types = array_map(static fn(ReflectionParameter $p) => $p->getType()?->getName(), (new ReflectionFunction($callback))->getParameters());

        $cast = static function($params) use ($types): array {
            array_walk($params, static function(&$value, $key) use ($types) {
                $type = $types[$key] ?? null;
                if($type && !($value instanceof $type) && !in_array($type, static::getPHPBuiltInTypes(), true)) {
                    $value = new $type($value);
                }
            }, $params);

            return $params;
        };

        return static fn(...$params) => $callback(...$cast($params));
    }

    /**
     * @throws ReflectionException
     */
    public static function makeInjectableOrVoid(mixed $callback): mixed
    {
        if(is_callable($callback)) {
            return static::makeInjectable($callback);
        }

        return $callback;
    }
}