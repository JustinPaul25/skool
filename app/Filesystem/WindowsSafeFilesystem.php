<?php

namespace App\Filesystem;

use Illuminate\Filesystem\Filesystem;

class WindowsSafeFilesystem extends Filesystem
{
    /**
     * {@inheritdoc}
     *
     * On Windows, rename() frequently fails with "Access is denied" when replacing
     * compiled Blade files (AV, indexer, or existing file handles). We remove the
     * destination first, then fall back to a direct write if rename() still fails.
     */
    public function replace($path, $content, $mode = null)
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            parent::replace($path, $content, $mode);

            return;
        }

        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        if ($tempPath === false) {
            throw new \RuntimeException('Unable to create a temporary file in ['.dirname($path).'].');
        }

        if (! is_null($mode)) {
            @chmod($tempPath, $mode);
        } else {
            @chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        if (is_file($path)) {
            @chmod($path, 0666);
            @unlink($path);
        }

        if (@rename($tempPath, $path)) {
            return;
        }

        file_put_contents($path, $content);
        @unlink($tempPath);
    }
}
