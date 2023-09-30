<?php

namespace Wolo\Request;

use Wolo\Request\Support\InstanceShortcuts;
use Wolo\Request\Support\RequestVariableCollection;

class Cookie extends InstanceShortcuts
{
    private static RequestVariableCollection $_cookie;

    protected static function instance(): RequestVariableCollection
    {
        if (!isset(self::$_cookie)) {
            self::$_cookie = new RequestVariableCollection($_COOKIE);
        }

        return self::$_cookie;
    }

    /**
     * Set item to $_COOKIE
     *
     * @param string $key
     * @param mixed $value
     * @param int|string|array $expires_or_options - when expires. (int)0 - forever,(string)"10 hours" -  will be converted to time using strtotime(), (int)1596885301 - timestamp. If $expires is in the past, it will be converted as forever.
     * @link https://www.php.net/manual/en/function.setcookie.php
     * @example
     * $options = [
     *  'expires' => 0,
     *  'path' => '/',
     *  'domain' => $cookie_host, // leading dot for compatibility or use subdomain, defaults to .$_SERVER['HTTP_HOST']
     *  'secure' =>  isset($_SERVER['HTTPS'])
     *  'httponly' => false,    // or false
     *  'samesite' => 'none' // None || Lax  || Strict
     * ];
     */
    public static function set(string $key, mixed $value, int|string|array $expires_or_options = 0): void
    {
        $options = [
            'expires' => 0,
            'path' => '/',
            'domain' => preg_replace('|^www\.(.*)$|', '.\\1', $_SERVER['HTTP_HOST']),
            'secure' => isset($_SERVER['HTTPS']),     // or false
            'httponly' => false,    // or false
            'samesite' => 'none' // None || Lax  || Strict
        ];
        if (is_array($expires_or_options)) {
            $options = array_merge($options, $expires_or_options);
        }
        else {
            $options['expires'] = $expires_or_options;
            $options['secure'] = isset($_SERVER['HTTPS']);
        }

        if (is_string($options['expires'])) {
            $options['expires'] = strtotime($options['expires']);
        }
        else if (is_numeric($options['expires'])) {
            $options['expires'] = (int)$options['expires'];
        }
        else {
            $options['expires'] = 0;
        }
        if ($options['expires'] === 0) {
            $options['expires'] = 2147483640;;
        }

        setcookie($key, $value, $options);
        self::instance()->set($key, $value);
    }

    public static function delete(string|int|array $keys): void
    {
        foreach ((array)$keys as $key) {
            if (static::has($key)) {
                // empty value and expiration one hour before
                self::set($key, '', ['expires' => time() - 3600]);
                //Actually, there is not a way to directly delete a cookie. Just use setcookie with expiration date in the past, to trigger the removal mechanism in your browser. https://www.pontikis.net/blog/create-cookies-php-javascript
                static::instance()->delete($key);
            }
        }
    }

    /**
     * @see static::delete() for replacement
     * @deprecated
     */
    public static function unset(string|int|array $keys): void
    {
        self::delete($keys);
    }
}