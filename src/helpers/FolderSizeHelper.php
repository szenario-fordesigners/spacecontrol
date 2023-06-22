<?php

namespace szenario\craftspacecontrol\helpers;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FolderSizeHelper
{
    public static function getDirectorySize($path)
    {
        clearstatcache();

        $bytestotal = 0;
        $path = realpath($path);
        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $path = $object->getRealPath();
                $stats = stat($path);

                $bytestotal += $stats['blocks'] ? $stats['blocks'] * 512 : $object->getSize();
            }
        }
        return $bytestotal;
    }
}