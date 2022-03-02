<?php declare(strict_types=1);

namespace Wolo\File;

use Wolo\Str;

class Folder
{
	/**
	 * Delete a folder including all its content
	 *
	 * @param string $path
	 * @throws \Exception
	 */
	public static function delete(string $path)
	{
		self::doFlush($path, true);
	}
	
	/**
	 * Flush folder content
	 *
	 * @param string $path
	 * @throws \Exception
	 */
	public static function flush(string $path)
	{
		self::doFlush($path, false);
	}
	
	/**
	 * If folder doest no exists make it
	 *
	 * @param string $path
	 * @param int    $chmod - chmod it
	 * @return string created dir path
	 */
	public static function make(string $path, int $chmod = 0777): string
	{
		if (!file_exists($path)) {
			mkdir($path, $chmod, true);
		}
		
		return $path;
	}
	
	/**
	 * Find only files inside folder
	 *
	 * @param string $path
	 * @param string ...$filterPatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function files(string $path, ...$filterPatterns): array
	{
		return self::scanner($path, false, false, true, true, [], $filterPatterns);
	}
	
	/**
	 * Find only files inside folder recursively
	 *
	 * @param string $path
	 * @param string ...$filterPatterns a regular expression OR simple string pattern with asterisks as wildcard, OR use ^ to mark start of the string OR $ to end of the string
	 * @throws \Exception
	 * @return array
	 */
	public static function filesRecursive(string $path, ...$filterPatterns): array
	{
		return self::scanner($path, true, false, true, true, [], $filterPatterns);
	}
	
	/**
	 * Find only folders inside folder
	 *
	 * @param string $path
	 * @param string ...$filterPatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function folders(string $path, ...$filterPatterns): array
	{
		return self::scanner($path, false, true, false, true, [], $filterPatterns);
	}
	
	/**
	 * Find only folders inside folder recursively
	 *
	 * @param string $path
	 * @param string ...$filterPatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function foldersRecursive(string $path, ...$filterPatterns): array
	{
		return self::scanner($path, true, true, false, true, [], $filterPatterns);
	}
	
	/**
	 * Get filenames side folder
	 *
	 * @param string $path
	 * @param string ...$filterPatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function fileNames(string $path, string ...$filterPatterns): array
	{
		return self::scanner($path, false, false, true, false, [], $filterPatterns);
	}
	
	/**
	 * Get files and sub folders inside path
	 *
	 * @param string $path
	 * @param string ...$filterPatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array - array with absolute paths
	 */
	public static function content(string $path, string ...$filterPatterns): array
	{
		return self::scanner($path, false, true, true, true, [], $filterPatterns);
	}
	
	/**
	 * Get files and sub folders inside path recursively
	 *
	 * @param string $path
	 * @param string ...$filterPatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array - array with absolute paths
	 */
	public static function contentRecursive(string $path, string ...$filterPatterns): array
	{
		return self::scanner($path, true, true, true, true, [], $filterPatterns);
	}
	
	/**
	 * Find only files inside folder excluding patterns
	 *
	 * @param string $path
	 * @param string ...$excludePatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function filesExcept(string $path, ...$excludePatterns): array
	{
		return self::scanner($path, false, false, true, true, $excludePatterns);
	}
	
	/**
	 * Find only files inside folder recursively excluding patterns
	 *
	 * @param string $path
	 * @param string ...$excludePatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function filesRecursiveExcept(string $path, ...$excludePatterns): array
	{
		return self::scanner($path, true, false, true, true, $excludePatterns);
	}
	
	/**
	 * Find only folders inside folder excluding patterns
	 *
	 * @param string $path
	 * @param string ...$excludePatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function foldersExcept(string $path, ...$excludePatterns): array
	{
		return self::scanner($path, false, true, false, true, $excludePatterns);
	}
	
	/**
	 * Find only folders inside folder recursively excluding patterns
	 *
	 * @param string $path
	 * @param string ...$excludePatterns a regular expression or simple string pattern with asterisks as wildcard
	 * @throws \Exception
	 * @return array
	 */
	public static function foldersRecursiveExcept(string $path, ...$excludePatterns): array
	{
		return self::scanner($path, true, true, false, true, $excludePatterns);
	}
	
	/**
	 * Get files and sub folders inside path excluding patterns
	 *
	 * @param string $path
	 * @param array  $excludePatterns
	 * @throws \Exception
	 * @return array - array with absolute paths
	 */
	public static function contentExcept(string $path, array $excludePatterns = []): array
	{
		return self::scanner($path, false, true, true, true, $excludePatterns);
	}
	
	/**
	 * Get files and sub folders inside path recursively excluding patterns
	 *
	 * @param string $path
	 * @param array  $filterPatterns
	 * @throws \Exception
	 * @return array - array with absolute paths
	 */
	public static function contentRecursiveExcept(string $path, array $filterPatterns = []): array
	{
		return self::scanner($path, true, true, true, true, [], $filterPatterns);
	}
	
	/**
	 * @throws \Exception
	 */
	public static function scanner(string $path, bool $recursive, bool $includeFolders, bool $includeFiles, bool $getAbsolutePaths, array $excludePatterns = [], array $filterPatterns = []): array
	{
		$output = [];
		$realpath   = realpath($path);
		if ($realpath === false) {
			throw new \Exception("cant resolve realpath of ('$path')");
		}
		if (!is_dir($realpath)) {
			throw new \Exception("$realpath folder does not exists");
		}
		foreach (scandir($realpath) as $file) {
			if ($file == '.' or $file == '..') {
				continue;
			}
			$file = realpath(Path::join($realpath, $file));
			
			if (is_dir($file) and $recursive === true) {
				$output = array_merge($output, self::scanner($file, $recursive, $includeFolders, $includeFiles, $getAbsolutePaths, $excludePatterns, $filterPatterns));
			}
			
			$outputFile = $getAbsolutePaths ? $file : basename($file);
			if ($filterPatterns and !self::isMatch($filterPatterns, $file)) {
				continue;
			}
			
			if ($excludePatterns and self::isMatch($excludePatterns, $file)) {
				continue;
			}
			
			if ((is_dir($file) and $includeFolders) or ($includeFiles and is_file($file))) {
				$output[] = $outputFile;
			}
		}
		
		return $output;
	}
	
	private static function isMatch($patterns, string $str): bool
	{
		if (is_null($patterns)) {
			$patterns = [];
		}
		$patterns = is_null($patterns) ? [] : (is_array($patterns) ? $patterns : [$patterns]);
		
		if (empty($patterns)) {
			return false;
		}
		
		foreach ($patterns as $pattern) {
			
			if ((Str::endsWith($pattern, '$') or Str::startsWith($pattern, '^')) and preg_match('/' . $pattern . '\z/u', $str) === 1) {
				debug("asdasd");
				
				return true;
			}
			if (Str::is($pattern, $str)) {
				return true;
			}
		}
		
		return false;
	}
	
	private static function doFlush(string $path, bool $selfRemove)
	{
		array_map('unlink', self::filesRecursive($path));
		array_map('rmdir', self::foldersRecursive($path));
		
		if ($selfRemove) {
			rmdir($path);
		}
	}
}