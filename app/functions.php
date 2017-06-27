<?php

function generate_message_filename()
{
    $time = microtime(true);
    return date('Ymd-His-', $time) . sprintf('%04d', (int)(($time - (int)$time) * 10000)) . '-' . sprintf('%04d', mt_rand(0, 10000)) . '.eml';
}

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

function array_neighbors($array, $value)
{
    $values = array_values($array);

    $return = [
        'prev' => false,
        'next' => false,
    ];

    foreach ($values as $i => $v) {
        if ($values[$i] == $value) {
            if (isset($values[$i - 1])) {
                $return['prev'] = $values[$i - 1];
            }
            if (isset($values[$i + 1])) {
                $return['next'] = $values[$i + 1];
            }
        }
    }

    return $return;
}
