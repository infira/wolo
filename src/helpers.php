<?php


use Wolo\VarDumper;

if (!function_exists('debug')) {
	function debug(...$moreVars)
	{
		VarDumper::debug(...$moreVars);
	}
}

if (!function_exists('debug')) {
	function getTrace(int $startAt = 0): string
	{
		$backTrace = debug_backtrace();
		$until     = 15;
		$trace     = [];
		$nr        = 1;
		for ($i = $startAt; $i <= $until; $i++) {
			if (isset($backTrace[$i]['file'])) {
				$trace[$nr] = $backTrace[$i]['file'] . ' : ' . $backTrace[$i]['line'];
				$nr++;
			}
		}
		
		return str_replace(getcwd(), "", $trace);
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

