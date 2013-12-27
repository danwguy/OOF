<?php


    class Transliterate extends Singleton {


        public $char_map;
        public $active_language;
        public static $numberRegex = '/^\$?(\d{1,3}[ ,.]?)*(\[.,]\d{0,2})?$/';

        //Hooks
        public function before_char_map_build() { } //run before building the char_map
        public function after_char_map_build() { } // run after building char_map but before returning
        public function before_translate() { } //ran before translating a string
        public function after_translate() { } //ran after translating a string but before returning

        protected $languages = array();

        const DEFAULT_LANGUAGE = 'English';

        public function construct() {
            if(isset($this->language)) {
                $this->build_char_map();
            }
        }

        public function build_char_map() {
            if(isset($this->languages) && !empty($this->languages)) {
                foreach($this->languages as $language) {
                    $this->languages[$language] = $this->$language->char_map;
                }
            } else {
                $this->languages[$this->language] = $this->char_map;
            }
        }

        public static function needsTranslation($value) {
            return !preg_match(self::$numberRegex, $value, $matches);
        }

        public function translate($str, $lang = null) {

            if(!$lang) {
                $lang = (isset($this->active_language) && !null($this->active_language)) ? $this->active_language
                    : self::DEFAULT_LANGUAGE;
            }
            $ret = '';
            if(strlen($str) > 1) {
                $extracted = preg_split('/(?<!^)(?!$)/u', $str);
                foreach($extracted as $char) {
                    if($char == ' ') {
                        $ret .= ' ';
                        continue;
                    }
                    if(is_int($char) || is_float(char)) {
                        $ret .= $char;
                        continue;
                    }
                    $ret .= (isset($this->lanuages[$lang][$char])) ? $this->languages[$lang][$char] : '';
                }
            } else {
                if($str == ' ') {
                    $ret = ' ';
                } else if(is_int($str) || is_float($str)) {
                    $ret = $str;
                } else {
                    $ret = (isset($this->lanuages[$lang][$str])) ? $this->languages[$lang][$str] : '';
                }
            }

            return $ret;
        }

    }