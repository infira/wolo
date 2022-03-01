<?php

namespace Wolo;

class Regex
{
	/**
	 * Get the string matching the given pattern.
	 *
	 * @param string $pattern
	 * @param string $subject
	 * @return string
	 */
	public static function match(string $pattern, string $subject): string
	{
		preg_match($pattern, $subject, $matches);
		
		if (!$matches) {
			return '';
		}
		
		return $matches[1] ?? $matches[0];
	}
	
	/**
	 * Get the string matching the given pattern.
	 *
	 * @param string $pattern
	 * @param string $subject
	 * @return array
	 */
	public static function matchAll(string $pattern, string $subject): array
	{
		preg_match_all($pattern, $subject, $matches);
		
		if (empty($matches[0])) {
			return [];
		}
		
		return $matches[1] ?? $matches[0];
	}
	
	/**
	 * Check whatever $pattern is valid regular expression pattern
	 *
	 * @param string $pattern
	 * @return bool
	 */
	public static function isPattern(string $pattern, array $checkWithDelimiters = ['/', '#', '~']): bool
	{
		//see https://www.php.net/manual/en/regexp.reference.delimiters.php
		foreach ($checkWithDelimiters as $delim) {
			if (preg_match('/^\\' . $delim . '[\s\S]+\\' . $delim . '[A-Za-z]?$/', $pattern)) {
				return true;
			}
		}
		
		return false;
	}
}
