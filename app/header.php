<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Emails</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/vs.min.css">
    <style>
        h2, .mb20 {
            margin-bottom: 20px;
        }
        .tab-pane {
            padding-top: 15px;
        }
        .tab-pane iframe {
            width: 100%;
            height: 400px;
            border: 0;
            outline: 1px solid #eee;
        }
        .tab-pane.tab-pane-loading {
            display: block !important;
            opacity: 0 !important;
        }
        pre {
            background: none;
            border: 0;
            padding: 0;
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
            var doc = 'contentDocument' in iframe ? iframe.contentDocument : iframe.contentWindow.document;
            iframe.style.height = doc.body.scrollHeight + 'px';
            var links = doc.getElementsByTagName('a');
            for (var i in links) {
                if (links.hasOwnProperty(i)) {
                    links[i].setAttribute('target', '_blank');
                }
            }
        }
    </script>
</head>
<body>

<div class="container">
