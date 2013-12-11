<?php


    class Controller {


        public $renderHeader = true;
        public $render = true;
        public $noModel = false;
        protected $_controller;
        protected $_action;
        protected $_template;
        protected $_config;

        public function __construct($controller, $action = null) {
            $this->load = Loader::load('Loader', 'core');
            foreach ($this->load->is_loaded() as $name => $obj) {
                $obj_name        = strtolower($name);
                $this->$obj_name = Loader::load($name);
            }

            $this->load->init();
            if (is_array($controller)) {
                $action     = $controller[1];
                $controller = $controller[0];
            }
            $this->_controller = ucfirst($controller);
            $this->_action     = $action;
            if (!$this->noModel) {
                $model_parts  = explode('Controller', $controller);
                $model        = $model_parts[0];
                $model        = ucfirst($this->inflection->singularize($model));
                $model        = ($this->config->item('underscore_to_camel_case'))
                    ? LanguageUtil::underscores_to_camel_case($model)
                    : $model;
                $this->$model = new $model;
            }
            $this->render    = 1;
            $this->_template = new Template($controller, $action);
        }

        public static function show_error($message, $code = 500, $header = 'An Error Occurred') {
            $error = Loader::load('Exceptions', 'core');
            echo $error->error($message, $header, 'general_errors', $code);
            exit();
        }

        public static function show_404($page = null, $log = false) {
            $error = Loader::load('Exceptions', 'core');
            echo $error->show_404($page, $log);
            exit;
        }

        public function set($name, $value = null) {
            if ($value && is_string($name)) {
                $vars = array($name => $value);
            }
            if (is_array($name)) {
                $vars = $name;
            }
            $this->load->vars($vars);
            if ($this->config->item('use_template')) {
                $this->_template->set($vars);
            }
        }

        public function override_view($page) {
            $this->_template->override_view($page);
        }

        public function download($file, $data) {
            if (!$file || !$data) {
                return false;
            }
            if (false === strpos($file, '.')) {
                return false;
            }

            $ex        = explode('.', $file);
            $extension = end($ex);

            if (defined('ENVIRONMENT') && file_exists(APP_PATH . 'config/' . ENVIRONMANT . '/mime_types.php')) {
                include(APP_PATH . 'config/' . ENVIRONMENT . '/mime_types.php');
            } else if (file_exists(APP_PATH . 'config/mime_types.php')) {
                include(APP_PATH . 'config/mime_types.php');
            }
            if (!isset($mimes[$extension])) {
                $mime = 'application/octet-stream';
            } else {
                $mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
            }

            if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== false) {
                header('Content-type: "' . $mime . '"');
                header('Content-Disposition: attachment; filename="' . $file . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header("Content-length: " . strlen($data));
            } else {
                header('Content-type: "' . $mime . '"');
                header('Content-Disposition: attachment; filename="' . $file . '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Pragma: no-cache');
                header('Content-length: ' . strlen($data));
            }
            exit($data);
        }

//    public function __destruct() {
//        if($this->render) {
//            $this->_template->render($this->renderHeader);
//        }
//    }

    }