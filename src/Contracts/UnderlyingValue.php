<?php

namespace Wolo\Contracts;

interface UnderlyingValue
{
    /**
     * Get the underlying value
     *
     * @return mixed
     */
    public function value();
}