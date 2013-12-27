<?php


    class ApcCache extends Driver {


        public function save($id, $data, $ttl = 90) {
            return apc_store($id, array($data, time(), $ttl), $ttl);
        }

        public function wipe() {
            return apc_clear_cache('user');
        }

        public function get($id) {
            return (is_array(apc_fetch($id))) ? apc_fetch($id) : false;
        }

        public function is_supported() {
            return (!extension_loaded('apc') || ini_get('apc.enabled') != "1") ? false : true;
        }

        public function cache_info($type = null) {
            return apc_cache_info($type);
        }

        public function get_metadata($id) {
            $stored = apc_fetch($id);

            if(sizeof($stored) !== 3) {
                return false;
            }
            list($data, $time, $ttl) = $stored;

            return array(
                'expire' => $time + $ttl,
                'data'   => $data,
                'mtime'  => $time
            );
        }


    }