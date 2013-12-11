<?php


    class Email {


        public $useragent       = "CodeIgniter";
        public $mailpath        = "/usr/sbin/sendmail"; // Sendmail path
        public $protocol        = "mail";               // mail/sendmail/smtp
        public $smtp_host       = "";                   // SMTP Server.  Example: mail.earthlink.net
        public $smtp_user       = "";                   // SMTP Username
        public $smtp_pass       = "";                   // SMTP Password
        public $smtp_port       = "25";                 // SMTP Port
        public $smtp_timeout    = 5;                    // SMTP Timeout in seconds
        public $smtp_crypto     = "";                   // SMTP Encryption. Can be null, tls or ssl.
        public $wordwrap        = true;                 // TRUE/FALSE  Turns word-wrap on/off
        public $wrapchars       = "76";                 // Number of characters to wrap at.
        public $mailtype        = "text";               // text/html  Defines email formatting
        public $charset         = "utf-8";              // Default char set: iso-8859-1 or us-ascii
        public $multipart       = "mixed";              // "mixed" (in the body) or "related" (separate)
        public $alt_message     = '';                   // Alternative message for HTML emails
        public $validate        = false;                // TRUE/FALSE.  Enables email validation
        public $priority        = "3";                  // Default priority (1 - 5)
        public $newline         = "\n";                 // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
        public $crlf            = "\n";                 // The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
                                                        // even on the receiving end think they need to muck with CRLFs, so using "\n", while
                                                        // distasteful, is the only thing that seems to work for all environments.
        public $send_multipart  = true;                  // TRUE/FALSE - Yahoo does not like multipart alternative, so this is an override.  Set to FALSE for Yahoo.
        public $bcc_batch_mode  = false;                 // TRUE/FALSE  Turns on/off Bcc batch feature
        public $bcc_batch_size  = 200;                   // If bcc_batch_mode = TRUE, sets max number of Bccs in each batch
        private $_safe_mode     = false;
        private $_subject       = "";
        private $_body          = "";
        private $_finalbody     = "";
        private $_alt_boundary  = "";
        private $_atc_boundary  = "";
        private $_header_str    = "";
        private $_smtp_connect  = "";
        private $_encoding      = "8bit";
        private $_IP            = false;
        private $_smtp_auth     = false;
        private $_replyto_flag  = false;
        private $_debug_msg     = array();
        private $_recipients    = array();
        private $_cc_array      = array();
        private $_bcc_array     = array();
        private $_headers       = array();
        private $_attach_name   = array();
        private $_attach_type   = array();
        private $_attach_disp   = array();
        private $_protocols     = array('mail', 'sendmail', 'smtp');
        private $_base_charsets = array('us-ascii', 'iso-2022-'); // 7-bit charsets (excluding language suffix)
        private $_bit_depths    = array('7bit', '8bit');
        private $_priorities    = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');
        private $_clear_vars    = array(
            '_subject' => '',
            '_body' => '',
            '_finalbody' => '',
            '_header_str' => '',
            '_replyto_flag' => false,
            '_recipients' => array(),
            '_cc_array' => array(),
            '_bcc_array' => array(),
            '_headers' => array(),
            '_debug_msg' => array()
        );

        public function __construct($config = array()) {
            if(count($config) > 0) {
                $this->initialize($config);
            } else {
                $this->_smtp_auth = ($this->smtp_user == '' && $this->smtp_pass == '') ? false : true;
                $this->_safe_mode = ((boolean)ini_get('safe_mode') === false) ? false : true;
            }

        }

        public function initialize($config = array()) {
            foreach($config as $k => $v) {
                if(isset($this->$k)) {
                    $method = 'set_'.$k;
                    if(method_exists($this, $method)) {
                        $this->$method($v);
                    } else {
                        $this->$k = $v;
                    }
                }
            }
            $this->clear();
            $this->_smtp_auth = ($this->smtp_user == '' && $this->smtp_pass == '') ? false : true;
            $this->_safe_mode = ((boolean) ini_get('safe_mode') === false) ? false : true;

            return $this;
        }

        public function clear($attach = false) {
            foreach($this->_clear_vars as $k => $v) {
                $this->$k = $v;
            }
            $this->_set_header('User_Agent', $this->useragent);
            $this->_set_header('Date', $this->_set_date());
            if($attach) {
                $this->_attach_name = array();
                $this->_attach_type = array();
                $this->_attach_disp = array();
            }

            return $this;

        }

        public function subject($sub) {
            $sub = $this->_prep_encoding($sub);
            $this->_set_header('Subject', $sub);
            return $this;
        }

        public function message($body) {
            $this->_body = rtrim(str_replace("\r", "", $body));
            return $this;
        }

        public function attach($filename, $dispo = 'attachment') {
            $this->_attach_name[] = $filename;
            $this->_attach_type[] = $this->_mime_types(pathinfo($filename, PATHINFO_EXTENSION));
            $this->_attach_disp[] = $dispo;
        }

        public function to($to) {
            $to = $this->_str_to_array($to);
            $to = $this->clean_email($to);
            if($this->validate) {
                $this->validate_email($to);
            }
            if($this->_get_protocol() != 'mail') {
                $this->_set_header('To', implode(", ", $to));
            }
            switch($this->_get_protocol()) {
                case 'smtp':
                    $this->_recipients = $to;
                    break;
                case 'sendmail':
                case 'mail':
                    $this->_recipients = implode(", ", $to);
                    break;
            }
            return $this;
        }

        public function reply_to($to, $name = null) {
            if(preg_match('/\<(.*)\>/', $to, $matches)) {
                $to = $matches[1];
            }
            if($this->validate) {
                $this->validate_email($this->_str_to_array($to));
            }
            if(!$name) {
                $name = $to;
            }
            if(strncmp($name, '"', 1) != 0) {
                $name = '"'.$name.'"';
            }
            $this->_set_header('Reply-To', $name.'<'.$to.'>');
            $this->_replyto_flag = true;

            return $this;
        }

        public function from($from, $name = null) {
            if(preg_match('/\<(.*)\>/', $from, $matches)) {
                $from = $matches[1];
            }
            if($this->validate) {
                $this->validate_email($this->_str_to_array($from));
            }
            if(!$name) {
                if(!preg_match('/[\200-\377]/', $name)) {
                    $name = '"'.addcslashes($name, "\0..\37\177'\"\\").'"';
                } else {
                    $name = $this->prep_encoding($name, true);
                }
            }
            $this->_set_header('From', $name.' <'.$from.'>');
            $this->_set_header('Return-Path', '<'.$from.'>');
            return $this;
        }

        public function cc($cc) {
            $cc = $this->_str_to_array($cc);
            $cc = $this->clean_email($cc);
            if($this->validate) {
                $this->validate_email($cc);
            }
            $this->_set_header('Cc', implode(", ", $cc));
            if($this->_get_protocol() == 'smtp') {
                $this->_cc_array = $cc;
            }
            return $this;
        }

        public function bcc($bcc, $limit = 0) {
            if(is_numeric($limit) && $limit > 0) {
                $this->bcc_batch_mode = true;
                $this->bcc_batch_size = $limit;
            }
            $bcc = $this->_str_to_array($bcc);
            $bcc = $this->clean_mail($bcc);
            if($this->validate) {
                $this->validate_email($bcc);
            }
            if(($this->_get_protocol() == 'smtp') || ($this->bcc_batch_mode && count($bcc) > $this->bcc_batch_size)) {
                $this->_bcc_array = $bcc;
            } else {
                $this->_set_header('Bcc', implode(", ", $bcc));
            }
            return $this;
        }

	    public function send() {
		    if(!$this->_replyto_flag) {
			    $this->reply_to($this->_headers['From']);
		    }
		    if((!isset($this->_recipients) && ! isset($this->_headers['To']))
		        && (!isset($this->_bcc_array) && !isset($this->_headers['Bcc']))
		        && (!isset($this->_headers['Cc']))) {
			    $this->_set_error_message('lang:email_no_recipients');
			    return false;
		    }
		    $this->_build_headers();
		    if($this->bcc_batch_mode && count($this->_bcc_array) > 0) {
			    if(count($this->_bcc_array) > $this->bcc_batch_size) {
				    return $this->batch_bcc_send();
			    }
		    }
		    $this->_build_message();
		    if(!$this->_spool_email()) {
			    return false;
		    }
		    return true;
	    }

	    public function batch_bcc_send() {
		    $float = $this->bcc_batch_size - 1;
		    $set = '';
		    $chunk = array();
		    for($i = 0; $i < count($this->_bcc_array); $i++) {
			    if(isset($this->_bcc_array[$i])) {
				    $set .= ", ".$this->_bcc_array[$i];
			    }
			    if($i == $float) {
				    $chunk[] = substr($set, 1);
				    $float = $float + $this->bcc_batch_size;
				    $set = '';
			    }
			    if($i == count($this->_bcc_array) - 1) {
				    $chunk[] = substr($set, 1);
			    }
		    }
		    for($i = 0; $i < count($chunk); $i++) {
			    unset($this->_headers['Bcc']);
			    unset($bcc);
			    $bcc = $this->_str_to_array($chunk[$i]);
			    $bcc = $this->clean_email($bcc);
			    if($this->protocol != 'smtp') {
				    $this->_set_header('Bcc', implode(", ", $bcc));
			    } else {
				    $this->_bcc_array = $bcc;
			    }
			    $this->_build_message();
			    $this->_spool_email();
		    }
	    }

        public function word_wrap($str, $charlim = null) {
            if(!$charlim) {
                $charlim = ($this->wrapchars == '') ? 76 : $this->wrapchars;
            }
            $str = preg_replace("| +|", " ", $str);
            if(strpos($str, "\r") !== false) {
                $str = str_replace(array("\r\n", "\r"), "\n", $str);
            }
            $unwrap = array();
            if(preg_match_all("|(\{unwrap\}.+?\{/unwrap\})|s", $str, $matches)) {
                for($i = 0; $i < count($matches[0]); $i++) {
                    $unwrap[] = $matches[1][$i];
                    $str = str_replace($matches[1][$i], "{{unwrapped".$i."}}", $str);
                }
            }
            $str = wordwrap($str, $charlim, "\n", false);
            $output = '';
            foreach(explode("\n", $str) as $line) {
                if(strlen($line) <= $charlim) {
                    $output .= $line.$this->newline;
                    continue;
                }
                $temp = '';
                while((strlen($line)) > $charlim) {
                    if(preg_match("!\[url.+\]://|www.!", $line)) {
                        break;
                    }
                    $temp .=substr($line, 0, $charlim - 1);
                    $line = substr($line, $charlim - 1);
                }
                if($temp != '') {
                    $output .= $temp.$this->newline.$line;
                } else {
                    $output .= $line;
                }
                $output .= $this->newline;
            }
            if(count($unwrap) > 0) {
                foreach($unwrap as $k => $v) {
                    $output = str_replace("{{unwrapped".$k."}}", $v, $output);
                }
            }
            return $output;
        }

        public function clean_email($email) {
            if(!is_array($email)) {
                if(preg_match('/\<(.*)\>/', $email, $match)) {
                    return $match[1];
                } else {
                    return $email;
                }
            }
            $cleaned = array();
            foreach($email as $addy) {
                if(preg_match('/\<(.*)\>/', $addy, $matches)) {
                    $cleaned[] = $matches[1];
                } else {
                    $cleaned[] = $addy;
                }
            }
            return $cleaned;
        }

        public function validate_email($email) {
            if(!is_array($email)) {
                $this->_set_error_message('lang:email_must_be_array');
                return false;
            }
            foreach($email as $v) {
                if(!$this->valid_email($v)) {
                    $this->_set_error_message('lang:email_invalid_address', $v);
                    return false;
                }
            }
            return true;
        }

        public function valid_email($email) {
            return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) ? false : true;
        }

        public function set_newline($new_line = "\n") {
            $valid = array("\r", "\n", "\r\n");
            if(!in_array($new_line, $valid)) {
                $this->newline = "\n";
                return;
            }

            $this->newline = $new_line;
            return $this;
        }

        public function set_crlf($crlf = "\n") {
            $valid = array("\r", "\r\n", "\n");
            if(!in_arrat($crlf, $valid)) {
                $this->crlf = "\n";
                return;
            }
            $this->crlf = $crlf;
            return $this;
        }

        public function set_priority($level = 3) {
            if(!is_numeric($level)) {
                $this->priority = 3;
                return;
            }
            if($level <1 || $level > 5) {
                $this->priority = 3;
                return;
            }
            $this->priority = $level;
            return $this;
        }

	    public function print_debugger() {
		    $msg = '';
		    if(count($this->_debug_msg) > 0) {
			    foreach($this->_debug_msg as $m) {
				    $msg .= $m;
			    }
		    }
		    $msg .= "<pre>".htmlspecialchars($this->_header_str)."\n".htmlspecialchars($this->_subject)."\n".htmlspecialchars($this->_finalbody)."</pre>";
		    return $msg;
	    }

        public function set_mailtype($type = 'text') {
            $this->mailtype = ($type == 'html') ? 'html' : 'text';
            return $this;
        }

        public function set_wordwrap($wrap = true) {
            $this->wordwrap = (boolean)$wrap;
            return $this;
        }

        public function set_alt_message($str = null) {
            $this->alt_message = $str;
            return $this;
        }

        public function set_protocol($protocol = 'mail') {
            $this->protocol = (!in_array($protocol, $this->_protocols, true)) ? 'mail' : strtolower($protocol);
            return $this;
        }

        protected function _set_header($header, $val) {
            $this->_headers[$header] = $val;
        }

        protected function _get_message_id() {
            $from = $this->_headers['Return-Path'];
            $from = str_replace(">", "", $from);
            $from = str_replace("<", "", $from);
            return "<".uniqid('').strstr($from, '@').">";
        }

        protected function _get_content_type() {
            if($this->mailtype == 'html' && count($this->_attach_name) == 0) {
                return 'html';
            } else if($this->mailtype == 'text' && count($this->_attach_name) > 0) {
                return 'plain-attach';
            } else if($this->mailtype == 'html' && count($this->_attach_name) > 0) {
                return 'html-attach';
            } else {
                return 'plain';
            }
        }

	    protected function _get_ip() {
		    if($this->_IP) {
			    return $this->_IP;
		    }
		    $cip = (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != '')
			    ? $_SERVER['HTTP_CLIENT_IP']
			    : false;
		    $rip = (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '')
			    ? $_SERVER['REMOTE_ADDR']
			    : false;
		    $fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '')
			    ? $_SERVER['HTTP_X_FORWARDED_FOR']
			    : false;
		    if($cip && $rip) {
			    $this->_IP = $cip;
		    } else if($rip) {
			    $this->_IP = $rip;
		    } else if($cip) {
			    $this->_IP = $cip;
		    } else if($fip) {
			    $this->_IP = $fip;
		    }
		    if(strpos($this->_IP, ',') !== false) {
			    $ex = explode(',', $this->_IP);
			    $this->_IP = end($ex);
		    }
		    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $this->_IP)) {
			    $this->_IP = '0.0.0.0';
		    }
		    unset($cip);
		    unset($rip);
		    unset($fip);
		    return $this->_IP;
	    }

	    protected function _get_hostname() {
		    return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain';
	    }

	    protected function _mime_types($ext = "") {
		    $mimes = array(	'hqx'	=>	'application/mac-binhex40',
		                       'cpt'	=>	'application/mac-compactpro',
		                       'doc'	=>	'application/msword',
		                       'bin'	=>	'application/macbinary',
		                       'dms'	=>	'application/octet-stream',
		                       'lha'	=>	'application/octet-stream',
		                       'lzh'	=>	'application/octet-stream',
		                       'exe'	=>	'application/octet-stream',
		                       'class'	=>	'application/octet-stream',
		                       'psd'	=>	'application/octet-stream',
		                       'so'	=>	'application/octet-stream',
		                       'sea'	=>	'application/octet-stream',
		                       'dll'	=>	'application/octet-stream',
		                       'oda'	=>	'application/oda',
		                       'pdf'	=>	'application/pdf',
		                       'ai'	=>	'application/postscript',
		                       'eps'	=>	'application/postscript',
		                       'ps'	=>	'application/postscript',
		                       'smi'	=>	'application/smil',
		                       'smil'	=>	'application/smil',
		                       'mif'	=>	'application/vnd.mif',
		                       'xls'	=>	'application/vnd.ms-excel',
		                       'ppt'	=>	'application/vnd.ms-powerpoint',
		                       'wbxml'	=>	'application/vnd.wap.wbxml',
		                       'wmlc'	=>	'application/vnd.wap.wmlc',
		                       'dcr'	=>	'application/x-director',
		                       'dir'	=>	'application/x-director',
		                       'dxr'	=>	'application/x-director',
		                       'dvi'	=>	'application/x-dvi',
		                       'gtar'	=>	'application/x-gtar',
		                       'php'	=>	'application/x-httpd-php',
		                       'php4'	=>	'application/x-httpd-php',
		                       'php3'	=>	'application/x-httpd-php',
		                       'phtml'	=>	'application/x-httpd-php',
		                       'phps'	=>	'application/x-httpd-php-source',
		                       'js'	=>	'application/x-javascript',
		                       'swf'	=>	'application/x-shockwave-flash',
		                       'sit'	=>	'application/x-stuffit',
		                       'tar'	=>	'application/x-tar',
		                       'tgz'	=>	'application/x-tar',
		                       'xhtml'	=>	'application/xhtml+xml',
		                       'xht'	=>	'application/xhtml+xml',
		                       'zip'	=>	'application/zip',
		                       'mid'	=>	'audio/midi',
		                       'midi'	=>	'audio/midi',
		                       'mpga'	=>	'audio/mpeg',
		                       'mp2'	=>	'audio/mpeg',
		                       'mp3'	=>	'audio/mpeg',
		                       'aif'	=>	'audio/x-aiff',
		                       'aiff'	=>	'audio/x-aiff',
		                       'aifc'	=>	'audio/x-aiff',
		                       'ram'	=>	'audio/x-pn-realaudio',
		                       'rm'	=>	'audio/x-pn-realaudio',
		                       'rpm'	=>	'audio/x-pn-realaudio-plugin',
		                       'ra'	=>	'audio/x-realaudio',
		                       'rv'	=>	'video/vnd.rn-realvideo',
		                       'wav'	=>	'audio/x-wav',
		                       'bmp'	=>	'image/bmp',
		                       'gif'	=>	'image/gif',
		                       'jpeg'	=>	'image/jpeg',
		                       'jpg'	=>	'image/jpeg',
		                       'jpe'	=>	'image/jpeg',
		                       'png'	=>	'image/png',
		                       'tiff'	=>	'image/tiff',
		                       'tif'	=>	'image/tiff',
		                       'css'	=>	'text/css',
		                       'html'	=>	'text/html',
		                       'htm'	=>	'text/html',
		                       'shtml'	=>	'text/html',
		                       'txt'	=>	'text/plain',
		                       'text'	=>	'text/plain',
		                       'log'	=>	'text/plain',
		                       'rtx'	=>	'text/richtext',
		                       'rtf'	=>	'text/rtf',
		                       'xml'	=>	'text/xml',
		                       'xsl'	=>	'text/xml',
		                       'mpeg'	=>	'video/mpeg',
		                       'mpg'	=>	'video/mpeg',
		                       'mpe'	=>	'video/mpeg',
		                       'qt'	=>	'video/quicktime',
		                       'mov'	=>	'video/quicktime',
		                       'avi'	=>	'video/x-msvideo',
		                       'movie'	=>	'video/x-sgi-movie',
		                       'doc'	=>	'application/msword',
		                       'word'	=>	'application/msword',
		                       'xl'	=>	'application/excel',
		                       'eml'	=>	'message/rfc822'
		    );

		    return ( ! isset($mimes[strtolower($ext)])) ? "application/x-unknown-content-type" : $mimes[strtolower($ext)];
	    }

        protected function _str_to_array($str) {
            if(!is_array($str)) {
                if(strpos($str, ',') !== false) {
                    $str = preg_split('/[\s,]/', $str, -1, PREG_SPLIT_NO_EMPTY);
                } else {
                    $str = trim($str);
                    settype($str, "array");
                }
            }
            return $str;
        }

	    protected function _spool_email() {
		    $this->_unwrap_specials();
		    $type = $this->_get_protocol();
		    if($type == 'mail') {
			    if(!$this->_send_with_mail()) {
				    $this->_set_error_message('lang:email_send_failure_phpmail');
				    return false;
			    }
		    } else if($type == 'sendmail') {
			    if(!$this->_send_with_sendmail()) {
				    $this->_set_error_message('lang:email_send_failure_sendmail');
				    return false;
			    }
		    } else if($type == 'smtp') {
			    if(!$this->_send_with_smtp()) {
				    $this->_set_error_message('lang:email_send_failure_smtp');
				    return false;
			    }
		    }
		    $this->_set_error_message('lang:email_sent', $this->_get_protocol());
		    return true;
	    }

	    protected function _send_with_mail() {
		    if($this->_safe_mode) {
			    if(!mail($this->_recipients, $this->_subject, $this->_finalbody, $this->_header_str)) {
				    return false;
			    }
			    return true;
		    } else {
			    if(!mail($this->_recipients, $this->_subject, $this->_finalbody, $this->_header_str, "-f ".$this->clean_email($this->_headers['From']))) {
				    return false;
			    }
			    return true;
		    }
	    }

	    protected function _send_with_sendmail() {
		    $fp = popen($this->mailpath . " -oi -f ".$this->clean_email($this->_headers['From'])." -t", "w");
		    if($fp === false || $fp === null) {
			    return false;
		    }
		    fputs($fp, $this->_header_str);
		    fputs($fp, $this->_finalbody);
		    $status = pclose($fp);
		    if(version_compare(PHP_VERSION, '4.2.3') == -1) {
			    $status = $status >> 8 & 0xFF;
		    }
		    if($status != 0) {
			    $this->_set_error_message('lang:email_exit_status', $status);
			    $this-_set_error_message('lang:email_no_socket');
			    return false;
		    }
		    return true;
	    }

	    protected function send_with_smtp() {
		    if($this->smtp_host == '') {
			    $this->_set_error_message('lang:email_no_hostname');
			    return false;
		    }
		    $this->_smtp_connect();
		    $this->_smtp_authenticate();
		    $this->_send_command('from', $this->clean_email($this->_headers['From']));
		    foreach($this->_recipients as $recip) {
			    $this->_send_command('to', $recip);
		    }
		    if(count($this->_cc_array) > 0) {
			    foreach($this->_cc_array as $cc) {
				    if($cc != '') {
					    $this->_send_command('to', $cc);
				    }
			    }
		    }
		    if(count($this->_bcc_array) > 0) {
			    foreach($this->_bcc_array as $bcc) {
				    if($bcc != '') {
					    $this->_send_command('to', $bcc);
				    }
			    }
		    }
		    $this->_send_command('data');
		    $this->_send_data($this->_header_str . preg_replace('/^\./m', '..$1', $this->_finalbody));
		    $this->_send_data('.');
		    $reply = $this->_get_smtp_data();
		    $this->_set_error_message($reply);
		    if(strncmp($reply, '250', 3) != 0) {
			    $this->_set_error_message('lang:email_smtp_error', $reply);
			    return false;
		    }
		    $this->_send_command('quit');
		    return true;
	    }

	    protected function _smtp_connect() {
		    $ssl = null;
		    if($this->smtp_crypto == 'ssl') {
			    $ssl = 'ssl://';
		    }
		    $this->_smtp_connect = fsockopen($ssl.$this->smtp_host, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);
		    if(!is_resource($this->_smtp_connect)) {
			    $this->_set_error_message('lang:email_smtp_error', $errno." ".$errstr);
			    return false;
		    }
		    $this->_set_error_message($this->_get_smtp_data());
		    if($this->smtp_crypto == 'tls') {
			    $this->_send_command('hello');
			    $this->_send_command('starttls');
			    stream_cocket_enable_crypto($this->_smtp_connect, true, STRE<STREAM_CRYPTO_METHOD_TLS_CLIENT);
		    }
		    return $this->_send_command('hello');
	    }

	    protected function _send_command($cmd, $data = null) {
		    switch ($cmd)
		    {
			    case 'hello' :

				    if ($this->_smtp_auth OR $this->_get_encoding() == '8bit')
					    $this->_send_data('EHLO '.$this->_get_hostname());
				    else
					    $this->_send_data('HELO '.$this->_get_hostname());

				    $resp = 250;
				    break;
			    case 'starttls'	:

				    $this->_send_data('STARTTLS');

				    $resp = 220;
				    break;
			    case 'from' :

				    $this->_send_data('MAIL FROM:<'.$data.'>');

				    $resp = 250;
				    break;
			    case 'to'	:

				    $this->_send_data('RCPT TO:<'.$data.'>');

				    $resp = 250;
				    break;
			    case 'data'	:

				    $this->_send_data('DATA');

				    $resp = 354;
				    break;
			    case 'quit'	:

				    $this->_send_data('QUIT');

				    $resp = 221;
				    break;
		    }

		    $reply = $this->_get_smtp_data();

		    $this->_debug_msg[] = "<pre>".$cmd.": ".$reply."</pre>";

		    if (substr($reply, 0, 3) != $resp)
		    {
			    $this->_set_error_message('lang:email_smtp_error', $reply);
			    return FALSE;
		    }

		    if ($cmd == 'quit')
		    {
			    fclose($this->_smtp_connect);
		    }

		    return TRUE;
	    }

	    protected function _send_data($data) {
		    if(!fwrite($this->_smtp_connect, $data.$this->newline)) {
			    $this->_set_error_message('lang:email_smtp_data_failure', $data);
			    return false;
		    }
		    return true;
	    }

	    protected function _get_smtp_data() {
		    $data = '';
		    while($str = fgets($this->_smtp_connect, 512)) {
			    $data .= $str;
			    if(substr($str, 3, 1) == " ") {
				    break;
			    }
		    }
		    return $data;
	    }

	    protected function _smtp_authenticate()
	    {
		    if ( ! $this->_smtp_auth)
		    {
			    return TRUE;
		    }

		    if ($this->smtp_user == ""  AND  $this->smtp_pass == "")
		    {
			    $this->_set_error_message('lang:email_no_smtp_unpw');
			    return FALSE;
		    }

		    $this->_send_data('AUTH LOGIN');

		    $reply = $this->_get_smtp_data();

		    if (strncmp($reply, '334', 3) != 0)
		    {
			    $this->_set_error_message('lang:email_failed_smtp_login', $reply);
			    return FALSE;
		    }

		    $this->_send_data(base64_encode($this->smtp_user));

		    $reply = $this->_get_smtp_data();

		    if (strncmp($reply, '334', 3) != 0)
		    {
			    $this->_set_error_message('lang:email_smtp_auth_un', $reply);
			    return FALSE;
		    }

		    $this->_send_data(base64_encode($this->smtp_pass));

		    $reply = $this->_get_smtp_data();

		    if (strncmp($reply, '235', 3) != 0)
		    {
			    $this->_set_error_message('lang:email_smtp_auth_pw', $reply);
			    return FALSE;
		    }

		    return TRUE;
	    }

	    protected function _unwrap_specials() {
		    $this->_finalbody = preg_replace_callback("/\{unwrap\}(.*?)\{\/unwrap\}/si", array($this, '_remove_nl_cb'), $this->_finalbody);
	    }

	    protected function _remove_nl_cb($matches) {
		    if(strpos($matches[1], "\r") !== false || strpos($matches[1], "\n") !== false) {
			    $matches[1] = str_replace(array("\r\n", "\r", "\n"), '', $matches[1]);
		    }
		    return $matches[1];
	    }

        protected function _get_mime_message() {
            return "This is a  multi-part message in MIME format.".$this->newline."Your email application may not support this format.";
        }

        protected function _build_headers() {
            $this->_set_header('X-Sender', $this->clean_email($this->_headers['From']));
            $this->_set_header('X-Mailer', $this->useragent);
            $this->_set_header('X-Priority', $this->_priorities[$this->priority - 1]);
            $this->_set_header('Message-ID', $this->_get_message_id());
            $this->_set_header('Mime-Version', '1.0');
        }

        protected function _set_date() {
            $timezone = date("Z");
            $operator = (strncmp($timezone, '-', 1) == 0) ? '-' : '+';
            $timezone = abs($timezone);
            $timezone = floor($timezone / 3600) * 100 + ($timezone % 3600) / 60;
            return sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $timezone);
        }

        protected function _write_headers() {
            if($this->protocol == 'mail') {
                $this->_subject = $this->_headers['Subject'];
                unset($this->_headers['Subject']);
            }
            reset($this->_headers);
            $this->_header_str = '';
            foreach($this->_headers as $k => $v) {
                $v = trim($v);
                if($v != '') {
                    $this->_header_str .= $k.": ".$v.$this->newline;
                }
            }
            if($this->_get_protocol() == 'mail') {
                $this->_header_str = rtrim($this->_header_str);
            }
        }

        protected function _build_message() {
            if($this->wordwrap && $this->mailtype != 'html') {
                $this->_body = $this->word_wrap($this->_body);
            }
            $this->_set_boundaries();
            $this->_write_headers();
            $hdr = ($this->_get_protocol() == 'mail') ? $this->newline : '';
            $body = '';
            $type = $this->_get_content_type();
            if($type == 'plain') {
                $hdr .= "Content-Type: text/plain; charset=".$this->charset.$this->newline;
                $hdr .= "Content-Transfer-Encoding: ".$this->_get_encoding();
                if($this->_get_protocol() == 'mail') {
                    $this->_header_str .= $hdr;
                    $this->_finalbody = $this->_body;
                } else {
                    $this->_finalbody = $hdr . $this->newline . $this->newline . $this->_body;
                }
                return;
            } else if($type == 'html') {
                if(!$this->send_multipart) {
                    $hdr .= "Content-Type: text/html; charset=".$this->charset . $this->newline;
                    $hdr .= "Content-Transfer-Encoding: quoted-printable";
                } else {
                    $hdr .= "Content-Type: multipart/alternative; boundary=\"".$this->_alt_boundary."\"".$this->newline.$this->newline;
                    $body .= $this->_get_mime_message() . $this->newline . $this->newline;
                    $body .= "--".$this->_alt_boundary . $this->newline;
                    $body .= "Content-Type: text/plain; charset=".$this->charset.$this->newline;
                    $body .= "Content-Transfer-Encoding: ".$this->_get_encoding() . $this->newline . $this->newline;
                    $body .= $this->_get_alt_message() . $this->newline . $this->newline . "--" . $this->_alt_boundary . $this->newline;
                    $body .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
                    $body .= "Content-Transfer-Encoding: quoted-printable".$this->newline.$this->newline;
                }
                $this->_finalbody = $body . $this->_prep_quoted_printable($this->_body) . $this->newline . $this->newline;
                if($this->_get_protocol() == 'mail') {
                    $this->_header_str .= $hdr;
                } else {
                    $this->_finalbody = $hdr . $this->_finalbody;
                }
                if($this->send_multipart) {
                    $this->_finalbody .= "--" . $this->_alt_boundary . "--";
                }
                return;
            } else if($type == 'plain-attach') {
                $hdr .= "Content-Type: multipart/".$this->multipart."; boundary=\"".$this->_atc_boundary."\"".$this->newline.$this->newline;
                if($this->_get_protocol() == 'mail') {
                    $this->_header_str .= $hdr;
                }
                $body .= $this->_get_mime_message() . $this->newline.$this->newline;
                $body .= "--".$this->_atc_boundary.$this->newline;
                $body .= "Content-Type: text/plain; charset=".$this->_cc_array.$this->newline;
                $body .= "Content-Transfer-Encoding: " . $this->_get_encoding(). $this->newline.$this->newline;
                $body .= $this->_body. $this->newline.$this->newline;
            } else if($type == 'html-attach') {
                $hdr .= "content-type: multipart/".$this->multipart."; boundary=\"".$this->_atc_boundary."\"". $this->newline.$this->newline;
                if($this->_get_protocol() == 'mail') {
                    $this->_header_str .= $hdr;
                }
                $body .= $this->_get_mime_message() . $this->newline.$this->newline;
                $body .= "--".$this->_atc_boundary . $this->newline;
                $body .= "Content-Type: multipart/alternative; boundary=\"" . $this->_alt_boundary . "\"" . $this->newline.$this->newline;
                $body .= "--" . $this->_alt_boundary . $this->newline;
                $body .= "Content-Type: text/plain; charset=".$this->charset.$this->newline;
                $body .= "Content-Transfer-Encoding: quoted-printable". $this->newline.$this->newline;
                $body .= $this->_get_alt_message(). $this->newline.$this->newline."--".$this->_alt_boundary.$this->newline;
                $body .= "Content-Type: text/html; charset=".$this->charset.$this->newline;
                $body .= "Content-Transfer-Encoding: quoted-printable". $this->newline.$this->newline;
                $body .= $this->_prep_quoted_printable($this->_body). $this->newline.$this->newline;
                $body .= "--".$this->_alt_boundary . "--" . $this->newline.$this->newline;
            }
            $attachment = array();
            $z = 0;
            for($i = 0; $i < count($this->_attach_name); $i++) {
                $filename = $this->_attach_name[$i];
                $basename = basename($filename);
                $cctype = $this->_attach_type[$i];

                if(!file_exists($filename)) {
                    $this->_set_error_message('lang:email_attach_missing', $filename);
                    return false;
                }
                $h = "--".$this->_atc_boundary.$this->newline;
                $h .= "Content-Type: ".$cctype."; ";
                $h .= "name=\"".$basename."\"".$this->newline;
                $h .= "Content-Disposition: ".$this->_attach_disp[$i].";".$this->newline;
                $h .= "Content-Transfer-Encoding: base64".$this->newline;

                $attachment[$z++] = $h;
                $file = filesize($filename) + 1;

                if(!$fp = fopen($filename, FILE_READ)) {
                    $this->_set_error_message('lang:email_attachment_unreadable', $filename);
                    return false;
                }
                $attachment[$z++] = chunk_split(base64_encode(fread($fp, $file)));
                fclose($fp);
            }

            $body .= implode($this->newline, $attachment).$this->newline."--".$this->_atc_boundary."--";

            if($this->_get_protocol() == 'mail') {
                $this->_finalbody = $body;
            } else {
                $this->_finalbody = $hdr . $body;
            }
            return;
        }

        protected function _get_encoding($ret = true) {
            $this->_encoding = (!in_array($this->_encoding, $this->_bit_depths)) ? '8bit' : $this->_encoding;
            foreach($this->_base_charsets as $charset) {
                if(strncmp($charset, $this->charset, strlen($charset)) == 0) {
                    $this->_encoding = '7bit';
                }
            }
            if($ret) {
                return $this->_encoding;
            }
        }

        protected function _get_alt_message() {
            if($this->alt_message != '') {
                return $this->word_wrap($this->alt_message, '76');
            }
            if(preg_match('/\<body.*?\>(.*)\<\/body\>/si', $this->_body, $matches)) {
                $body = $matches[1];
            } else {
                $body = $this->body;
            }
            $body = trim(strip_tags($body));
            $body = preg_replace('#<!--(.*)--\>#', "", $body);
            $body = str_replace("\t", "", $body);
            for($i = 20; $i >= 3; $i--) {
                $n = '';
                for($x = 1; $x <= $i; $x++) {
                    $n .= "\n";
                }
                $body = str_replace($n, "\n\n", $body);
            }
            return $this->word_wrap($body, '76');

        }

        protected function _get_protocol($ret = true) {
            $this->protocol = strtolower($this->protocol);
            $this->protocol = (in_array($this->protocol, $this->_protocols, true)) ? $this->protocol : 'mail';
            if($ret) {
                return $this->protocol;
            }
        }

	    protected function _prep_encoding($str, $from = false) {
		    $str = str_replace(array("\r", "\n"), array('', ''), $str);
		    $limit = 75 - 7 - strlen($this->charset);
		    $convert = array('_', '=', '?');
		    if($from) {
			    $convert[] = ',';
			    $convert[] = ';';
		    }
		    $output = '';
		    $temp = '';
		    for($i = 0, $len = strlen($str); $i < $len; $i++) {
			    $char = substr($str, $i, 1);
			    $ascii = ord($char);
			    if($ascii < 32 || $ascii > 126 || in_array($char, $convert)) {
				    $char = '=' . dechex($ascii);
			    }
			    if($ascii == 32) {
				    $char = '_';
			    }
			    if((strlen($temp) + strlen($char)) >= $limit) {
				    $output .= $temp.$this->crlf;
				    $temp = '';
			    }
			    $temp .= $char;
		    }
		    $str = $output.$temp;
		    $str = trim(preg_replace('/^(.*)$/m', ' =?'.$this->charset.'?Q?$1?=', $str));

		    return $str;
	    }

	    protected function _prep_quoted_printable($str, $charlim = null) {
		    if(!$charlim || $charlim > 76) {
			    $charlim = 76;
		    }
		    $str = preg_replace("| +|", " ", $str);
		    $str = preg_replace('/\x00+/', '', $str);
		    if(strpos($str, "\r")) {
			    $str = str_replace(array("\r\n", "\r"), "\n", $str);
		    }
		    $str = str_replace(array('{unwrap}', '{/unwrap}'), '', $str);
		    $lines = explode("\n", $str);
		    $escape = '=';
		    $output = '';
		    foreach($lines as $line) {
			    $len = strlen($line);
			    $temp = '';
			    for($i = 0; $i < $len; $i++) {
				    $char = substr($line, $i, 1);
				    $ascii = ord($char);
				    if($i == ($len - 1)) {
					    $char = ($ascii == 32 || $ascii == 9) ? $escape.sprintf('%02s', dechex($ascii)) : $char;
				    }
				    if($ascii == 61) {
					    $char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));
				    }
				    if((strlen($temp) + strlen($char)) >= $charlim) {
					    $output .= $temp.$escape.$this->crlf;
					    $temp = '';
				    }
				    $temp .= $char;
			    }
			    $output .= $temp.$this->crlf;
		    }
		    $output = substr($output, 0, strlen($this->crlf) * -1);
		    return $output;
	    }

        protected function _set_boundaries() {
            $this->_alt_boundary = "B_ALT_".uniqid('');
            $this->_atc_boundary = "B_ATC_".uniqid('');
        }

	    protected function _set_error_message($msg, $v = null) {
		    $OOF = OOF::get_instance();
		    $OOF->lang->load('email');
		    if(substr($msg, 0, 5) != 'lang:' || false == ($line = $OOF->lang->line(substr($msg, 5)))) {
			    $this->_debug_msg[] = str_replace('%s', $v, $msg)."<br />";
		    } else {
			    $this->_debug_msg[] = str_replace('%s', $v, $line)."<br />";
		    }
	    }

    }