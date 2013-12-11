<?php


class Router {

	public $config;
	public $routes = array();
	public $error_routes = array();
	public $folder;
	public $class = '';
	public $method = 'index';
	public $directory = '';
	public $default_controller = '';
	public $default_method = '';

	protected $_rewrite_class_underscores = false;
	protected $_rewrite_method_underscores = false;


	public function __construct() {
		$this->config = Loader::load('Config', 'core');
		$this->uri = Loader::load('URI', 'core');
		$this->_rewrite_class_underscores =
			($this->config->item('class_names', 'underscore_to_camel_case'))
				? true
				: false;
		$this->_rewrite_method_underscores =
			($this->config->item('class_actions', 'underscore_to_camel_case'))
				? true
				: false;
	}

	public function set_directory($dir = null) {
		if(!$dir) {
			return false;
		}
		$this->directory = str_replace(array('/', '.'), '', $dir).'/';
        return true;
	}

	public function set_class($class = null) {
		if(!$class) {
			return false;
		}
		if($this->_rewrite_class_underscores) {
			$class = LanguageUtil::underscores_to_camel_case($class);
		}
		$class = str_replace(array('/', '.'), '', ucfirst($class));
        $this->class = ucfirst($class);

		return true;
	}

	public function set_method($method = null) {
		if(!$method) {
			return false;
		}
		if($this->_rewrite_method_underscores) {
			$method = LanguageUtil::underscores_to_camel_case($method);
		}
		$this->method = $method;
		return true;
	}

	public function fetch_method() {
		if($this->method == $this->fetch_class()) {
			return 'index';
		}
		return (isset($this->method)) ? $this->method : $this->config->item('default_method');
	}

	public function fetch_class() {
		return (isset($this->class)) ? $this->class : $this->config->item('default_controller');
	}

	public function fetch_directory() {
		return (isset($this->directory)) ? $this->directory : '';
	}

	public function _set_overrides($routes) {
		if(!is_array($routes)) {
			return;
		}
		if(isset($routes['directory'])) {
			$this->set_directory($routes['directory']);
		}
		if(isset($routes['controller']) && $routes['controller'] != '') {
			$this->set_class($routes['controller']);
		}
		if(isset($routes['function'])) {
			$routes['function'] = ($routes['function'] != '') ? $routes['function'] : 'index';
			$this->set_method($routes['function']);
		}
	}

	public function route() {
		return $this->_set_routing();
	}

	private function _set_routing() {
		$segments = array();
		if($this->config->item('enable_query_strings') && isset($_GET[$this->config->item('controller_trigger')])) {
			if(isset($_GET[$this->config->item('directory_trigger')])) {
				$this->set_directory(trim($this->uri->filter_uri($_GET[$this->congif->item('directory_trigger')])));
				$segments[] = $this->fetch_directory();
			}
			if(isset($_GET[$this->config->item('controller_trigger')])) {
				$controller = trim($this->uri->filter_uri($_GET[$this->config->item('controller_trigger')]));
				$controller =  ucfirst($controller);
				$this->set_class($controller);
				$segments[] = $this->fetch_class();
			}
			if(isset($_GET[$this->config->item('method_trigger')])) {
				$this->set_method(trim($this->uri->filter_uri($_GET[$this->config->item('method_trigger')])));
				$segments[] = $this->fetch_method();
			}
		}
		if(defined('ENVIRONMENT') && is_file(APP_PATH.'config/'.ENVIRONMENT.'/routing.php')) {
			include(APP_PATH.'config/'.ENVIRONMENT.'/routing.php');
		} else if(is_file(APP_PATH.'config/routing.php')) {
			include(APP_PATH.'config/routing.php');
		}

		$this->routes = (isset($routing) || is_array($routing)) ? $routing : array();
		unset($routing);

		$default_controller = (isset($this->routes['default_controller']) || $this->routes['default_controller'] != '')
			? $this->routes['default_controller']
			: false;
		if($default_controller) {
			$this->default_controller = ucfirst($default_controller);
		} else {
			$this->default_controller = false;
		}
        $this->default_method = (isset($this->routes['default_method']) || $this->routes['default_method'] != '')
			? $this->routes['default_method']
			: false;

        if(sizeof($segments) > 0) {
            return $this->_validate($segments);
        }

        $this->uri->fetch_uri();

        if($this->uri->uri_string == '') {
            return $this->_set_default_controller();
        }

        $this->uri->remove_url_suffix();

        $this->uri->segment();

        $this->_parse_routes();

        $this->uri->reindex_segments();


    }

