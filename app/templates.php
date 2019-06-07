<?php

/**
 * Page title
 * @param string $title
 * @param string $subtitle
 */
function emails_page_title($title, $subtitle = '')
{
    echo '<h2>' . enc($title) . (!empty($subtitle) ? ' <small>(' . enc($subtitle) . ')</small>' : '') . '</h2>';
}

function emails_list($base_url, $inbox_path, $page_files, $start_pos, $files_count)
{
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
            echo ($files_count - $start_pos++) . '. ' . date('d.m.Y H:i:s', strtotime($date)) . ' – ' . enc($parser->getHeader('subject')) . '<br>';
            echo '<code>' . enc($one) . '</code>';
            echo '</a>';
        }
    }
    echo '</div>';
}

/**
 * Pager for list page
 * @param string $base_url
 * @param int $page
 * @param int $total_pages
 */
function emails_list_pager($base_url, $page, $total_pages)
{
    if ($total_pages > 1) {
        echo '<ul class="pager">';
        echo '<li' . ($page == 1 ? ' class="disabled"' : '') . '><a href="' . enc("$base_url?p=" . ($page > 1 ? $page - 1 : 1)) . '">&larr; Newer</a></li> ';
        echo '<li' . ($page == $total_pages ? ' class="disabled"' : '') . '><a href="' . enc("$base_url?p=" . ($page < $total_pages ? $page + 1 : $total_pages)) . '">Older &rarr;</a></li>';
        echo '</ul>';
    }
}

/**
 * Simple nav under title
 * @param string $base_url
 */
function emails_simple_nav($base_url)
{
    echo '<div class="mb20">';
    echo '<a href="' . enc($base_url) . '" class="btn btn-sm btn-default"><i class="glyphicon glyphicon-arrow-left"></i> Back</a>';
    echo '</div>';
}

/**
 * Nav under title
 * @param string $base_url
 * @param string $name
 * @param bool $dl_html
 * @param int $back_page
 * @param bool $prev_file
 * @param bool $next_file
 */
function emails_full_nav($base_url, $name, $dl_html, $back_page = 1, $prev_file = false, $next_file = false)
{
    $name_urlenc = urlencode($name);
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
    echo '<a href="' . enc("$base_url?name=$name_urlenc&source=1") . '" class="btn btn-sm btn-default" target="_blank"><i class="glyphicon glyphicon-eye-open"></i> Show EML</a> ';
    echo '<a href="' . enc("$base_url?name=$name_urlenc&source=1&dl=1") . '" class="btn btn-sm btn-default" download><i class="glyphicon glyphicon-download"></i> Download EML</a> ';
    if ($dl_html) {
        echo '<a href="' . enc("$base_url?name=$name_urlenc&html=1&dl=1") . '" class="btn btn-sm btn-default" download><i class="glyphicon glyphicon-download"></i> Download HTML</a>';
    }
    echo '</div>';
}

/**
 * Headers block
 * @param array $headers
 */
function emails_headers($headers)
{
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
}

/**
 * Tabs
 * @param string $base_url
 * @param string $name
 * @param string $html
 * @param string $text
 * @param array $headers
 * @param string $raw_headers
 * @param \PhpMimeMailParser\Attachment[] $attachments
 */
function emails_tabs($base_url, $name, $html, $text, $headers, $raw_headers, $attachments)
{
    // Tabs names
    $active = true;
    echo '<ul class="nav nav-tabs" id="tabs">';
    if (!empty($html)) {
        echo '<li' . ($active ? ' class="active"' : '') . '><a href="#html" data-toggle="tab">HTML</a></li>';
        echo '<li><a href="#html_source" data-toggle="tab">HTML Source</a></li>';
        $active = false;
    }
    if (!empty($text)) {
        echo '<li' . ($active ? ' class="active"' : '') . '><a href="#text" data-toggle="tab">Text</a></li>';
    }
    echo '<li><a href="#headers" data-toggle="tab">Headers</a></li>';
    echo '<li><a href="#headers_raw" data-toggle="tab">Raw Headers</a></li>';
    if (!empty($attachments)) {
        echo '<li><a href="#attachments" data-toggle="tab">Attachments</a></li>';
    }
    echo '</ul>';

    // Tabs content
    $active = true;
    echo '<div class="tab-content">';
    emails_tab_html($base_url, $name, $html, $active);
    emails_tab_text($text, $active);
    emails_tab_headers($headers);
    emails_tab_headers_raw($raw_headers);
    emails_tab_attachments($base_url, $name, $attachments);
    echo '</div>';
}

/**
 * Tab content: HTML
 * @param string $base_url
 * @param string $name
 * @param string $html
 * @param bool $active
 */
function emails_tab_html($base_url, $name, $html, &$active)
{
    if (empty($html)) {
        return;
    }
    $name_urlenc = urlencode($name);
    echo '<div class="tab-pane tab-pane-loading' . ($active ? ' active' : '') . '" id="html">';
    echo '<iframe src="' . enc("$base_url?name=$name_urlenc&html=1") . '" frameborder="0" onload="initIframe(this)"></iframe>';
    echo '</div>';
    echo '<div class="tab-pane tab-pane-loading" id="html_source">';
    echo '<pre><code class="html">' . enc($html) . '</code></pre>';
    echo '</div>';
    $active = false;
}

/**
 * Tab content: Text
 * @param string $text
 * @param bool $active
 */
function emails_tab_text($text, &$active)
{
    if (empty($text)) {
        return;
    }
    echo '<div class="tab-pane tab-pane-loading' . ($active ? ' active' : '') . '" id="text"><pre class="text">' . enc($text) . '</pre></div>';
    $active = false;
}

/**
 * Tab content: Headers
 * @param array $headers
 */
function emails_tab_headers($headers)
{
    echo '<div class="tab-pane tab-pane-loading" id="headers">';
    echo '<table class="table table-bordered table-condensed">';
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
    echo '</div>';
}

/**
 * Tab content: Raw Headers
 * @param string $raw_headers
 */
function emails_tab_headers_raw($raw_headers)
{
    echo '<div class="tab-pane tab-pane-loading" id="headers_raw"><pre>' . enc($raw_headers) . '</pre></div>';
}

/**
 * Tab content: Attachments
 * @param string $base_url
 * @param string $name
 * @param \PhpMimeMailParser\Attachment[] $attachments
 */
function emails_tab_attachments($base_url, $name, $attachments)
{
    if (empty($attachments)) {
        return;
    }
    $name_urlenc = urlencode($name);
    echo '<div class="tab-pane tab-pane-loading" id="attachments">';
    echo '<ol>';
    foreach ($attachments as $attachment) {
        $attachment_name = $attachment->getFilename();
        $attachment_name_urlenc = urlencode($attachment_name);
        echo '<li><a href="' . enc("$base_url?name=$name_urlenc&attachment=$attachment_name_urlenc") . '" download>';
        echo enc($attachment_name) . '</a> <small>(' . enc($attachment->getContentType()) . ')</small></li>';
    }
    echo '</ol>';
    echo '</div>';
}
