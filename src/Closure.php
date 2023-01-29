<?php

namespace Wolo;

use ReflectionException;
use ReflectionParameter;
use Wolo\Callables\CallableInterceptor;
use Wolo\Reflection\Reflection;
use Wolo\Reflection\ReflectionType;

class Closure
{
    /**
     * A util to make callback type injectable
     *
     * @param  \Closure  $callback
     * @return \Closure
     * @throws ReflectionException
     */
    public static function makeInjectable(\Closure $callback): \Closure
    {
        return CallableInterceptor::from($callback)->get(true);
    }

    /**
     * @throws ReflectionException
     */
    public static function makeInjectableOrVoid(mixed $callback): mixed
    {
        if ($callback instanceof \Closure) {
            return static::makeInjectable($callback);
        }

        return $callback;
    }

    /**
     * @throws ReflectionException
     */
    public static function getParameterNamesAndTypes(\Closure $closure): array
    {
        return Reflection::getParameterNamesAndTypes($closure);
    }

    /**
     * @throws ReflectionException
     */
    public static function getFirstParam(\Closure $closure): ?ReflectionParameter
    {
        return Reflection::getFirstParam($closure);
    }

    /**
     * @throws ReflectionException
     */
    public static function getParamAt(int $index, \Closure $closure): ?ReflectionParameter
    {
        return Reflection::getParamAt($index, $closure);
    }

    /**
     * @return ReflectionParameter[]
     * @throws ReflectionException
     */
    public static function getParameters(\Closure $closure): array
    {
        return Reflection::getParameters($closure);
    }

    /**
     * Does closure param matches to type
     */
    public static function paramMatchesType(int $paramIndex, \Closure $closure, string|\ReflectionNamedType $type): bool
    {
        $param = self::getParamAt($paramIndex, $closure);
        if (!$param) {
            return false;
        }

        return ReflectionType::valueMatches($type, $param->getType(), true);
    }
}