	private function _set_request($segs = array()) {
        $segs = $this->_validate_request($segs);

        if(empty($segs)) {

			return $this->_set_default_controller();
		}

		$this->set_class($segs[0]);

		if(isset($segs[1])) {
			$this->set_method($segs[1]);
		} else {
			$segs[1] = $this->config->item('default_method');
		}

		$this->uri->rsegments = $segs;
	}

	private function _set_default_controller() {

		if(!$this->default_controller) {
			exit("There was no default controller setup in the routing file. Please fix the error so I know
					what to show you");
		}
		if(strpos($this->default_controller, '/') !== false) {
			$exploded = explode('/', $this->default_controller);
			$this->set_class($exploded[0]);
			$this->set_method($exploded[1]);
			$this->_set_request($exploded);
		} else {
            $this->set_class($this->default_controller);
            $this->set_method($this->default_method);
            $this->_set_request(array($this->default_controller, $this->default_method));
        }

        $this->uri->reindex_segments();
    }

	private function _parse_routes() {
		$url = implode('/', $this->uri->segments);
		if(isset($this->routes[$url])) {
			return $this->_set_request(explode('/', $this->routes[$url]));
		}

		foreach($this->routes as $k => $v) {
			$k = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $k));
			if(preg_match('#^'.$k.'&#', $url)) {
				if(strpos($v, '$') && strpos($k, '(')) {
					$v = preg_relace('#^'.$k.'$#', $v, $url);
				}
				return $this->_set_request(explode('/', $v));
			}
		}
		$this->_set_request($this->uri->segments);
	}

	private function _validate_request($segs = array()) {
        if(empty($segs)) {
            return $segs;
        }

        $aut = Loader::load('AutoLoad', 'core');
        if(isset($segs[0])) {
            if($this->_rewrite_class_underscores) {
                $class = ucfirst(LanguageUtil::underscores_to_camel_case($segs[0]));
            } else if($aut->is_excluded($segs[0])) {
                $class = ucfirst(LanguageUtil::underscores_to_camel_case($segs[0]));
            } else {
                $class = $segs[0];
            }
            if(file_exists(APP_PATH.'controllers/'.$class.'.php')
               || file_exists(APP_PATH.'controllers/'.$class.'Controller.php')) {
                return $segs;
            }
        }


        if(is_dir(APP_PATH.'controllers/'.$segs[0])) {
			$this->set_directory($segs[0]);
			$segs = array_slice($segs, 1);

			if(!empty($segs)) {
				if(!file_exists(APP_PATH.'controllers/'.$this->fetch_directory().$segs[0].'.php')) {
					if(!empty($this->routes['404_override'])) {
						$ex = explode('/', $this->routes['404_override']);
						$this->set_directory('');
						$this->set_class($ex[0]);
						$this->set_method(isset($ex[1]) ? $ex[1] : 'index');

						return $ex;
					} else {
						OOF::show_404($this->fetch_directory().$segs[0]);
					}
				}
			} else {
				if(strpos($this->default_controller, '/') !== false) {
					$ex = explode('/', $this->default_controller);
					$this->set_class($ex[0]);
					$this->set_method($ex[1]);
				} else {
					$this->set_class($this->default_controller);
					$this->set_method($this->default_method);
				}
				if(!file_exists(APP_PATH.'controllers/'.$this->fetch_directory().$this->default_controller.'.php')) {
					$this->directory = '';
					return array();
				}
			}
			return $segs;
		}
		if(!empty($this->routes['404_override'])) {
			$ex = eplode('/', $this->routes['404_override']);
			$this->set_class($ex[0]);
			$this->set_method(isset($ex[1]) ? $ex[1] : 'index');
			return $ex;
		}
		OOF::show_404($segs[0]);
	}

}