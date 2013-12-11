<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 8/27/13
 * Time: 12:33 PM
 * To change this template use File | Settings | File Templates.
 */

    class DB {

        public $conn;
        public $logging_conn;
        public $query_count;
        public $transaction_level;
	    public $config;
	    public $inflect;


        protected static $instance;
        protected $_dbHandle;
	    protected $_db_config = array();
        protected $_active;

        public static function get() {
            if(!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        function __construct() {
	        $this->config = Loader::load('Config', 'core');
	        $this->_db_config = $this->config->item('database');
            $active = $this->_db_config['active'];
            $this->_active = $this->_db_config[$active];
	        $this->inflect = Loader::load('Inflection', 'core');
            $this->query_count = 0;
            $this->transaction_level = 0;
            $this->connect();
        }

        function connect() {
            $this->conn = mysql_connect(
	            $this->_active['host'],
	            $this->_active['user'],
	            $this->_active['password']
            );
            $this->_dbHandle = $this->conn;
            if (!$this->conn) {
                $this->error("Connection failed at ".DateTimeUtil::format(date_create(), DateTimeUtil::DATETIME_FORMAT_LOGGING)." using DB_HOST: ".DB_HOST.", DB_USER: ".DB_USER);
            }
            mysql_select_db(
	            $this->_active['db_name']
	            , $this->conn
            );
            $this->logging_conn = mysql_connect(
	            $this->_active['host'],
	            $this->_active['user'],
	            $this->_active['password'],
	            true
            );
            if (!$this->logging_conn) {
                $this->error("Connection failed at ".DateTimeUtil::format(date_create(), DateTimeUtil::DATETIME_FORMAT_LOGGING)." using DB_HOST: ".DB_HOST.", DB_USER: ".DB_USER);
            }
	        mysql_select_db(
		        $this->_active['db_name']
		        , $this->logging_conn
	        );
        }

        function disconnect() {
            if ($this->conn) {
                mysql_close($this->conn);
                mysql_close($this->logging_conn);
            }
        }

        function fetch_all($sql, $params = null) {
            $result = $this->execute($sql, $params);
            if (!$result || mysql_num_rows($result) == 0) {
                return array();
            } else {
                $rows = null;
                $num_fields = mysql_num_fields($result);
                /** @noinspection PhpAssignmentInConditionInspection */
                while ($row = mysql_fetch_row($result)) {
                    $assoc_row = array();
                    for ($i = 0; $i < $num_fields; $i++) {
                        $field = mysql_fetch_field($result, $i);
                        $key = $field->name;
                        $value = $row[$i];
                        if (!isset($assoc_row[$key])) { //if there is a key conflict, give priority to the first
                            if (is_null($value)) { //null values should stay as null objects
                                $assoc_row[$key] = null;
                            }  else if (in_array($field->type, array("date"))) { //if the field is a regular date/datetime
                                $assoc_row[$key] = new DateTime($value, new DateTimeZone(date_default_timezone_get())); //Dates don't have a "timezone" so we'll just use the one that the system is using.
                            } else if (in_array($field->type, array("datetime", "timestamp"))) { //if the field is a regular date/datetime
                                $datetime = new DateTime($value, new DateTimeZone("UTC")); //Datetimes come out of the db in UTC, so make sure PHP treats them as such.
                                $assoc_row[$key] = $datetime->setTimezone(new DateTimeZone(date_default_timezone_get())); //UTC may not be so useful, so convert to the timezone that php is using at the time
                            } else if (is_numeric($value)) { //make an int or float if you can
                                if (($int_value = intval($value)) == $value) { //make it an int if it is one
                                    $assoc_row[$key] = $int_value;
                                } else if ((string)($float_value = (float)$value) === (string)$value) { //make it a float if there isn't any precision loss
                                    $assoc_row[$key] = $float_value;
                                } else {
                                    $assoc_row[$key] = $value;
                                }
                            } //this needs to go last, otherwise fields that store general information might not handle numbers correctly.
                            else if (in_array($field->type, array("blob", "text", "tinytext", "mediumtext", "longtext"))) { //text fields might be storing serialized arrays
                                $decoded_array = json_decode($value, true);
                                if (json_last_error() == JSON_ERROR_NONE) {
                                    $assoc_row[$key] = $decoded_array;
                                } else {
                                    $assoc_row[$key] = $value;
                                }
                            } else {
                                $assoc_row[$key] = $value;
                            }
                        }
                    }
                    $rows[] = $assoc_row;
                }
                return $rows;
            }
        }

        function fetch_column($sql, $params = null, $column_name = null) {
            $rows = $this->fetch_all($sql, $params);
            if ($rows) {
                $return_array = array();
                foreach ($rows as $row) {
                    if ($column_name && isset($row[$column_name])) {
                        $return_array[] = $row[$column_name];
                    } else {
                        $return_array[] = array_shift($row);
                    }
                }
                return $return_array;
            } else {
                return array();
            }
        }

        function fetch_one($sql, $params = null, $limit_one = true) {
            if ($limit_one && !preg_match("/(?i)[\s\n]LIMIT[\s\n]+\d/", $sql)) {
                $sql .= " LIMIT 1";
            }
            $rows = $this->fetch_all($sql, $params);
            if ($rows) {
                return array_shift($rows);
            } else {
                return array();
            }
        }

        function fetch_value($sql, $params = null) {
            $row = $this->fetch_one($sql, $params, false);
            if ($row) {
                return array_shift($row);
            } else {
                return null;
            }
        }

        function prepare_param($param) {
            //php truth primatives should turn into database truth primatives
            if (is_null($param)) {
                return "NULL";
            } else if ($param === false) {
                return "FALSE";
            } else if ($param === true) {
                return "TRUE";
            } else {
                $param = LanguageUtil::to_string($param, true);
                // When someone searched "'" or "john'" it would break the search
                if (preg_match("/^['`].*?['`]$/", $param)) {
                    $param = trim($param, "'`"); //remove existing single quotes
                }
                $param = mysql_real_escape_string($param); //escape for safety
                $param = str_replace("?", "'||0x3F||'", $param); //specially encode question marks in a param so PHP doesn't think they are another param.
                return "'".$param."'"; //return the cleaned param with single quotes around it
            }
        }

        function substitute_placeholders($sql, $params) {
            if ($params) {
                $parsed_params = array_map(array($this, "prepare_param"), $params);
                $sql_chars = str_split($sql);
                foreach ($sql_chars as &$char) {
                    if ($char == "?") {
                        $char = array_shift($parsed_params);
                    }
                }
                $sql = implode("", $sql_chars);
            }
            return $sql;
        }

        function execute($sql, $params = null) {
            $parsed_sql = $this->substitute_placeholders($sql, $params);
            $query_start_time = microtime(true);
            try {
                $result = mysql_query($parsed_sql, $this->conn);
            } catch (Exception $e) {
                $this->error($parsed_sql);
                throw $e;
            }
            $query_end_time = microtime(true);
            if (defined("LOG_SQL_QUERIES") && LOG_SQL_QUERIES) {
                mysql_query("INSERT INTO log_sql_queries SET
			query='".mysql_real_escape_string($sql)."',
			query_hash='".md5($sql)."',
			date='".DateTimeUtil::format(new DateTime, DateTimeUtil::DATE_FORMAT_MYSQL)."',
			total_time='".($query_end_time-$query_start_time)."',
			count='1',
			request_uri='".mysql_real_escape_string(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "")."'
			ON DUPLICATE KEY UPDATE
			total_time=total_time+'".($query_end_time-$query_start_time)."',
			count=count+1", $this->logging_conn);
            }

            // log the query, including query count and execution time
            $s = "Query #: ".$this->query_count++."\n";
            $s .= "Execution Time (seconds): ".round($query_end_time-$query_start_time, 4);
            $s .= "\n";
            $s .= "Query string: ".stripcslashes(preg_replace("/\s+/", " ", $parsed_sql));
            if($this->config->item('logging', 'log_sql')) {
                Logger::get_instance()->log_sql($s);
            }

            if (!$result) {
                $this->error($parsed_sql);
            }
            return $result;
        }

        function begin_trans() {
            $this->transaction_level = max($this->transaction_level+1, 1);
            if ($this->transaction_level == 1) {
                $sql = "START TRANSACTION";
                return $this->execute($sql);
            } else {
                return null;
            }
        }

        function commit_trans() {
            if ($this->transaction_level == 1) {
                $this->transaction_level = max($this->transaction_level-1, 0);
                $sql = "COMMIT";
                return $this->execute($sql);
            } else {
                $this->transaction_level = max($this->transaction_level-1, 0);
                return null;
            }
        }

        function rollback_trans() {
            $this->transaction_level = 0;
            $sql = "ROLLBACK";
            return $this->execute($sql);
        }

        function disable_fk_checks() {
            $sql = "SET foreign_key_checks = 0";
            return $this->execute($sql);
        }

        function enable_fk_checks() {
            $sql = "SET foreign_key_checks = 1";
            return $this->execute($sql);
        }

        function num_rows($result) {
            if ($result) {
                return mysql_num_rows($result);
            } else {
                return null;
            }
        }

        function num_results(array $results) {
            if($results) {
                return count($results);
            } else {
                return null;
            }
        }

        function last_insert_id() {
            return mysql_insert_id($this->conn);
        }

        function affected_rows() {
            return mysql_affected_rows($this->conn);
        }

        # TODO: add automatic rollback in error-handling?
        function error($sql = null) {
            $error = mysql_error($this->conn);
            echo "There was an error with the query ".nl2br($sql)."<br />  The database said \"$error\"";

            if ($sql) {
                Logger::get_instance()->log("DB error: $sql");
            }

            # log the error
            Logger::get()->log("DB error: $error");
            //$GLOBALS['logger']->debug(print_r(debug_backtrace(), true));
            throw new Exception("DB error: $error");
        }

        public function verbose($function, $sql, $params = null) {
            echo "<pre>";
            echo "SQL Template:<br />";
            print_r($sql);
            echo "</pre><br />";
            echo "<pre>";
            if ($params) {
                $parsed_params = array_map(array($this, "prepare_param"), $params);
                $param_array = ArrayUtil::transpose(array($params, $parsed_params));
                echo "Params:<br />";
                foreach ($param_array as $key => $param) {
                    echo "Raw Param $key: $param[0]<br />";
                    echo "Prepared Param $key: $param[1]<br />";
                }
            }
            echo "</pre><br />";
            echo "<pre>";
            echo "Final SQL:<br />";
            echo($this->substitute_placeholders($sql, $params));
            echo "</pre><br />";
            echo "<pre>";
            echo "Result:<br />";
            print_r($this->$function($sql, $params));
            echo "</pre><br /><br />";
        }

        public static function assoc_array_by_first_column(array $array) {
            if (!is_array($array) || empty($array)) {
                return array();
            } else {
                $mapped_array = array();
                foreach ($array as $subarray) {
                    $mapped_array[array_shift($subarray)] = sizeof($subarray) == 1 ? array_shift($subarray) : $subarray;
                }
                return $mapped_array;
            }
        }

        public function set_database($database) {
            $this->execute("USE $database");
        }

        public function setTable($table) {
            $this->_table = $table;
        }

        public function setModel($model) {
            $this->_model = $model;
        }
    }