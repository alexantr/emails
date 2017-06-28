<?php

ini_set('default_charset', 'UTF-8');
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    mb_internal_encoding('UTF-8');
}
mb_regex_encoding('UTF-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';

$inbox_path = __DIR__ . '/../inbox';
$per_page = 10;

$base_url = preg_replace('/\/index\.php$/', '/', $_SERVER['PHP_SELF']);

$name = isset($_GET['name']) ? $_GET['name'] : '';
$name = str_replace(['/', '\\'], '', $name);

$files = get_eml_list($inbox_path);
$total_files = count($files);

if ($total_files == 0) {
    include 'header.php';
    echo '<h2>Nothing found</h2>';
    include 'footer.php';
    exit;
}

if (empty($name)) {

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

    echo '<h2>Emails <small>(' . $total_files . ($total_files == 1 ? ' item' : ' items') . ')</small></h2>';

    echo '<div class="list-group">';
    foreach ($page_files as $one) {
        $one_urlenc = urlencode($one);
        $full_one_path = "$inbox_path/$one";
        if (is_file($full_one_path)) {
            $parser = new PhpMimeMailParser\Parser();
            $parser->setPath($full_one_path);
            $date = $parser->getHeader('date');
            echo '<a href="' . enc("$base_url?name=$one_urlenc") . '" class="list-group-item">';
            echo '<span class="badge">' . fsize(filesize($full_one_path)) . '</span> ';
            echo ($total_files - $start_num++) . '. ' . date('d.m.Y H:i:s', strtotime($date)) . ' – ' . enc($parser->getHeader('subject')) . '<br>';
            echo '<code>' . enc($one) . '</code>';
            echo '</a>';
        }
    }
    echo '</div>';

    if ($total_pages > 1) {
        echo '<ul class="pager">';
        echo '<li' . ($page == 1 ? ' class="disabled"' : '') . '><a href="' . enc("$base_url?p=" . ($page > 1 ? $page - 1 : 1)) . '">&larr; Newer</a></li> ';
        echo '<li' . ($page == $total_pages ? ' class="disabled"' : '') . '><a href="' . enc("$base_url?p=" . ($page < $total_pages ? $page + 1 : $total_pages)) . '">Older &rarr;</a></li>';
        echo '</ul>';
    }

    include 'footer.php';
    exit;

} else {

    $file_path = "$inbox_path/$name";
    $name_urlenc = urlencode($name);

    // file not found
    if (!is_file($file_path)) {
        include 'header.php';
        echo '<h2>File not found</h2>';
        echo '<div class="mb20">';
        echo '<a href="' . enc($base_url) . '" class="btn btn-sm btn-default"><i class="glyphicon glyphicon-arrow-left"></i> Back</a>';
        echo '</div>';
        include 'footer.php';
        exit;
    }

    // download eml
    if (isset($_GET['source']) && $_GET['source'] == '1') {
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Content-Type: application/x-force-download; name="' . $name . '"');
        readfile($file_path);
        exit;
    }

    $parser = new PhpMimeMailParser\Parser();
    $parser->setPath($file_path);

    $html = $parser->getMessageBody('html');
    //$html_embedded = $parser->getMessageBody('htmlEmbedded');

    // show html - for iframe
    if (isset($_GET['html']) && $_GET['html'] == '1') {
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
                header('Content-Disposition: attachment; filename="' . $attachment_name . '"');
                header('Content-Length: ' . mb_strlen($content, '8bit'));
                header('Content-Type: application/x-force-download; name="' . $attachment_name . '"');
                echo $content;
            }
        }
        exit;
    }

    $total_files = count($files);

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

    $headers = $parser->getHeaders();
    $text = $parser->getMessageBody('text');

    include 'header.php';

    echo '<h2>' . enc($name) . ' <small>(' . ($total_files - $count) . ' / ' . $total_files . ')</small></h2>';

    echo '<div class="mb20">';
    echo '<div class="pull-right">';
    if ($prev_file) {
        $prev_urlenc = urlencode($prev_file);
        echo '<a href="' . enc("$base_url?name=$prev_urlenc") . '" class="btn btn-sm btn-default"><i class="glyphicon glyphicon-chevron-left"></i> Prev</a> ';
    } else {
        echo '<button type="button" class="btn btn-sm btn-default" disabled><i class="glyphicon glyphicon-chevron-left"></i> Prev</button> ';
    }
    if ($next_file) {
        $next_urlenc = urlencode($next_file);
        echo '<a href="' . enc("$base_url?name=$next_urlenc") . '" class="btn btn-sm btn-default">Next <i class="glyphicon glyphicon-chevron-right"></i></a> ';
    } else {
        echo '<button type="button" class="btn btn-sm btn-default" disabled>Next <i class="glyphicon glyphicon-chevron-right"></i></button> ';
    }
    echo '</div>';
    echo '<a href="' . enc("$base_url?p=$back_page") . '" class="btn btn-sm btn-default"><i class="glyphicon glyphicon-arrow-left"></i> Back</a> ';
    echo '<a href="' . enc("$base_url?name=$name_urlenc&source=1") . '" class="btn btn-sm btn-default" download><i class="glyphicon glyphicon-download"></i> Download</a>';
    echo '</div>';

    echo '<dl class="dl-horizontal mb20">';
    if (isset($headers['date'])) {
        echo '<dt>Date:</dt><dd>' . enc($headers['date']) . '</dd>';
    }
    if (isset($headers['from'])) {
        echo '<dt>From:</dt><dd>' . enc($headers['from']) . '</dd>';
    }
    if (isset($headers['to'])) {
        echo '<dt>To:</dt><dd>' . enc($headers['to']) . '</dd>';
    }
    if (isset($headers['cc'])) {
        echo '<dt>Cc:</dt><dd>' . enc($headers['cc']) . '</dd>';
    }
    if (isset($headers['bcc'])) {
        echo '<dt>Bcc:</dt><dd>' . enc($headers['bcc']) . '</dd>';
    }
    if (isset($headers['reply-to'])) {
        echo '<dt>Reply-To:</dt><dd>' . enc($headers['reply-to']) . '</dd>';
    }
    if (isset($headers['subject'])) {
        echo '<dt>Subject:</dt><dd>' . enc($headers['subject']) . '</dd>';
    }
    echo '</dl>';

    $active = true;

    echo '<ul class="nav nav-tabs" id="tabs">';
    if (!empty($html)) {
        echo '<li' . ($active ? ' class="active"' : '') . '><a href="#html" data-toggle="tab">HTML</a></li>';
        echo '<li><a href="#html_source" data-toggle="tab">HTML Source</a></li>';
        $active = false;
    }
    if (!empty($text)) {
        echo '<li' . ($active ? ' class="active"' : '') . '><a href="#text" data-toggle="tab">Text</a></li>';
        $active = false;
    }
    echo '<li><a href="#headers" data-toggle="tab">Headers</a></li>';
    echo '<li><a href="#headers_raw" data-toggle="tab">Raw Headers</a></li>';
    if (count($attachments) > 0) {
        echo '<li><a href="#attachments" data-toggle="tab">Attachments</a></li>';
    }
    echo '</ul>';

    $active = true;

    echo '<div class="tab-content">';

    // Html
    if (!empty($html)) {
        echo '<div class="tab-pane tab-pane-loading' . ($active ? ' active' : '') . '" id="html">';
        echo '<iframe src="' . enc("$base_url?name=$name_urlenc&html=1") . '" frameborder="0" scrolling="no" onload="resizeIframe(this)">' . nl2br(enc($text)) . '</iframe>';
        echo '</div>';
        echo '<div class="tab-pane tab-pane-loading" id="html_source">';
        echo '<pre><code class="html">' . enc($html) . '</code></pre>';
        echo '</div>';
        $active = false;
    }

    // Text
    if (!empty($text)) {
        echo '<div class="tab-pane tab-pane-loading' . ($active ? ' active' : '') . '" id="text"><pre class="text">' . enc($text) . '</pre></div>';
        $active = false;
    }

    // Headers
    echo '<div class="tab-pane tab-pane-loading" id="headers">';
    echo '<table class="table table-bordered table-condensed">';
    foreach ($headers as $header_name => $header_text) {
        if (!is_array($header_text)) {
            $header_text = [$header_text];
        }
        foreach ($header_text as $sub_header_text) {
            echo '<tr><th>' . enc($header_name) . '</th><td>' . enc($sub_header_text) . '</td></tr>';
        }
    }
    echo '</table>';
    echo '</div>';

    // Raw Headers
    echo '<div class="tab-pane tab-pane-loading" id="headers_raw"><pre>' . enc($parser->getHeadersRaw()) . '</pre></div>';

    // Attachments
    if (count($attachments) > 0) {
        echo '<div class="tab-pane tab-pane-loading" id="attachments">';
        echo '<ol>';
        foreach ($attachments as $attachment) {
            $attachment_name = $attachment->getFilename();
            $attachment_name_urlenc = urlencode($attachment_name);
            echo '<li><a href="' . enc("$base_url?name=$name_urlenc&attachment=$attachment_name_urlenc") . '" download>' . enc($attachment_name) . '</a> <small>(' . enc($attachment->getContentType()) . ')</small></li>';
        }
        echo '</ol>';
        echo '</div>';
    }

    echo '</div>';

    include 'footer.php';
    exit;
}
