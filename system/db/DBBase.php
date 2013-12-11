<?php


class DBBase extends Singleton {

	public $username;
	public $password;
	public $hostname;
	public $database;
	public $driver;
	public $prefix;
	public $auto_init = true;
	public $port;
	public $pconnect = false;
	public $conn_id;
	public $result_id;
	public $debug;
	public $benchmark;
	public $query_count = 0;
	public $markers = '?';
	public $save_queries = true;
	public $queries = array();
	public $query_times = array();
	public $data_cache = array();
	public $transactions_enabled = true;
	public $cache_on = false;
	public $cache_dir = 'tmp/cache/db';

	protected $_cache;
    protected $_config;
    protected $_driver;
	protected $_results;
    protected $_type;
    protected $_active_group;
    protected $_db_config;
    protected $_drivers_paths = 'db/drivers/';
	protected $_protect_identifiers = true;
	protected $_reserved_identifiers = array("*");

    public function construct() {
        $this->_config = Config::getItem('database');
        $this->_active_group = $this->_config['active'];
        $this->_db_config = $this->_config[$this->_active_group];
	    if(isset($this->_active_group['auto_connect']) && $this->_active_group['auto_connect']) {
		    $this->init();
	    }
    }

	public function init() {
		$this->_setup_driver();
		$this->_setup_results();
		$this->connect();
	}

    protected function _setup_driver() {
        $this->_type = $this->_active_group['db_type'];
        preg_match("/(m)([y|s])(sql)([_](pdo))*$/", $this->_type, $matches);
        if(!$matches) {
            OOF::show_error("You do not have a valid database type in your config file. Please open the site config
                            and add a db_type to the database array");
            exit;
        }
        $type_name = array_shift($matches);
        $driver = strtoupper($matches[0]) .
                  ($matches[1] == 's' ? 'S' : 'y') .
                  strtoupper($matches[2]) .
                  (isset($matches[4]) ? strtoupper($matches[4]) : '').'Driver';
        if(file_exists(SYS_PATH.$this->_drivers_paths.$type_name.'/'.$driver.'.php')) {
            require_once(SYS_PATH.$this->_drivers_paths.$type_name.'/'.$driver.'.php');
        } else {
            OOF::show_error("We are unable to find the driver file you specified in the database config."."\r\n".
                            "In file: ".SYS_PATH.$this->_drivers_paths.$type_name.'/'.$driver.'.php');
            exit;
        }
	    if(!class_exists($driver)) {
		    OOF::show_error("We were unable to find the driver class specified. Please ensure that there is a
		                    file in the correct driver folder named ".$driver);
	    }
	    $this->_driver = new $driver();
	    if($this->_active_group['auto_connect']) {
		    $this->init();
	    }
    }

	protected function _setup_results() {

	}

} 