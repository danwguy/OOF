<?php


    class FileCache extends Driver {


        protected $_cache_path;
        protected $_file_handler;
        protected $_config;

        public function __construct() {
            $this->_config       = Loader::load('Config', 'core');
            $this->_file_handler = Loader::load('FileManager', 'core');
            $path                = $this->_config->item('cache_path');
            $this->_cache_path   = ($path == '') ? APP_PATH . 'cache/' : $path;
        }

        public function delete($filename) {
            return unlink($this->_cache_path . $filename);
        }

        public function cache_info($type = null) {
            return $this->_file_handler->get_directory_info($this->_cache_path);
        }

        public function wipe() {
            return $this->_file_handler->delete_all_in_directory($this->_cache_path);
        }

        public function get($filename) {
            $filename = $this->_cache_path . $filename;
            if(!file_exists($filename)) {
                return false;
            }
            $data = read_file($filename);
            $data = unserialize($data);

            if(time() > $data['time'] + $data['ttl']) {
                unlink($filename);

                return false;
            }

            return $data['data'];

        }

        public function is_supported() {
            return $this->_file_handler->is_file_writable($this->_cache_path);
        }

        public function get_metadata($filename) {
            if(!file_exists($this->_cache_path . $filename)) {
                return false;
            }
            $data = $this->_file_handler->read_file($this->_cache_path . $filename);
            $data = unserialize($data);

            if(is_array($data)) {
                $mymtime = filemtime($this->_cache_path . $filename);
                if(!isset($data['ttl'])) {
                    return false;
                }

                return array(
                    'expire' => $mymtime + $data['ttl'],
                    'mtime'  => $mymtime
                );
            }

            return false;
        }

        public function save($filename, $data, $ttl = 90) {
            $savable_array = array(
                'time' => time(),
                'data' => $data,
                'ttl'  => $ttl
            );
            if($this->_file_handler->create_file($this->_cache_path . $filename, serialize($savable_array))) {
                @chmod($this->_cache_path . $filename, 0777);

                return true;
            }

            return false;
        }

        protected static function getPath() {
            return ROOT . DS . 'tmp' . DS . 'cache' . DS;
        }

    }