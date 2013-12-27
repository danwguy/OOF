<?php


    class CurlHandler extends Singleton {


        const RETURN_DATA_PLAIN = 0;
        const RETURN_DATA_JSON  = 1;
        const RETURN_DATA_HTML  = 2;
        const RETURN_DATA_XML   = 3;

        private $_curl_handle;
        private $_target_url;
        private $_curl_opts;
        private $_curl_info;
        private $_result;
        private $_errors;
        private $_request_start_time;
        private $_request_end_time;
        private $_request_duration;

        public function construct() {
            $this->_curl_handle = curl_init();
            $this->_curl_opts   = array();
            $this->_errors      = array();
        }

        public function __destruct() {
            curl_close($this->_curl_handle);
        }

        private function curl() {
            $this->_curl_opts[CURLOPT_URL] = $this->_target_url;
            $standard_curl_opts            = array(
                "CURLOPT_RETURNTRANSFER" => true,
                "CURLINFO_HEADER_OUT"    => true
            );
            foreach($standard_curl_opts as $curl_opt => $curl_opt_value) {
                if(!isset($this->_curl_opts[constant($curl_opt)])) {
                    $this->_curl_opts[constant($curl_opt)] = $curl_opt_value;
                }
            }
            if(curl_setopt_array($this->_curl_handle, $this->_curl_opts)) {
                $this->_curl_info          = array();
                $this->_request_start_time = new DateTime();
                $microstart                = microtime(true);
                $this->_result             = curl_exec($this->_curl_handle);
                $microstop                 = microtime(true);
                $this->_request_end_time   = new DateTime();
                $this->_request_duration   = ($microstop - $microstart) * 1000;
                $this->_curl_info          = curl_getinfo($this->_curl_handle);
                $this->_errors[]           = array(
                    "number"  => curl_errno($this->_curl_handle),
                    "message" => curl_error($this->_curl_handle)
                );
            } else {
                $this->_errors[] = array(
                    "number"  => 1,
                    "message" => "Call to curl_setopt_array() failed. Aborted curl attempt"
                );
                trigger_error("Call to curl_setopt_array() failed. Aborted curl attempt.", E_USER_WARNING);
            }
            $this->_log();

            return $this->_result;
        }

        public function set_curl_opts(array $curl_opts) {
            $this->_curl_opts = $curl_opts;
        }

        public function get_last_result() {
            return $this->_result;
        }

        public function get_last_error() {
            return ArrayUtil::pop($this->_errors);
        }

        public function get_errors() {
            return $this->_errors;
        }

        public function get_curl_info() {
            return $this->_curl_info;
        }

        public function is_successful() {
            $last_error = $this->get_last_error();

            return ($last_error && $last_error['number'] == 0);
        }

        private function _log() {
            if($this->config->item('log_external_requests')) {
                if(isset($this->_curl_opts[CURLOPT_POSTFIELDS])) {
                    $data = $this->_curl_opts[CURLOPT_POSTFIELDS];
                    unset($this->_curl_opts[CURLOPT_POSTFIELDS]);
                } else {
                    $data = null;
                }
                $last_error = $this->get_last_error();
                LogExternal::build(
                           array(
                                "target_url"       => $this->_target_url,
                                "data"             => $data,
                                "result"           => $this->_result,
                                "error_number"     => $last_error['number'],
                                "error_message"    => $last_error['message'],
                                "opts"             => $this->_curl_opts,
                                "external_info"    => $this->_curl_info,
                                "created_on"       => $this->_request_start_time,
                                "ended_on"         => $this->_request_end_time,
                                "request_duration" => $this->_request_duration
                           ));
            }
        }

        private function _parse_html($response) {
            $old = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML($response);
            libxml_use_internal_errors($old);

            return new SimpleXMLElement(simplexml_import_dom($dom)->asXML());
        }

        private function _parse_xml($response) {
            return new SimpleXMLElement($response);
        }

        private function _parse_json($response) {
            return json_decode($response, true);
        }

        public function query($url = null, $return_data_type = null) {
            if($url) {
                $this->_target_url = $url;
            }
            switch($return_data_type) {
                case self::RETURN_DATA_JSON;
                    return $this->_parse_json($this->curl());
                case self::RETURN_DATA_HTML:
                    return $this->_parse_html($this->curl());
                case self::RETURN_DATA_XML:
                    return $this->_parse_xml($this->curl());
                default:
                    return $this->curl();
            }
        }

        public function submit($url = null) {
            if($url) {
                $this->_target_url = $url;
            }
            $this->curl();

            return $this->is_successful();
        }

    }