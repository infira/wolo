<?php

namespace Wolo\Request;

use JetBrains\PhpStorm\NoReturn;
use Wolo\Request\Support\InstanceShortcuts;
use Wolo\Request\Support\RequestVariableCollection;
use Wolo\Request\Support\ServerCollection;
use Wolo\Request\Support\Traits\FileShortcuts;
use Wolo\Request\Support\Traits\GetShortcuts;
use Wolo\Request\Support\Traits\PostShortcuts;
use Wolo\Request\Support\Traits\ServerShortcuts;

class Http extends InstanceShortcuts
{
    private static RequestVariableCollection $request;
    private static RequestVariableCollection $post;
    private static RequestVariableCollection $get;
    private static RequestVariableCollection $file;
    private static ServerCollection $server;
    use GetShortcuts;
    use PostShortcuts;
    use ServerShortcuts;
    use FileShortcuts;

    protected static function instance(): RequestVariableCollection
    {
        return static::request();
    }


    /**
     * $_SERVER values
     *
     * @return RequestVariableCollection
     */
    public static function post(): RequestVariableCollection
    {
        if (!isset(static::$post)) {
            static::$post = new RequestVariableCollection($_POST);
        }

        return static::$post;
    }

    /**
     * $_REQUEST values
     *
     * @return RequestVariableCollection
     */
    public static function request(): RequestVariableCollection
    {
        if (!isset(static::$request)) {
            static::$request = new RequestVariableCollection($_REQUEST);
        }

        return static::$request;
    }

    /**
     * $_GET values
     *
     * @return RequestVariableCollection
     */
    public static function url(): RequestVariableCollection
    {
        if (!isset(static::$get)) {
            static::$get = new RequestVariableCollection($_GET);
        }

        return static::$get;
    }

    /**
     * $_FILES values
     *
     * @return RequestVariableCollection
     */
    public static function file(): RequestVariableCollection
    {
        if (!isset(static::$file)) {
            static::$file = new RequestVariableCollection($_FILES);
        }

        return static::$file;
    }

    /**
     * $_SERVER values
     *
     * @link https://www.php.net/manual/en/reserved.variables.server.php
     *
     * @return ServerCollection
     */
    public static function server(): ServerCollection
    {
        if (!isset(static::$server)) {
            static::$server = new ServerCollection($_SERVER);
        }

        return static::$server;
    }

    /**
     * $_SERVER["REQUEST_METHOD"] == 'post'
     *
     * @return bool
     */
    public static function isPost(): bool
    {
        return static::requestMethod() === 'post';
    }

    /**
     * $_SERVER["REQUEST_METHOD"] == 'patch'
     *
     * @return bool
     */
    public static function isPatch(): bool
    {
        return static::requestMethod() === 'patch';
    }

    /**
     * $_SERVER["REQUEST_METHOD"] == 'option'
     *
     * @return bool
     */
    public static function isOption(): bool
    {
        return static::requestMethod() === 'option';
    }

    /**
     * Get request method $_SERVER["REQUEST_METHOD"]
     *
     * @param bool $inLowercase
     * @return string|null
     */
    public static function requestMethod(bool $inLowercase = true): ?string
    {
        $rm = $_SERVER["REQUEST_METHOD"] ?? null;
        if ($rm && $inLowercase) {
            $rm = strtolower($rm);
        }

        return $rm;
    }


    /**
     * is current request ajax type
     *
     * @return bool
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Does current request accept json
     *
     * @return bool
     */
    public static function acceptJSON(): bool
    {
        if (!isset($_SERVER['HTTP_ACCEPT'])) {
            return false;
        }
        if (str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            return true;
        }

        return false;
    }

    /**
     * Got to link
     *
     * @param string $link - where to go
     */
    #[NoReturn] public static function go(string $link): void
    {
        header('Location: '.str_replace('&amp;', '&', $link));
        exit;
    }

    /**
     * Redirect page using 301 header
     *
     * @param string $link - where to go
     */
    #[NoReturn] public static function go301(string $link): void
    {
        Header("HTTP/1.1 301 Moved Permanently", true, 301);
        static::go($link);
    }

    /**
     * Redirect to referer url
     *
     * @param string $addExtraToRefLink - add extra params to link
     */
    #[NoReturn] public static function goToReferer(string $addExtraToRefLink = ''): void
    {
        $link = static::server()->referer().$addExtraToRefLink;
        static::go($link);
    }

    public static function referer(): ?string
    {
        return static::server()->referer();
    }

    public static function currentUrl(): string
    {
        $url = 'http';
        if (isset($_SERVER['HTTPS'])) {
            $isHttps = strtolower($_SERVER['HTTPS']);
            if ($isHttps === 'on') {
                $url .= 's';
            }
        }

        return $url.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    /**
     * Get current url path without query parameters
     * @return string
     *
     */
    public static function currentUrlQueryPath(): string
    {
        return explode('?', static::currentUrl())[0] ?? '';
    }

    /**
     * $_SERVER["HTTP_HOST"]
     *
     * @return string|null
     */
    public static function host(): ?string
    {
        return static::server()->host();
    }

    /**
     * Get user IP
     * https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
     *
     * @return string
     */
    public static function ip(): string
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        }
        elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }
        elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        }
        elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }
        else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    public static function getHeaders(bool $lowerKeys = false): array
    {
        $headers = apache_request_headers();
        if (!$headers) {
            return [];
        }
        if (!$lowerKeys) {
            return $headers;
        }
        $output = [];
        foreach ($headers as $headerKey => $value) {
            $output[strtolower($headerKey)] = $value;
        }
        return $output;
    }

    public static function hasHeader(string $key): bool
    {
        return array_key_exists($key, self::getHeaders(true));
    }

    public static function getHeader(string $key, mixed $default = null): mixed
    {
        return self::getHeaders(true)[strtolower($key)] ?? $default;
    }
}