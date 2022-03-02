<?php

namespace Wolo\Date;

use \DateTime;
use \DateTimeZone;

/**
 * @mixin DateTime
 * @mixin \Carbon\Carbon
 */
class DateDriver
{
	/**
	 * @var DateTime|\Carbon\Carbon|mixed
	 */
	private $driver;
	
	public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
	{
		$class        = Date::$driver;
		$this->driver = new $class($datetime, $timezone);
	}
	
	public function __call(string $name, array $arguments)
	{
		return $this->driver->$name(...$arguments);
	}
	
	public function f(string $format): string
	{
		return $this->format($format);
	}
	
	/**
	 * Format to d m Y separated by $separator
	 *
	 * @param string $separator = '.'
	 * @return string
	 */
	public function dmy(string $separator = '.'): string
	{
		return $this->separatorFormatter('dmY', $separator);
	}
	
	private function separatorFormatter(string $letters, string $separator): string
	{
		return $this->format(join($separator, str_split($letters)));
	}
	
	/**
	 * Format to Date::$dateFormat
	 *
	 * @see Date::$dateFormat
	 * @return string
	 */
	public function date(): string
	{
		return $this->format(Date::$dateFormat);
	}
	
	/**
	 * Format to Date::$dateTimeFormat
	 *
	 * @see Date::$dateTimeFormat
	 * @return string
	 */
	public function dateTime(): string
	{
		return $this->format(Date::$dateTimeFormat);
	}
	
	/**
	 * Format to Date::$niceTimeFormat
	 *
	 * @see Date::$niceTimeFormat
	 * @return string
	 */
	public function niceTime(): string
	{
		return $this->format(Date::$niceTimeFormat);
	}
	
	
	/**
	 * format to Y-m-d
	 *
	 * @return string
	 */
	public function sqlDate(): string
	{
		return $this->format('Y-m-d');
	}
	
	/**
	 * format to Y-m-d H:i:s
	 *
	 * @return string
	 */
	public function sqlDateTime(): string
	{
		return $this->format('Y-m-d H:i:s');
	}
	
	/**
	 * Is current time in the past
	 *
	 * @return bool
	 */
	public function inPast(): bool
	{
		return ($this->getTimestamp() < time());
	}
	
	/**
	 * Is current time in the past
	 *
	 * @return bool
	 */
	public function inFuture(): bool
	{
		return ($this->getTimestamp() > time());
	}
	
	/**
	 * Is current time present
	 *
	 * @return bool
	 */
	public function isPresent(): bool
	{
		return ($this->getTimestamp() == time());
	}
	
	public function time(): int
	{
		return $this->getTimestamp();
	}
}