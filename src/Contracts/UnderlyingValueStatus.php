<?php

namespace Wolo\Contracts;

interface UnderlyingValueStatus
{
    /**
     * Has conditional value
     * When value is string and "1", "true", "on", and "yes" then returns true
     * Otherwise it validates using empty()
     *
     * @example ok('true'); //true
     * @example ok('false'); //false
     * @example ok(true); //true
     * @example ok(false); //false
     * @example ok(1); //true
     * @example ok(0); //false
     * @example ok('1'); //true
     * @example ok('0'); //false
     * @example ok('hello world'); //true
     * @example ok(''); //false
     * @link https://www.php.net/manual/en/function.filter-var.php
     */
    public function ok(): bool;

    /**
     * Has NOT conditional value
     * When value is string and "1", "true", "on", and "yes" then returns true
     * Otherwise it validates using empty()
     *
     * @example ok('true'); //true
     * @example ok('false'); //false
     * @example ok(true); //true
     * @example ok(false); //false
     * @example ok(1); //true
     * @example ok(0); //false
     * @example ok('1'); //true
     * @example ok('0'); //false
     * @example ok('hello world'); //true
     * @example ok(''); //false
     * @link https://www.php.net/manual/en/function.filter-var.php
     */
    public function notOk(): bool;


    /**
     * Determine if the underl is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool;
}