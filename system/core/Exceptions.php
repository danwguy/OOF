<?php


class Exceptions {

	public $action;
	public $severity;
	public $message;
	public $filename;
	public $line;
	public $ob_level;
	public $logger;
	public $levels = array(
		E_ERROR => 'Error',
		E_WARNING => 'Warning',
		E_PARSE => 'Parse Error',
		E_NOTICE => 'Notice',
		E_CORE_ERROR => 'Core Error',
		E_CORE_WARNING => 'Core Warning',
		E_COMPILE_ERROR => 'Compiler Error',
		E_COMPILE_WARNING => 'Compiler Warning',
		E_USER_ERROR => 'User Error(pebkac)',
		E_USER_WARNING => 'User Warning',
		E_USER_NOTICE => 'User Notice',
		E_STRICT => 'Runtime Notice'
	);

	public function __construct() {
		$this->ob_level = ob_get_level();
//		$this->logger = Loader::load('Logger', 'core');
	}

	public function show_404($page = null, $log = false) {
		$heading = "404 Page Not Found";
		$message = "The page you are looking for was not found on this server";
		if($log) {
			$this->logger->log('error', '404 Page Not Found -> '.$page);
		}
		echo $this->_show_error($heading, $message, 'error_404', 404);
		exit;
	}

	public function set_status_header($code = 200, $text = null) {
		$possible = array(
			200	=> 'OK',
			201	=> 'Created',
			202	=> 'Accepted',
			203	=> 'Non-Authoritative Information',
			204	=> 'No Content',
			205	=> 'Reset Content',
			206	=> 'Partial Content',

			300	=> 'Multiple Choices',
			301	=> 'Moved Permanently',
			302	=> 'Found',
			304	=> 'Not Modified',
			305	=> 'Use Proxy',
			307	=> 'Temporary Redirect',

			400	=> 'Bad Request',
			401	=> 'Unauthorized',
			403	=> 'Forbidden',
			404	=> 'Not Found',
			405	=> 'Method Not Allowed',
			406	=> 'Not Acceptable',
			407	=> 'Proxy Authentication Required',
			408	=> 'Request Timeout',
			409	=> 'Conflict',
			410	=> 'Gone',
			411	=> 'Length Required',
			412	=> 'Precondition Failed',
			413	=> 'Request Entity Too Large',
			414	=> 'Request-URI Too Long',
			415	=> 'Unsupported Media Type',
			416	=> 'Requested Range Not Satisfiable',
			417	=> 'Expectation Failed',

			500	=> 'Internal Server Error',
			501	=> 'Not Implemented',
			502	=> 'Bad Gateway',
			503	=> 'Service Unavailable',
			504	=> 'Gateway Timeout',
			505	=> 'HTTP Version Not Supported'
		);
		if(!is_numeric($code)) {
			$this->error(
				'Status codes must be numeric. The one supplied: '.$code.' is not',
				'An error occurred',
				'general_errors',
				500);
		}
		if(isset($possible[$code]) && !$text) {
			$text = $possible[$code];
		}
		if(!$text) {
			$this->error(
				'No status text found. Please ensure you are passing a valid status code',
				'An error occurred',
				'general_errors',
				500
			);
		}
		$server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : false;
		if(substr(php_sapi_name(), 0, 3) == 'cgi') {
			header("Status: {$code} {$text}", true);
		} else if($server_protocol == 'HTTP/1.1' || $server_protocol == 'HTTP/1.0') {
			header($server_protocol." {$code} {$text}", true, $code);
		} else {
			header("HTTP/1.1 {$code} {$text}", true, $code);
		}
	}

	public function error($heading, $message, $template = 'general_errors', $status = 500) {
		return $this->_show_error($heading, $message, $template, $status);
	}

	public function php_error($severity, $message, $path, $line) {
		$this->_show_php_error($severity, $message, $path, $line);
	}

	private function _log_exception($severity, $message, $path, $line) {
		$severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
		$this->logger->log(
			'error',
			'Severity: '.$severity.' :-> '.$message.' in: '.$path.' on: '.$line,
			true
		);
	}

	private function _show_error($heading, $message, $template = 'general_errors', $status = 500) {
		$this->set_status_header($status);
		$message = '<p>'.implode('</p><p>', (!is_array($message)) ? array($message) : $message).'</p>';
		if(ob_get_level() > $this->ob_level + 1) {
			ob_end_flush();
		}
		ob_start();
		include(APP_PATH.'errors/'.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	private function _show_php_error($severity, $message, $path, $line) {
		$severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
		$path = str_replace("\\", "/", $path);
		if(false !== strpos($path, '/')) {
			$ex = explode('/', $path);
			$path = $ex[count($ex) - 2].'/'.end($ex);
		}
		if(ob_get_level() > $this->ob_level + 1) {
			ob_end_flush();
		}
		ob_start();
		include(APP_PATH.'errors/error_php.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

    public static function handleException($severity, $message, $file_path, $line) {
        if($severity == E_STRICT) {
            return;
        }
        $me = Loader::load('Exceptions');
        if(($severity & error_reporting()) == $severity) {
            $me->php_error($severity, $message, $file_path, $line);
        }
    }
}