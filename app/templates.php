<?php

/**
 * Page title
 * @param string $title
 * @return void
 */
function emails_page_title(string $title, bool $with_subtitle = false): void
{
    echo '<h1 class="h2 mt-3 ' . ($with_subtitle ? 'mb-2' : 'mb-4') . '">' . enc($title) . '</h1>' . PHP_EOL;
}

function emails_page_subtitle(string $title, string $secondary = ''): void
{
    echo '<h2 class="h4 mb-4">';
    echo enc($title);
    if (!empty($secondary)) {
        echo ' <small class="fw-normal text-secondary">(' . enc($secondary) . ')</small>';
    }
    echo '</h2>' . PHP_EOL;
}

/**
 * Alert
 * @param string $message
 * @param bool $warning
 * @return void
 */
function emails_alert(string $message, bool $warning = false): void
{
    echo '<div class="alert alert-' . ($warning ? 'warning' : 'danger') . ' mb-4">' . nl2br(enc($message)) . '</div>' . PHP_EOL;
}

/**
 * Emails list on main page
 * @param string $base_url
 * @param array $emails
 * @param int $start_index
 * @param int $end_index
 * @return void
 */
function emails_list(string $base_url, array $emails, int $start_index, int $end_index): void
{
    echo '<div class="list-group mb-4">' . PHP_EOL;
    for ($index = $start_index; $index >= $end_index; $index--) {
        $parser = new PhpMimeMailParser\Parser();
        $parser->setText($emails[$index]);
        $date = $parser->getHeader('date');
        echo '<a href="' . enc("$base_url?index=$index") . '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">';
        echo '<div class="ms-2 me-auto"><div class="fw-bold">' . enc($parser->getHeader('subject')) . '</div> ' . $date . '</div>';
        echo '<span class="badge text-bg-secondary rounded-pill">' . fsize(mb_strlen($emails[$index], '8bit')) . '</span> ';
        echo '</a>' . PHP_EOL;
    }
    echo '</div>' . PHP_EOL;
}

/**
 * Pager for list page
 * @param string $base_url
 * @param int $page
 * @param int $total_pages
 * @return void
 */
function emails_list_pager(string $base_url, int $page, int $total_pages): void
{
    if ($total_pages > 1) {
        echo '<ul class="pagination justify-content-center mb-4">';
        echo '<li class="page-item' . ($page == 1 ? ' disabled' : '') . '"><a href="' . enc("$base_url?p=" . ($page > 1 ? $page - 1 : 1)) . '" class="page-link">&larr; Newer</a></li> ';
        echo '<li class="page-item' . ($page == $total_pages ? ' disabled' : '') . '"><a href="' . enc("$base_url?p=" . ($page < $total_pages ? $page + 1 : $total_pages)) . '" class="page-link">Older &rarr;</a></li>';
        echo '</ul>' . PHP_EOL;
    }
}

/**
 * Simple nav under title
 * @param string $base_url
 * @return void
 */
function emails_simple_nav(string $base_url): void
{
    echo '<div class="mb-4">';
    echo '<a href="' . enc($base_url) . '" class="btn btn-secondary">&larr; Back</a>';
    echo '</div>' . PHP_EOL;
}

/**
 * Nav under title
 * @param string $base_url
 * @param int $index
 * @param bool $dl_html
 * @param int $back_page
 * @param int|bool $prev_index
 * @param int|bool $next_index
 * @return void
 */
function emails_full_nav(string $base_url, int $index, bool $dl_html, int $back_page = 1, int|bool $prev_index = false, int|bool $next_index = false): void
{
    $back_url = url($base_url, $back_page > 1 ? ['p' => $back_page] : []);
    $source_url = url($base_url, ['index' => $index, 'source' => 1]);
    $dl_eml_url = url($base_url, ['index' => $index, 'source' => 1, 'dl' => 1]);
    $dl_html_url = url($base_url, ['index' => $index, 'html' => 1, 'dl' => 1]);

    echo '<div class="d-flex flex-wrap align-items-start gap-1 mb-4">';
    echo '<a href="' . enc($back_url) . '" class="btn btn-secondary">&larr; Back</a> ';
    echo '<a href="' . enc($source_url) . '" class="btn btn-outline-secondary" target="_blank">Show EML</a>';
    echo '<a href="' . enc($dl_eml_url) . '" class="btn btn-outline-secondary">Download EML</a> ';
    if ($dl_html) {
        echo '<a href="' . enc($dl_html_url) . '" class="btn btn-outline-secondary">Download HTML</a>';
    }
    echo '<div class="ms-md-auto">';
    if ($prev_index !== false) {
        $prev_url = url($base_url, ['index' => $prev_index]);
        echo '<a href="' . enc($prev_url) . '" class="btn btn-secondary">&lt; Next</a> ';
    } else {
        echo '<button type="button" class="btn btn-secondary" disabled>&lt; Next</button> ';
    }
    if ($next_index !== false) {
        $next_url = url($base_url, ['index' => $next_index]);
        echo '<a href="' . enc($next_url) . '" class="btn btn-secondary">Prev &gt;</a> ';
    } else {
        echo '<button type="button" class="btn btn-secondary" disabled>Prev &gt;</i></button> ';
    }
    echo '</div>';
    echo '</div>' . PHP_EOL;
}

