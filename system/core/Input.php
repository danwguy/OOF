<?php


class Input extends Singleton {

    public $ip_address;
    public $user_agent;
    public $config;
    public $security;

    private $_allow_get = true;
    private $_standardize_lines = true;
    private $_xss_filter = false;
    private $_csrf_protection = false;

    protected $headers = array();

    public function construct() {

        $this->_allow_get = (Config::getItem('allow_get') === true);
        $this->_xss_filter = (Config::getItem('xss_filter') === true);
        $this->_csrf_protection = (Config::getItem('csrf', 'protection') === true);

        $this->security = Loader::load('Security', 'core');

        $this->_sanitize_globals();
    }


    public function get($index = null, $xss_clean = false) {
        if($index == null && !empty($_GET)) {
            $get = array();
            foreach(array_keys($_GET) as $key) {
                $get[$key] = ($xss_clean)
                    ? $this->security->clean(ArrayUtil::element($key, $_GET, false))
                    : ArrayUtil::element($key, $_GET, false);
            }
            return $get;
        }
        return ($xss_clean)
            ? $this->security->clean(ArrayUtil::element($index, $_GET, false))
            : ArrayUtil::element($index, $_GET, false);
    }

    public function post($index = null, $xss_clean = false) {
        if(!$index && !empty($_POST)) {
            $post = array();
            foreach(array_keys($_POST) as $key) {
                $post[$key] = ($xss_clean)
                    ? $this->security->clean(ArrayUtil::element($key, $_POST, false))
                    : ArrayUtil::element($key, $_POST, false);
            }
            return $post;
        }
        return ($xss_clean)
            ? $this->security->clean(ArrayUtil::element($index, $_POST, false))
            : ArrayUtil::element($index, $_POST, false);
    }

    public function request($index = null, $xss_clean = false) {
            return ($xss_clean)
                ? $this->security->clean(ArrayUtil::element($index, $_REQUEST, ''))
                : ArrayUtil::element($index, $_REQUEST, '');
    }

    public function cookie($index = '', $xss = false) {
        return ($xss)
            ? $this->security->clean(ArrayUtil::element($index, $_COOKIE, ''))
            : ArrayUtil::element($index, $_COOKIE, '');
    }

