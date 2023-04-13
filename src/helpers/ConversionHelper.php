<?php

namespace szenario\craftspacecontrol\helpers;
use Craft;
class ConversionHelper
{
    // SPACE STATISTICS DISPLAY
    public static function getHumanReadableSize($bytes)
    {
        if ($bytes > 0) {
            $base = floor(log($bytes) / log(1024));
            $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"); //units of measurement
            return number_format(($bytes / pow(1024, floor($base))), 3) . " $units[$base]";
        } else return "0 bytes";
    }
}