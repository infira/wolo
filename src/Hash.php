<?php

namespace Wolo;

use Closure;
use Exception;
use ReflectionException;
use ReflectionFunction;
use Serializable;
use stdClass;

class Hash
{
    /**
     * Basically convert any value to string
     * Use this cases use this to
     *
     * @param  mixed  ...$data
     * @return string
     * @throws ReflectionException
     * @throws Exception
     * @example md5(Hash::hashable(value)) , or hash('algo',Hash::hashable(value))
     */
    public static function hashable(...$data): string
    {
        $output = [];
        foreach ($data as $value) {
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
            elseif (Is::stringable($value)) {
                $value = (string)$value;
            }
            else {
                $value = VarDumper::varDump($value);
            }
            $output[] = preg_replace('![\s]+!u', '', $value);
        }

        return implode('-', $output);
    }


    /**
     * make hash from any typeof value using $algorithm
     * @param  string  $algo
     * @param ...$data
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash-algos.php
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function make(string $algo = 'md5', ...$data): string
    {
        return hash($algo, static::hashable(...$data));
    }

    /**
     * make hash using md5 algorithm
     * @param ...$data
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function md5(...$data): string
    {
        return static::make('md5', ...$data);
    }

    /**
     * make hash using sha1 algorithm
     * @param ...$data
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function sha1(...$data): string
    {
        return static::make('md5', ...$data);
    }

    /**
     * make hash using crc32b algorithm
     * @param ...$data
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function crc32b(...$data): string
    {
        return static::make('md5', ...$data);
    }

    /**
     * make hash using sha512 algorithm
     * @param ...$data
     * @return string
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/function.hash.php
     */
    public static function sha512(...$data): string
    {
        return static::make('md5', ...$data);
    }
}