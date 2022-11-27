<?php

namespace Wolo\Request;

use Wolo\Request\Support\InstanceShortcuts;
use Wolo\Request\Support\RequestVariableCollection;

class Session extends InstanceShortcuts
{
    protected static string|null $SID = null;

    /**
     * Is session expired
     *
     * @var boolean
     */
    private static bool $isExpired = false;

    /**
     * Is php session started with session_start()
     *
     * @var bool
     */
    public static bool $isStarted = false;

    private static string|null $sessionName;
    private static int $timeout = 86400;
    private static RequestVariableCollection $_session;

    protected static function instance(): RequestVariableCollection
    {
        if (!isset(static::$_session)) {
            static::$_session = new RequestVariableCollection($_SESSION);
        }

        return static::$_session;
    }

    /**
     * Config sessions
     *
     * @param  string  $sessionName  - name of the PHP session
     * @param  string|null  $SID  start or restore session with own provided session ID,
     */
    public static function init(string $sessionName = 'PHPSESSID', string $SID = null): void
    {
        if ($sessionName !== 'PHPSESSID') {
            $sessionName = "PHPSESSID_$sessionName";
        }
        static::$sessionName = $sessionName;
        if (!static::$isStarted) {
            static::$isStarted = true;
            if ((int)ini_get('session.auto_start') === 0) {
                static::start($SID);
            }
        }
        static::setSID(session_id());


        $upTime = static::get('_sessionUpdateTime', time());
        $between = time() - $upTime;
        if ($between > static::$timeout && $upTime > 0) {
            static::destroy(true);
            static::$isExpired = true;
        }
        else {
            static::$isExpired = false;
        }
        //debug(static::$sessionName);
        //debug($_SESSION);
        //debug('------------------------------------------------');
        static::set('_sessionUpdateTime', time());
    }

    /**
     * Retrieves a 32bit session id hash
     *
     * @return string
     */
    public static function getSID(): string
    {
        return static::$SID;
    }

    /**
     * Set a 32bit session id hash
     *
     * @param  string  $SID
     */
    private static function setSID(string $SID): void
    {
        static::$SID = $SID;
    }

    /**
     * Destroy session
     *
     * @param  bool  $takeNewID  - take new session ID
     */
    public static function destroy(bool $takeNewID = true): void
    {
        static::flush();
        session_unset();
        session_destroy();
        if (static::$sessionName) {
            setcookie(static::$sessionName, '', 1);
            session_name(static::$sessionName);
            session_set_cookie_params(static::$timeout);
        }
        static::start(); //start new session
        //take new session ID
        if ($takeNewID) {
            session_regenerate_id(true);
            $SID = session_id();
            static::setSID($SID);
        }
        unset($_COOKIE[session_name()]);
    }

    public static function close(): void
    {
        session_write_close();
    }

    /**
     * @link https://stackoverflow.com/questions/3185779/the-session-id-is-too-long-or-contains-illegal-characters-valid-characters-are
     * @return bool
     */
    private static function doStart(): bool
    {
        if (Cookie::has(static::$sessionName)) {
            $sessid = Cookie::get(static::$sessionName);
        }
        else {
            return session_start();
        }

        if (!preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $sessid)) {
            return false;
        }

        return session_start();
    }

    private static function start(string $SID = null): void
    {
        if ($SID) {
            session_id($SID);
            Cookie::set(static::$sessionName, $SID);
        }
        if (static::$sessionName) {
            session_name(static::$sessionName);
        }
        session_set_cookie_params(static::$timeout);
        if (!static::doStart()) {
            session_id(uniqid('', true));
            session_start();
            session_regenerate_id();
        }
    }

    /**
     * Checks is session expired
     *
     * @return bool
     */
    public static function isExpired(): bool
    {
        return static::$isExpired;
    }

    /**
     * @param  int  $timeout
     */
    public static function setTimeout(int $timeout): void
    {
        static::$timeout = $timeout;
    }
}