<?php

namespace Wolo\Contracts;

interface UnderlyingValueByKey
{
    /**
     * Get a single key's value from the first matching item in the collection.
     *
     * @param  string|int  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function valueAt($key, $default = null);
}