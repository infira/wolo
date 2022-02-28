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
		$end   = $syntax[1];
		foreach ($vars as $name => $value) {
			$string = str_replace([$start . $name . $end], $value, $string);
		}
		
		return $string;
	}
}