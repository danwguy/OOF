<?php
/**
 * Created by JetBrains PhpStorm.
 * User: robert
 * Date: 10/4/13
 * Time: 1:25 PM
 * To change this template use File | Settings | File Templates.
 */

class Loader {

	protected static $_loaded_classes = array();

	protected $_classes = array();

    protected $_ob_level;

    protected $_cached_vars = array();

	protected $_models = array();

    protected $_varmap = array('unit_test' => 'unit', 'user_agent' => 'agent');

	protected $_view_paths = array();

	protected $_helper_paths = array();

	protected $_model_paths = array();

	protected $_library_paths = array();

	protected $_base_classes = array();

	protected $_loaded_files = array();

	protected static $_autoload_directories = array('libraries', 'core', 'models');

	public function __construct() {
        $this->_ob_level = ob_get_level();
		$this->_library_paths = array(APP_PATH, SYS_PATH);
		$this->_view_paths = array(APP_PATH.'views/' => true);
		$this->_model_paths = array(APP_PATH);
		$this->_helper_paths = array(APP_PATH);
	}

	public function init() {
		$this->_classes = array();
		$this->_loaded_files = array();
		$this->_models = array();
		$this->_base_classes = self::$_loaded_classes;
		$this->_autoload();
		return $this;
	}

    public function file($path, $return = false) {
        return $this->_load(array('_path' => $path, '_return' => $return));
    }

    public function database($params = null, $return = false, $active_record = null) {
        $OOF = OOF::get_instance();
        if(class_exists('DB') && !$return && is_null($active_record) && isset($OOF->db) && is_object($OOF->db)) {
            return false;
        }
        require_once(SYS_PATH.'database/DB.php');
        if($return) {
            return DB($params, $active_record);
        }
        $OOF->db = '';
        $OOF->db =& DB($params, $active_record);
    }

    public function db_utility() {
        if(!class_exists('DB')) {
            $this->database();
        }
        $OOF = OOF::get_instance();
        $OOF->load->db_forge();
        require_once(SYS_PATH.'database/DB_utility.php');
        require_once(SYS_PATH.'database/drivers/'.$OOF->db->db_driver.'_utility.php');
        $class = 'DB_'.$OOF->db->db_driver.'_utility';
        $OOF->db_util = new $class();
    }

    public function db_forge() {
        if(!class_exists('DB')) {
            $this->database();
        }
        $OOF = OOF::get_instance();
        require_once(SYS_PATH.'database/DB_forge.php');
        require_once(SYS_PATH.'database/drivers/'.$OOF->db->db_driver.'_forge.php');
        $class = 'DB_'.$OOF->db->db_driver.'_forge';
        $OOF->db_forge = new $class();
    }

    public function view($view, $vars = array(), $return = false) {
        return $this->_load(array('_view' => $view, '_vars' => $this->_object_to_array($vars), '_return' => $return));
    }

    public function config($file = null, $use_sections = false, $graceful = false) {
        $OOF = Loader::load('OOF');
        $OOF->config->load($file, $use_sections, $graceful);
    }

    public function vars($vars = array(), $val = null) {
        if($val && is_string($vars)) {
            $vars = array($vars => $val);
        }
        $vars = $this->_object_to_array($vars);
        if(is_array($vars) && sizeof($vars) > 0) {
            foreach($vars as $k => $v) {
                $this->_cached_vars[$k] = $v;
            }
        }
    }

    public function get_var($key) {
        return (isset($this->_cached_vars[$key])) ? $this->_cached_vars[$key] : null;
    }

    public static function load($class, $folder = 'libraries', $prefix = '', $args = array()) {
        if(isset(self::$_loaded_classes["{$class}"])) {
            return self::$_loaded_classes[$class];
        }
        $name = false;
	    if(strtoupper($class) == 'DB') {
		    $conf = ($args) ? $args : Config::getItem('database');
		    return self::$_loaded_classes[$class] = OOF::setup_db($conf);
	    }
	    if(!in_array($folder, self::$_autoload_directories)) {
	        foreach(array(APP_PATH, SYS_PATH) as $path) {
	            if(file_exists($path.$folder.'/'.$class.'.php')) {
	                $name = $prefix.$class;
	                if(!class_exists($name) === false) {
	                    require($path.$folder.'/'.$class.'.php');
	                }
	                break;
	            }
	        }
	    } else {
		    $name = $prefix.$class;
	    }
        if(file_exists(APP_PATH.$folder.'/'.Config::getItem('subclass_prefix').$class.'.php')) {
            $name = Config::getItem('subclass_prefix').$class;
            if(class_exists($name) === false) {
                require(APP_PATH.$folder.'/'.Config::getItem('subclass_prefix').$class.'.php');
            }
        }
        if(!$name) {
            exit('We were unable to find the class: '.$class.' that you were asking for');
        }
	    if(is_subclass_of($name, 'Singleton')) {
		    self::$_loaded_classes[$class] = $name::get_instance();
	    } else {
            self::$_loaded_classes[$class] = (!empty($args)) ? new $name($args) : new $name();
	    }
        return self::$_loaded_classes[$class];
    }

