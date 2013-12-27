<?php


    class Language {


        public $language = array();
        public $is_loaded = array();

        public function __construct() {

        }

        public function load($langfile = null, $idiom = null, $return = false, $suffix = true, $alt_path = null) {
            $langfile = str_replace('.php', '', $langfile);

            if($suffix) {
                $langfile = str_replace('_lang', '', $langfile) . '_lang';
            }
            $langfile .= '.php';

            if(in_array($langfile, $this->is_loaded, true)) {
                return;
            }
            $config = Loader::load('Config');
            if(!$idiom) {
                $default_lang = (isset($config['language'])) ? $config['language'] : 'english';
                $idiom        = ($default_lang == '') ? 'english' : $default_lang;
            }
            if($alt_path && file_exists($alt_path . 'language/' . $idiom . '/' . $langfile)) {
                include($alt_path . 'language/' . $idiom . '/' . $langfile);
            } else {
                $found = false;
                $oof   = Loader::load('OOF');
                foreach($oof->load->get_package_paths(true) as $path) {
                    if(file_exists($path . 'language/' . $idiom . '/' . $langfile)) {
                        include($path . 'language/' . $idiom . '/' . $langfile);
                        $found = true;
                        break;
                    }
                }
                if(!$found) {
                    OOF::show_error("Unable to load the requested language file: language/" . $idiom . "/" . $langfile);
                }
            }
            if(!isset($lang)) {
                return;
            }
            if($return) {
                return $lang;
            }
            $this->is_loaded[] = $langfile;
            $this->language    = array_merge($this->language, $lang);
            unset($lang);

            return true;
        }

        public function line($line = null) {
            return ($line == '' || !isset($this->language[$line])) ? false : $this->language[$line];
        }
    }