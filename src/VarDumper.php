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

    private static function isJSON(): bool
    {
        return str_contains(self::getHeader('accept'), 'json');
    }

    public static function debug(...$vars)
    {
        foreach ($vars as $var) {
            if (self::isConsole()) {
                self::console($vars);
            }
            elseif (self::isJSON()) {
                self::json($var);
            }
            else {
                self::browser($var);
            }
        }
    }

    public static function pre($var)
    {
        echo '<pre>' . self::dump($var) . '</pre>';
    }

    public static function json($var)
    {
        echo json_encode($var);
    }

    public static function console($var)
    {
        echo self::dump($var);
    }

    /**
     * Dump variable into printable string
     *
     * @param mixed $var
     * @return string
     */
    public static function dump($var): string
    {
        if (is_array($var) or is_object($var)) {
            return print_r($var, true);
        }
        else {
            ob_start();
            var_dump($var);

            return ob_get_clean();
        }
    }
}