    public function set_cookie() {
        $num = func_get_num_args();
        $possibles = array('name' => '', 'value' => '', 'expire' => '', 'domain' => '', 'path' => '/', 'prefix' => '', 'secure' => false);
        if(is_array(array_shift(func_get_args()))) {
            $args = array_shit(func_get_args());
            foreach($possibles as $item => $default) {
                if(isset($args[$item])) {
                    $$item = $args[$item];
                } else {
                    $$item = $default;
                }
            }
        } else {
            $args = func_get_args();
            $i = 0;
            foreach($possibles as $k => $v) {
                if(isset($args[$i])) {
                    $$k = $args[$i];
                } else {
                    $$k = $v;
                }
            }
//            for($i = 0; $i < $num; $i++) {
//                $$possibles[$i] = func_get_arg($i);
//            }
        }
        if($prefix == '' && $this->config->item('cookie', 'prefix') != '') {
            $prefix = $this->config->item('cookie', 'prefix');
        }
        if($domain == '' && $this->config->item('cookie', 'prefix') != '') {
            $domain = $this->config->item('cookie', 'prefix');
        }
        if($path == '/' && $this->config->item('cookie', 'path') != '/') {
            $path = $this->config->item('cookie', 'path');
        }
        if(!$secure && $this->config->item('cookie', 'secure')) {
            $secure = $this->config>item('cookie', 'secure');
        }
        if(!is_numeric($expire)) {
            $expire = time() - 86500;
        } else {
            $expire = ($expire > 0) ? time() + $expire : 0;
        }

        setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);

    }

    public function server($index = '', $xss = false) {
        return ($xss)
            ? $this->security->clean(ArrayUtil::element($index, $_SERVER, ''))
            : ArrayUtil::element($index, $_SERVER, '');
    }

    public function ip_address() {
        if($this->ip_address) {
            return $this->ip_address;
        }

        $proxies = $this->config->item('poxy_ips');
        if(!empty($proxies)) {
            $proxies = explode(',', str_replace(' ', '', $proxies));
            foreach(array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_CLIENT_IP') as $header) {
                if(($spoof = $this->server($header))) {
                    if(strpos($spoof, ',')) {
                        $spoof = explode(',', $spoof, 2);
                        $spoof = $spoof[0];
                    }
                    if(!$this->valid_ip($spoof)) {
                        $spoof = false;
                    } else {
                        break;
                    }
                }
            }
            $this->ip_address = ($spoof && in_array($_SERVER['REMOTE_ADDR'], $proxies, true))
                ? $spoof
                : $_SERVER['REMOTE_ADDR'];
        } else {
            $this->ip_address = $_SERVER['REMOTE_ADDR'];
        }
        if(!$this->valid_ip($this->ip_address)) {
            $this->ip_address = '0.0.0.0';
        }
        return $this->ip_address;
    }

    public function valid_ip($ip, $which = '') {
        $which = strtolower($which);

        if(is_callable('filter_var')) {
            switch($which) {
                case 'ipv4':
                    $use = FILTER_FLAG_IPV4;
                    break;
                case 'ipv6':
                    $use = FILTER_FLAG_IPV6;
                    break;
                default:
                    $use = '';
                    break;
            }
            return (bool)filter_var($ip, FILTER_VALIDATE_IP, $use);
        }

        if($which != 'ipv6' && $which != 'ipv4'){
            if(strpos($ip, ':')) {
                $which = 'ipv6';
            } else if(strpos($ip, '.')) {
                $which = 'ipv4';
            } else {
                return false;
            }
        }
        $run = '_valid_'.$which;
        return $this->$run($ip);
    }

    protected function _valid_ipv4($ip) {
        $ip_segments = explode('.', $ip);
        if(sizeof($ip_segments) != 4) {
            return false;
        }

        if($ip_segments[0][0] == 0) {
            return false;
        }

        foreach($ip_segments as $seg) {
            if($seg == '' || preg_match("/[^0-9]/", $seg) || $seg > 255 || strlen($seg) > 3) {
                return false;
            }
        }

        return true;
    }

    protected function _valid_ipv6($str) {
        $groups = 8;
        $collapsed = false;

        $chunks = array_filter(preg_split('(:{1,2})/', $str, null, PREG_SPLIT_DELIM_CAPTURE));

        if(strpos(end($chunks), '.') !== false) {
            $ipv4 = array_pop($chunks);

            if(!$this->_valid_ipv4($ipv4)) {
                return false;
            }
            $groups--;
        }

        while($seg = array_pop($chunks)) {
            if($seg[0] == ':') {
                if(--$groups == 0) {
                    return false;
                }
                if(strlen($seg) > 2) {
                    return false;
                }
                if($seg == '::') {
                    if($collapsed) {
                        return false;
                    }
                    $collapsed = true;
                }
            } else if(preg_match("/[^0-9a-f]/i", $seg) || strlen($seg) > 4) {
                return false;
            }
        }

        return $collapsed || $groups == 1;
    }

    public function user_agent() {
        if($this->user_agent) {
            return $this->user_agent;
        }

        $this->user_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : false;

        return $this->user_agent;
    }

    private function _sanitize_globals() {
        $protected = array('_SERVER', '_GET', '_POST', '_FILES', '_REQUEST', '_SESSION', '_ENV', 'GLOBALS',
                           'HTTP_RAW_POST_DATA', 'system_folder', 'application_folder', 'BM', 'EXT', 'CFG', 'URI', 'RTR',
                            'OUT', 'IN');
        foreach(array($_GET, $_POST, $_COOKIE) as $global) {
            if(!is_array($global)) {
                if(!in_array($global, $protected)) {
                    global $$global;
                    $$global = null;
                }
            } else {
                foreach($global as $k => $v) {
                    if(!in_array($k, $protected)) {
                        global $$k;
                        $$k = null;
                    }
                }
            }
        }

        if(!$this->_allow_get) {
            $_GET = array();
        } else {
            if(is_array($_GET) && sizeof($_GET) > 0) {
                foreach($_GET as $key => $val) {
                    $_GET[$this->_clean_input_key($key)] = $this->_clean_input_val($val);
                }
            }
        }

        if(is_array($_POST) && sizeof($_POST) > 0) {
            foreach($_POST as $key => $val) {
                $_POST[$this->_clean_input_key($key)] = $this->_clean_input_val($val);
            }
        }

        if(is_array($_COOKIE) && sizeof($_COOKIE) > 0) {
            unset($_COOKIE['$version']);
            unset($_COOKIE['$path']);
            unset($_COOKIE['$domain']);
            foreach($_COOKIE as $key => $val) {
                $_COOKIE[$this->_clean_input_key($key)] = $this->_clean_input_val($val);
            }
        }

        $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);

        if($this->_csrf_protection && !$this->is_cli_request()) {
            $this->security->csrf_verify();
        }
    }

    private function _clean_input_val($data) {
        if(is_array($data)) {
            $new = array();
            foreach($data as $k => $v) {
                $new[$this->_clean_input_key($k)] = $this->_clean_input_val($v);
            }
            return $new;
        }

        if(version_compare(phpversion(), '5.4', '<') && get_magic_quotes_gpc()) {
            $data = stripslashes($data);
        }

        $data = LanguageUtil::remove_invisible_chars($data);

        if($this->_xss_filter) {
            $data = $this->security->clean($data);
        }

        if($this->_standardize_lines) {
            if(strpos($data, "\r")) {
                $data = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $data);
            }
        }

        return $data;
    }

    private function _clean_input_key($str) {
        if(!preg_match("/^[a-z0-9:_\/-]+$/i", $str)) {
            exit("Invalid Characters in array Key");
        }

        return $str;
    }

    public function request_headers($xss = false) {
        if(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers['Content-Type'] = (isset($_SERVER['CONTENT_TYPE']))
                ? $_SERVER['CONTENT_TYPE']
                : @getenv('CONTENT_TYPE');
            foreach($_SERVER as $k => $v) {
                if(strncmp($k, 'HTTP_', 5) == 0) {
                    $headers[substr($k, 5)] = ($xss)
                        ? $this->security->clean(ArrayUtil::element($k, $_SERVER, ''))
                        : ArrayUtil::element($k, $_SERVER, '');
                }
            }
        }
        foreach($headers as $key => $val) {
            $key = str_replace('_', ' ', strtolower($key));
            $key = str_replace(' ', '-', ucwords($key));
            $this->headers[$key] = $val;
        }

        return $this->headers;
    }

    public function get_request_header($index, $xss = false) {
        if(empty($this->headers)) {
            $this->request_headers();
        }
        if(!isset($this->headers[$index])) {
            return false;
        }

        return ($xss) ? $this->security->clean($this->headers[$index]) : $this->headers[$index];
    }

    public function is_ajax() {
        return ($this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
    }

    public function is_cli() {
        return (php_sapi_name() === 'cli' || defined('STDIN'));
    }
}