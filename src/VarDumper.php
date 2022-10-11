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
        if (count($vars) === 3 && $vars[1] === '=>') {
            self::debug([$vars[0] => $vars[2]]);
        }
        elseif (count($vars) === 2 && str_ends_with($vars[0], ':=>')) {
            self::debug([substr($vars[0], 0, -3) => $vars[1]]);
        }
        else {
            foreach ($vars as $var) {
                if (self::isConsole()) {
                    echo self::console($var);
                }
                else {
                    echo self::pre($var);
                }
            }
        }
    }

    public static function pre($var): string
    {
        return '<pre>' . self::dump($var) . '</pre>';
    }

    public static function console($var): string
    {
        return self::dump($var);
    }

    /**
     * Dump variable into printable string
     *
     * @param mixed $var
     * @return string
     */
    public static function dump(mixed $var): string
    {
        if (is_array($var) || is_object($var)) {
            return print_r($var, true);
        }
        ob_start();
        var_dump($var);

        return ob_get_clean();
    }
}
