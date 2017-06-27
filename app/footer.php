</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>

<script>
    jQuery(function ($) {
        hljs.initHighlightingOnLoad();
        $('.tab-pane').removeClass('tab-pane-loading');
        $('#tabs').find('a[href^="#"]').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>

</body>
</html>