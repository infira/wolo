<?php

namespace Wolo;

/**
 * Class to dump variable info
 */
class VarDumper
{
    private static function getHeader(string $name): ?string
    {
        foreach (getallheaders() as $header => $value) {
            if (strtolower($header) === strtolower($name)) {
                return $value;
            }
        }

        return null;
    }

    private static function isConsole(): bool
    {
        if (!defined('PHP_SAPI')) {
            return false;
        }

        return str_contains(PHP_SAPI, 'cli');
    }

    public static function debug(...$vars): void
    {
        foreach ($vars as $var) {
            if (self::isConsole()) {
                echo self::console($var);
            }
            else {
                echo self::pre($var);
            }
        }
    }

    public static function pre($var): string
    {
        return '<pre>'.self::dump($var).'</pre>';
    }

    public static function console($var): string
    {
        return self::dump($var);
    }

    /**
     * Dump variable into printable string
     *
     * @param  mixed  $var
     * @return string
     */
    public static function dump(mixed $var): string
    {
        if (is_array($var) || is_object($var)) {
            return print_r($var, true);
        }

        return self::varDump($var);
    }

    public static function varDump(mixed $var): string
    {
        ob_start();
        var_dump($var);

        return ob_get_clean();
    }
}
