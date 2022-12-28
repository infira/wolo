<?php

namespace Wolo;

class Closure
{
    /**
     * A tool to make callback type injectable
     */
    public static function makeInjectable(\Closure $callback, callable $converter = null): \Closure
    {
        $types = array_map(static fn(\ReflectionParameter $p) => $p->getType(), (new \ReflectionFunction($callback))->getParameters());

        return static function (...$params) use ($types, $callback, $converter) {
            $keys = array_keys($params);
            $params = array_map(
                static function ($value, $key) use ($types, $converter) {
                    $type = $types[$key] ?? null;
                    if (self::canInject($value, $type)) {
                        $type = (string)$type;
                        if ($converter) {
                            return $converter($type, $value);
                        }

                        if ($type === \stdClass::class) {
                            return (object)$value;
                        }

                        return new $type($value);
                    }

                    return $value;
                },
                $params,
                $keys
            );

            return $callback(...$params);
        };
    }

    public static function canInject(mixed $value, ?\ReflectionType $type): bool
    {
        if (is_null($type)) {
            return false;
        }
        if ($type instanceof \ReflectionNamedType) {
            if ($type->isBuiltin() || ($type->allowsNull() && is_null($value))) {
                return false;
            }
            $type = $type->getName();
        }
        else {
            $type = (string)$type;
        }

        return !($value instanceof $type);
    }

    public static function makeInjectableOrVoid(mixed $callback): mixed
    {
        if ($callback instanceof \Closure) {
            return static::makeInjectable($callback);
        }

        return $callback;
    }

    public static function getParameterNamesAndTypes(\Closure $closure): array
    {
        $params = self::getParameters($closure);

        return array_combine(
            array_map(
                static fn(\ReflectionParameter $p) => $p->getName(),
                $params
            ),
            array_map(
                static fn(\ReflectionParameter $p) => $p->getType()?->getName(),
                $params
            )
        );
    }

    public static function getFirstParam(\Closure $closure): ?\ReflectionParameter
    {
        return self::getParamAt(0, $closure);
    }

    public static function getParamAt(int $index, \Closure $closure): ?\ReflectionParameter
    {
        return self::getParameters($closure)[$index] ?? null;
    }

    /**
     * @return \ReflectionParameter[]
     */
    public static function getParameters(\Closure $closure): array
    {
        return (new \ReflectionFunction($closure))->getParameters();
    }
}