<?php
    echo $html->doctype();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>My E-Commerce Website</title>
    <?php
        echo $html->meta('Content-Type', 'text/html; charset=utf-8', 'equiv');
        $meta = array(
            array('name' => 'robots', 'content' => 'no-cache'),
            array('name' => 'description', 'content' => 'OOF tutorial'),
            array('name' => 'keywords', 'content' => 'Electronics')
        );
        echo $html->meta($meta);
        echo $html->includeCss('style');
    ?>
    <style>

    </style>
</head>

<body>
    <div class="navigation">
<?php
    echo $html->heading('My E-Commerce Website');
    $captcha = Captcha::get_instance();
    $captcha->create();
    echo $captcha->image;
?>
