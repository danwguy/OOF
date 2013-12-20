<?php

	/**
	 * Class Config
	 * @method static Config get_instance() Returns the Config class
	 */

	class Config {


		public $return = '';
		public static $return_fail = '';

		protected $_config_paths = array(APP_PATH);
		protected $loaded = array();
		protected static $_config;

		private $items = array();
		private static $config_items = array();

		const FILENAME = 'site_config.php';

		public function __construct() {
			if(empty($this->items)) {
				$this->_read_config();
			}
			$this->_setup_basepath();
			$this->_setup_return();
		}

		public function system_url() {
			$n = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", BASE_PATH));

			return $this->item_slashed('base_url') . end($n);
		}

		public static function get_config($replacement = array()) {
			if(isset(self::$_config)) {
				return self::$_config[0];
			}
			if(!defined("ENVIRONMENT") || !file_exists($path = APP_PATH . 'config/' . ENVIRONMENT . '/site_config.php')) {
				$path = APP_PATH . 'config/site_config.php';
			}

			if(!file_exists($path)) {
				exit('The site_config file does not seem to exist');
			}
			require($path);

			if(!isset($config) || !is_array($config)) {
				exit("Your site_config file is not properly formatted. It MUST be an array");
			}
			if(!empty($replacement)) {
				foreach($replacement as $k => $v) {
					if(isset($config[$k])) {
						$config[$k] = $v;
					}
				}
			}

			return self::$_config[0] = & $config;
		}

		public function load($file = '', $merge = false, $graceful = false) {
			$file   = ($file == '') ? 'config' : str_replace('.php', '', $file);
			$found  = false;
			$loaded = false;

			$check = defined('ENVIRONMENT') ? array(
				ENVIRONMENT . '/' . $file,
				$file
			) : array($file);

			foreach($this->_config_paths as $path) {
				foreach($check as $loc) {
					$file_path = $path . 'config/' . $loc . '.php';
					if(in_array($file_path, $this->loaded, true)) {
						$loded = true;
						continue 2;
					}
					if(file_exists($file_path)) {
						$found = true;
						break;
					}
				}
				if(!$found) {
					continue;
				}

				include($file_path);

				if(!isset($config) || !is_array($config)) {
					if($graceful) {
						return false;
					}
					OOF::show_error('Your ' . $file_path . ' file either doesn\'t exist or is not a valid config array');
				}
				if($merge) {
					if(isset($this->items[$file])) {
						$this->items[$file] = array_merge($this->items[$file], $config);
					} else {
						$this->items[$file] = $config;
					}
				} else {
					$this->items = array_merge($this->items, $config);
				}
				$this->loaded[] = $file_path;
				unset($config);
				$loaded = true;
			}
			if(!$loaded) {
				if($graceful) {
					return false;
				}
				OOF::show_error("The config file " . $file . ".php doesn't seem to exist");
			}

			return true;
		}

		public function site_url($uri = null) {
			if(!$uri) {
				return $this->item_slashed('base_url') . $this->item('index_page');
			}
			if(!$this->item('enable_query_string')) {
				$suffix = ($this->item('url_suffix') == '') ? '' : $this->item('url_suffix');

				return $this->item_slashed('base_url') . $this->item_slashed('index_page') . $this->_uri_string($uri) . $suffix;
			} else {
				return $this->item_slashed('base_url') . $this->item('index_page') . '?' . $this->_uri_string($uri);
			}
		}

		public function item_slashed($item, $depth = null) {
			return item($item, $depth, true);
		}

		protected function _read_config() {
			$this->items = self::$config_items = self::get_config();
		}

		protected function _uri_string($uri) {
			if(!$this->item('enable_query_string')) {
				if(is_array($uri)) {
					$uri = implode('/', $uri);
				}
				$uri = trim($uri, '/');
			} else {
				if(is_array($uri)) {
					$i   = 0;
					$str = '?';
					foreach($uri as $k => $v) {
						$str .= $k . '=' . $v . '&';
					}
					$uri = substr($str, 0, -1);
				}
			}

			return $uri;
		}

		public function item($var, $depth = null, $slash = false) {
			if($depth) {
				return (isset($this->items[$var][$depth])) ? (($slash) ? rtrim($this->items[$var][$depth], '/') . '/'
					: $this->items[$var][$depth]) : $this->return;
			}

			return (isset($this->items[$var])) ? (($slash) ? rtrim($this->items[$var], '/') . '/' : $this->items[$var])
				: $this->return;
		}

		protected function _setup_return() {
			if(isset($this->items['return'])) {
				if($this->items['return'] == 'false') {
					$this->return = self::$return_fail = false;
				} else if($this->items['return'] == 'true') { //Why would you, I don't know but just in case
					$this->return = self::$return_fail = true;
				} else {
					$this->return = self::$return_fail = $this->items['return'];
				}
			}
		}

		public function set_item($var, $val, $overwrite = false) {
			if($overwrite) {
				$this->items[$var] = $val;

				return true;
			}
			if(isset($this->items[$var])) {
				return false;
			}
			$this->items[$var] = $val;

			return true;
		}

		public function _assign($items = array()) {
			if(is_array($items)) {
				foreach($items as $k => $v) {
					$this->set_item($k, $v, true);
				}
			}
		}

		public static function getItem($var, $depth = null) {
			if(!isset(self::$config_items) || empty(self::$config_items)) {
				$class = new self();
			}
			if(!isset(self::$config_items[$var])) {
				return self::$return_fail;
			}

			return ($depth) ? self::$config_items[$var][$depth] : self::$config_items[$var];
		}

		public function base_url($uri = '') {
			return $this->item_slashed('base_url') . ltrim($this->_uri_string($uri), '/');
		}

		protected function _setup_basepath() {
			if(!isset($this->items['base_url']) || $this->items['base_url'] == '') {
				if(isset($_SERVER['HTTP_HOST'])) {
					$base = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
					$base .= '://' . $_SERVER['HTTP_HOST'];
					$base .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
				} else {
					$base = 'http://localhost/';
				}
				$this->set_item('base_url', $base);
			}
		}

		public function get_all_config() {
			return $this->items;
		}

	}