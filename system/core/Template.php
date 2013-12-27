<?php


    class Template {


        protected $variables = array();
        protected $_controller;
        protected $_action;
        protected $override_page;
        protected $_ob_level;
        public $renderHeader = true;

        public function __construct($controller, $action) {
            $controller        = explode("Controller", $controller);
            $this->_controller = $controller[0];
            $this->_action     = $action;
            $this->uri         = Loader::load('URI');
            $this->_ob_level   = ob_get_level();
        }

        public function set($name, $value = null) {
//        $this->variables[$name] = $value;
            if(is_array($name)) {
                foreach($name as $k => $v) {
                    $this->variables[$k] = $v;
                }
            } else if(is_string($name) && isset($value)) {
                $this->variables[$name] = $value;
            }
        }

        public function override_view($page) {
            $this->override_page = $page;
        }

        protected function _get_output($renderHeader) {
            $output = array();
            if($renderHeader) {
                if(file_exists(APP_PATH . 'views/' . $this->_controller . '/header.php')) {
                    $output['header'] = array('render' => true,
                                              'path'   => APP_PATH . 'views/' . $this->_controller . '/header.php');
                } else {
                    $output['header'] = array('render' => true, 'path' => APP_PATH . 'views/header.php');
                }
                if(file_exists(APP_PATH . 'views/' . $this->_controller . '/footer.php')) {
                    $output['footer'] = array('render' => true,
                                              'path'   => APP_PATH . 'views/' . $this->_controller . '/footer.php');
                } else {
                    $output['footer'] = array('render' => true, 'path' => APP_PATH . 'views/footer.php');
                }
            }
            if($this->override_page) {
                if(file_exists(APP_PATH . 'views/' . $this->_controller . '/' . $this->override_page)) {
                    $output['content'] = array('render' => true, 'path' =>
                        APP_PATH . 'views/' . $this->_controller . '/' . $this->override_page);
                } else if(file_exists(APP_PATH . 'views/' . $this->_controller . '/' . $this->_action . '.php')) {
                    $output['content'] = array('render' => true, 'path' =>
                        APP_PATH . 'views/' . $this->_controller . '/' . $this->_action . '.php');
                } else if(file_exists($this->override_page)) {
                    $output['content'] = array('render' => true, 'path' => $this->override_page);
                } else {
                    $output['content'] = array('render' => true, 'path' => 'error');
                }

            } else {
                if(file_exists(APP_PATH . 'views/' . $this->_controller . '/' . $this->_action . '.php')) {
                    $output['content'] = array('render' => true, 'path' =>
                        APP_PATH . 'views/' . $this->_controller . '/' . $this->_action . '.php');
                } else if(file_exists(APP_PATH . 'views/' . $this->uri->rsegments[0] . '/' . $this->_action . '.php')) {
                    $output['content'] = array('render' => true, 'path' =>
                        APP_PATH . 'views/' . $this->uri->rsegments[0] . '/' . $this->_action . '.php');
                } else {
                    $possible_underscore = LanguageUtil::camel_case_to_underscores($this->_controller);
                    if(file_exists(APP_PATH . 'views/' . $possible_underscore . '/' . $this->_action . '.php')) {
                        $output['content'] = array('render' => true, 'path' =>
                            APP_PATH . 'views/' . $possible_underscore . '/' . $this->_action . '.php');
                    } else {
                        $output['content'] = array('render' => true, 'path' => 'error');
                    }
                }
            }

            return $output;
        }


        /**
         * @param bool $renderHeader true/false render the header and footer
         *
         * @param bool $_return      see @return for explanation
         *
         * @return string|void if $_return param is true this will return a string, the content, else it will send output to the browser
         */
        public function render($renderHeader = true, $_return = false) {

            $html = Loader::load('HTML');
            $OOF  = Loader::load('OOF');
            extract($this->variables);

            ob_start();
            $output = $this->_get_output($renderHeader);
            if($output) {
                foreach(array('header', 'content', 'footer') as $piece) {
                    if(isset($output[$piece]) && $output[$piece]['render']) {
                        if(file_exists($output[$piece]['path'])) {
                            if((bool)@
                                ini_get('short_open_tag') === false && Config::getItem('rewrite_short_tags') == true
                            ) {
                                echo eval('?>' . preg_replace(
                                        "/;*\s*\?>/",
                                        "; ?>",
                                        str_replace('<?=', '<?php echo ', file_get_contents($output[$piece]['path']))));
                            } else {
                                include($output[$piece]['path']);
                            }
                        } else {
                            OOF::show_404("{$this->_controller}/{$this->_action}");
                        }
                    }
                }
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

    }