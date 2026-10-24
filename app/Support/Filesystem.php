<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use RuntimeException;

/**
 * Filesystem with a Windows-safe atomic replace.
 *
 * Laravel writes a compiled Blade view by renaming a temporary file over the
 * existing compiled file. On Windows that rename fails with "Access is
 * denied" while another request is including the same compiled view. Filament
 * pages load several Livewire widgets at once, so after the view cache is
 * cleared they can all compile the same shared partial simultaneously and one
 * of them returns a 500.
 *
 * If the target already contains exactly the new bytes we skip the write
 * entirely; otherwise the rename is retried briefly until the reader has
 * released the file.
 */
class Filesystem extends BaseFilesystem
{
    protected int $replaceAttempts = 20;

    protected int $replaceDelayMicroseconds = 25_000;

    public function replace($path, $content, $mode = null)
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        // A parallel request compiled the same template while this one was
        // working — the bytes are identical, so there is nothing to write.
        if ($this->exists($path) && @file_get_contents($path) === $content) {
            return;
        }

        $tempPath = tempnam(dirname($path), basename($path));

        if ($tempPath === false) {
            throw new RuntimeException("Unable to create a temporary file for [{$path}].");
        }

        // Fix permissions of tempPath because `tempnam()` creates it with permissions set to 0600...
        if (! is_null($mode)) {
            @chmod($tempPath, $mode);
        } else {
            @chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        for ($attempt = 0; $attempt < $this->replaceAttempts; $attempt++) {
            if (@rename($tempPath, $path)) {
                return;
            }

            usleep($this->replaceDelayMicroseconds);
        }

        @unlink($tempPath);

        throw new RuntimeException("Unable to replace the file at path: {$path}");
    }
}
