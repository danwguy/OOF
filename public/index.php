<?php

define('DS', DIRECTORY_SEPARATOR);




	/*
	 * You can set this to anything you want and the application
	 * will try to load config files from application/{ENVIRONMENT}/site_config.php
	 * If none is found there the application will fall back to application/site_config.php
	 * this way you don't have to change the directories but you can if you want to
	 */
	define('ENVIRONMENT', 'production');

	/*
	 * This can be set to whatever you like and the system will load files from this dir
	 * default is ../application and it is recommended to keep your site inside of that folder
	 * and create sub-folders for different sites. i.e. application/robs_blog
	 * Same goes for the system_folder, default is ../system. You can change it if you
	 * decide to move the system folder but make sure you update this
	 * these are relative to this file, hence the ../
	 */
	$application_folder = '../application';

	$system_folder = '../system';

	/*
	 *Use this to setup where you would like framework to look for your
	 * classes. By default it is the application/controllers and
	 * application/models directories but you can change them to whatever
	 * you would like;
	 */
	$autoload['application'] = array('controllers', 'models');


	/*
	 * You can add things to the config array here that will dynamically be passed to
	 * the config class when it is loaded. Just use the below format.
	 */
//	$add_to_config['name_of_item'] = 'value_of_item';

	if(realpath($system_folder)) {
		$system_folder = realpath($system_folder) . '/';
	}

	$system_folder = rtrim($system_folder, '/').'/';

	if(!is_dir($system_folder)) {
		exit('Your system folder does not exist. Please open up the '.pathinfo(__FILE__, PATHINFO_BASENAME).
	         ' and fix the $system_folder variable');
	}




	//Name of this file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	//base path
	define('BASE_PATH', str_replace("\\", "/", realpath($system_folder.'../')).'/');

	define('SYSDIR', trim(strrchr(trim(str_replace("\\", "/", BASE_PATH), '/'), '/'), '/'));

	//Path to the online instance
	define('SYS_PATH', str_replace("\\", "/", $system_folder));

	//path to the public folder
	define('PUBLIC_PATH', BASE_PATH . 'public/');

	//path to the images folder
	define('IMAGE_PATH', PUBLIC_PATH . 'img/');

	//path to the css folder
	define('CSS_PATH', PUBLIC_PATH . 'css/');

	//path to the javascript folder
	define('JS_PATH', PUBLIC_PATH . 'js/');

    define('TMP_PATH', BASE_PATH.'tmp/');

	//path to the application
    if(is_dir($application_folder)) {
        define('APP_PATH', str_replace("\\", "/", realpath($application_folder)).'/');
    } else {
        if(!is_dir(BASE_PATH.$application_folder.'/')) {
            exit("Sorry your application folder does not seem to exists. Please open up the ".SELF." File
                    and fix the problem");
        }
        define('APP_PATH', BASE_PATH.$application_folder.'/');
    }



$url = (isset($_GET['url'])) ? $_GET['url'] : '';
require_once(SYS_PATH.'core/ObjectOrientedFramework.php');