/**
 * Headers block
 * @param array $headers
 * @return void
 */
function emails_headers(array $headers): void
{
    echo '<dl class="row mb-4">';
    if (isset($headers['date'])) {
        echo '<dt class="col-sm-3">Date:</dt><dd class="col-sm-9">' . enc($headers['date']) . '</dd>';
    }
    if (isset($headers['from'])) {
        echo '<dt class="col-sm-3">From:</dt><dd class="col-sm-9">' . enc($headers['from']) . '</dd>';
    }
    if (isset($headers['to'])) {
        echo '<dt class="col-sm-3">To:</dt><dd class="col-sm-9">' . enc($headers['to']) . '</dd>';
    }
    if (isset($headers['cc'])) {
        echo '<dt class="col-sm-3">Cc:</dt><dd class="col-sm-9">' . enc($headers['cc']) . '</dd>';
    }
    if (isset($headers['bcc'])) {
        echo '<dt class="col-sm-3">Bcc:</dt><dd class="col-sm-9">' . enc($headers['bcc']) . '</dd>';
    }
    if (isset($headers['reply-to'])) {
        echo '<dt class="col-sm-3">Reply-To:</dt><dd class="col-sm-9">' . enc($headers['reply-to']) . '</dd>';
    }
    if (isset($headers['delivered-to'])) {
        echo '<dt class="col-sm-3">Delivered-To:</dt><dd class="col-sm-9">' . enc($headers['delivered-to']) . '</dd>';
    }
    if (isset($headers['subject'])) {
        echo '<dt class="col-sm-3">Subject:</dt><dd class="col-sm-9">' . enc($headers['subject']) . '</dd>';
    }
    echo '</dl>' . PHP_EOL;
}

/**
 * Tabs
 * @param string $base_url
 * @param int $index
 * @param string $html
 * @param string $text
 * @param array $headers
 * @param string $raw_headers
 * @param \PhpMimeMailParser\Attachment[] $attachments
 * @return void
 */
function emails_tabs(string $base_url, int $index, string $html, string $text, array $headers, string $raw_headers, array $attachments): void
{
    // Tabs names
    $active = true;
    $tabs = [];
    if (!empty($html)) {
        $tabs['html'] = 'HTML';
        $tabs['html_source'] = 'HTML Source';
    }
    if (!empty($text)) {
        $tabs['text'] = 'Text';
    }
    $tabs['headers'] = 'Headers';
    $tabs['headers_raw'] = 'Raw Headers';
    if (!empty($attachments)) {
        $tabs['attachments'] = 'Attachments (' . count($attachments) . ')';
    }

    echo '<ul class="nav nav-pills mb-3" id="tabs">';
    foreach ($tabs as $tab_id => $tab_text) {
        echo '<li class="nav-item">';
        echo '<button class="nav-link' . ($active ? ' active' : '') . '" id="' . $tab_id . '-tab" data-bs-toggle="tab" data-bs-target="#' . $tab_id . '" type="button" role="tab" aria-controls="' . $tab_id . '" aria-selected="' . ($active ? 'true' : 'false') . '">' . $tab_text . '</button>';
        echo '</li>';
        $active = false;
    }
    echo '</ul>';

    // Tabs content
    $active = true;
    echo '<div class="tab-content">' . PHP_EOL;
    if (!empty($html)) {
        emails_tab_html($base_url, $index, $html, $active);
    }
    if (!empty($text)) {
        emails_tab_text($text, $active);
    }
    emails_tab_headers($headers, $active);
    emails_tab_headers_raw($raw_headers);
    if (!empty($attachments)) {
        emails_tab_attachments($base_url, $index, $attachments);
    }
    echo '</div>' . PHP_EOL;
}

/**
 * Tab content: HTML
 * @param string $base_url
 * @param int $index
 * @param string $html
 * @param bool $active
 * @return void
 */
