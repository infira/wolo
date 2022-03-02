<?php

namespace Wolo\Request;

use Exception;

/**
 * @method static array all() - all values from $_SESSION
 * @method static mixed get(string $key, mixed $default = null) - get from $_SESSION
 * @method static mixed set(string $key, $value) - set to $_SESSION
 * @method static mixed delete(string $key) - delete from $_SESSION
 * @method static mixed unset(string $key) - delete from $_SESSION
 * @method static bool exists(string $key) - does $key exists in $_SESSION
 * @method static bool has(string $key) - does $key exists in $_SESSION
 * @method static mixed flush() - flush $_SESSION
 */
class Session
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
	
	private static string|null               $sessionName;
	private static int                       $timeout = 86400;
	private static RequestVariableCollection $_session;
	
	public static function __callStatic(string $name, array $arguments)
	{
		if (in_array($name, ['get', 'set', 'delete', 'unset', 'exists', 'has', 'flush'])) {
			if (!isset(self::$_session)) {
				self::$_session = new RequestVariableCollection($_SESSION);
			}
			
			return self::$_session->$name(...$arguments);
		}
		throw new Exception("unknown method('$name')");
	}
	
	/**
	 * Config sessions
	 *
	 * @param string      $sessionName - name of the PHP session
	 * @param string|null $SID         start or restore session with own provided session ID,
	 */
	public static function init(string $sessionName = 'PHPSESSID', string $SID = null)
	{
		if ($sessionName !== 'PHPSESSID') {
			$sessionName = "PHPSESSID_$sessionName";
		}
		self::$sessionName = $sessionName;
		if (self::$isStarted == false) {
			self::$isStarted = true;
			if (ini_get('session.auto_start') == 0) {
				if (headers_sent()) {
					debug_print_backtrace();
				}
				self::start($SID);
			}
		}
		//debug('session_id()', session_id());
		self::setSID(session_id());
		
		
		$upTime  = self::get('_sessionUpdateTime', time());
		$between = time() - $upTime;
		if ($between > self::$timeout and $upTime > 0) {
			self::destroy(true);
			self::$isExpired = true;
		}
		else {
			self::$isExpired = false;
		}
		//debug(self::$sessionName);
		//debug($_SESSION);
		//debug('------------------------------------------------');
		self::set('_sessionUpdateTime', time());
	}
	
	/**
	 * Retrieves a 32bit session id hash
	 *
	 * @return string
	 */
	public static function getSID(): string
	{
		return self::$SID;
	}
	
	/**
	 * Set a 32bit session id hash
	 *
	 * @param string $SID
	 */
	private static function setSID(string $SID)
	{
		self::$SID = $SID;
	}
	
	/**
	 * Destroy session
	 *
	 * @param bool $takeNewID - take new session ID
	 */
	public static function destroy(bool $takeNewID = true)
	{
		self::flush();
		session_unset();
		session_destroy();
		if (self::$sessionName) {
			setcookie(self::$sessionName, '', 1);
			session_name(self::$sessionName);
			session_set_cookie_params(self::$timeout);
		}
		self::start(); //start new session
		//take new session ID
		if ($takeNewID) {
			session_regenerate_id(true);
			$SID = session_id();
			self::setSID($SID);
		}
		unset($_COOKIE[session_name()]);
	}
	
	public static function close()
	{
		session_write_close();
	}
	
	/**
	 * @see https://stackoverflow.com/questions/3185779/the-session-id-is-too-long-or-contains-illegal-characters-valid-characters-are
	 * @return bool
	 */
	private static function doStart(): bool
	{
		if (Cookie::exists(self::$sessionName)) {
			$sessid = Cookie::get(self::$sessionName);
		}
		else {
			return session_start();
		}
		
		if (!preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $sessid)) {
			return false;
		}
		
		return session_start();
	}
	
	private static function start(string $SID = null)
	{
		if ($SID) {
			session_id($SID);
			Cookie::set(self::$sessionName, $SID);
		}
		if (self::$sessionName) {
			session_name(self::$sessionName);
		}
		session_set_cookie_params(self::$timeout);
		if (!self::doStart()) {
			session_id(uniqid());
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
		return self::$isExpired;
	}
	
	/**
	 * @param int $timeout
	 */
	public static function setTimeout(int $timeout): void
	{
		self::$timeout = $timeout;
	}
}