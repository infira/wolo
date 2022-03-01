<?php


use Wolo\VarDumper;

if (!function_exists('debug')) {
	function debug(...$moreVars)
	{
		VarDumper::debug(...$moreVars);
	}
}


if (!function_exists('checkArray')) {
	function checkArray($array): bool
	{
		if (is_array($array)) {
			if (count($array) > 0) {
				return true;
			}
		}
		
		return false;
	}
}

