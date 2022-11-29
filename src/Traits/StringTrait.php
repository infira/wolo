<?php

namespace Wolo\Traits;

use Wolo\Hash;
use Wolo\Is;
use Wolo\VarDumper;

trait StringTrait
{
    /**
     * Simple string templating
     *
     * @param  mixed  $template
     * @param  array  $vars
     * @param  string|array  $syntax
     * @return string
     * @example render('my name is {name}',['name' => 'gen']) // 'my name is gen'
     */
    public static function vars(mixed $template, array $vars, string|array $syntax = '{}'): string
    {
        $map = [];
        foreach ($vars as $name => $value) {
            foreach ((array)$syntax as $singleSyntax) {
                [$start, $end] = mb_str_split($singleSyntax, 1);
                $map[$start.$name.$end] = $value;
            }
        }

        return strtr($template, $map);
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
     * @param  string|iterable<string>  $pattern
     * @param  string  $value
     * @return bool
     * @author https://github.com/illuminate/support/blob/master/Str.php
     */
    public static function is(string|iterable $pattern, $value): bool
    {
        $value = (string)$value;

        if (!is_iterable($pattern)) {
            $pattern = [$pattern];
        }

        foreach ($pattern as $pattern) {
            $pattern = (string)$pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === $value) {
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
     * @see static::basename()
     * @deprecated
     */
    public static function classBasename(string|object $class): string
    {
        return static::basename($class);
    }

    /**
     * Turns \My\CoolNamespace\MyClass into myClass
     * works as well with /my/path
     *
     * @param  string|object  $value
     * @return string
     */
    public static function basename(string|object $value): string
    {
        $value = is_object($value) ? get_class($value) : $value;

        return basename(str_replace('\\', '/', $value));
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
     * @see VarDumper::varDump
     * @deprecated
     */
    public static function dump(mixed $var): string
    {
        return VarDumper::varDump($var);
    }

    /**
     * can string be converted to string using (string)$var
     * @param  mixed  $var
     * @return bool
     * @see Is::stringable()
     * @deprecated
     */
    public static function isStringable(mixed $var): bool
    {
        return Is::stringable($var);
    }

    /**
     * @see Hash::md5()
     * @deprecated
     */
    public static function md5(...$data): string
    {
        return Hash::md5(...$data);
    }

    /**
     * @see Hash::sha1()
     * @deprecated
     */
    public static function sha1(...$data): string
    {
        return Hash::sha1(...$data);
    }

    /**
     * @see Hash::crc32b()
     * @deprecated
     */
    public static function crc32b(...$data): string
    {
        return Hash::crc32b(...$data);
    }

    /**
     * @see Hash::sha512()
     * @deprecated
     */
    public static function sha512(...$data): string
    {
        return Hash::sha512(...$data);
    }

    /**
     * @see Hash::make()
     * @deprecated
     */
    public static function hash(string $algo = 'md5', ...$data): string
    {
        return Hash::make($algo, ...$data);
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