<?php

namespace Wolo\File;

class Dir
{
	/**
	 * Add / to end of path if needed
	 *
	 * @param string $path
	 * @return string
	 */
	public static function slash(string $path)
	{
		if (!$path) {
			return '/';
		}
		$path = rtrim($path);
		if (!str_ends_with($path, '/')) {
			$path .= '/';
		}
		
		return $path;
	}
	
	/**
	 * Makes a correct file path concat('directory/path','myFile.txt') => directory/path/myFile.txt
	 *
	 * @param string $directoryPath
	 * @param string $filename
	 * @return string
	 */
	public static function concat(string $directoryPath, string $filename)
	{
		return self::slash($directoryPath) . $filename;
	}
}