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
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $object) {
                // Skip symlinks to prevent infinite loops
                if ($object->isLink()) {
                    continue;
                }

                $path = $object->getRealPath();
                if ($path === false) {
                    continue; // Skip if we can't get real path
                }

                $stats = stat($path);
                if ($stats === false) {
                    continue; // Skip if we can't get stats
                }

                $bytestotal += $stats['blocks'] ? $stats['blocks'] * 512 : $object->getSize();
            }
        }
        return $bytestotal;
    }
}