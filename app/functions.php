<?php

function enc(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $base_url, array $query = []): string
{
    return $base_url . ($query !== [] ? '?' . http_build_query($query) : '');
}

function fsize($size): string
{
    $size = (int)$size;
    if ($size < 1024) {
        return $size . ' B';
    }
    $units = ['KiB', 'MiB', 'GiB'];
    foreach ($units as $unit) {
        $size /= 1024;
        if ($size < 1024) {
            break;
        }
    }
    return round($size, $size < 10 ? 2 : 1) . ' ' . $unit;
}

function dl_headers(string $filename, int|string $content_length): void
{
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $content_length);
    header('Content-Type: application/x-force-download; name="' . $filename . '"');
}
