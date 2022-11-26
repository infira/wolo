<?php

namespace Wolo\Request;

use RuntimeException;
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
     * @param  string  $key
     * @param  mixed  $value
     * @param  int|string  $expires  - when expires. (int)0 - forever,(string)"10 hours" -  will be converted to time using strtotime(), (int)1596885301 - timestamp. If $expires is in the past, it will be converted as forever.
     * @param  bool  $secure  Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to TRUE, the cookie will only be set if a secure connection exists. On the server-side, it's on the programmer to send this kind of cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]).
     * @see https://www.php.net/manual/en/function.setcookie.php
     */
    public static function set(string $key, mixed $value, int|string $expires = 0, bool $secure = true): void
    {
        $cookie_host = preg_replace('|^www\.(.*)$|', '.\\1', $_SERVER['HTTP_HOST']);
        if (is_string($expires)) {
            if ($expires[0] !== "+") {
                $expires = "+$expires";
            }
            $expiresInTime = strtotime($expires);
        }
        elseif (is_numeric($expires)) {
            $expiresInTime = (int)$expires;
        }
        else {
            $expiresInTime = 0;
        }

        if ($expiresInTime === 0) {
            $expires = 2147483640;
        }
        $_COOKIE[$key] = $value;
        setcookie($key, $value, $expires, "/", $cookie_host, $secure);
    }

    public static function delete(string|int|array $keys): void
    {
        foreach ((array)$keys as $key) {
            if (static::has($key)) {
                //Actually, there is not a way to directly delete a cookie. Just use setcookie with expiration date in the past, to trigger the removal mechanism in your browser. https://www.pontikis.net/blog/create-cookies-php-javascript
                static::instance()->delete($key);
                // empty value and expiration one hour before
                setcookie($key, '', time() - 3600);
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