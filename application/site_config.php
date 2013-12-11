<?php

$config = array(
    'class_functions' => array(
        'underscore_to_camel' => true
    ),
    'email' => array(
        'from' => 'robert.mason.lti@gmail.com',
        'reply_to' => 'robert.mason.li@gmail.com',
        'html' => true
    ),
    'database' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pass' => ''
    ),
    'compress_output' => false,
    'cache_path' => '',
    'date_formatting' => array(
        'log_file' => 'Y-m-d H:i:s',
        'screen_output' => 'l \the jS \of F Y \at h:i:s a',
        'db_storage' => 'Y-m-d H:i:s'
    ),
    'allowed_url_chars' => 'a-z 0-9~%.:_\-',
	'allow_get' => false,
	'xss_filter' => false,
	'underscore_to_camel_case' => true,
	'paginate_limit' => 5,
	'site_name' => 'Robert Mason Home'
);
