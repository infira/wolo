<?php

namespace Wolo\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

class Reflection
{
    /**
     * get class traits
     *
     * @param  string|object  $objectOrClass
     * @param  int  $depth  - check also parents traits, 0 all teh way to to last parent
     * @return array
     * @throws ReflectionException
     */
    public static function getClassTraits(string|object $objectOrClass, int $depth = 0): array
    {
        $class = new ReflectionClass($objectOrClass);
        $traits = $class->getTraits();
        if ($depth !== null) {
            $currentDepth = 0;
            $depth = $depth === 0 ? 99 : $depth;
            while ($parent = $class->getParentClass() and $currentDepth <= $depth) {
                array_push($traits, ...$parent->getTraits());
                $class = $parent;
                $currentDepth++;
            }
        }

        return $traits;
    }

    /**
     * Does class has trait
     *
     * @param  string|object  $objectOrClass
     * @param  string|object  $findObjectOrClass
     * @param  bool  $checkParents
     * @return bool
     * @throws ReflectionException
     */
    public static function classHasTrait(string|object $objectOrClass, string|object $findObjectOrClass, bool $checkParents = true): bool
    {
        $findClass = is_object($findObjectOrClass) ? $findObjectOrClass::class : $findObjectOrClass;
        $traits = self::getClassTraits($objectOrClass, $checkParents ? 0 : null);
        foreach ($traits as $trait) {
            if ($trait->getName() === $findClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ReflectionParameter[]
     * @throws ReflectionException
     */
    public static function getParameters(callable|ReflectionFunctionAbstract $callable): array
    {
        if ($callable instanceof ReflectionFunctionAbstract) {
            return $callable->getParameters();
        }
        if (is_array($callable)) {
            return (new ReflectionMethod(...$callable))->getParameters();
        }

        return (new ReflectionFunction($callable))->getParameters();
    }

    /**
     * @throws ReflectionException
     */
    public static function getParameterNamesAndTypes(callable|ReflectionFunctionAbstract $callable): array
    {
        $params = self::getParameters($callable);

        return array_combine(
            array_map(
                static fn(ReflectionParameter $p) => $p->getName(),
                $params
            ),
            array_map(
                static fn(ReflectionParameter $p) => $p->getType()?->getName(),
                $params
            )
        );
    }

    /**
     * @throws ReflectionException
     */
    public static function getFirstParam(callable|ReflectionFunctionAbstract $callable): ?ReflectionParameter
    {
        return self::getParamAt(0, $callable);
    }

    /**
     * @throws ReflectionException
     */
    public static function getParamAt(int $index, callable|ReflectionFunctionAbstract $callable): ?ReflectionParameter
    {
        return self::getParameters($callable)[$index] ?? null;
    }
}
