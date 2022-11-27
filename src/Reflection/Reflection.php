<?php

namespace Wolo\Reflection;

class Reflection
{
    /**
     * get class traits
     *
     * @param  string|object  $objectOrClass
     * @param  int  $depth  - check also parents traits, 0 all teh way to to last parent
     * @return array
     */
    public static function getClassTraits(string|object $objectOrClass, int $depth = 0): array
    {
        $class = new \ReflectionClass($objectOrClass);
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
}
