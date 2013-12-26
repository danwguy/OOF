<?php

    class AutoLoad {


        protected $_exclusions = array(
            'ObjectOrientedFramework',
            'ArrayUtil',
            'AesCtr',
            'BreadCrumbs',
            'DateTimeUtil',
            'DriverLibrary',
            'FileManager',
            'LanguageUtil',
            'ApcCache',
            'DummyCache',
            'FileCache',
            'MemcachedCache',
            'SqlQuery',
            'TableObject'
        );

        protected $_load_paths = array(
            'system/core',
            'system/db',
            'system/libraries',
            'system/libraries/cache',
            'system/libraries/cache/drivers',
            'application/controllers',
            'application/models',
            'application/helpers'
        );

        protected $_registered = false;

        protected $_extra = array();

        public function __construct($data = null) {
            if($data) {
                if(!is_array($data)) {
                    $data = array($data);
                }
                $this->add($data);
            }
        }

        public function is_excluded($class) {
            return in_array(ucfirst(LanguageUtil::underscores_to_camel_case($class)), $this->_exclusions);
        }

        public function add(array $data = array()) {
            if(!empty($data)) {
                foreach(array('load_paths', 'exclusions') as $type) {
                    $temp = array();
                    $class_type = "_".$type;
                    if(isset($data[$type])) {
                        foreach($data[$type] as $v) {
                            if(!in_array($v, $this->$class_type)) {
                                $temp[] = $v;
                            }
                        }
                        $this->$class_type = array_merge($temp, $this->$class_type);
                        unset($data[$type]);
                    }
                }
                if(!empty($data)) {
                    $this->_extra = $data;
                }
            }
        }

        public function autoload($class) {
		        $name = null;
	        $filename = null;
	        if(class_exists('LanguageUtil')) {
		        if(in_array(LanguageUtil::underscores_to_camel_case($class), $this->_exclusions)) {
			        $name = LanguageUtil::underscores_to_camel_case($class);
			        $filename = $name.'.php';
		        } else if(class_exists('Config')) {
			        if(Config::getItem('underscore_to_camel_case')) {
				        $name = LanguageUtil::underscores_to_camel_case($class);
				        $filename = $name.'.php';
			        } else {
				        $name = $class;
				        $filename = $name.'.php';
			        }
		        }
	        }
	        if(!$name) {
		        $name = $class;
	        }
	        if(!$filename) {
		        $filename = $name.'.php';
	        }
		        $folder = $this->_load_paths;
            while(!($loaded = (class_exists($name) || interface_exists($name))) && $folder) {
                $path = BASE_PATH . ($f = array_shift($folder)) . "/" . $filename;
                @include_once($path);
            }
            return ($loaded) ? true : false;
        }

        public function register() {
            if(!$this->_registered) {
                spl_autoload_register("AutoLoad::autoload");
                $this->_registered = true;
            }
        }
    }

