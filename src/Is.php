<?php

namespace Wolo;

use Stringable;

class Is
{
    /**
     * Is a valid email address
     * https://www.php.net/manual/en/function.filter-var.php
     * @param  string  $address
     * @return bool
     */
    public static function email(string $address): bool
    {
        return (bool)filter_var($address, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if the $nr is between $from AND $to
     *
     * @param  float|int|string  $nr  - nr to check
     * @param  float|int|string  $from  - between start
     * @param  float|int|string  $to  - between end
     * @return boolean
     */
    public static function between(float|int|string $nr, float|int|string $from, float|int|string $to): bool
    {
        return ($nr >= $from and $nr <= $to);
    }

    /**
     * can string be converted to string using (string)$value
     * @param  mixed  $value
     * @return bool
     */
    public static function stringable(mixed $value): bool
    {
        return is_scalar($value)
            || is_null($value)
            || (is_object($value) && method_exists($value, '__toString'));
    }

    public static function match(string $pattern, $subject): bool
    {
        return (bool)Regex::match($pattern, $subject);
    }

    /**
     * Has conditional value
     * When value is string and "1", "true", "on", and "yes" then returns true
     * Otherwise it validates using empty()
     * @link https://www.php.net/manual/en/function.filter-var.php
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
     */
    public static function ok(mixed $value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return false;
            }
            $is = filter_var(trim($value), FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);
            if (is_bool($is)) {
                return $is;
            }
        }

        return !empty($value);
    }

    /** @see static::ok() */
    public static function notOk(mixed $value): bool
    {
        return $value;
    }
}