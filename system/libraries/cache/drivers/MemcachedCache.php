<?php
    /**
     * Created by JetBrains PhpStorm.
     * User: robert
     * Date: 10/10/13
     * Time: 7:46 AM
     * To change this template use File | Settings | File Templates.
     */

    class MemcachedCache extends Driver {


        private $_memcached;

        protected $_memcached_config = array(
            'default' => array(
                'default_host'   => '127.0.0.1',
                'default_port'   => 11211,
                'default_weight' => 1
            )
        );


        public function get($id) {
            return (is_array($this->_memcached->get($id))) ? $this->_memcached->get($id) : false;
        }

        public function delete($id) {
            return $this->_memcached->delete($id);
        }

        public function save($id, $data, $ttl = 90) {
            if(get_class($this->_memcached) == 'Memcached') {
                return $this->_memcached->set($id, array($data, time(), $ttl), $ttl);
            } else if(get_class($this->_memcached) == 'Memcache') {
                return $this->_memcached->set($id, array($data, time(), 0, $ttl));
            }

            return false;
        }

        public function get_metadata($id) {
            $stored = $this->_memcached->get($id);
            if(sizeof($stored) !== 3) {
                return false;
            }
            list($data, $time, $ttl) = $stored;

            return array(
                'expire' => $time + $ttl,
                'mtime'  => $time,
                'data'   => $data
            );
        }

        public function is_supported() {
            if(!extension_loaded('memcached')) {
                return false;
            }
            $this->_setup_memcached();

            return true;
        }

        public function wipe() {
            $this->_memcached->flush();
        }

        public function cache_info($type = null) {
            return $this->_memcached->getStats();
        }

        private function _setup_memcached() {
            $OOF = OOF::get_instance();
            if($OOF->config->load('memcached', true, true)) {
                if(is_array($OOF->config->config['memcached'])) {
                    $this->_memcached_config = null;
                    foreach($OOF->config->config['memcached'] as $name => $option) {
                        $this->_memcached_config[$name] = $option;
                    }
                }
            }

            $this->_memcached = new Memcached();

            foreach($this->_memcached_config as $name => $config_val) {
                if(!array_key_exists('hostname', $config_val)) {
                    $config_val['hostname'] = $this->_default_options['default_host'];
                }
                if(!array_key_exists('post', $config_val)) {
                    $config_val['port'] = $this->_default_options['default_port'];
                }
                if(!array_key_exists('weight', $config_val)) {
                    $config_val['weight'] = $this->_default_options['default_weight'];
                }
                $this->_memcached->addServer($config_val['hostname'], $config_val['port'], $config_val['weight']);
            }
        }

    }