    public function language($file = array(), $lang = null) {
        $OOF = OOF::get_instance();
        if(!is_array($file)) {
            $file = array($file);
        }
        foreach($file as $language) {
            $OOF->lang->load($language, $lang);
        }
    }

    public function driver($library = null, $params = null, $obj_name = null) {
        if(!class_exists('Driver_Library')) {
            require_once(SYS_PATH.'libraries/DriverLibrary.php');
        }
        if(!$library) {
            return false;
        }
        if(!strpos($library, '/')) {
            $library = ucfirst($library).'/'.$library;
        }
        return $this->library($library, $params, $obj_name);
    }

    public function model($model, $name = null, $db_conn = false) {

        if(!$model) {
            return;
        }

        if(is_array($model)) {
            foreach($model as $hottie) {
                $this->model($hottie);
            }
            return;
        }
        $path = '';
        if(($last = strrpos($model, '/')) !== false) {
            $path = substr($model, 0, $last + 1);
            $model = substr($model, $last + 1);
        }

        if(!$name) {
            $name = $model;
        }
        if(in_array($name, $this->_models, true)) {
            return;
        }
        $OOF = Loader::load('OOF', 'core');
        if(isset($OOF->$name)) {
            OOF::show_error("The model you are trying to load: ".$name." is already a resource being used");
        }
//        $model = strtolower($model);
        foreach($this->_model_paths as $model_path) {
            if(!file_exists($model_path.'models/'.$path.$model.'.php')) {
                continue;
            }
            if($db_conn && !class_exists('DB')) {
                $db_conn = '';
                $OOF->load->database($db_conn, false, true);
            }
            if(!class_exists('Model')) {
                self::load_class('Model');
            }
            require_once($model_path.'models/'.$path.$model.'.php');
            $model = ucfirst($model);
            $OOF->$name = new $model();
            $this->_models[] = $name;
            return;
        }
        OOF::show_error("Unable to locate the model: ".$model." that you were asking for. Sorry");
    }

    public function library($library = '', $params = null, $obj_name = null) {
        if(is_array($library)) {
            foreach($library as $class) {
                $this->library($class, $params);
            }
            return;
        }
        if(!$library || isset($this->_base_classes[$library])) {
            return false;
        }
        if(!is_null($params) && !is_array($params)) {
            $params = null;
        }
        $this->_load_class($library, $params, $obj_name);
    }

    public function remove_package_path($path = '', $remove_config_path = true) {
        $config =& $this->_get_component('config');
        if(!$path) {
            $void = array_shift($config->_config_paths);
            $void = array_shift($this->_model_paths);
            $void = array_shift($this->_library_paths);
            $void = array_shift($this->_view_paths);
        } else {
            $path = rtrim($path, '/').'/';
            foreach(array('_library_paths', '_model_paths') as $path) {
                if($key = array_search($path, $this->{$path})) {
                    unset($this->{$path}{$key});
                }
            }
            if(isset($this->_view_paths[$path.'views/'])) {
                unset($this->_view_paths[$path.'views/']);
            }
            if($remove_config_path) {
                if($key = array_search($path, $config->_config_paths)) {
                    unset($config->_config_paths[$key]);
                }
            }
        }
        $this->_library_paths = array_unique(array_merge($this->_library_paths, array(APP_PATH, SYS_PATH)));
        $this->_model_paths = array_unique(array_merge($this->_model_paths, array(APP_PATH)));
        $this->_view_paths = array_merge($this->_view_paths, array(APP_PATH.'views/' => true));
        $config->_config_paths = array_unique(array_merge($config->_config_paths, array(APP_PATH)));

    }

