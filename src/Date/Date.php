<?php

namespace Wolo\Date;

use stdClass;
use DateTime;
use DateTimeZone;
use Wolo\File\FileOperations;

/**
 * Use composer require carbondate/carbon to use more advanced features
 * Date::$driver = \Carbon\Carbon::class
 * @method static string f(string|int $value, string $format) alias to format()
 * @method static string dmy(string|int $value) Format to d m Y separated by $separator
 * @method static string date(string|int $value) Format to Date::$dateFormat
 * @method static string dateTime(string|int $value) Format to Date::$dateTimeFormat
 * @method static string niceTime(string|int $value) Format to Date::$niceTimeFormat
 * @method static string sqlDate(string|int $value) format to Y-m-d
 * @method static string sqlDateTime(string|int $value) format to Y-m-d H:i:s
 *
 * @mixin DateTime
 * @mixin \Carbon\Carbon::class
 */
class Date
{
	/**
	 * @var string DateTime::class OR \Carbon\Carbon::class::class
	 */
	public static string $driver = DateTime::class;
	
	public static string $dateFormat     = 'd.m.Y';
	public static string $dateTimeFormat = 'd.m.Y H:i:s';
	public static string $niceTimeFormat = 'H:i:s';
	
	/**
	 * Constructs DateTime objet
	 *
	 * @param string|int        $datetime
	 * @param DateTimeZone|null $timezone
	 * @return DateDriver
	 */
	public static function of(string|int $datetime = 'now', ?DateTimeZone $timezone = null): DateDriver
	{
		$ts = null;
		if (is_int($datetime)) {
			$ts       = $datetime;
			$datetime = 'now';
		}
		$driver = new DateDriver($datetime, $timezone);
		if ($ts) {
			$driver->setTimestamp($ts);
		}
		
		return $driver;
	}
	
	public static function __callStatic(string $name, array $arguments)
	{
		return self::of($arguments[0])->$name(...array_slice($arguments, 1));
	}
	
	/**
	 * convert to timestamp
	 *
	 * @param string|int      $time
	 * @param string|int|null $now - use base time or string, defaults to now ($now is converted to time)
	 * @return int - converted timestamp
	 */
	public static function time(string|int $time, string|int $now = null): int
	{
		$dm = self::of($time);
		if ($now !== null) {
			$dm->setTimestamp(self::of($now)->time());
		}
		
		return $dm->time();
	}
	
	/**
	 * Get last of the month date
	 *
	 * @param string|null $date - date to time
	 * @return int
	 */
	public static function lastDayOfMonth(string $date = null): int
	{
		return self::time(date("Y-m-t", self::time($date)));
	}
	
	/**
	 * Count days between dates
	 * $ignore ignore day numbers like sunday = 7
	 *
	 * @param string|null $start   - null means now
	 * @param string|null $endDate - null means now
	 * @param array       $ignore
	 * @throws \Exception
	 * @return int
	 */
	public static function daysBetween(string $start = null, string $endDate = null, array $ignore = []): int
	{
		$result  = 0;
		$start   = self::of($start)->sqlDate();
		$endDate = self::of($endDate)->sqlDate();
		while ($start != $endDate) {
			$time = self::time($start);
			if (!in_array(strftime("%u", $time), $ignore)) {
				$result++;
			}
			$start = self::of(self::time("+1 day", $time))->sqlDate();
		}
		
		return $result;
	}
	
	/**
	 * Get array range with dates
	 *
	 * @param        $from   - null means now
	 * @param        $to     - null means now
	 * @param string $step   - how many times to add each step
	 * @param string $format - format range item
	 * @return array
	 */
	public static function range(string|int $from, string|int $to, string $step = '+1 day', string $format = 'd.m.Y'): array
	{
		$dates = [];
		$from  = self::time($from);
		$to    = self::time($to);
		
		while ($from <= $to) {
			if (is_callable($format)) {
				$val = $format($from);
				if (is_object($val)) {
					$dates[$val->value] = $val->label;
				}
				else {
					$dates[] = $val;
				}
			}
			else {
				$dates[] = date($format, $from);
			}
			$from = strtotime($step, $from);
		}
		
		return $dates;
	}
	
	/**
	 * if $date is actual date
	 *
	 * @param string $date
	 * @throws \Exception
	 * @return bool
	 */
	public static function is(string $date): bool
	{
		$dateTime = self::of($date)->sqlDate();
		if ($dateTime == "1970-01-01") {
			return false;
		}
		
		return true;
	}
}