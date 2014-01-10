<?php


    class Logger extends Singleton {


        private $log_files;
        private $_config;
        private $_log_to_db = false;
        private $_log_to_files = false;
        private $logging_dir = null;
        private $_enabled;
        private $_date_format = 'Y-m-d H:i:s';
        private $_level = 0;

        protected $_levels = array('ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4);


        public function construct() {
            $this->_config       = Config::getItem('logging');
            $this->_log_to_db    = (isset($this->_config['log_to_database']) && $this->_config['log_to_database'])
                ? true
                : false;
            $this->_log_to_files = (isset($this->_config['log_to_files']) && $this->_config['log_to_files'])
                ? true
                : false;
            $this->_enabled      = (isset($this->_config['enable'])) ? $this->_config['enable'] : false;
            $this->_level        = (isset($this->_config['logging_level'])
                                    && is_numeric(
                    $this->_config['logging_level']))
                ? $this->_config['logging_level']
                : 0;
            $date_format         = Config::getItem('date_formatting', 'log_file');
            $this->_date_format  = ($date_format)
                ? $date_format
                : $this->_date_format;
            $this->_setup_structure();
        }

        protected function _setup_structure() {
            if($this->_log_to_files) {
                //make sure the logging file path exists, and if not, create it.
                $this->logging_dir = BASE_PATH . $this->_config['path'];
                if(@!file_exists($this->logging_dir)) {
                    if(!mkdir($this->logging_dir, 0775, true)) {
                        OOF::show_error(
                           "Unable to create the logging directory " . $this->logging_dir . ".
                                        Please create the directory manually and then try again");
                    }
                }

                //for each of the log files, make sure that they exist and can be written to.
                $this->log_files = $this->_config['log_files'];
                if($this->log_files) {
                    foreach($this->log_files as $log_file) {
                        $log_file_path = $this->logging_dir . '/' . $log_file;
                        if(@!file_exists($log_file_path)) {
                            $fp = @fopen($log_file_path, "a");
                            if($fp) {
                                fclose($fp);
                            } else {
                                OOF::show_error("Unable to create log file at " . $log_file_path);
                            }
                        }
                        if(@!is_writable($log_file_path)) {
                            OOF::show_error("The log file at " . $log_file_path . " is not writable");
                        }
                    }
                } else {
                    OOF::show_error(
                       "There is no list of acceptable log files to use for logging.
                                                           Please open the site_config and add the files you would like to use into the
                                                           log_files array and then try again");
                }
            }
            if($this->_log_to_db) {
                $db = DB::get();
                if(!isset($this->_config['db_log_table'])) {
                    OOF::show_error(
                       'You have not setup a database to log to. Please open your site_config.php and
                                                           define a db_log_table or set log_to_db to false to turn off this feature');
                }
                if(!$db->table_exists($this->_config['db_log_table'])) {
                    $saved = $db->create_table(
                                $this->_config['db_log_table'],
                                array(
                                     'id'         => 'int(11) PRIMARY NOT NULL AUTOINCREMENT',
                                     'class'      => 'varchar(255) NULL',
                                     'method'     => 'varchar(255) NULL',
                                     'args'       => 'varchar(255) NULL',
                                     'message'    => 'text NULL',
                                     'backtrace'  => 'text NULL',
                                     'created_on' => 'datetime NULL'
                                )
                    );
                    if(!$saved) {
                        OOF::show_error(
                           "Unable to create the log table in the database. Please create one using
                                                                   The guidelines defined in the config and then re-run");
                    }
                }
            }
        }

        public function log($string, $file = null, $log_backtrace = true) {
            if($this->_log_to_db) {
                $this->_database_log($string, $log_backtrace);
            }
            if($this->_log_to_files) {
                $this->_file_log($string, ($file) ? $file : $this->log_files[0], $log_backtrace);
            }
        }

        public function log_exception($level = 'error', $msg, $php_error = false) {
            if(!$this->_enabled) {
                return false;
            }
            $level = strtoupper($level);
            if(!isset($this->_levels[$level]) || ($this->_levels[$level] > $this->_level)) {
                return false;
            }

            $path = $this->logging_dir . '/error.log';
//            $path = $this->logging_dir . '/' . $level . '_log-' . date('Y-m-d') . '.log';
            if(!$fp = @fopen($path, FILE_WRITE_END_CREATE)) {
                $this->log('unable to open or create path: '.$path, 'debug');
                return false;
            }
            $message = 'OOF Error log - ' . date($this->_date_format). "\r\n";
            $message .= $level . ' ' . (($level == 'INFO') ? ' -' : '-') . ' ' . date($this->_date_format) . ' --> '
                       . $msg . "\r\n"."\r\n";

            flock($fp, LOCK_EX);
            fwrite($fp, $message);
            flock($fp, LOCK_UN);
            fclose($fp);

            @chmod($path, PERMISSION_FILE_WRITE);

            return true;
        }

        protected function _file_log($string, $file, $log_backtrace) {
            if(substr($file, -3) != 'log' || substr($file, -3) != 'txt') {
                $file = $file . '.log';
            }
            if(!in_array($file, $this->log_files)) {
                OOF::show_error("Invalid log file used");
                return;
            }
            $log_file_path = $this->logging_dir . '/' . $file;
            $fp            = @fopen($log_file_path, FILE_READ_WRITE_END_CREATE);
            if($fp) {
                $now        = new DateTime();
                $log_string = "\r\n" . "\r\n" . "Timestamp: " . DateTimeUtil::format(
                                                                            $now,
                                                                            $this->_date_format) . "\r\n";
                if(is_array($string)) {
                    $out = '';
                    foreach($string as $k => $v) {
                        $out .= $k . ': '.(is_array($v) ? print_r($v, true) : $v). "\r\n";
                    }
                    $string = $out;
                }
                if($log_backtrace) {
                    $log_string = CustomException::to_string($string, false, false);
                } else {
                    $log_string .= $string;
                }
                fwrite($fp, $log_string);
                fclose($fp);
            } else {
                OOF::show_error("Unable to open the log file at " . $log_file_path . " for writing");
            }
            return;
        }

        protected function _database_log($content, $trace) {
            $now        = new DateTime();
            $created_on = DateTimeUtil::format($now, DateTimeUtil::DATE_FORMAT_MYSQL);
            $sql        = "INSERT INTO " . $this->_config['db_log_table'];
            if($trace) {
                $debug = debug_backtrace();
                foreach($debug as $array) {
                    $class[]     = (isset($array['class'])) ? $array['class'] : 'No Class';
                    $methods[]   = (isset($array['function'])) ? $array['function'] : 'No Function';
                    $args[]      = (isset($array['args'])) ? $array['args'] : 'No Args';
                    $messages[]  = $content;
                    $backtrace[] = $array;
                }
            }

        }

        public function log_sql($sql) {
            return $this->log($sql, 'sql.log');
        }

        public function __call($name, $arguments) {
            $name = str_replace('log_', "", $name);
            $this->log($arguments[0], strtolower($name) . ".log");
        }
    }