<?php

ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/templates.php';
require_once __DIR__ . '/Mail/Mbox.php';

$file = '/var/mail/dev';
//$file = __DIR__ . '/../inbox/dev';

$per_page = 10;

$base_url = preg_replace('/\/index\.php$/', '/', $_SERVER['PHP_SELF']);

$index = isset($_GET['index']) ? (int)$_GET['index'] : false;

$emails = [];

if (!is_file($file)) {
    include 'header.php';
    emails_page_title('File not found');
    include 'footer.php';
    exit;
}

$mbox = new Mail_Mbox($file);
$mbox->open();
$size = $mbox->size();
for ($n = $size - 1; $n >= 0; $n--) {
    $emails[$n] = $mbox->get($n);
}
$mbox->close();

$total_emails = count($emails);


if ($total_emails == 0) {
    include 'header.php';
    emails_page_title('No emails found');
    include 'footer.php';
    exit;
}

if ($index === false) {
    // LIST EMLS

    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $total_pages = ceil($total_emails / $per_page);
    if ($page < 1) {
        $page = 1;
    }
    if ($page > $total_pages) {
        $page = $total_pages;
    }
    // 22: 22-1*10+10=22, 22-2*10+10=12, 22-3*10+10=2
    $start_index = $total_emails - $page * $per_page + $per_page - 1;
    $end_index = max(0, $start_index - $per_page + 1);

    include 'header.php';
    emails_page_title('Emails from ' . $file, $total_emails . ($total_emails == 1 ? ' item' : ' items'));
    emails_list($base_url, $emails, $start_index, $end_index);
    emails_list_pager($base_url, $page, $total_pages);
    include 'footer.php';
    exit;

} else {
    // VIEW EML

    // file not found
    if (!isset($emails[$index])) {
        include 'header.php';
        emails_page_title('Email not found');
        emails_simple_nav($base_url);
        include 'footer.php';
        exit;
    }

    // show or download eml
    if (isset($_GET['source']) && $_GET['source'] == '1') {
        if (isset($_GET['dl']) && $_GET['dl'] == '1') {
            // todo: set name from date
            dl_headers(($index + 1) . '.eml', mb_strlen($emails[$index], '8bit'));
        } else {
            header('Content-Type: text/plain');
        }
        echo $emails[$index];
        exit;
    }

    $parser = new PhpMimeMailParser\Parser();
    $parser->setText($emails[$index]);

    $html = $parser->getMessageBody('html');

    // show or download html
    if (isset($_GET['html']) && $_GET['html'] == '1') {
        if (isset($_GET['dl']) && $_GET['dl'] == '1') {
            // todo: set name from date
            $dl_name = ($index + 1) . '.html'; //preg_replace('/\.eml$/i', '.html', $name);
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

    // desc
    $prev_index = $index + 1;
    $next_index = $index - 1;
    if ($prev_index > $total_emails - 1) {
        $prev_index = false;
    }
    if ($next_index < 0) {
        $next_index = false;
    }

    $back_page = ceil(($total_emails - $index) / $per_page);

    include 'header.php';
    emails_page_title(($index + 1) . ' / ' . $total_emails);
    emails_full_nav($base_url, $index, !empty($html), $back_page, $prev_index, $next_index);
    emails_headers($headers);
    emails_tabs($base_url, $index, $html, $text, $headers, $headers_raw, $attachments);
    include 'footer.php';
    exit;
}
