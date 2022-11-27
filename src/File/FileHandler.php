<?php

namespace Wolo\File;

use SplFileInfo;

/**
 * @property-read SplFileInfo $info;
 */
class FileHandler
{
    private string $file;

    public function __construct(string $path)
    {
        $this->file = $path;
    }

    public function __get(string $name)
    {
        if ($name === 'info') {
            $this->info = $this->info();

            return $this->info;
        }
        throw new \RuntimeException("property('$name') does not exists");
    }

    public function __debugInfo(): ?array
    {
        return pathinfo($this->file);
    }

    public function info(): SplFileInfo
    {
        return File::info($this->file);
    }

    /**
     * get file extension
     * @param  bool  $lowercase  = true, convert extension to lowercase
     * @return string
     * @link https://www.php.net/manual/en/splfileinfo.getextension.php
     */
    public function extension(bool $lowercase = false): string
    {
        return File::extension($this->file, $lowercase);
    }

    /**
     * Get file basename without extension
     *
     * @return string
     */
    public function fileNameWithoutExtension(): string
    {
        return File::basenameWithoutExtension($this->file);
    }

    /**
     * file name without path info (with extension)
     * @param  string  $suffix  - Optional suffix to omit from the base name returned.
     * @return string
     * @link https://www.php.net/manual/en/splfileinfo.getbasename.php
     */
    public function basename(string $suffix = ''): string
    {
        return File::basename($this->file, $suffix);
    }

    /**
     * detect file content mime type
     * @return string
     * @link https://www.php.net/manual/en/function.mime-content-type.php
     */
    public function mimeType(): string
    {
        return File::mimeType($this->file);
    }

    /**
     * Gets the path without filename
     * @return string
     * @link https://www.php.net/manual/en/splfileinfo.getpath.php
     */
    public function path(): string
    {
        return File::path($this->file);
    }

    /**
     * get file lines
     * @link https://php.net/manual/en/function.file.php
     */
    public function lines(): array
    {
        return File::lines($this->file);
    }

    /**
     * get file content
     * @return string|null - if file does not exists returns null
     * @link https://www.php.net/manual/en/function.file-get-contents.php
     */
    public function content(): ?string
    {
        return File::content($this->file);
    }

    /**
     * put file content, returns number of bytes written
     * @param  mixed  $content
     * @return int
     * @link https://www.php.net/manual/en/function.file-put-contents.php
     */
    public function put(string $content): int
    {
        return File::put($this->file, $content);
    }

    public function exists(): bool
    {
        return file_exists($this->file);
    }


    /**
     * Determine if a file or directory is missing.
     *
     * @return bool
     */
    public function missing(): bool
    {
        return !$this->exists();
    }

    /**
     * Change file name, extension will remain the same
     * @param  string  $name
     * @return bool
     */
    public function changeName(string $name): bool
    {
        return $this->rename($name.'.'.$this->extension(), true);
    }

    /**
     * Change file extension
     * @param  string  $extension
     * @return void
     */
    public function changeExtension(string $extension): bool
    {
        return $this->rename($this->fileNameWithoutExtension().'.'.$extension, true);
    }

    /**
     * Rename/move file into new location
     * @param  string  $target  - when is directory then renames file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public function rename(string $target, bool $overwrite = false): bool
    {
        return File::rename($this->file, $target, $overwrite);
    }

    /**
     * Rename/move  file into new location only if exists
     * @param  string  $target  - when is directory then renames file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public function renameIfExists(string $target, bool $overwrite = false): bool
    {
        return File::renameIfExists($this->file, $target, $overwrite);
    }

    /**
     * Copy file into no location
     * @param  string  $target  - when is directory then copies file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public function copy(string $target, bool $overwrite = false): bool
    {
        return File::copy($this->file, $target, $overwrite);
    }

    /**
     * Copy file into no location only if exists
     * @param  string  $target  - when is directory then copies file into that directory without changing the name
     * @param  bool  $overwrite
     * @return bool
     */
    public function copyIfExists(string $target, bool $overwrite = false): bool
    {
        return File::copyIfExists($this->file, $target, $overwrite);
    }

    /**
     * Remove file (directory will not be removed)
     */
    public function remove(): void
    {
        File::remove($this->file);
    }

    public function removeIfExists(): void
    {
        File::removeIfExists($this->file);
    }

    //region helpers
    private function getPath(string $file): string
    {
        return Path::join($this->file, $file);
    }
    //endregion

    //region deprecated
    /**
     * @see static::remove()
     * @deprecated
     */
    public function delete(): void
    {
        $this->remove();
    }

    /**
     * @see static::rename()
     * @deprecated
     */
    public function move(string $targetPath, bool $overwrite = false): bool
    {
        return $this->rename($targetPath, $overwrite);
    }

    /**
     * @see static::fileNameWithoutExtension
     * @deprecated
     */
    public function basenameWithoutExtension(): string
    {
        return $this->fileNameWithoutExtension($this->file);
    }
    //endregion
}