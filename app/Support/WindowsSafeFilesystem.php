<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;

class WindowsSafeFilesystem extends Filesystem
{
    /**
     * Override atomic replace on Windows environments that disallow rename/delete.
     *
     * @param  string  $path
     * @param  string  $content
     * @param  int|null  $mode
     * @return void
     */
    public function replace($path, $content, $mode = null)
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            parent::replace($path, $content, $mode);

            return;
        }

        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        file_put_contents($path, $content);

        if (! is_null($mode)) {
            @chmod($path, $mode);
        }
    }
}
