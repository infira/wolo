<?php

namespace Wolo\Request;

use Exception;

/**
 * @method static array all() - all values from $_COOKIE
 * @method static mixed get(string $key, mixed $default = null) - get from $_COOKIE
 * @method static bool exists(string $key) - does $key exists in $_COOKIE
 * @method static bool has(string $key) - does $key exists in $_COOKIE
 * @method static mixed flush() - flush $_COOKIE
 */
class Cookie
{
	private static RequestVariableCollection $_cookie;
	
	public static function __callStatic(string $name, array $arguments)
	{
		if (in_array($name, ['get', 'set', 'exists', 'has', 'flush'])) {
			if (!isset(self::$_cookie)) {
				self::$_cookie = new RequestVariableCollection($_cookie);
			}
			
			return self::$_cookie->$name(...$arguments);
		}
		throw new Exception("unknown method('$name')");
	}
	
	/**
	 * Set item to $_COOKIE
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $expires - when expires. (int)0 - forever,(string)"10 hours" -  will be converted to time using strtotime(), (int)1596885301 - timestamp. If $expires is in the past, it will be converted as forever.
	 * @param bool   $secure  Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to TRUE, the cookie will only be set if a secure connection exists. On the server-side, it's on the programmer to send this kind of cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]).
	 * @see https://www.php.net/manual/en/function.setcookie.php
	 */
	public static function set(string $key, mixed $value, int $expires = 0, bool $secure = true)
	{
		$cookie_host = preg_replace('|^www\.(.*)$|', '.\\1', $_SERVER['HTTP_HOST']);
		if ($expires) {
			if (is_string($expires)) {
				if ($expires[0] != "+") {
					$expires = "+$expires";
				}
				$expires = strtotime($expires);
			}
			elseif (is_numeric($expires)) {
				$expires = intval($expires);
			}
		}
		else {
			$expires = 0;
		}
		
		if ($expires == 0) {
			$expires = 2147483640;
		}
		$_COOKIE[$key] = $value;
		setcookie($key, $value, $expires, "/", $cookie_host, $secure);
	}
	
	/**
	 * Deletes item from $_COOKIE
	 *
	 * @param string $key
	 * @return void
	 */
	public static function delete(string $key)
	{
		if (self::exists($key)) {
			//Actually, there is not a way to directly delete a cookie. Just use setcookie with expiration date in the past, to trigger the removal mechanism in your browser. https://www.pontikis.net/blog/create-cookies-php-javascript
			unset($_COOKIE[$key]);
			// empty value and expiration one hour before
			setcookie($key, '', time() - 3600);
		}
	}
	
	public static function unset(string $key)
	{
		self::delete($key);
	}
}