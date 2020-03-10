<?php

function enc($str)
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function get_eml_list($path)
{
    if (!is_dir($path)) {
        return [];
    }
    $items = scandir($path);
    $files = [];
    foreach ($items as $item) {
        if (strpos($item, '.') === 0) {
            continue;
        }
        if (is_file($path . '/' . $item) && preg_match('/\.eml$/', $item)) {
            $files[] = $item;
        }
    }
    return array_reverse($files);
}

function fsize($size, $round = 1)
{
    $size = (int)$size;
    if ($size < 1024) {
        return $size . ' B';
    } elseif (($size / 1024) < 1024) {
        return round(($size / 1024), $round) . ' KB';
    } elseif (($size / 1024 / 1024) < 1024) {
        return round(($size / 1024 / 1024), $round) . ' MB';
    } else {
        return round(($size / 1024 / 1024 / 1024), $round) . ' GB';
    }
}

function dl_headers($filename, $content_length)
{
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $content_length);
    header('Content-Type: application/x-force-download; name="' . $filename . '"');
}
