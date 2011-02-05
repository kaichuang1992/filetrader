<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.to_human_size.php
 * Type:     modifier
 * Name:     to_human_size
 * Purpose:  convert byte to human readable size
 * -------------------------------------------------------------
 */
function smarty_modifier_to_human_size($bytes = 0)
{
        $kilobyte = 1024;
        $megabyte = $kilobyte * $kilobyte;
        $gigabyte = $megabyte * $megabyte;

        if ($bytes > $gigabyte)
                return (int) ($bytes / $gigabyte) . "GB";
        if ($bytes > $megabyte)
                return (int) ($bytes / $megabyte) . "MB";
        if ($bytes > $kilobyte)
                return (int) ($bytes / $kilobyte) . "kB";
        return $bytes;
}
?>
