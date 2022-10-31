<?php

namespace Wolo\File;

use SplFileInfo;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use const PATHINFO_EXTENSION;

/**
 * @property-read Filesystem $fs - Symfony filesystem
 */
class FileOperations
{
    private string $file;

    public function __construct(string $realPath)
    {
        $this->file = $realPath;
    }

    public static function of(string $file): static
    {
        return new static($file);
    }

    /**
     * Creates new SplFileInfo object of a file
     *
     * @param string $file
     * @return SplFileInfo
     */
    public static function info(string $file): SplFileInfo
    {
        return new SplFileInfo($file);
    }

    public function extension(bool $forceLowerCase = false): string
    {
        if(!$this->file) {
            return '';
        }

        $extension = pathinfo($this->file, PATHINFO_EXTENSION);

        if($forceLowerCase) {
            $extension = self::toLower($extension);
        }

        return $extension;
    }

    /**
     * Get file basename without extension
     *
     * @return string
     */
    public function basenameWithoutExtension(): string
    {
        if(!$this->file) {
            return '';
        }

        return rtrim(basename($this->file, $this->extension()), '.');
    }

    public function basename(): string
    {
        return basename($this->file);
    }

    public function mimeType(): string
    {
        return mime_content_type($this->file);
    }

    /**
     * Gets the path without filename
     *
     * @return string
     */
    public function path(): string
    {
        return pathinfo($this->file, PATHINFO_DIRNAME);
    }

    public function realPath(): string
    {
        return $this->file;
    }

    public function content(): string
    {
        return file_get_contents($this->file);
    }

    /**
     * Put file content using php file_put_contents
     *
     * @param string $content
     * @return int - number of bytes written
     */
    public function put(string $content): int
    {
        return file_put_contents($this->file, $content);
    }

    public function exists(): bool
    {
        return file_exists($this->file);
    }

    public function delete(): bool
    {
        return unlink($this->file);
    }

    public function rename(string $newName, string $newExtension = null): void
    {
        $newExtension = $newExtension ?: $this->extension();
        File::fs()->rename($this->file, $this->path() . '/' . $newName . '.' . $newExtension);
    }

    /**
     * Move file to another directory
     *
     * @throws IOException When target file or directory already exists
     * @throws IOException When origin cannot be renamed
     */
    public function move(string $target, bool $overwrite = false): void
    {
        File::fs()->rename($this->file, $target, $overwrite);
    }

    /**
     * Copies a file.
     *
     * If the target file is older than the origin file, it's always overwritten.
     * If the target file is newer, it is overwritten only when the
     * $overwriteNewerFiles option is set to true.
     *
     * @throws FileNotFoundException When originFile doesn't exist
     * @throws IOException           When copy fails
     */
    public function copy(string $target, bool $overwrite = false): void
    {
        File::fs()->copy($this->file, $target, $overwrite);
    }

    private static function toLower(string $string): string
    {
        if(false !== $encoding = mb_detect_encoding($string)) {
            return mb_strtolower($string, $encoding);
        }

        return mb_strtolower($string, $encoding);
    }
}