<?php


    class Benchmark {


        public $markers = array();

        public function add($name) {
            $this->markers[$name] = microtime();
        }

        public function time_elapsed($start = null, $end = null, $decimals = 4) {
            if(!$start) {
                return '{time_elapsed}';
            }
            if(!isset($this->markers[$start])) {
                return '';
            }
            if(!isset($this->markers[$end])) {
                $this->markers[$end] = microtime();
            }
            list($sm, $ss) = explode(' ', $this->markers[$start]);
            list($em, $es) = explode(' ', $this->markers[$end]);

            return number_format(($em + $es) - ($sm + $ss), $decimals);
        }

        public function memory_usage() {
            return '{memory_usage}';
        }

    }