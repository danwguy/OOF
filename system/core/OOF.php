<?php


//class OOF {
//
//    public $config;
//    public $input;
//	public $exception;
//    public $output;
//    public $lang;
//
//    public function __construct() {
//
//	    $this->config = Loader::load('Config', 'core');
//
//	    $this->input = Loader::load('Input', 'core');
//
//	    $this->exception = Loader::load('Exceptions', 'core');
//
//        $this->output = Loader::load('Output', 'core');
//        $this->lang = Loader::load('Language');
//
//    }
//
//	public static function show_error($message, $code = 500, $header = 'An Error Occurred') {
//		$error = Loader::load('Exceptions', 'core');
//		echo $error->error($message, $header, 'general_errors', $code);
//		exit();
//	}
//
//	public static function show_404($page = null, $log = false) {
//		$error = Loader::load('Exceptions', 'core');
//		echo $error->show_404($page, $log);
//		exit;
//	}
//
//}
    /**
     * This is just to test an idea I have about making this a shell and funnel
     * requests to the actual target of the controller
     */

    class OOF {


        protected $_controller;
        protected $_log_all;
        protected $_log;

        protected static $_control;
        protected static $_logRequests;

        public function __construct() {
            $rout              = Loader::load('Router');
            $config            = Loader::load('Config');
            $this->_log_all    = self::$_logRequests = ($config->item('logger', 'log_all_requests')) ? true : false;
            $class             = $rout->fetch_class();
            $method            = $rout->fetch_method();
            $this->_controller = self::$_control = Loader::load('Controller', 'core', '', array($class, $method));
        }

        protected static function logAll($data) {
            if(Config::getItem('logging', 'log_all_requests')) {
                $logger = Loader::load('Logger');
                $func = '_'.$data['type'].'_message';
                $message = self::$func($data['args']);
                $logger->log($message);
            }
        }

	    public static function setup_db($config = null, $active_record_override = null) {
		    if(is_string($config) && strpos($config, '://') === false) {
			    if(!$db = Config::getItem('database')) {
				    self::show_error("The site config doesn't appear to have database settings");
			    }
			    if(count($db) == 0) {
				    self::show_error("No database connections settings were found in the config");
			    }
			    if($config != '') {
				    $active_group = $config;
			    } else {
				    $active_group = $db['active'];
			    }
			    if(!isset($active_group) || !isset($db[$active_group])) {
				    self::show_error("You have specified an invalid database connection group");
			    }
			    $config = $db[$active_group];
		    } else if(is_string($config)) {
			    if(($dns = @parse_url($config)) === false) {
				    self::show_error("Invalid DB Connection String");
			    }
			    $config = array(
				    'db_type' => $dns['scheme'],
				    'host' => (isset($dns['host'])) ? rawurldecode($dns['host']) : '',
				    'user' => (isset($dns['user'])) ? rawurldecode($dns['user']) : '',
				    'password' => (isset($dns['pass'])) ? rawurldecode($dns['pass']) : '',
				    'db_name' => (isset($dns['path'])) ? rawurldecode(substr($dns['path'], 1)) : ''
			    );
			    if(isset($dns['query'])) {
				    parse_str($dns['query'], $extra);
				    foreach($extra as $k => $v) {
					    if(strtoupper($v) == "TRUE") {
						    $v = true;
					    } else if(strtoupper($v) == "FALSE") {
						    $v = false;
					    }
					    $config[$k] = $v;
				    }
			    }
		    }
		    //seriously, we havent specified the db by now?
		    if(!isset($config['db_type']) || $config['db_type'] == '') {
			    self::show_error("You have not selected a database type to connect to!");
		    }
		    if($active_record_override !== null) {
			    $active_record = $active_record_override;
		    }
		    require_once(SYS_PATH.'db/DBDriver.php');

		    if(!isset($active_record) || $active_record == true) {
			    require_once(SYS_PATH.'db/DBActiveRecord.php');
			    require_once(SYS_PATH.'db/staging/ActiveRecord.php');
		    } else {
			    require_once(SYS_PATH.'db/staging/Driver.php');
		    }
		    preg_match("/(m)([y|s])(sql)([_](pdo))*$/", $config['db_type'], $matches);
		    if(!$matches) {
			    self::show_error("You do not have a valid database type in your config file. Please open the site config
                            and add a db_type to the database array");
		    }
		    $type_name = array_shift($matches);
		    $driver = strtoupper($matches[0]) .
		              ($matches[1] == 's' ? 'S' : 'y') .
		              strtoupper($matches[2]) .
		              (isset($matches[4]) ? strtoupper($matches[4]) : '').'Driver';
		    require_once(SYS_PATH.'db/drivers/'.$type_name.'/'.$driver.'.php');
		    $DB = new $driver($config);
		    if($DB->auto_connect) {
			    $DB->init();
		    }
		    return $DB;
	    }

        protected function _logging($data) {
            self::logAll($data);
//            if($this->_log_all) {
//                $func = '_'.$data['type']."_message";
//                $message = $this->$func($data['args']);
//                $this->_log->log($message, Logger::DEBUG_FILE, true);
//            }
        }

        public function run() {
            $this->_controller->_template->render();
        }

        public function __get($key) {
            $this->_logging(array(
                                 'type' => 'get',
                                 'args' => $key
                            )
            );
            return (property_exists($this->_controller, $key)) ? $this->_controller->$key : '';
        }

        public function __set($prop, $val) {
            $this->_logging(array(
                                 'type' => 'set',
                                 'args' => array(
                                     'prop' => $prop,
                                     'val' => $val
                                 )
                            )
            );
            $this->_controller->$prop = $val;
            return $this->_controller;
        }

        public function __call($method, $args) {
            $this->_logging(array(
                                 'type' => 'call',
                                 'args' => array(
                                     'method' => $method,
                                     'args' => $args
                                 )
                            )
            );
            return call_user_func_array(array($this->_controller, $method), $args);
        }

        public function __callStatic($method, $args) {
            self::logAll(array(
                                 'type' => 'static',
                                 'args' => array(
                                     'method' => $method,
                                     'args' => $args
                                 )
                            )
            );
            return call_user_func_array(array('Controller', $method), $args);
        }

        protected static function _get_message($data) {
            return 'Access to: '.get_called_class().'->'.$data;
        }

        protected static function _set_message($data) {
            return 'Attempt to set '.get_called_class().'->'.$data['prop']. ' to '.$data['val'];
        }

        protected static function _call_message($data) {
            return 'Executing method '.$data['method']. ' in class '.get_called_class().
                   "\n".'the call was: $'.get_called_class().'->'.$data['method'].'('.implode(", ", $data['args']).')';
        }

        protected static function _static_message($data) {
            return 'Executing static method '.$data['method']. ' in class '.get_called_class().
                   "\n".'The call was: '.get_called_class().'::'.$data['method'].'('.implode(", ", $data['args']).')';
        }

    }