    public function is_loaded($class = null) {
	    if($class) {
            return (isset(self::$_loaded_classes[$class])) ? self::$_loaded_classes[$class] : false;
	    } else {
		    return self::$_loaded_classes;
	    }
    }

    public function add_package_path($path, $view_cascade = true) {
        $path = rtrim($path, '/').'/';
        array_unshift($this->_library_paths, $path);
        array_unshift($this->_model_paths, $path);
        $this->_view_paths = array($path.'views/' => $view_cascade) + $this->_view_paths;
        $config =& $this->_get_component('config');
        array_unshift($config->_config_paths, $path);
    }

    public function get_package_paths($include_base = false) {
        return ($include_base) ? $this->_library_paths : $this->_model_paths;
    }


	private function _autoload() {
		if(defined("ENVIRONMENT") && file_exists(APP_PATH.'config/'.ENVIRONMENT.'/autoload.php')) {
			include(APP_PATH.'config/'.ENVIRONMENT.'/autoload.php');
		} else {
			include(APP_PATH.'config/autoload.php');
		}
		if(!isset($autoload)) {
			return false;
		}

		if(isset($autoload['packages'])) {
			foreach($autoload['packages'] as $package) {
				$this->add_package_path($package);
			}
		}
		if(isset($autoload['config']) && sizeof($autoload['config']) > 0) {
			$OOF = OOF::get_instance();
			foreach($autoload['config'] as $k => $v) {
				$OOF->config->load($v);
			}
		}
		if(isset($autoload['language']) && sizeof($autoload['language']) > 0) {
			$this->language($autoload['language']);
		}
		if(isset($autoload['libraries']) && sizeof($autoload['libraries']) > 0) {
			if(in_array('database', $autoload['libraries'])) {
				$this->database();
				$autoload['libraries'] = array_diff($autoload['librarioes'], array('database'));
			}
			foreach($autoload['libraries'] as $item) {
				$this->library($item);
			}
		}
		if(isset($autoload['model'])) {
			$this->model($autoload['model']);
		}
	}

