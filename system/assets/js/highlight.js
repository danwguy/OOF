function loadScript(url, fn) {
    var head = document.getElementsByTagName('head')[0],
        script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    script.onreadystatechange = fn;
    script.onload = fn;

    head.appendChild(script);
}

var spans = [];

var fixSpans = function() {
    $(document).ready(function() {
        $('span').each(function() {
            if($(this).parent().get(0).tagName == 'SPAN') {
                spans.push($(this));
            }
        });
        $.each(spans, function() {
            var text = $(this).html();
            $(this).after(text);
            $(this).remove();
        });
    });
    return;
}


if(typeof $ != 'function') {
    loadScript('//code.jquery.com/jquery-1.10.2.min.js', fixSpans);
} else {
    fixSpans();
}
