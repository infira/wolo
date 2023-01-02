<?php

namespace Wolo\Reflection;

use ReflectionNamedType;
use ReflectionUnionType;
use Wolo\TypeJuggling;

class ReflectionType
{
    public static function valueMatches(mixed $value, \ReflectionType $type): bool
    {
        if (is_null($value) && $type->allowsNull()) {
            return true;
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();
            if ($name === 'mixed') {
                return true;
            }

            return TypeJuggling::valueMatches($value, $type->getName());
        }


        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $uType) {
                if (self::valueMatches($value, $uType)) {
                    return true;
                }
            }

            return false;
        }

        //if ((PHP_VERSION_ID >= 80100) && $type instanceof \ReflectionIntersectionType) {
        foreach ($type->getTypes() as $uType) {
            if (!self::valueMatches($value, $uType)) {
                return false;
            }
        }

        return true;
        //}
    }
}
