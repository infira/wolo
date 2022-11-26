<?php

namespace Wolo\Traits;

use Closure;
use Exception;
use ReflectionException;
use ReflectionFunction;
use Serializable;
use stdClass;
use Stringable;
use Wolo\Regex;

trait StringTrait
{
    /**
     * Simple string templating
     *
     * @param  string  $string
     * @param  array  $vars
     * @param  string  $syntax
     * @return string
     */
    public static function vars(string $string, array $vars, string $syntax = '{}'): string
    {
        $start = $syntax[0];
        $end = $syntax[1] ?? $syntax[0];
        foreach ($vars as $name => $value) {
            $string = str_replace([$start.$name.$end], $value, $string);
        }

        return $string;
    }

    public static function endsWith(string $string, string $width): bool
    {
        return str_ends_with($string, $width);
    }

    public static function startsWith(string $string, string $width): bool
    {
        return str_starts_with($string, $width);
    }

    /**
     * Determine if a given string matches a given pattern.
     * Used Laravel Str::is for based, added little more functionality, it detects if pattern is already regular expression
     *
     * @param  string|array  $patterns
     * @param  string  $value
     * @return bool
     * @author https://github.com/illuminate/support/blob/master/Str.php
     */
    public static function is(string|array $patterns, string $value): bool
    {
        if (empty($patterns)) {
            return false;
        }
        $patterns = (array)$patterns;

        foreach ($patterns as $pattern) {
            $pattern = (string)$pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === $value) {
                return true;
            }

            if (Regex::isPattern($pattern) && preg_match($pattern, $value) === 1) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^'.$pattern.'\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * generate document no from ID
     * e.g. ID of docNo(1234,6) becomes 001234
     *
     * @param  int  $documentID
     * @param  int  $length
     * @return string
     */
    public static function docNo(int $documentID, int $length = 6): string
    {
        return str_repeat("0", ($length - strlen($documentID))).$documentID;
    }

    /**
     * Turns \My\CoolNamespace\MyClass into myClass
     *
     * @param  string|object  $class
     * @return string
     */
    public static function classBasename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Generates v4 UUID
     *
     * @return string
     */
    public static function UUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',        // 32 bits for
            // "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     * @throws Exception
     * @author https://github.com/illuminate/support/blob/master/Str.php
     */
    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Dump variable into printable string
     *
     * @param  mixed  $var
     * @return string
     */
    public static function dump(mixed $var): string
    {
        ob_start();
        var_dump($var);

        return ob_get_clean();
    }

    /**
     * can string be converted to string using (string)$var
     * @param  mixed  $var
     * @return bool
     */
    public static function isStringable(mixed $var): bool
    {
        return is_string($var)
            || is_int($var)
            || is_float($var)
            || $var instanceof Stringable;
    }

    /**
     * Generate any variable to hashable string
     * Use cases use this to md5(Str::hashable(value), or hash('algo',Str::hashable(value))
     *
     * @param  mixed  ...$hashable
     * @return string
     * @throws ReflectionException
     * @throws Exception
     */
    public static function hashable(...$hashable): string
    {
        $output = [];
        foreach ($hashable as $value) {
            if ($value instanceof Closure) {
                $ref = new ReflectionFunction($value);
                $value = $ref->__toString();
                $value = preg_replace('/@@.+/', '', $value);//remove file location
                $value = self::hashable($value, $ref->getStaticVariables());
            }

            elseif ($value instanceof Serializable) {
                $value = $value->serialize();
            }
            elseif (is_array($value) || $value instanceof stdClass) {
                $valueDump = [];
                foreach ((array)$value as $k => $v) {
                    $valueDump[self::hashable($k)] = self::hashable($v);
                }
                $value = serialize($valueDump);
            }
            elseif (static::isStringable($value)) {
                $value = (string)$value;
            }
            else {
                $value = static::dump($value);
            }
            $output[] = preg_replace('![\s]+!u', '', $value);
        }

        return implode('-', $output);
    }

    /**
     * make hash using md5 algorithm
     * @param ...$hashable
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function md5(...$hashable): string { return static::hash('md5', ...$hashable); }

    /**
     * make hash using sha1 algorithm
     * @param ...$hashable
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function sha1(...$hashable): string { return static::hash('md5', ...$hashable); }

    /**
     * make hash using crc32b algorithm
     * @param ...$hashable
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function crc32b(...$hashable): string { return static::hash('md5', ...$hashable); }

    /**
     * make hash using sha512 algorithm
     * @param ...$hashable
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function sha512(...$hashable): string { return static::hash('md5', ...$hashable); }

    /**
     * make hash from any typeof value using $algorithm
     * @param  string  $algo
     * @param ...$hashable
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash-algos.php
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function hash(string $algo = 'md5', ...$hashable): string
    {
        return hash($algo, static::hashable(...$hashable));
    }

    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}