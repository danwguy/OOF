<?php


	class Zip {


		public $zip_data = '';
		public $directory = '';
		public $entries = 0;
		public $file_num = 0;
		public $offset = 0;
		public $now;

		public function __construct() {
			$this->now = time();
		}

		public function read_file($path, $preserve = false) {
			if(!file_exists($path)) {
				return false;
			}
			if(false !== ($data = file_get_contents($path))) {
				$name = str_replace("\\", "/", $path);
				if(!$preserve) {
					$name = preg_replace("|.*/(.+)|", "\\1", $name);
				}
				$this->add_data($name, $data);

				return true;
			}

			return false;
		}

		public function read_dir($path, $preserve = true, $root = null) {
			if(!$fp = @open_dir($path)) {
				return false;
			}
			if(!$root) {
				$root = dirname($path) . '/';
			}

			while(false !== ($file = readdir($fp))) {
				if(substr($file, 0, 1) == '.') {
					continue;
				}
				if(@is_dir($path . $file)) {
					$this->read_dir($path . $file . '/', $preserve, $root);
				} else {
					if(false !== ($data = file_get_contents($path . $file))) {
						$name = str_replace("\\", "/", $path);
						if(!$preserve) {
							$name = str_replace($root, '', $path);
						}
						$this->add_data($name . $file, $data);
					}
				}
			}

			return true;
		}

		public function get_zip() {
			if($this->entries == 0) {
				return false;
			}
			$zip_data = $this->zip_data;
			$zip_data .= $this->directory . "\x50\x4b\x05\x06\x00\x00\x00\x00";
			$zip_data .= pack('v', $this->entries);
			$zip_data .= pack('v', $this->entries);
			$zip_data .= pack('V', strlen($this->directory));
			$zip_data .= pack('V', strlen($this->zip_data));
			$zip_data .= "\x00\x00";

			return $zip_data;
		}

		public function add_dir($dir) {
			foreach((array) $dir as $d) {
				if(!preg_match("#.+/$#", $d)) {
					$d .= '/';
				}
				$d_time = $this->_get_mod_time($d);
				$this->_add_dir($d, $d_time['file_mtime'], $d_time['file_mdate']);
			}
		}

		public function add_date($path, $data = null) {
			if(is_array($path)) {
				foreach($path as $p => $d) {
					$file_data = $this->_get_mod_time($p);
					$this->_add_data($p, $d, $file_data['file_mtime'], $file_data['file_mdate']);
				}
			} else {
				$file_data = $this->_get_mod_time($path);
				$this->_add_date($path, $data, $file_data['file_mtime'], $file_data['file_mdate']);
			}
		}

		public function download($file = 'backup.zip') {
			if(!preg_match("|.+?\.zip$|", $file)) {
				$file .= '.zip';
			}
			$oof          = Loader::load('OOF');
			$zip          = $this->get_zip();
			$zip_download = & $zip;
			$oof->download($file, $zip_download);
		}

		public function archive($path) {
			if(!$fp = @fopen($path, FILE_WRITE_CREATE_TRUNCATE)) {
				return false;
			}
			flock($fp, LOCK_EX);
			fwrite($fp, $this->get_zip());
			flock($fp, LOCK_UN);
			fclose($fp);

			return true;
		}

		public function clear_data() {
			$this->zip_data  = '';
			$this->directory = '';
			$this->entries   = 0;
			$this->file_num  = 0;
			$this->offset    = 0;
		}


		protected function _get_mod_time($dir) {
			$date = (@filemtime($dir)) ? filemtime($dir) : getdate($this->now);

			$time['file_mtime'] = ($date['hours'] << 11) + ($date['minutes'] << 5) + $date['seconds'] / 2;
			$time['file_mdate'] = (($date['year'] - 1980) << 9) + ($date['mon'] << 5) + $date['mday'];

			return $time;
		}

		protected function _add_dir($dir, $mtime, $mdate) {
			$dir = str_replace("\\", "/", $dir);

			$this->zip_data .= "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00"
			                   . pack('v', $mtime)
			                   . pack('v', $mdate)
			                   . pack('V', 0)
			                   . pack('V', 0)
			                   . pack('V', 0)
			                   . pack('v', strlen($dir))
			                   . pack('v', 0)
			                   . $dir
			                   . pack('V', 0)
			                   . pack('V', 0)
			                   . pack('V', 0);

			$this->directory .= "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00"
			                    . pack('v', $mtime)
			                    . pack('v', $mdate)
			                    . pack('V', 0)
			                    . pack('V', 0)
			                    . pack('V', 0)
			                    . pack('v', strlen($dir))
			                    . pack('v', 0)
			                    . pack('v', 0)
			                    . pack('v', 0)
			                    . pack('v', 0)
			                    . pack('V', 16)
			                    . pack('V', $this->offset)
			                    . $dir;
			$this->offset = strlen($this->zip_data);
			$this->entries++;
		}

		protected function _add_data($path, $data, $mtime, $mdate) {
			$path = str_replace("\\", "/", $path);

			$uncompressed_size = strlen($data);
			$crc32             = crc32($data);

			$gzdata          = gzcompress($data);
			$gzdata          = substr($gzdata, 2, -4);
			$compressed_size = strlen($gzdata);

			$this->zip_data .= "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00"
			                   . pack('v', $mtime)
			                   . pack('v', $mdate)
			                   . pack('V', $crc32)
			                   . pack('V', $compressed_size)
			                   . pack('V', $uncompressed_size)
			                   . pack('v', strlen($path))
			                   . pack('v', 0)
			                   . $path
			                   . $gzdata;
			$this->directory .= "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00"
			                    . pack('v', $mtime)
			                    . pack('v', $mdate)
			                    . pack('V', $crc32)
			                    . pack('V', $compressed_size)
			                    . pack('V', $uncompressed_size)
			                    . pack('v', strlen($path))
			                    . pack('v', 0)
			                    . pack('v', 0)
			                    . pack('v', 0)
			                    . pack('v', 0)
			                    . pack('V', 32)
			                    . pack('V', $this->offset)
			                    . $path;

			$this->offset = strlen($this->zip_data);
			$this->entries++;
			$this->file_num++;
		}
	}