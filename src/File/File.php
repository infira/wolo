<?php

namespace Wolo\File;

use Wolo\File\Exception\FileNotFoundException;
use Wolo\File\Exception\IOException;
use Wolo\Is;

/**
 * Utility to quickly get info about files and manipulate
 *
 * @author gen@infira.ee
 */
class File
{
    public static function of(string $file): FileHandler
    {
        return new FileHandler($file);
    }

    public static function info(string $file): \SplFileInfo
    {
        self::validateFile($file);

        return new \SplFileInfo($file);
    }

    /**
     * get file extension
     * @param  string  $file
     * @param  bool  $lowercase  = true, convert extension to lowercase
     * @return string
     * @link https://www.php.net/manual/en/splfileinfo.getextension.php
     */
    public static function extension(string $file, bool $lowercase = false): string
    {
        $extension = static::info($file)->getExtension();

        return $lowercase ? mb_strtolower($extension, 'UTF-8') : $extension;
    }

    /**
     * get file basename without extension
     * @param  string  $file
     * @return string
     * @link https://www.php.net/manual/en/splfileinfo.getbasename.php
     */
    public static function basenameWithoutExtension(string $file): string
    {
        $info = static::info($file);

        return $info->getBasename('.'.$info->getExtension());
    }

    /**
     * file name without path info (with extension)
     * @param  string  $file
     * @param  string  $suffix  - Optional suffix to omit from the base name returned.
     * @return string
     * @link https://www.php.net/manual/en/splfileinfo.getbasename.php
     */
    public static function basename(string $file, string $suffix = ""): string
    {
        return static::info($file)->getBasename($suffix);
    }

    /**
     * detect file content mime type
     * @param  string  $file
     * @return string
     * @link https://www.php.net/manual/en/function.mime-content-type.php
     */
    public static function mimeType(string $file): string
    {
        self::validateFile($file);

        return mime_content_type($file);
    }

    /**
     * Gets the path without filename
     * @param  string  $file
     * @return string
     * @link https://www.php.net/manual/en/splfileinfo.getpath.php
     */
    public static function path(string $file): string
    {
        self::validateFile($file);

        return static::info($file)->getPath();
    }

    /**
     * get file lines
     * @param  string  $file
     * @return array
     * @link https://php.net/manual/en/function.file.php
     */
    public static function lines(string $file): array
    {
        self::validateFile($file);

        return file($file);
    }

    /**
     * get file content
     * @param  string  $file
     * @param  mixed  $default  - return if file does not exist
     * @return string
     * @link https://www.php.net/manual/en/function.file-get-contents.php
     */
    public static function content(string $file, string $default = ''): string
    {
        if (!file_exists($file)) {
            return $default;
        }

        return (string)file_get_contents($file);
    }

    /**
     * put file content, returns number of bytes written
     * @param  string  $file
     * @param  mixed  $content
     * @return int|false
     * @link https://www.php.net/manual/en/function.file-put-contents.php
     */
    public static function put(string $file, mixed $content): int|false
    {
        return file_put_contents($file, $content);
    }

    /**
     * Does file exists
     * @param  string  $file
     * @return bool
     */
    public static function exists(string $file): bool
    {
        return file_exists($file);
    }

    /**
     * Rename/move file into new location
     * @param  string  $file
     * @param  string  $target  - when is directory then renames file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public static function rename(string $file, string $target, bool $overwrite = true): bool
    {
        if (Is::url($file) || Is::url($target)) {
            throw new IOException("Cant use to rename from/to URL");
        }
        $target = self::getTarget($file, $target);
        if (!$overwrite && file_exists($target)) {
            throw new IOException("cannot overwrite existing file('$target')");
        }

        return rename($file, $target);
    }

    /**
     * Rename/move  file into new location only if exists
     * @param  string  $file
     * @param  string  $target  - when is directory then renames file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public static function renameIfExists(string $file, string $target, bool $overwrite = false): bool
    {
        if (!file_exists($file)) {
            return false;
        }

        return self::rename($file, $target, $overwrite);
    }

    /**
     * Copy file into no location
     * @param  string  $file
     * @param  string  $target  - when is directory then copies file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public static function copy(string $file, string $target, bool $overwrite = true): bool
    {
        if (Is::url($file) || Is::url($target)) {
            throw new IOException("Cant use to copy from/to URL");
        }
        $target = self::getTarget($file, $target);
        if (!$overwrite && file_exists($target)) {
            throw new IOException("cannot overwrite existing file('$target')");
        }

        return copy($file, $target);
    }

    /**
     * Copy file into no location only if exists
     * @param  string  $file
     * @param  string  $target  - when is directory then copies file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public static function copyIfExists(string $file, string $target, bool $overwrite = false): bool
    {
        if (Is::url($file) || Is::url($target)) {
            throw new IOException("Cant use to copy from/to URL");
        }
        if (!file_exists($file)) {
            return false;
        }

        return self::copy($file, $target, $overwrite);
    }

    /**
     * Remove file (directory will not be removed)
     */
    public static function remove(string|array $files): void
    {
        foreach ((array)$files as $file) {
            self::validateFile($file);
            unlink($file);
        }
    }

    /**
     * Remove file(s) only if they exists
     */
    public static function removeIfExists(string|array $files): void
    {
        foreach ((array)$files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    //region helpers

    /**
     * @param  string  $file
     * @param  string  $target
     * @return string
     */
    public static function getTarget(string $file, string $target): string
    {
        self::validateFile($file);

        //just give file a name filename
        if ($target === basename($target)) {
            $targetFile = $target;
            $target = self::path($file);
        }

        if (!is_file($target) && !is_dir($target)) {
            throw new IOException("target('$target') is either file nor directory");
        }

        if (isset($targetFile)) {
            $target = Path::join($target, $targetFile);
        }

        if (is_dir($target)) {
            $target = Path::join($target, self::basename($file));
        }

        return $target;
    }

    private static function validateFile(string $file): void
    {
        if (!is_file($file)) {
            throw new FileNotFoundException("file('$file') is not a file or does not exists");
        }
    }
    //endregion

    //region deprecated
    /**
     * @see static::remove()
     * @deprecated
     */
    public static function delete(string|array $files): void
    {
        self::remove($files);
    }

    /**
     * @see static::rename()
     * @deprecated
     */
    public static function move(string $file, string $targetPath, bool $overwrite = false): bool
    {
        return self::rename($file, $targetPath, $overwrite);
    }
    //endregion
}