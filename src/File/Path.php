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
     * Also removes double // and empty parts
     *
     * @param  string  ...$paths
     * @return string
     */
    public static function join(string ...$paths): string
    {
        $finalPath = null;
        $wasScheme = false;
        foreach ($paths as $path) {
            $path = trim($path);
            if ($path === '') {
                continue;
            }

            if (null === $finalPath) {
                // For first part we keep slashes, like '/top', 'C:\' or 'phar://' ,'http://', 'https://', 'file://' etc
                $finalPath = $path;
                $wasScheme = (str_contains($path, '://'));
                continue;
            }

            //remove any empty path separators and double //
            if (str_contains($path, '/')) {
                $path = self::join(...explode('/', $path));
            }

            if (!str_ends_with($finalPath, '/') && !str_ends_with($finalPath, '\\') && !str_starts_with($path, '/')) {
                $finalPath .= '/';
            }

            // If first part included a scheme like 'phar://' we allow \current part to start with '/', otherwise trim
            $finalPath .= $wasScheme ? $path : ltrim($path, '/');
            $wasScheme = false;
        }

        return $finalPath ?? '';
    }
}