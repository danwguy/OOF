<?php



	if(defined('DEVELOPMENT_ENVIRONMENT') && DEVELOPMENT_ENVIRONMENT) {
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
	} else {
		error_reporting(E_ALL);
		ini_set('display_errors', 'off');
		ini_set('log_errors', 'On');
		ini_set('error_log', BASE_PATH . 'tmp' . DS . 'logs' . DS . 'error.log');
	}


function stripSlashesDeep($value) {
    $value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
    return $value;
}

function removeMagicQuotes() {
    if(get_magic_quotes_gpc()) {
        $_GET = stripSlashesDeep($_GET);
        $_POST = stripSlashesDeep($_POST);
        $_COOKIE = stripSlashesDeep($_COOKIE);
    }
}

function unregisterGlobals() {
    if(ini_get('register_globals')) {
        $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
        foreach($array as $value) {
            foreach($GLOBALS[$value] as $key => $var) {
                if($var === $GLOBALS[$key]) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
}

    function performAction($controller, $action, $querystring = null, $render = 0) {
        $controllername = ucfirst($controller).'Controller';
        $dispatch = new $controllername($controller, $action);
        $dispatch->render = $render;
        return call_user_func_array(array($dispatch, $action), $querystring);
    }

    function routeUrl($url) {
        global $routing;

        foreach($routing as $pattern => $result) {
            if(preg_match($pattern, $url)) {
                return preg_replace($pattern, $result, $url);
            }
        }

        return ($url);
    }

function callHook() {
    global $url;
    global $defaults;


    if(!isset($url) || $url == '') {
        $controller = $defaults['controller'];
        $action = $defaults['action'];
	    $queryString = '';
    } else {
        $url = routeUrl($url);
        $urlArray = explode("/", $url);
        $controller = array_shift($urlArray);
        if(isset($urlArray[0])) {
            $action = array_shift($urlArray);
        } else {
            $action = $defaults['action'];
        }
        $queryString = $urlArray;
    }
	$queryString = is_array($queryString) ? $queryString : array($queryString);
	$controllerName = ucfirst(LanguageUtil::underscores_to_camel_case($controller)).'Controller';

	$dispatch = new $controllerName($controller, $action);

	if((int)method_exists($controllerName, $action)) {
        call_user_func_array(array($dispatch, "beforeAction"), $queryString);
        call_user_func_array(array($dispatch, $action), $queryString);
        call_user_func_array(array($dispatch, "afterAction"), $queryString);
    } else {
        //error generation goes here for now we just echo and exit
        echo "SOMETHING FAILED AND THERE WAS NO METHOD: ".$action." INSIDE THE CONTROLLER: ".$controllerName."<br />";
        echo "<pre>";
        print_r($dispatch);
        echo "</pre>";
        exit();
    }
}

    function __autoload($class) {
        $filename     = $class . ".php";
        $folder_array = array(
            "system/core", "system/db", "system/libraries", 'application/controllers', "application/models", "application/libraries", "application/helpers");
        while (!($loaded = (class_exists($class) || interface_exists($class))) && $folder_array) {
            $path = BASE_PATH. "" . ($folder = array_shift($folder_array)) . "/" . $filename;
            @include_once $path;
        }
        if ($loaded) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     * new code
     */
    function gzipOutput() {
        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (0 !== strpos($ua, 'Mozilla/4.0 (compatible; MSIE ')
            || false !== strpos($ua, 'Opera')) {
            return false;
        }

        $version = (float)substr($ua, 30);
        return (
            $version < 6
            || ($version == 6  && false === strpos($ua, 'SV1'))
        );
    }


//    gzipOutput() || ob_start("ob_gzhandler");

	$inflect = Loader::load('inflection', 'core');
//    setReporting();
    removeMagicQuotes();
    unregisterGlobals();
    callHook();