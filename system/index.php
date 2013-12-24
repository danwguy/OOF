<?php
//echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";
$str = $_SERVER['REDIRECT_QUERY_STRING'];
$str_parts = explode("&", $str);
$post = array();
foreach($str_parts as $data) {
    $mine = explode("=", $data);
    $post[$mine[0]] = $mine[1];
}
$type = $post['error'];
$path = $post['request'];
$path_pieces = explode("/", $path);
$file = end($path_pieces);
$class = substr($file, 0, -4);
$url = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'];
$parts = explode("/", $_SERVER['PHP_SELF']);
$url .= "/".$parts[1];
?>
<!DOCTYPE html>
    <html>
<head>
    <title> Bad Request</title>
    <link rel="stylesheet" href="../assets/css/requests.css" />
</head>
<body>
<div class="wrapper">
    <div class="head-img">
        <img src="../assets/img/<?php echo ($type == 'script-access') ? 'bad-request-header' : '500-bad-request-header'; ?>.png" />
    </div>
    <div class="header box">
        <div class="header-title">Bad Request Encountered</div>
        <div class="title-sub">
        <p>Bad request, bad, bad request. You naughty little request you.</p>
        <?php
        echo ($type == 'script-access')
            ? "<p>It seems you are trying to access a file directly, sorry but that is not allowed</p>"
            : "<p>It seems like you are trying to get a file that doesn't exist</p>";
            ?>
        </div>
    </div>
    <div class="explanation box">
        <p class="dynamic-reason">
            <?php
            echo ($type == 'script-access')
                ? "In order to access the class ".$class.", you will need to go through the framework and
                    make the proper header requests. We do not allow direct script access."
                : "I know you were looking for ".$file." but it isn't here. Check the url and make sure
                    there aren't any typos or anything. You really should be letting the framework get this file for
                    you, instead of trying to manually grab it.";
            ?>
        </p>
        <p> If you feel like you have reached this page in error and you have made the correct requests, you should
            double check your assumption <a href="http://github.com/danwguy/OOF/wiki" target="_blank">Here</a>.<br />
            If after checking the documentation you still feel that this is an error, please report it as a bug on the
            <a href="http://github.com/danwguy/OOF" target="_blank">github</a> page.
        </p>
    </div>
    <div class="return-home">
        <a href="<?php echo $url; ?>">
            <img src="../assets/img/return-image.png" />
        </a>
    </div>
</div>
</body>
    </html>