function emails_tab_html(string $base_url, int $index, string $html, bool &$active): void
{
    echo '<div class="tab-pane' . ($active ? ' show active' : '') . '" id="html">';
    echo '<iframe src="' . enc("$base_url?index=$index&html=1") . '" frameborder="0" onload="initIframe(this)"></iframe>';
    echo '</div>' . PHP_EOL;
    echo '<div class="tab-pane" id="html_source">';
    echo '<pre>' . enc($html) . '</pre>';
    echo '</div>' . PHP_EOL;
    $active = false;
}

/**
 * Tab content: Text
 * @param string $text
 * @param bool $active
 * @return void
 */
function emails_tab_text(string $text, bool &$active): void
{
    echo '<div class="tab-pane' . ($active ? ' show active' : '') . '" id="text">';
    echo '<pre class="text">' . enc($text) . '</pre>';
    echo '</div>' . PHP_EOL;
    $active = false;
}

/**
 * Tab content: Headers
 * @param array $headers
 * @param bool $active
 * @return void
 */
function emails_tab_headers(array $headers, bool &$active): void
{
    echo '<div class="tab-pane' . ($active ? ' show active' : '') . '" id="headers">';
    echo '<table class="table table-bordered table-sm">';
    foreach ($headers as $header_name => $header_text) {
        if (!is_array($header_text)) {
            $header_text = [$header_text];
        }
        $show_num = count($header_text) > 1;
        $num = 1;
        foreach ($header_text as $sub_header_text) {
            echo '<tr><th>' . enc($header_name) . ($show_num ? " <small>($num)</small>" : '') . '</th>';
            echo '<td>' . enc($sub_header_text) . '</td></tr>';
            $num++;
        }
    }
    echo '</table>';
    echo '</div>' . PHP_EOL;
}

/**
 * Tab content: Raw Headers
 * @param string $raw_headers
 * @return void
 */
function emails_tab_headers_raw(string $raw_headers): void
{
    echo '<div class="tab-pane" id="headers_raw"><pre>' . enc($raw_headers) . '</pre></div>' . PHP_EOL;
}

/**
 * Tab content: Attachments
 * @param string $base_url
 * @param int $index
 * @param \PhpMimeMailParser\Attachment[] $attachments
 * @return void
 */
function emails_tab_attachments(string $base_url, int $index, array $attachments): void
{
    echo '<div class="tab-pane" id="attachments">';
    echo '<ol>';
    foreach ($attachments as $attachment) {
        $attachment_name = $attachment->getFilename();
        $attachment_name_urlenc = urlencode($attachment_name);
        $dl_url = url($base_url, ['index' => $index, 'attachment' => $attachment_name_urlenc]);
        echo '<li><a href="' . enc($dl_url) . '">';
        echo enc($attachment_name) . '</a> <small>(' . enc($attachment->getContentType()) . ')</small></li>';
    }
    echo '</ol>';
    echo '</div>' . PHP_EOL;
}

/**
 * Page header
 * @param string $site_title
 * @param string $base_url
 * @return void
 */
function emails_page_header(string $site_title, string $base_url): void
{
    $manifest_link = $base_url == '/' ? '<link rel="manifest" href="/fav/site.webmanifest">' : '';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{$site_title}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="shortcut icon" href="{$base_url}favicon.ico">
    <link rel="icon" type="image/png" href="{$base_url}fav/favicon-96x96.png" sizes="96x96">
    <link rel="apple-touch-icon" sizes="180x180" href="{$base_url}fav/apple-touch-icon.png">
    {$manifest_link}
    <style>
        .tab-pane iframe {
            width: 100%;
            border: 0;
            outline: 1px solid var(--bs-border-color);
        }
        pre.text {
            white-space: pre-wrap;
            word-wrap: normal;
            word-break: normal;
        }
        #headers table td {
            word-break: break-all;
        }
    </style>
    <script>
        function initIframe(iframe) {
            let doc = 'contentDocument' in iframe ? iframe.contentDocument : iframe.contentWindow.document;
            let height = Math.max(doc.body.scrollHeight + 10, 150);
            iframe.style.height = height + 'px';
            let links = doc.getElementsByTagName('a');
            for (let i in links) {
                if (links.hasOwnProperty(i)) {
                    links[i].setAttribute('target', '_blank');
                    links[i].setAttribute('rel', 'noopener noreferrer');
                }
            }
        }
    </script>
</head>
<body>

<div class="container-xxl">

HTML;
}

/**
 * Page footer
 * @return void
 */
function emails_page_footer(): void
{
    echo <<<HTML
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>

HTML;
}
