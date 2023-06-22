<?php

namespace szenario\craftspacecontrol\helpers;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FolderSizeHelper
{
    public static function folderSize(string $dir): int
    {
        clearstatcache();

        $size = 0;

        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : FolderSizeHelper::folderSize($each);
        }

        return $size;
    }

    public static function getDirectorySize($path)
    {
        clearstatcache();

        $bytestotal = 0;
        $path = realpath($path);
        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }
        return $bytestotal;
    }

    public static function getAllFiles($path)
    {
        clearstatcache();

        $files = [];

        $path = realpath($path);
        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $files[] = $object->getPathName();
            }
        }

        return $files;
    }
}