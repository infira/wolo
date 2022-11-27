<?php

namespace Wolo;

class Closure
{
    /**
     * A tool to make callback type injectable
     */
    public static function makeInjectable(\Closure $callback): \Closure
    {
        $types = array_map(static fn(\ReflectionParameter $p) => $p->getType(), (new \ReflectionFunction($callback))->getParameters());

        return static function (...$params) use ($types, $callback) {
            $keys = array_keys($params);
            $params = array_map(static function ($value, $key) use ($types) {
                /** @var \ReflectionNamedType $type */
                $type = $types[$key] ?? null;

                if (
                    is_null($type)
                    || $type->isBuiltin()
                    || ($type->allowsNull() && is_null($value))
                ) {
                    return $value;
                }

                return new ($type->getName())($value);
            }, $params, $keys);

            return $callback(...$params);
        };
    }

    public static function makeInjectableOrVoid(mixed $callback): mixed
    {
        if ($callback instanceof \Closure) {
            return static::makeInjectable($callback);
        }

        return $callback;
    }
}