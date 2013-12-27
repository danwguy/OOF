<?php


    class CustomFunc {


        public $enabled = false;
        public $in_progress = false;
        public $functions = array();

        protected $_config;


        public function __construct() {
            $this->_config = Loader::load('Config');
            $this->_init();
        }

        private function _init() {
            if(!$this->_config->item('enable_custom_functions')) {
                return;
            }

            if(defined('ENVIRONMENT') && is_file(APP_PATH . 'config/' . ENVIRONMENT . '/functions.php')) {
                include(APP_PATH . 'config/' . ENVIRONMENT . '/functions.php');
            } else if(is_file(APP_PATH . 'config/functions.php')) {
                include(APP_PATH . 'config/functions.php');
            }

            if(!isset($functions) || !is_array($functions)) {
                return;
            }
            $this->functions =& $functions;
            $this->enabled   = true;
        }

        public function run($data) {
            if(!is_array($data)) {
                return false;
            }
            if($this->in_progress) {
                return;
            }

            return $this->_run_function($data);
        }

        private function _run_function($data) {
            if(!isset($data['path']) || !isset($date['filename'])) {
                return false;
            }
            $path = APP_PATH . $data['path'] . '/' . $data['filename'];
            if(!file_exists($path)) {
                return false;
            }
            $class    = false;
            $function = false;
            $params   = null;

            if(isset($data['class']) && $data['class'] != '') {
                $class = $data['class'];
            }
            if(isset($data['function'])) {
                $function = $data['function'];
            }
            if(isset($data['params'])) {
                $params = $data['params'];
            }
            if(!$class && !$function) {
                return false;
            }
            $this->in_progress = true;
            if($class) {
                if(!class_exists($class)) {
                    require_once($path);
                }
                $run = new $class();
                $run->$function($params);
            } else {
                if(!function_exists($function)) {
                    require_once($path);
                }
                /** @noinspection PhpUndefinedFunctionInspection */
                $function($params);
            }
            $this->in_progress = false;

            return true;
        }

        public function call($func = null) {
            if(!$this->enabled || !isset($this->functions[$func])) {
                return false;
            }

            return $this->_call_function($func);
        }

        private function _call_function($func) {
            if(isset($this->functions[$func][0]) && is_array($this->functions[$func][0])) {
                foreach($this->functions[$func] as $v) {
                    $this->run($v);
                }
            } else {
                $this->run($this->functions[$func]);
            }

            return true;
        }
    }