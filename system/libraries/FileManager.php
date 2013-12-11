<?php


	class FileManager {


		public $config;

		public static $file_data = array();

		public function __construct() {
			$this->config = Loader::load('Config', 'core');
		}

		public function read_file($file) {
			if(!file_exists($file)) {
				return false;
			}
			if(function_exists('file_get_contents')) {
				return file_get_contents($file);
			}
			if(!$fp = fopen($file, FILE_READ)) {
				return false;
			}
			flock($fp, LOCK_SH);

			$ret = '';
			if(filesize($file) > 0) {
				$ret = & fread($fp, filesize($file));
			}
			flock($fp, LOCK_UN);
			fclose($fp);

			return $ret;
		}


		public function delete_files($path, $dir = false, $level = 0) {
			$path = rtrim($path, DS);
			if(!$directory = opendir($path)) {
				return false;
			}
			while(false !== ($filename = readdir($directory))) {
				if($filename != "." && $filename != "..") {
					if(is_dir($path . DS . $filename)) {
						if(substr($filename, 0, 1) != '.') {
							$this->delete_files($path . DS . $filename, $dir, $level + 1);
						}
					} else {
						unlink($path . DS . $filename);
					}
				}
			}
			closedir($directory);
			if($dir && $level > 0) {
				return rmdir($path);
			}

			return true;
		}

        public static function isFileWritable($file) {
            if(DS == '/' && ini_get('safe_mode') == false) {
                return is_writable($file);
            }
            if(is_dir($file)) {
                $file = rtrim($file, '/').'/'.md5(mt_rand(1, 100).mt_rand(1, 100));
                if(($fp = fopen($file, FILE_CREATE_WRITE)) === false) {
                    return false;
                }
                fclose($fp);
                chmod($file, PERMISSION_DIR_WRITE);
                unlink($file);
                return true;
            } else if(!is_file($file) || ($fp = fopen($file, FILE_CREATE_WRITE)) === false) {
                return false;
            }
            fclose($fp);
            return true;
        }

		public function is_file_writable($file) {
			return self::isFileWritable($file);
		}

		public function symbolic_permissions($perms) {
			if(($perms & 0xC000) == 0xC000) {
				$symbolic = 's'; // Socket
			} elseif(($perms & 0xA000) == 0xA000) {
				$symbolic = 'l'; // Symbolic Link
			} elseif(($perms & 0x8000) == 0x8000) {
				$symbolic = '-'; // Regular
			} elseif(($perms & 0x6000) == 0x6000) {
				$symbolic = 'b'; // Block special
			} elseif(($perms & 0x4000) == 0x4000) {
				$symbolic = 'd'; // Directory
			} elseif(($perms & 0x2000) == 0x2000) {
				$symbolic = 'c'; // Character special
			} elseif(($perms & 0x1000) == 0x1000) {
				$symbolic = 'p'; // FIFO pipe
			} else {
				$symbolic = 'u'; // Unknown
			}

			// Owner
			$symbolic .= (($perms & 0x0100) ? 'r' : '-');
			$symbolic .= (($perms & 0x0080) ? 'w' : '-');
			$symbolic .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

			// Group
			$symbolic .= (($perms & 0x0020) ? 'r' : '-');
			$symbolic .= (($perms & 0x0010) ? 'w' : '-');
			$symbolic .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

			// World
			$symbolic .= (($perms & 0x0004) ? 'r' : '-');
			$symbolic .= (($perms & 0x0002) ? 'w' : '-');
			$symbolic .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

			return $symbolic;
		}

		public function octal_perms($perms) {
			return substr(sprintf('%o', $perms), 3);
		}

		protected function _name($file) {
			return substr(strrchr($file, DS), 1);
		}

		protected function _server_path($file) {
			return $file;
		}

		protected function _size($file) {
			return filesize($file);
		}

		protected function _date($file) {
			return filemtime($file);
		}

		protected function _readable($file) {
			return is_readable($file);
		}

		protected function _writable($file) {
			return is_writable($file);
		}

		protected function _executable($file) {
			return is_executable($file);
		}

		protected function _fileperms($file) {
			return fileperms($file);
		}

		public function get_file_info($file, $ret_array = array(
			'name',
			'server_path',
			'size',
			'date'
		)) {
			if(!file_exists($file)) {
				return false;
			}
			if(is_string($ret_array)) {
				$ret_array = explode(',', $ret_array);
			}
			self::$file_data = array();
			foreach($ret_array as $point) {
				if(method_exists($this, '_' . $point)) {
					$func                    = '_' . $point;
					self::$file_data[$point] = $this->$func($file);
				}
			}

			return self::$file_data;
		}

		public function get_dir_info($source, $top_only = true, $recursion = false) {
			$relative_path = $source;
			if($fp = opendir($source)) {
				if(!$recursion) {
					self::$file_data = array();
					$source          = rtrim(realpath($source), DS) . DS;
				}
				while(false != ($files = readdir($fp))) {
					if(is_dir($source . $files) && strncmp($files, '.', 1) !== 0 && !$top_only) {
						$this->get_dir_info($source . $files . DS, $top_only, true);
					} else if(strncmp($files, '.', 1) !== 0) {
						self::$file_data[$files]                  = $this->get_file_info($source . $files);
						self::$file_data[$files]['relative_path'] = $relative_path;
					}
				}

				return self::$file_data;
			} else {
				return false;
			}
		}

		public function write_file($path, $data, $mode = FILE_WRITE_CREATE_TRUNCATE) {
			if(!$fp = fopen($path, $mode)) {
				return false;
			}
			flock($fp, LOCK_EX);
			fwrite($fp, $data);
			flock($fp, LOCK_UN);
			fclose($fp);

			return true;
		}

		public function get_filenames($source, $include_path = false, $recursion = false) {
			if($fp = opendir($source)) {
				if(!$recursion) {
					self::$file_data = array();
					$source          = rtrim(realpath($source), DS) . DS;
				}
				while(false !== ($file = readdir($fp))) {
					if(is_dir($source . $file) && strncmp($file, '.', 1) !== 0) {
						$this->get_filenames($source . $file . DS, $include_path, true);
					} else if(strncmp($file, '.', 1) !== 0) {
						self::$file_data[] = ($include_path) ? $source . $file : $file;
					}
				}

				return self::$file_data;
			} else {
				return false;
			}
		}
	}