    protected function _load($_data) {
        foreach(array('_view', '_vars', '_path', '_return') as $_val) {
            $$_val = (isset($_data[$_val])) ? $_data[$_val] : false;
        }
        $exists = false;

        if($_path != '') {
            $_x = explode('/', $_path);
            $_file = end($_x);
        } else {
            $_ext = pathinfo($_view, PATHINFO_EXTENSION);
            $_file = ($_ext == '') ? $_view.'.php' : $_view;
            foreach($this->_view_paths as $file => $cascade) {
                if(file_exists($file.$_file)) {
                    $_path = $file.$_file;
                    $exists = true;
                    break;
                }
                if(!$cascade) {
                    break;
                }
            }
        }
        if(!$exists && !file_exists($_path)) {
            OOF::show_error('Sorry we are unable to load the file: '.$_file.' that you requested');
        }
        $OOF = Loader::load('OOF');
	    $html = Loader::load('HTML');
        foreach(get_object_vars($OOF) as $k => $v) {
            if(!isset($this->$k)) {
                $this->$k = $OOF->$k;
            }
        }
        if(is_array($_vars)) {
            $this->_cached_vars = array_merge($this->_cached_vars, $_vars);
        }
        extract($this->_cached_vars);
        ob_start();
        if((bool) @ini_get('short_open_tag') === false && Config::getItem('rewrite_short_tags') == true) {
            echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_path))));
        } else {
            include($_path);
        }
        if($_return) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }
        if(ob_get_level() > $this->_ob_level + 1) {
            ob_end_flush();
        } else {
            $OOF->output->append_output(ob_get_contents());
            @ob_end_clean();
        }
    }

    protected function _init_class($class, $prefix = '', $config = false, $obj_name = null) {
        if(!$config) {
            $config_component = $this->_get_component('config');
            if(is_array($config_component->_config_paths)) {
                foreach($config_component->_config_paths as $path) {
                    if(defined('ENVIRONMENT') && file_exists($path .'config/'.ENVIRONMENT.'/'.strtolower($class).'.php')) {
                        include($path.'config/'.ENVIRONMENT.'/'.strtolower($class).'.php');
                        break;
                    } else if(defined('ENVIRONMENT') && file_exists($path . 'config/'.ENVIRONEMTN.'/'.ucfirst(strtolower($class)).'.php')) {
                        include($path . 'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php');
                        break;
                    } else if(file_exists($path . 'config/' . strtolower($class).'.php')) {
                        include($path . 'config/' . strtolower($class).'.php');
                        break;
                    } else if(file_exists($path . 'config/' . ucfirst(strtolower($class)) . '.php')) {
                        include($path . 'config/' . ucfirst(strtolower($class)) . '.php');
                        break;
                    }
                }
            }
        }
        if(!$prefix) {
            if(class_exists(Config::getItem('subclass_prefix').$class)) {
                $classobj = Config::getItem('subclass_prefix').$class;
            } else {
                $classobj = $class;
            }
        } else {
            $classobj = $prefix.$class;
        }
        if(!class_exists($classobj)) {
            OOF::show_error("Class ".$class." doesn't exist");
        }
        $class = strtolower($class);
        if(is_null($obj_name)) {
            $classvar = (!isset($this->_varmap[$class])) ? $class : $this->_varmap[$class];
        } else {
            $classvar = $obj_name;
        }
        $this->_classes[$class] = $classvar;

        $OOF = OOF::get_instance();
        if($config) {
            $OOF->$classvar = new $classobj($config);
        } else {
            $OOF->$classvar = new $classobj;
        }
    }

    protected function _object_to_array($object) {
        return (is_object($object)) ? get_object_vars($object) : $object;
    }

    protected function _prep_filename($filename, $ext) {
        if(!is_array($filename)) {
            return array(strtolower(str_replace('.php', '', str_replace($ext, '', $filename)).$ext));
        }
        foreach($filename as $k => $v) {
            $filename[$k] = strtolower(str_replace('.php', '', str_replace($ext, '', $v)).$ext);
        }
        return $filename;
    }

    protected function _load_class($class, $params = null, $obj_name = null) {
        $class = str_replace('.php', '', trim($class, '/'));
        $sub_dir = '';
        if(($last_path = strrpos($class, '/')) !== false) {
            $sub_dir = substr($class, 0, $last_path + 1);
            $class = substr($class, $last_path + 1);
        }
        foreach(array(ucfirst($class), strtolower($class)) as $class) {
            $subclass = APP_PATH . 'libraries/' . $sub_dir . Config::getItem('subclass_prefix').$class.'.php';
            if(file_exists($subclass)) {
                $base_class = SYS_PATH . 'libraries/' . ucfirst($class). '.php';
                if(!file_exists($base_class)) {
                    OOF::show_error("Unable to load the class: ".$class." that you requested");
                }
                if(in_array($subclass, $this->_loaded_files)) {
                    if(!is_null($obj_name)) {
                        $OOF = OOF::get_instance();
                        if(!isset($OOF->$obj_name)) {
                            return $this->_init_class($class, $OOF->config->item('subclass_prefix'), $params, $obj_name);
                        }
                    }
                    $dup = true;
                    return;
                }
                include_once($base_class);
                include_once($subclass);
                $this->_loaded_files[] = $subclass;

                return $this->_init_class($class, Config::getItem('subclass_prefix'), $params, $obj_name);
            }
            $dup = true;
            foreach($this->_library_paths as $path) {
                $file_path = $path . 'libraries/' . $sub_dir . $class . '.php';
                if(!file_exists($file_path)) {
                    continue;
                }
                if(in_array($file_path, $this->_loaded_files)) {
                    if(!is_null($obj_name)) {
                        $OOF = OOF::get_instance();
                        if(!isset($OOF->$obj_name)) {
                            return $this->_init_class($class, '', $params, $obj_name);
                        }
                    }
                    $dup = true;
                    return;
                }
                include_once($file_path);
                $this->_loaded_files[] = $file_path;
                return $this->_init_class($class, '', $params, $obj_name);
            }
        }
        if(!$sub_dir) {
            $path = strtolower($class).'/'.$class;
            return $this->_load_class($path, $params);
        }
        if(!$dup) {
            OOF::show_error('Sorry, we are unable to load class: ' . $class);
        }
    }

    protected function _obj_to_array($obj) {
        return (is_object($obj)) ? OOF::get_object_vars($obj) : $obj;
    }

    protected function &_get_component($com) {
        $OOF = OOF::get_instance();
        return $OOF->$com;
    }

}