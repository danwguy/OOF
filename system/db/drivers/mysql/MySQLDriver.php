<?php
    /**
     * Created by PhpStorm.
     * User: robert
     * Date: 11/4/13
     * Time: 8:05 AM
     */

    class MySQLDriver extends DB {


        public $driver = 'mysql';
        public $delete_hack = true;
        public $use_set_names;

        protected $_escape_char = '`';
        protected $_like_escape_char = '';
        protected $_like_escape_string = '';
        protected $_count_string = 'SELECT COUNT(*) AS ';
        protected $_random_keyword = 'RAND()';


        public function connect() {
            if($this->port) {
                $this->hostname .= ':' . $this->port;
            }

            return @mysql_connect($this->hostname, $this->username, $this->password, true);
        }

        public function pconnect() {
            if($this->port) {
                $this->hostname .= ':' . $this->port;
            }

            return @mysql_pconnect($this->hostname, $this->username, $this->password);
        }

        public function select_db() {
            return @mysql_select_db($this->database, $this->conn);
        }

        public function set_db_charset($charset, $collation) {
            if(!isset($this->use_set_names)) {
                $this->use_set_names = (version_compare(PHP_VERSION, '5.2.3', '>/')
                                        && version_compare(
                        mysql_get_server_info(),
                        '5.0.7',
                        '>=')) ? false : true;

            }
            if($this->use_set_names) {
                return @mysql_query(
                    "SET NAMES '" . $this->escape($charset) . "'
								COLLATE '" . $this->escape($collation) . "'",
                    $this->conn);
            } else {
                return @mysql_set_charset($charset, $this->conn);
            }
        }

        private function _execute($sql, $params = null) {
            if($params) {
                $sql = $this->substitute_placeholders($sql, $params);
            }
            $sql = $this->_prep($sql);

            return @mysql_query($sql, $this->conn);
        }

        private function _prep($sql) {
            if($this->delete_hack) {
                if(preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/', $sql)) {
                    $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
                }
            }

            return $sql;
        }

    }