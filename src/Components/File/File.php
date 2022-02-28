<?php

namespace Wolo\File;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @method static \SplFileInfo info(string $file): Creates new SplFileInfo object of a file
 * @method static string extension(string $file, bool $forceLowerCase = false) get file extension
 * @method static string basenameWithoutExtension(string $file) get file basename without extension
 * @method static string basename(string $file) file name without path info (with extension)
 * @method static string mimeType(string $file) detect file content mime type
 * @method static string path(string $file) Gets the path without filename
 * @method static string realPath(string $file) get file absolute path
 * @method static string content(string $file) get file content
 * @method static int put(string $file, string $content) put file content, returns number of bytes written
 * @method static bool exists(string $file) put file content
 * @method static bool rename(string $file, string $newName, string $newExtension = null) rename file name, if $newExtension null then only basename will be changed
 * @method static bool move(string $file, string $target, bool $overwrite) move file to another directory
 * @method static bool copy(string $file, string $target, bool $overwrite) copy file to another directory
 *
 * @eee FileOperations
 */
class File
{
	private static Filesystem $fs;
	
	public static function __callStatic(string $name, array $arguments)
	{
		return FileOperations::of($arguments[0])->$name(...array_slice($arguments, 1));
	}
	
	/**
	 * Alias to create new Symfony Filesystem
	 *
	 * @return \Symfony\Component\Filesystem\Filesystem
	 */
	public static function fs(): Filesystem
	{
		if (!isset(self::$fs)) {
			self::$fs = new Filesystem();
		}
		
		return self::$fs;
	}
	
	/**
	 * Delete file(s)
	 *
	 * @param iterable|string ...$files
	 * @return void
	 */
	public static function delete(iterable|string ...$files)
	{
		foreach ($files as $file) {
			self::fs()->remove($file);
		}
	}
}