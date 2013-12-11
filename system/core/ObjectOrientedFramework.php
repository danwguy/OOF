<?php


    define('OOF_VERSION', '1.0b');
    define('DEVELOPMENT_ENVIRONMENT', true);

    if (defined('DEVELOPMENT_ENVIRONMENT') && DEVELOPMENT_ENVIRONMENT) {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 'off');
        ini_set('log_errors', 'On');
        ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');
    }

    if (defined('ENVIRONMENT') && file_exists(APP_PATH . 'config/' . ENVIRONMENT . '/constants.php')) {
        require_once(APP_PATH . 'config/' . ENVIRONMENT . '/constants.php');
    } else {
        require_once(APP_PATH . 'config/constants.php');
    }
    require_once(SYS_PATH . 'core/AutoLoad.php');

    if (defined('ENVIRONMENT') && file_exists(APP_PATH . 'config/' . ENVIRONMENT . '/autoload.php')) {
        require_once(APP_PATH . 'config/' . ENVIRONMENT . '/autoload.php');
    } else {
        require_once(APP_PATH . 'config/autoload.php');
    }


    if (isset($autoload) && is_array($autoload) && !empty($autoload)) {
        $AUT = new AutoLoad($autoload);
    } else {
        $AUT = new Autoload();
    }


    $AUT->register();

    set_error_handler('Exceptions::handleException');

    $bm = Loader::load('Benchmark');
    $bm->add('total_execution_time_start');
    $bm->add('loading_time:_base_classes_start');

    $FUNC = Loader::load('CustomFunc');
    $FUNC->call('pre_system');



    $CNFG = Loader::load('Config', 'core');

    if (isset($assign_to_config) && !empty($assign_to_config)) {
        $CNFG->_assign($assign_to_config);
    }

    $URI = Loader::load('URI', 'core');

    $ROUT = Loader::load('Router', 'core');

    $ROUT->route();

    if (isset($routing)) {
        $ROUT->_set_overrides($routing);
    }

    $OUT = Loader::load('Output');

    if ($FUNC->call('cache_override') === false) {
        if ($OUT->_display_cache($CNFG, $URI) === true) {
            exit;
        }
    }


    $SEC = Loader::load('Security', 'core');

    $INP = Loader::load('Input', 'core');

    $LANG = Loader::load('Language');

    $INF = Loader::load('Inflection', 'core');


    if (!file_exists(APP_PATH . 'controllers/' . $ROUT->fetch_directory() . $ROUT->fetch_class() . '.php')
        && !file_exists(APP_PATH . 'controllers/' . $ROUT->fetch_directory() . $ROUT->fetch_class() . 'Controller.php')) {
        echo "<br />Shit we ran into an error here<br />";
        echo "<br />";
        echo "<br />";
        echo "<br />" . APP_PATH . 'controllers/' . $ROUT->fetch_directory() . $ROUT->fetch_class() . '.php';
        echo "<br />" . APP_PATH . 'controllers/' . $ROUT->fetch_directory() . $ROUT->fetch_class() . 'Controller.php';
        echo "<br /><br /><br /><br /><br />";
        OOF::show_error(
            "Unable to load the default controller. Please open " . APP_PATH
            . 'config/routing.php and set a default controller'
        );
    }
    $bm->add('loading_time:_base_classes_end');


    $class  = $ROUT->fetch_class();
    $method = $ROUT->fetch_method();

    $controllerName = ($CNFG->item('class_name', 'underscore_to_camel_case'))
        ? ucfirst(LanguageUtil::underscores_to_camel_case($class))
        : ucfirst($class);
    $parts          = explode('Controller', $controllerName);
    $controllerName = $parts[0] . 'Controller';

    if (!class_exists($controllerName) || strncmp($method, '_', 1) == 0) {
        if (!empty($ROUT->routes['404_override'])) {
            $ex     = explode('/', $ROUT->routes['404_override']);
            $class  = $ex[0];
            $method = (isset($ex[1])) ? $ex[1] : 'index';
            if (!class_exists($class)) {
                OOF::show_404("{$class}/{$method}");
            }
        } else {
            OOF::show_404("{$class}/{$method}");
        }
    }


    $load = Loader::load('Loader', 'core');

    if (!class_exists($controllerName)) {
        OOF::show_error(
            "Unable to load the default controller. Please make sure the controller is properly specified
                                     in the " . APP_PATH . 'config/routing.php file'
        );
    }

    $FUNC->call('pre_controller');

    $bm->add('controller_execution_time_(' . $controllerName . ' / ' . $method . ')_start');

    $OOF = Loader::load('OOF', 'core');

    $dispatch = new $controllerName($class, $method);

    $FUNC->call('post_controller_construct');

    if (method_exists($dispatch, '_remap')) {
        $dispatch->_remap($method, array_slice($URI->rsegments, 2));
    } else {
        if (!method_exists($controllerName, $method)) {
            if (!empty($ROUT->routes['404_override'])) {
                $ex     = explode('/', $ROUT->routes['404_override']);
                $class  = $ex[0];
                $method = (isset($ex[1])) ? $ex[1] : $ROUT->routes['default_method'];
                if (!class_exists($class)) {
                    OOF::show_404("{$class}/{$method}");
                }
                unset($dispatch);
                $dispatch = new $class();
            } else {
                OOF::show_404("{$class}/{$method}");
            }
            //error generation goes here for now we just echo and exit
            echo "SOMETHING FAILED AND THERE WAS NO METHOD: " . $action . " INSIDE THE CONTROLLER: " . $controllerName
                 . "<br />";
            echo "<pre>";
            print_r($dispatch);
            echo "</pre>";
            exit();
        }
        if (method_exists($controllerName, 'beforeAction')) {
            call_user_func_array(array($dispatch, "beforeAction"), array_slice($URI->rsegments, 2));
        }


        call_user_func_array(array($dispatch, $method), array_slice($URI->rsegments, 2));


        if (method_exists($controllerName, 'afterAction')) {
            call_user_func_array(array($dispatch, "afterAction"), array_slice($URI->rsegments, 2));
        }

        $bm->add('controller_execution_time_(' . $controllerName . ' / ' . $method . ')_end');

        $FUNC->call('post_controller');

    }
    if ($FUNC->call('display_override') == false) {
        $OUT->display();
    }

    $FUNC->call('post_system');
