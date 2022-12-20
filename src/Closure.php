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
                $type = $types[$key] ?? null;

                if (is_null($type)) {
                    return $value;
                }
                if ($type instanceof \ReflectionNamedType) {
                    if ($type->isBuiltin() || ($type->allowsNull() && is_null($value))) {
                        return $value;
                    }
                    $type = $type->getName();
                }
                else {
                    $type = (string)$type;
                }

                if ($value instanceof $type) {
                    return $value;
                }

                return new $type($value);
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