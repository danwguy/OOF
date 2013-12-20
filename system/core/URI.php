<?php

	/**
	 * Class URI
	 * @method static URI get_instance() Returns the URI class
	 */

	class URI  {


		public $keyval;
		public $uri_string;
		public $segments = array();
		public $rsegments = array();

		protected $config;

		public function __construct() {
			$this->config = Loader::load('Config');
			$this->fetch_uri();
		}

		public function fetch_uri() {
			if(strtoupper($this->config->item('uri_protocol')) == 'AUTO') {
				if($uri = $this->_detect_uri()) {
					$this->_set_uri($uri);

					return;
				}

				$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
				if(trim($path, '/') != '') {
					$this->_set_uri($path);

					return;
				}

				if(is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '') {
					$this->_set_uri(key($_GET));

					return;
				}

				$this->uri_string = '';

				return;
			}

			$uri = strtoupper($this->config->item('uri_protocol'));
			if($uri == 'REQUEST_URI') {
				$this->_set_uri($this->_detect_uri());

				return;
			} else if($uri == 'CLI') {
				$this->_set_uri($this->_parse_cli_args());

				return;
			}

			$path = (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
			$this->_set_uri($path);
		}

		public function filter_uri($str) {
			return $this->_filter_uri($str);
		}

		private function _set_uri($str) {
			$str = LanguageUtil::remove_invisible_chars($str, false);

			$this->uri_string = ($str == '/') ? '' : $str;
		}

		private function _detect_uri() {
			if(!isset($_SERVER['REQUEST_URI']) || !isset($_SERVER['SCRIPT_NAME'])) {
				return '';
			}
            $uri = $_SERVER['REQUEST_URI'];
//			$uri = $_SERVER['REQUEST_URI'];
//			if(strpos($uri, $_SERVER['SCRIPT_NAME']) == 0) {
//				$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
//			} else if(strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) == 0) {
//				$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
//			}


			if(strncmp($uri, '?/', 2) === 0) {
				$uri = substr($uri, 2);
			}

			$parts = preg_split('#\?#i', $uri, 2);
			$uri   = preg_split('#/#i', $parts[0], 3);
            $uri = $uri[2];
			if(isset($parts[1])) {
				$_SERVER['QUERY_STRING'] = $parts[1];
				parse_str($_SERVER['QUERY_STRING'], $_GET);
			} else {
				$_SERVER['QUERY_STRING'] = '';
				$_GET                    = array();
			}


			if($uri == '/' || empty($uri)) {
				return '/';
			}

			$uri = parse_url($uri, PHP_URL_PATH);

			return str_replace(array(
			                        '//',
			                        '../'
			                   ), '/', trim($uri, '/'));
		}

		private function _parse_cli_args() {
			$args = array_slice($_SERVER['argv'], 1);

			return $args ? '/' . implode('/', $args) : '';
		}

		private function _filter_uri($str) {
			if($str != '' && $this->config->item('allowed_url_chars') != '' && !$this->config->item('enable_query_strings')) {
				if(!preg_match("|^[" . str_replace(array('\\-', '\-'), '-', preg_quote($this->config->item('allowed_url_chars'), '-')) . "]+$|i", $str)) {
					OOF::show_error('You have submitted a URI with characters that are not allowed.', 400);
				}
			}

			$bad = array(
				'$',
				'(',
				')',
				'%28',
				'%29'
			);
			$ok  = array(
				'&#36;',
				'&#40;',
				'&$41;',
				'&#40;',
				'&#41;'
			);

			return str_replace($bad, $ok, $str);
		}

        public function segment() {
            $this->_explode_segments();
        }

		private function _explode_segments() {
			foreach(explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uri_string)) as $value) {
				$value = trim($this->_filter_uri($value));
				if($value != '') {
					$this->segments[] = $value;
				}
			}
		}

		private function _remove_url_suffix() {
			if($this->config->item('url_suffix') != '') {
				$this->uri_string = preg_replace("|".preg_quote($this->config->item('url_suffix')).'$|', '', $this->uri_string);
			}
		}

		private function _reindex() {
			array_unshift($this->segments, null);
			unset($this->segments[0]);
		}

		public function explode_uri_segments() {
			return $this->_explode_segments();
		}

		public function get_segment($num, $no_result = false) {
			return (!isset($this->segments[$num])) ? $no_result : $this->segments[$num];
		}

		public function assoc_uri($num = 3, $default = array()) {
			return $this->_uri_to_assoc($num, $default);
		}

		public function remove_url_suffix() {
			return $this->_remove_url_suffix();
		}

		public function reindex_segments() {
			return $this->_reindex();
		}

		private function _uri_to_assoc($n = 3, $default = array()) {
			$total_segments = 'total_segments';
			$segment_array  = 'segment_array';

			if(!is_numeric(n)) {
				return $default;
			}
			if(isset($this->keyval[$n])) {
				return $this->keyval[$n];
			}
			if($this->$total_segments() < $n) {
				if(count($default) == 0) {
					return array();
				}
				$retval = array();
				foreach($default as $v) {
					$retval[$v] = false;
				}

				return $retval;
			}

			$segments = aray_slie($this->$segment_array(), ($n - 1));
			$i        = 0;
			$lastval  = '';
			$retval   = array();
			foreach($segments as $s) {
				if($i % 2) {
					$retval[$lastval] = $s;
				} else {
					$retval[$s] = false;
					$lastval    = $s;
				}
				$i++;
			}

			if(count($default) > 0) {
				foreach($default as $val) {
					if(!array_key_exists($val, $retval)) {
						$retval[$val] = false;
					}
				}
			}

			$this->keyval[$n] = $retval;

			return $retval;
		}

		public function build_uri(array $data) {
			$temp = array();
			foreach($data as $k => $v) {
				$temp[] = $k;
				$temp[] = $v;
			}

			return implode('/', $temp);
		}

		public function segment_array() {
			return $this->segments;
		}

		public function total_segments() {
			return count($this->segments);
		}

		public function uri_string() {
			return $this->uri_string;
		}

	}