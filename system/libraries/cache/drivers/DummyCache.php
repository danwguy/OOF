<?php


    class DummyCache extends Driver {


        public static $return_true = array(
            'delete',
            'clean',
            'save',
            'is_supported'
        );

        public function __call($method, $args) {
            return (in_array($method, self::$return_true)) ? true : false;
        }
    }