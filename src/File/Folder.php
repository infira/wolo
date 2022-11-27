<?php

namespace Wolo\File;

use Wolo\File\Exception\FolderNotFoundException;
use Wolo\File\Exception\IOException;
use Wolo\Str;

class Folder
{
    /**
     * Delete a folder including all its content
     *
     * @param  string  $directory
     */
    public static function delete(string $directory): void
    {
        self::doFlush($directory, true);
    }

    /**
     * Flush folder content
     *
     * @param  string  $directory
     */
    public static function flush(string $directory): void
    {
        self::doFlush($directory, false);
    }

    /**
     * If folder doest no exists make it
     *
     * @param  string  $directory
     * @param  int  $chmod  - chmod it
     * @return string created dir path
     */
    public static function make(string $directory, int $chmod = 0777): string
    {
        if (empty($directory)) {
            throw new IOException("directory('$directory') cannot be empty");
        }
        if (is_dir($directory)) {
            return $directory;
        }
        if (!mkdir($directory, $chmod, true) && !is_dir($directory)) {
            throw new IOException(sprintf('Directory "%s" was not created', $directory));
        }

        return $directory;
    }

    /**
     * Find only files inside folder
     *
     * @param  string  $directory
     * @param  string  ...$filterPatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function files(string $directory, ...$filterPatterns): array
    {
        return self::scanner($directory, false, false, true, true, [], $filterPatterns);
    }

    /**
     * Find only files inside folder recursively
     *
     * @param  string  $directory
     * @param  string  ...$filterPatterns  a regular expression OR simple string pattern with asterisks as wildcard, OR use ^ to mark start of the string OR $ to end of the string
     * @return array
     */
    public static function filesRecursive(string $directory, ...$filterPatterns): array
    {
        return self::scanner($directory, true, false, true, true, [], $filterPatterns);
    }

    /**
     * Find only folders inside folder
     *
     * @param  string  $directory
     * @param  string  ...$filterPatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function folders(string $directory, ...$filterPatterns): array
    {
        return self::scanner($directory, false, true, false, true, [], $filterPatterns);
    }

    /**
     * Find only folders inside folder recursively
     *
     * @param  string  $directory
     * @param  string  ...$filterPatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function foldersRecursive(string $directory, ...$filterPatterns): array
    {
        return self::scanner($directory, true, true, false, true, [], $filterPatterns);
    }

    /**
     * Get filenames side folder
     *
     * @param  string  $directory
     * @param  string  ...$filterPatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function fileNames(string $directory, string ...$filterPatterns): array
    {
        return self::scanner($directory, false, false, true, false, [], $filterPatterns);
    }

    /**
     * Get files and sub folders inside path
     *
     * @param  string  $directory
     * @param  string  ...$filterPatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array - array with absolute paths
     */
    public static function content(string $directory, string ...$filterPatterns): array
    {
        return self::scanner($directory, false, true, true, true, [], $filterPatterns);
    }

    /**
     * Get files and sub folders inside path recursively
     *
     * @param  string  $directory
     * @param  string  ...$filterPatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array - array with absolute paths
     */
    public static function contentRecursive(string $directory, string ...$filterPatterns): array
    {
        return self::scanner($directory, true, true, true, true, [], $filterPatterns);
    }

    /**
     * Find only files inside folder excluding patterns
     *
     * @param  string  $directory
     * @param  string  ...$excludePatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function filesExcept(string $directory, ...$excludePatterns): array
    {
        return self::scanner($directory, false, false, true, true, $excludePatterns);
    }

    /**
     * Find only files inside folder recursively excluding patterns
     *
     * @param  string  $directory
     * @param  string  ...$excludePatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function filesRecursiveExcept(string $directory, ...$excludePatterns): array
    {
        return self::scanner($directory, true, false, true, true, $excludePatterns);
    }

    /**
     * Find only folders inside folder excluding patterns
     *
     * @param  string  $directory
     * @param  string  ...$excludePatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function foldersExcept(string $directory, ...$excludePatterns): array
    {
        return self::scanner($directory, false, true, false, true, $excludePatterns);
    }

    /**
     * Find only folders inside folder recursively excluding patterns
     *
     * @param  string  $directory
     * @param  string  ...$excludePatterns  a regular expression or simple string pattern with asterisks as wildcard
     * @return array
     */
    public static function foldersRecursiveExcept(string $directory, ...$excludePatterns): array
    {
        return self::scanner($directory, true, true, false, true, $excludePatterns);
    }

    /**
     * Get files and sub folders inside path excluding patterns
     *
     * @param  string  $directory
     * @param  array  $excludePatterns
     * @return array - array with absolute paths
     */
    public static function contentExcept(string $directory, array $excludePatterns = []): array
    {
        return self::scanner($directory, false, true, true, true, $excludePatterns);
    }

    /**
     * Get files and sub folders inside path recursively excluding patterns
     *
     * @param  string  $directory
     * @param  array  $filterPatterns
     * @return array - array with absolute paths
     */
    public static function contentRecursiveExcept(string $directory, array $filterPatterns = []): array
    {
        return self::scanner($directory, true, true, true, true, [], $filterPatterns);
    }

    public static function scanner(string $directory, bool $recursive, bool $includeFolders, bool $includeFiles, bool $getAbsolutePaths, array $excludePatterns = [], array $filterPatterns = []): array
    {
        $output = [];
        self::validateDirectory($directory);
        foreach (scandir($directory) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $file = Path::join($directory, $file);

            if ($recursive && is_dir($file)) {
                array_push($output, ...self::scanner($file, true, $includeFolders, $includeFiles, $getAbsolutePaths, $excludePatterns, $filterPatterns));
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

    //region helpers
    private static function validateDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new FolderNotFoundException("directory('$directory') is not a directory or does not exists");
        }
    }

    private static function isMatch(string|array $patterns, string $str): bool
    {
        if (empty($patterns)) {
            return false;
        }
        $patterns = (array)$patterns;

        foreach ($patterns as $pattern) {
            if (preg_match('/'.$pattern.'\z/u', $str) === 1 && (Str::endsWith($pattern, '$') || Str::startsWith($pattern, '^'))) {
                return true;
            }
            if (Str::is($pattern, $str)) {
                return true;
            }
        }

        return false;
    }

    private static function doFlush(string $directory, bool $selfRemove): void
    {
        self::validateDirectory($directory);

        array_map('unlink', self::filesRecursive($directory));
        array_map('rmdir', self::foldersRecursive($directory));

        if ($selfRemove) {
            rmdir($directory);
        }
    }
}