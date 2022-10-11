<?php

declare(strict_types=1);

namespace Wolo\File;

use Exception;
use RuntimeException;
use Wolo\Str;

class Folder
{
    /**
     * Delete a folder including all its content
     *
     * @param string $path
     * @throws Exception
     */
    public static function delete(string $path): void
    {
        self::doFlush($path, true);
    }

    /**
     * Flush folder content
     *
     * @param string $path
     * @throws Exception
     */
    public static function flush(string $path): void
    {
        self::doFlush($path, false);
    }

    /**
     * If folder doest no exists make it
     *
     * @param string $path
     * @param int $chmod - chmod it
     * @return string created dir path
     * @throws Exception
     */
    public static function make(string $path, int $chmod = 0777): string
    {
        if (!$path) {
            throw new RuntimeException('path cannot be empty');
        }
        if (is_file($path)) {
            throw new RuntimeException('path cannot be file');
        }
        if (is_dir($path)) {
            return $path;
        }
        if (!mkdir($path, $chmod, true) && !is_dir($path)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
        }

        return $path;
    }

    /**
     * Find only files inside folder
     *
     * @param string $path
     * @param string ...$filterPatterns a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
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
     * @return array - array with absolute paths
     * @throws Exception
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
     * @return array - array with absolute paths
     * @throws Exception
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
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
     */
    public static function foldersRecursiveExcept(string $path, ...$excludePatterns): array
    {
        return self::scanner($path, true, true, false, true, $excludePatterns);
    }

    /**
     * Get files and sub folders inside path excluding patterns
     *
     * @param string $path
     * @param array $excludePatterns
     * @return array - array with absolute paths
     * @throws Exception
     */
    public static function contentExcept(string $path, array $excludePatterns = []): array
    {
        return self::scanner($path, false, true, true, true, $excludePatterns);
    }

    /**
     * Get files and sub folders inside path recursively excluding patterns
     *
     * @param string $path
     * @param array $filterPatterns
     * @return array - array with absolute paths
     * @throws Exception
     */
    public static function contentRecursiveExcept(string $path, array $filterPatterns = []): array
    {
        return self::scanner($path, true, true, true, true, [], $filterPatterns);
    }

    /**
     * @throws Exception
     */
    public static function scanner(string $path, bool $recursive, bool $includeFolders, bool $includeFiles, bool $getAbsolutePaths, array $excludePatterns = [], array $filterPatterns = []): array
    {
        $output = [];
        $realpath = realpath($path);
        if ($realpath === false) {
            throw new RuntimeException("cant resolve realpath of ('$path')");
        }
        if (!is_dir($realpath)) {
            throw new RuntimeException("$realpath folder does not exists");
        }
        foreach (scandir($realpath) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $file = realpath(Path::join($realpath, $file));

            if ($recursive && is_dir($file)) {
                $output = array_merge($output, self::scanner($file, $recursive, $includeFolders, $includeFiles, $getAbsolutePaths, $excludePatterns, $filterPatterns));
            }

            $outputFile = $getAbsolutePaths ? $file : basename($file);
            if ($filterPatterns && !self::isMatch($filterPatterns, $file)) {
                continue;
            }

            if ($excludePatterns && self::isMatch($excludePatterns, $file)) {
                continue;
            }

            if ((is_dir($file) && $includeFolders) || ($includeFiles && is_file($file))) {
                $output[] = $outputFile;
            }
        }

        return $output;
    }

    private static function isMatch(string|array $patterns, string $str): bool
    {
        if (empty($patterns)) {
            return false;
        }
        $patterns = (array)$patterns;

        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern . '\z/u', $str) === 1 && (Str::endsWith($pattern, '$') || Str::startsWith($pattern, '^'))) {
                return true;
            }
            if (Str::is($pattern, $str)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private static function doFlush(string $path, bool $selfRemove): void
    {
        $realpath = realpath($path);
        if ($realpath === false) {
            return;
        }
        if (is_file($realpath)) {
            throw new RuntimeException("path ('$realpath') is a file");
        }

        array_map('unlink', self::filesRecursive($realpath));
        array_map('rmdir', self::foldersRecursive($realpath));

        if ($selfRemove) {
            rmdir($realpath);
        }
    }
}