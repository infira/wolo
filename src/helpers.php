<?php


use Wolo\VarDumper;

if (!function_exists('debug'))
{
	function debug(...$moreVars)
	{
		if (count($moreVars) > 1)
		{
			$html = VarDumper::dump($moreVars);
		}
		else
		{
			$html = VarDumper::dump($moreVars[0]);
		}
		echo "<pre>$html</pre>";
	}
}


if (!function_exists('checkArray'))
{
	function checkArray($array): bool
	{
		if (is_array($array))
		{
			if (count($array) > 0)
			{
				return true;
			}
		}
		
		return false;
	}
}

