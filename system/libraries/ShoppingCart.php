<?php


class ShoppingCart {

    protected $_id_rules = '\.a-z0-9_-';
    protected $_name_rules = '\.\:\-_ a-z0-9';

    private $OOF;
    private $_cart_contents = array();
    private $_config;

    public function __construct($params = array()) {
        $this->OOF = Loader::load('OOF');
        $config = array();
        if(count($params) > 1) {
            $this->set_overrides($params);
        }
        $this->OOF->load->library('session', $config);
        $this->_init($this->OOF->config->item('shopping_cart'));
    }

    protected function _init($config = array()) {
        $this->_config = $config;
        $session = false;
        $database = false;
        if($this->_config['save_to_session']) {
            if($this->OOF->session->userdata('cart_contents')) {
                $this->_cart_contents = $this->OOF->session->userdata('cart_contents');
                $session = true;
            }
        } else if($this->_config['save_to_database']) {

        }
    }

} 