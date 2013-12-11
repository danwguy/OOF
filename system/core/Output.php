<?php


class Output {

    public $exception;

    protected $_final_output;
    protected $_cache_expire = 0;
    protected $_mime_type = array();
    protected $_headers = array();
    protected $_enable_profile = false;
    protected $_zlib = false;
    protected $_profile_sections = array();
    protected $_parse_exec_vars = true;

    public function __construct() {
        $this->_zlib = @ini_get('zlib.output_compression');
        if(defined('ENVIRONMENT') && file_exists(APP_PATH.'config/'.ENVIRONMENT.'/mimes.php')) {
            include(APP_PATH.'config/'.ENVIRONMENT.'/mime_types.php');
        } else {
            include(APP_PATH.'config/mime_types.php');
        }
        $this->_mime_type = $mimes;
        $this->exception = Loader::load('Exceptions');
    }

    public function get_output() {
        return $this->_final_output;
    }

    public function set_output($output) {
        $this->_final_output = $output;
        return $this;
    }

    public function append_output($output) {
        $this->_final_output .= $output;
        return $this;
    }

    public function set_content_type($mime) {
        if(strpos($mime, '/') === false) {
            $extension = ltrim($mime, '.');
            if(isset($this->_mime_type[$extension])) {
                $mime =& $this->_mime_type[$extension];
                if(is_array($mime)) {
                    $mime = current($mime);
                }
            }
        }
        $header = 'Content-Type: '.$mime;
        $this->_headers[] = array($header, true);
        return $this;
    }

    public function set_header($header, $replace = true) {
        if($this->_zlib && strncasecmp($header, 'content-length', 14) == 0) {
            return;
        }
        $this->_headers[] = array($header, $replace);
        return $this;
    }

    public function set_status_header($code = 200, $text = '') {
        $this->exception->set_status_header($code, $text);
        return $this;
    }

    public function set_profiler_sections($sections) {
        if(is_array($sections)) {
            foreach($sections as $section => $enable) {
                $this->_profile_sections[$section] = ($enable) ? true : false;
            }
        }
        return $this;
    }

    public function enable_profiler($enable = true) {
        $this->_enable_profile = (is_bool($enable)) ? $enable : true;
        return $this;
    }

    public function cache($time) {
        $this->_cache_expire = (is_numeric($time)) ? $time : 0;
        return $this;
    }

    public function display($output = null) {
	    if(class_exists('Config')) {
		    $cfg = Loader::load('Config');
	    } else {
		    global $CNFG;
		    $cfg =& $CNFG;
	    }
	    if(class_exists('Benchmark')) {
		    $bench = Loader::load('Benchmark');
	    } else {
		    global $bm;
		    $bench =& $bm;
	    }
	    $html = Loader::load('HTML');
        if(class_exists('OOF')) {
            $OOF = Loader::load('OOF');
        }
        if(!$output) {
            $output = $this->_final_output;
        }

        if($this->_cache_expire > 0 && isset($OOF) && !method_exists($OOF, '_output')) {
            $this->_write_cache($output);
        }

	    $time_elapsed = $bench->time_elapsed('total_execution_time_start', 'total_execution_time_end');
        if($this->_parse_exec_vars) {
            $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
	        $output = str_replace('{time_elapsed}', $time_elapsed, $output);
            $output = str_replace('{memory_usage}', $memory, $output);
        }
        if($cfg->item('compress_output') && !$this->_zlib) {
            if(extension_loaded('zlib')) {
                if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
                    ob_start('ob_gzhandler');
                }
            }
        }
        if(count($this->_headers) > 0) {
            foreach($this->_headers as $header) {
                @header($header[0], $header[1]);
            }
        }
        if(!isset($OOF)) {
            echo $output;
            return true;
        }
        if($this->_enable_profile) {
            $OOF->load->library('profiler');
            if(!empty($this->_profile_sections)) {
                $OOF->profiler->set_sections($this->_profile_sections);
            }
            if(preg_match("|</body>.*?</html>|is", $output)) {
                $output = preg_replace("|</body>.*?</html>|is", '', $output);
                $output .= $OOF->profiler->run();
                $output .= '</body></html>';
            } else {
                $output .= $OOF->profiler->run();
            }
        }
        if(method_exists($OOF, '_output')) {
            $OOF->_output($output);
        } else {
            echo $output;
        }
    }

    public function _display_cache(&$cfg, &$uri) {
        $cache_path = ($cfg->item('cache_path') == '') ? TMP_PATH.'cache/' : $cfg->item('cache_path');
        $url = $cfg->item('base_url').
               $cfg->item('index_page').
               $uri->uri_string;
        $filepath = $cache_path.md5($url);
        if(!file_exists($filepath)) {
            return false;
        }
        if(!$fp = @fopen($filepath, FILE_READ)) {
            return false;
        }
        flock($fp, LOCK_SH);
        $cache = '';
        if(filesize($filepath) > 0) {
            $cache = fread($fp, filesize($filepath));
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        if(!preg_match("/(\d+TSOOF--->)/", $cache, $match)) {
            return false;
        }
        if(time() >= trim(str_replace('TSOOF--->', '', $match[1]))) {
            if(FileManager::isFileWritable($cache_path)) {
                @unlink($filepath);
                return false;
            }
        }
        $this->display(str_replace($match[0], '', $cache));
        return true;
    }

    public function _write_cache($output) {
        $oof = Loader::load('OOF');
        $path = $oof->config->item('cache_path');
        $cache_path = ($path == '') ? TMP_PATH .'cache/' : $path;
        if(!is_dir($cache_path) || !FileManager::isFileWritable($cache_path)) {
            return;
        }
        $uri = $oof->config->item('base_url').
               $oof->config->item('index_page').
               $oof->uri->uri_string();
        $cache_path .= md5($uri);
        if(!$fp = @fopen($cache_path, FILE_WRITE_CREATE_TRUNCATE)) {
            return;
        }
        $expire = time() + ($this->_cache_expire * 60);
        if(flock($fp, LOCK_EX)) {
            fwrite($fp, $expire.'TSOOF--->'.$output);
            flock($fp, LOCK_UN);
        } else {
            return;
        }
        fclose($fp);
        @chmod($cache_path, PERMISSION_FILE_WRITE);
    }

}