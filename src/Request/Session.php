<?php

namespace Wolo\Request;

use Wolo\Request\Support\InstanceShortcuts;
use Wolo\Request\Support\RequestVariableCollection;

class Session extends InstanceShortcuts
{
    private static RequestVariableCollection $_session;

    protected static string|null $sessionId = null;

    /**
     * Is session expired
     *
     * @var boolean
     */
    protected static bool $isExpired = false;

    /**
     * Is php session started with session_start()
     *
     * @var bool
     */
    protected static bool $isStarted = false;
    protected static ?int $uptime = null;

    /**
     * @see https://www.php.net/manual/en/session.configuration.php
     * @var array
     */
    protected static array $startOptions = [
        'cookie_lifetime' => 0
    ];

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
     * @param  string|null  $sessionId  string|null  $sessionId  - start session with id
     * @param  array|null  $startOptions
     * @see https://www.php.net/manual/en/session.configuration.php
     */
    public static function init(string $sessionId = null, array $startOptions = null): void
    {
        if ($startOptions !== null) {
            self::setStartOptions($startOptions);
        }
        if (!static::$isStarted && (int)ini_get('session.auto_start') === 0) {
            static::start($sessionId);
        }

        if (self::$uptime !== null) {
            $upTime = static::get('_sessionUpdateTime', time());
            if ($upTime > 0 && (time() - $upTime) > static::$uptime) {
                static::destroy(true);
                static::$isExpired = true;
            }
            else {
                static::$isExpired = false;
            }
            self::updateUpTime();
        }
    }

    /**
     * Retrieves a 32bit session id hash
     *
     * @return string
     * @see self::getId()
     * @deprecated
     */
    public static function getSID(): string
    {
        return static::$sessionId;
    }

    /**
     * Retrieves a 32bit session id hash
     *
     * @return string
     */
    public static function getId(): string
    {
        return static::$sessionId;
    }

    /**
     * Set a 32bit session id hash
     *
     * @param  string  $id
     */
    private static function setId(string $id): void
    {
        static::$sessionId = $id;
    }

    /**
     * Destroy session
     *
     * @param  bool  $startNewSession
     */
    public static function destroy(bool $startNewSession = false): void
    {
        session_destroy();
        static::$isStarted = false;
        if ($startNewSession) {
            self::start(session_create_id());
            self::updateUpTime();
        }
    }

    /**
     * @param  string|null  $sessionId  - start session with id
     * @return void
     */
    public static function start(string $sessionId = null): void
    {
        if ($sessionId) {
            session_id($sessionId);
        }
        $opts = array_merge(self::$startOptions);
        static::$isStarted = session_start($opts);
        static::$_session = new RequestVariableCollection($_SESSION);
        static::setId(session_id());
    }

    public static function close(): void
    {
        session_write_close();
    }

    /**
     * Checks is session uptime exceeded
     *
     * @return bool
     */
    public static function isExpired(): bool
    {
        return static::$isExpired;
    }

    /**
     * Used in init to manualy handle session uptime, when uptime is exceeded session will be destroyd and new one started
     *
     * @param  int  $seconds
     */
    public static function setUptime(int $seconds): void
    {
        static::$uptime = $seconds;
    }

    /**
     * @param  array  $options
     * @see https://www.php.net/manual/en/session.configuration.php
     */
    public static function setStartOptions(array $options): void
    {
        static::$startOptions = $options;
    }

    private static function updateUpTime(): void
    {
        if (self::$uptime !== null) {
            static::set('_sessionUpdateTime', time());
        }
    }
}