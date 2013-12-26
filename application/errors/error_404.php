

	<!DOCTYPE html>
<html lang="en">
<head>
<title>404 Page Not Found</title>
<link rel="stylesheet" href="../../system/assets/css/requests.css" />
</head>
<body>
<?php
    $pieces = (isset($_SERVER['REDIRECT_QUERY_STRING'])) ? $_SERVER['REDIRECT_QUERY_STRING'] : false;
    if($pieces) {
        $post = array();
        $parts = explode("&", $pieces);
        foreach($parts as $data) {
            $yep = explode("=", $data);
            $post[$yep[0]] = $yep[1];
        }
    }
    $url = $post['url'];
    $url_chunks = explode("/", $url);
    $file = end($url_chunks);
    $url = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'];
    $parts = explode("/", $_SERVER['PHP_SELF']);
    $url .= "/".$parts[1];
?>
<div class="wrapper">
    <div class="head-img">
        <img src="../../system/assets/img/bad-request-header.png" />
    </div>
    <div class="header box">
        <div class="header-title">Oh snap! that's not right!</div>
        <div class="title-sub">
            <p><?php echo $message; ?></p>
        </div>
    </div>
    <div class="explanation box">
        <p class="dynamic-reason">
            I know you were looking for <?php echo $file; ?> but it isn't here. Check the url and make sure
            there aren't any typos or anything. You really should be letting the framework get this file for
            you, instead of trying to manually grab it
        </p>
        <p> If you feel like you have reached this page in error and you have made the correct requests, you should
            double check your assumption <a href="http://github.com/danwguy/OOF/wiki" target="_blank">Here</a>.<br />
            If after checking the documentation you still feel that this is an error, please report it as a bug on the
            <a href="http://github.com/danwguy/OOF" target="_blank">github</a> page.
        </p>
    </div>
    <div class="return-home">
        <a href="<?php echo $url; ?>">
            <img src="../../system/assets/img/return-image.png" />
        </a>
    </div>
</div>
<!--	<div id="container">-->
<!--		<h1>--><?php //echo $heading; ?><!--</h1>-->
<?php //echo $message; ?>
<!--</div>-->
</body>
</html>