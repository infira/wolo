<?php

namespace Wolo;

use Wolo\Reflection\ReflectionType;

class TypeJuggling
{
    public static function valueMatches(mixed $value, \ReflectionType|string $type, bool $allowClassString = false): bool
    {
        if ($type instanceof \ReflectionType) {
            return ReflectionType::valueMatches($value, $type, $allowClassString);
        }

        if ($type === 'mixed') {
            return true;
        }

        return match ($type) {
            'int', 'integer' => is_int($value),
            'bool', 'boolean' => is_bool($value),
            'float', 'double' => is_float($value),
            'string' => is_string($value),
            'iterable' => is_iterable($value),
            'array' => is_array($value),
            'object' => is_object($value),
            default => ($allowClassString && is_string($value))
                ? is_a($value, $type, true)
                : $value instanceof $type
        };
    }

    public static function cast(mixed $value, \ReflectionNamedType|string $type): mixed
    {
        if (self::valueMatches($value, $type)) {
            return $value;
        }

        if ($type instanceof \ReflectionNamedType) {
            $type = $type->getName();
        }

        if ($type === \stdClass::class && !($value instanceof \stdClass) && is_object($value)) {
            throw new \RuntimeException("cant cast to stdClass when value is already an object");
        }

        return match ($type) {
            'int', 'integer' => (int)$value,
            'bool', 'boolean' => (bool)$value,
            'float', 'double' => (float)$value,
            'string' => (string)$value,
            'array' => (array)$value,
            'object' => (object)$value,
            'iterable' => throw new \RuntimeException("cant cast to iterable use class-string"),
            default => new $type($value)
        };
    }
}