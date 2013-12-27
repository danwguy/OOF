<?php


    class Model extends TableObject {


        protected $_model;

        public function __construct() {

            $inflect = Loader::load('Inflection', 'core');

            $this->_model = get_class($this);
            $this->_table = strtolower($inflect->pluralize($this->_model));
            $db           = DB::get();
            $db->setTable($this->_table);
            $db->setModel($this->_model);
            $args = func_get_args();
            $args = array_shift($args);

            if(!isset(self::$column_list)) {
                $this->_describe();
            }
            parent::__construct($args);
            $this->_limit = $this->config->item('paginate_limit');
        }

        public function __get($key) {
            if(isset($this->$key)) {
                return $this->$key;
            } else {
                $OOF = Loader::load('OOf', 'core');

                return $OOF->$key;
            }
        }

        public function __destruct() { }

    }