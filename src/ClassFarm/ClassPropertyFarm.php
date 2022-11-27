<?php

namespace Wolo\ClassFarm;

use RuntimeException;

trait ClassPropertyFarm
{
    public static function registerPropertyClass(string $name, callable|string $constructor): void
    {
        $cid = static::class."_property_$name";
        ClassFarm::put($cid, $constructor);
    }

    public function __get(string $name)
    {
        $cid = static::class."_property_$name";
        if (ClassFarm::has($cid)) {
            return ClassFarm::get($cid);
        }
        throw new RuntimeException('unknown property');
    }
}