<?php

$routing = array('/admin\/(.*?)\/(.*?)\/(.*)/' => 'admin/\1_\2/\3');

$routing['default_controller'] = 'HomeController';
$routing['default_method'] = 'index';