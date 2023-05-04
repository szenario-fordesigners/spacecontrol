<?php

namespace szenario\craftspacecontrol\helpers;

class FolderSizeHelper
{
   public static function folderSize(string $dir): int
   {
       $size = 0;

       foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
           $size += is_file($each) ? filesize($each) : FolderSizeHelper::folderSize($each);
       }

       return $size;
   }
}