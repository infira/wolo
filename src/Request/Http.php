<?php

namespace Wolo\Request;

use JetBrains\PhpStorm\NoReturn;
use RuntimeException;

//require_once '../../vendor/autoload.php';

/**
 * @method static array all() - all values from $_REQUEST
 * @method static mixed get(string $key = null, mixed $default = null) - get from $_REQUEST
 * @method static mixed set(string $key, $value) - set to $_REQUEST
 * @method static mixed delete(string $key) - delete from $_REQUEST
 * @method static mixed unset(string $key) - delete from $_REQUEST
 * @method static bool exists(string $key) - does $key exists in $_REQUEST
 * @method static bool has(string $key) - does $key exists in $_REQUEST
 * @method static mixed flush() - flush $_REQUEST
 *
 * @method static array allGET() - all values from $_GET
 * @method static mixed getGET(string $key = null, mixed $default = null) - get from $_GET
 * @method static mixed setGET(string $key, $value) - set to $_GET
 * @method static mixed deleteGET(string $key) - delete from $_GET
 * @method static mixed unsetGET(string $key) - delete from $_GET
 * @method static bool existsGET(string $key) - does $key exists in $_GET
 * @method static bool hasGET(string $key) - does $key exists in $_GET
 * @method static mixed flushGET() - flush $_GET
 *
 * @method static array allPOST() - all values from $_POST
 * @method static mixed getPOST(string $key = null, mixed $default = null) - get from $_POST
 * @method static mixed setPOST(string $key, $value) - set to $_POST
 * @method static mixed deletePOST(string $key) - delete from $_POST
 * @method static mixed unsetPOST(string $key) - delete from $_POST
 * @method static bool existsPOST(string $key) - does $key exists in $_POST
 * @method static bool hasPOST(string $key) - does $key exists in $_POST
 * @method static mixed flushPOST() - flush $_POST
 *
 * @method static array allFILE() - all values from $_FILES
 * @method static mixed getFILE(string $key = null, mixed $default = null) - get from $_FILES
 * @method static mixed setFILE(string $key, $value) - set to $_FILES
 * @method static mixed deleteFILE(string $key) - delete from $_FILES
 * @method static mixed unsetFILE(string $key) - delete from $_FILES
 * @method static bool existsFILE(string $key) - does $key exists in $_FILES
 * @method static bool hasFILE(string $key) - does $key exists in $_FILES
 * @method static mixed flushFILE() - flush $_FILES
 *
 * @method static array allSERVER() - all values from $_SERVER
 * @method static mixed getSERVER(string $key = null, mixed $default = null) - get from $_SERVER
 * @method static mixed setSERVER(string $key, $value) - set to $_SERVER
 * @method static bool existsSERVER(string $key) - does $key exists in $_SERVER
 * @method static bool hasSERVER(string $key) - does $key exists in $_SERVER
 */
class Http
{
    private static RequestVariableCollection $_request;
    private static RequestVariableCollection $_post;
    private static RequestVariableCollection $_get;
    private static RequestVariableCollection $_file;
    private static ServerCollection $_server;

    public static function __callStatic(string $name, array $arguments)
    {
        if (in_array($name, ['get', 'set', 'delete', 'unset', 'exists', 'has', 'flush'])) {
            return self::request()->$name(...$arguments);
        }
        if (in_array($name, ['getPOST', 'setPOST', 'deletePOST', 'unsetPOST', 'existsPOST', 'hasPOST', 'flushPOST'])) {
            $name = substr($name, -4);

            return self::post()->$name(...$arguments);
        }
        if (in_array($name, ['getGET', 'setGET', 'deleteGET', 'unsetGET', 'existsGET', 'hasGET', 'flushGET'])) {
            $name = substr($name, 0, -3);

            return self::url()->$name(...$arguments);
        }
        if (in_array($name, ['getFILE', 'setFILE', 'deleteFILE', 'unsetFILE', 'existsFILE', 'hasFILE', 'flushFILE'])) {
            $name = substr($name, -4);

            return self::file()->$name(...$arguments);
        }
        if (in_array($name, ['getSERVER', 'existsSERVER', 'hasSERVER'])) {
            $name = substr($name, -4);

            return self::server()->$name(...$arguments);
        }
        throw new RuntimeException("unknown method('$name')");
    }

    /**
     * $_SERVER values
     *
     * @return RequestVariableCollection
     */
    public static function post(): RequestVariableCollection
    {
        if (!isset(self::$_post)) {
            self::$_post = new RequestVariableCollection($_POST);
        }

        return self::$_post;
    }

    /**
     * $_REQUEST values
     *
     * @return RequestVariableCollection
     */
    public static function request(): RequestVariableCollection
    {
        if (!isset(self::$_request)) {
            self::$_request = new RequestVariableCollection($_GET);
        }

        return self::$_request;
    }

    /**
     * $_GET values
     *
     * @return RequestVariableCollection
     */
    public static function url(): RequestVariableCollection
    {
        if (!isset(self::$_get)) {
            self::$_get = new RequestVariableCollection($_GET);
        }

        return self::$_get;
    }

    /**
     * $_FILES values
     *
     * @return RequestVariableCollection
     */
    public static function file(): RequestVariableCollection
    {
        if (!isset(self::$_file)) {
            self::$_file = new RequestVariableCollection($_FILES);
        }

        return self::$_file;
    }

    /**
     * $_SERVER values
     *
     * @see https://www.php.net/manual/en/reserved.variables.server.php
     *
     * @return ServerCollection
     */
    public static function server(): ServerCollection
    {
        if (!isset(self::$_server)) {
            self::$_server = new ServerCollection($_SERVER);
        }

        return self::$_server;
    }

    /**
     * $_SERVER["REQUEST_METHOD"] == 'post'
     *
     * @return bool
     */
    public static function isPost(): bool
    {
        return self::requestMethod() === 'post';
    }

    /**
     * $_SERVER["REQUEST_METHOD"] == 'patch'
     *
     * @return bool
     */
    public static function isPatch(): bool
    {
        return self::requestMethod() === 'patch';
    }

    /**
     * $_SERVER["REQUEST_METHOD"] == 'option'
     *
     * @return bool
     */
    public static function isOption(): bool
    {
        return self::requestMethod() === 'option';
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
        header('Location: ' . str_replace('&amp;', '&', $link));
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
        self::go($link);
    }

    /**
     * Redirect to referer url
     *
     * @param string $addExtraToRefLink - add extra params to link
     */
    #[NoReturn] public static function goToReferer(string $addExtraToRefLink = ''): void
    {
        $link = self::server()->referer() . $addExtraToRefLink;
        self::go($link);
    }

    public static function referer(): ?string
    {
        return self::server()->referer();
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

        return $url . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * $_SERVER["HTTP_HOST"]
     *
     * @return string|null
     */
    public static function host(): ?string
    {
        return self::server()->host();
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
}