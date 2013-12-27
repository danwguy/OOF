<?php


    class Cache extends DriverLibrary {


        public static $support = array();

        protected $_valid_drivers = array(
            'cache_apc', 'cache_memcached', 'cache_file', 'cache_dummy'
        );
        protected $_adapter = 'dummy';
        protected $_cache_path = null;
        protected $_backup_driver;

        public function __construct($config = array()) {
            if(!empty($config)) {
                $this->_init($config);
            }
        }

        public function save($id, $data, $ttl = 90) {
            return $this->{$this->_adapter}->save($id, $data, $ttl);
        }

        public function get($id) {
            return $this{$this->_adapter}->get($id);
        }

        public function get_metadata($id) {
            return $this->{$this->_adapter}->get_metadata($id);
        }

        public function delete($id) {
            return $this->{$this->_adapter}->delete($id);
        }

        public function info($type = 'user') {
            return $this->{$this->_adapter}->info($type);
        }

        public function clean() {
            return $this->{$this->_adapter}->wipe();
        }

        public function __get($child) {
            $obj = parent::__get($child);
            if(!$this->is_supported($child)) {
                $this->_adapter = $this->_backup_driver;
            }

            return $obj;
        }

        public function is_supported($driver) {
            if(!isset(self::$support[$driver])) {
                self::$support[$driver] = $this->{$driver}->is_supported();
            }

            return self::$support[$driver];
        }

        private function _init($config) {

            foreach(array('adapter', 'memcached') as $k) {
                if(isset($config[$k])) {
                    $param          = '_' . $k;
                    $this->{$param} = $config[$k];
                }
            }
            if(isset($config['backup'])) {
                if(in_array('cache_' . $config['backup'], $this->_valid_drivers)) {
                    $this->_backup_driver = $config['backup'];
                }
            }
        }

    }