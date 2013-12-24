<?php
$attempt = $_SERVER['REDIRECT_QUERY_STRING'];
$pieces = explode("=", $attempt);
$path = end($pieces);
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
        <img src="../assets/img/bad-request-header.png" />
    </div>
    <div class="header box">
        <div class="header-title">Bad Request Encountered</div>
        <p>It seems you are trying to access a file directly, sorry but this is not allowed.</p>
    </div>
    <div class="explanation box">
        <p class="dynamic-reason">
            In order to access the class <?php echo $class; ?>, you will need to go through the framework and
            make the proper header requests. We do not allow direct script access.
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