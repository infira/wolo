<?php

namespace Wolo;

class Str
{
	/**
	 * Simple string templating
	 *
	 * @param string $string
	 * @param array  $vars
	 * @param string $syntax
	 * @return string
	 */
	public static function vars(string $string, array $vars, string $syntax = '{}'): string
	{
		$start = $syntax[0];
		$end   = $syntax[1] ?? $syntax[0];
		foreach ($vars as $name => $value) {
			$string = str_replace([$start . $name . $end], $value, $string);
		}
		
		return $string;
	}
	
	public static function endsWith(string $string, string $width): bool
	{
		return substr($string, strlen($width) * -1) === $width;
	}
	
	public static function startsWith(string $string, string $width): bool
	{
		return substr($string, 0, strlen($width) - 1) === $width;
	}
	
	/**
	 * Determine if a given string matches a given pattern.
	 * Used Laravel Str::is for based, added little more functionality, it detects if pattern is already regular expression
	 *
	 * @param string|array $patterns
	 * @param string       $value
	 * @return bool
	 * @author https://github.com/illuminate/support/blob/master/Str.php
	 */
	public static function is($patterns, string $value): bool
	{
		if (is_null($patterns)) {
			$patterns = [];
		}
		$patterns = is_null($patterns) ? [] : (is_array($patterns) ? $patterns : [$patterns]);
		
		if (empty($patterns)) {
			return false;
		}
		
		foreach ($patterns as $pattern) {
			$pattern = (string)$pattern;
			
			// If the given value is an exact match we can of course return true right
			// from the beginning. Otherwise, we will translate asterisks and do an
			// actual pattern match against the two strings to see if they match.
			if ($pattern == $value) {
				return true;
			}
			
			if (Regex::isPattern($pattern) and preg_match($pattern, $value) === 1) {
				return true;
			}
			
			$pattern = preg_quote($pattern, '#');
			
			// Asterisks are translated into zero-or-more regular expression wildcards
			// to make it convenient to check if the strings starts with the given
			// pattern such as "library/*", making any string check convenient.
			$pattern = str_replace('\*', '.*', $pattern);
			
			if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
				return true;
			}
		}
		
		return false;
	}
}