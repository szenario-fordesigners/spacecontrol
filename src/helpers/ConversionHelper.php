<?php

namespace szenario\craftspacecontrol\helpers;

use Craft;

class ConversionHelper
{
    // SPACE STATISTICS DISPLAY
    public static function getHumanReadableSize(int $bytes): string
    {
        if ($bytes > 0) {
            $base = floor(log($bytes) / log(1024));
            $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"); //units of measurement
            return number_format(($bytes / pow(1024, floor($base))), 2) . " $units[$base]";
        } else return "0 bytes";
    }

    // convert aliases to paths
    public static function craftPathToAbsolute(string $path): string
    {
        // split by alias prefix
        $splits = explode('@', $path);

        // drop empty splits (happens when path starts with an alias)
        $splits = array_filter($splits, 'strlen');

        $resolvedPath = '';

        // loop through all the splits
        foreach ($splits as $split) {
            $effectiveSplit = $split;

            // check if there is a slash in the string
            $slashPosition = strpos($split, '/');
            if ($slashPosition) {
                // if so, we need to cut the string to only get the alias
                $effectiveSplit = substr($split, 0, $slashPosition);
            }

            $resolvedAlias = Craft::getAlias('@' . $effectiveSplit);

            if ($slashPosition) {
                // if we have cut the string, we need to append it again
                $resolvedPath .= $resolvedAlias . substr($split, $slashPosition);
            } else {
                $resolvedPath .= $resolvedAlias;
            }
        }

        return $resolvedPath;
    }
}