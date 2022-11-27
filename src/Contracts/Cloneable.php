<?php

namespace Wolo\Contracts;

interface Cloneable
{
    public function clone(): static;
}