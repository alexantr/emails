<?php

ini_set('default_charset', 'UTF-8');
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    mb_internal_encoding('UTF-8');
}
mb_regex_encoding('UTF-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/templates.php';

$inbox_path = __DIR__ . '/../inbox';
$per_page = 10;

$base_url = preg_replace('/\/index\.php$/', '/', $_SERVER['PHP_SELF']);

$name = isset($_GET['name']) ? str_replace(['/', '\\'], '', trim($_GET['name'])) : '';

$files = get_eml_list($inbox_path);
$total_files = count($files);

if ($total_files == 0) {
    include 'header.php';
    emails_page_title('Nothing found');
    include 'footer.php';
    exit;
}

if (empty($name)) {
    // LIST EMLS

    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $total_pages = ceil($total_files / $per_page);
    if ($page < 1) {
        $page = 1;
    }
    if ($page > $total_pages) {
        $page = $total_pages;
    }
    $start = $page * $per_page - $per_page;
    $start_num = $start;

    $page_files = array_slice($files, $start, $per_page);

    include 'header.php';
    emails_page_title('Emails', $total_files . ($total_files == 1 ? ' item' : ' items'));
    emails_list($base_url, $inbox_path, $page_files, $start, $total_files);
    emails_list_pager($base_url, $page, $total_pages);
    include 'footer.php';
    exit;

} else {
    // VIEW EML

    $file_path = "$inbox_path/$name";

    // file not found
    if (!is_file($file_path)) {
        include 'header.php';
        emails_page_title('File not found');
        emails_simple_nav($base_url);
        include 'footer.php';
        exit;
    }

    // show or download eml
    if (isset($_GET['source']) && $_GET['source'] == '1') {
        if (isset($_GET['dl']) && $_GET['dl'] == '1') {
            dl_headers($name, filesize($file_path));
        } else {
            header('Content-Type: text/plain');
        }
        readfile($file_path);
        exit;
    }

    $parser = new PhpMimeMailParser\Parser();
    $parser->setPath($file_path);

    $html = $parser->getMessageBody('html');

    // show or download html
    if (isset($_GET['html']) && $_GET['html'] == '1') {
        if (isset($_GET['dl']) && $_GET['dl'] == '1') {
            $dl_name = preg_replace('/\.eml$/i', '.html', $name);
            dl_headers($dl_name, mb_strlen($html, '8bit'));
        }
        echo $html;
        exit;
    }

    $attachments = $parser->getAttachments();

    // download attachment
    if (isset($_GET['attachment'])) {
        foreach ($attachments as $attachment) {
            $attachment_name = $attachment->getFilename();
            if ($_GET['attachment'] == $attachment_name) {
                $content = $attachment->getContent();
                dl_headers($attachment_name, mb_strlen($content, '8bit'));
                echo $content;
                exit;
            }
        }
        exit;
    }

    $headers = $parser->getHeaders();
    $headers_raw = $parser->getHeadersRaw();
    $text = $parser->getMessageBody('text');

    $prev_file = false;
    $next_file = false;
    $count = 0;
    foreach ($files as $one) {
        if ($one == $name) {
            $n = array_neighbors($files, $one);
            $prev_file = $n['prev'];
            $next_file = $n['next'];
            break;
        }
        $count++;
    }
    $back_page = ceil(($count + 1) / $per_page);

    include 'header.php';
    emails_page_title($name, ($total_files - $count) . ' / ' . $total_files);
    emails_full_nav($base_url, $name, !empty($html), $back_page, $prev_file, $next_file);
    emails_headers($headers);
    emails_tabs($base_url, $name, $html, $text, $headers, $headers_raw, $attachments);
    include 'footer.php';
    exit;
}
