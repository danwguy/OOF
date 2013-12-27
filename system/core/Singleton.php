<?php


    abstract class Singleton {


        public $config;

        public static $instances = array();

        abstract function construct();

        protected final function __construct() {
//        if(!isset($this->config)) {
//            $this->config = Loader::load('Config');
//        }
            $this->construct();
        }

        public static function get_instance() {
            $class = get_called_class();
            if(!isset(self::$instances[$class])) {
                self::$instances[$class] = new $class;
            }

            return self::$instances[$class];
        }

        public final function __clone() {
            trigger_error("Unable to clone singleton class __CLASS__", E_USER_ERROR);
        }

    }