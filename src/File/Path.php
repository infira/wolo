<?php

namespace Wolo\File;

class Path
{
    /**
     * Add trailing slash if not exists
     *
     * @param  string  $path
     * @return string
     */
    public static function slash(string $path): string
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
     * Join path parts together into a canonical path.
     *
     * @author https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Filesystem/Path.php
     * @param  string  ...$paths
     * @return string
     */
    public static function join(string ...$paths): string
    {
        $finalPath = null;
        $wasScheme = false;

        foreach ($paths as $path) {
            if ('' === $path) {
                continue;
            }

            if (null === $finalPath) {
                // For first part we keep slashes, like '/top', 'C:\' or 'phar://'
                $finalPath = $path;
                $wasScheme = (str_contains($path, '://'));
                continue;
            }

            // Only add slash if previous part didn't end with '/' or '\'
            if (!in_array(mb_substr($finalPath, -1), ['/', '\\'])) {
                $finalPath .= '/';
            }

            // If first part included a scheme like 'phar://' we allow \current part to start with '/', otherwise trim
            $finalPath .= $wasScheme ? $path : ltrim($path, '/');
            $wasScheme = false;
        }

        return $finalPath ?? '';
    }
}