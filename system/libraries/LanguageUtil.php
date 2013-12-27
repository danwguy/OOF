<?php


    class LanguageUtil {


        const VOWELS            = "aeiouAEIOU";
        const CONSONANTS        = "bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";
        const TITLE_CASE_IGNORE = "a an the and but or for nor so yet about above across after against along amid among around as at before behind below beneath beside besides between beyond but by down during except for from in inside into like minus near of off on onto outside over past per plus since than through to toward towards under unlike until up upon via with within without";
        const DIRTY_WORDS       = "fuck bitch ass cunt slut whore fucking asshole bitches cunts sluts whores";

        public static $dw = array();

        public static function get_indefinite_article($noun) {
            if(LanguageUtil::is_valid_string($noun)) {
                if(in_array($noun[0], str_split(LanguageUtil::VOWELS))) {
                    return "an";
                } else {
                    return "a";
                }
            } else {
                return null;
            }
        }

        public static function word_limit($string, $limit, $end_char = '&#8230;') {
            if(trim($string) == '') {
                return $string;
            }

            preg_match('/^\s*+(?:\S++\s*+){1,' . (int)$limit . '}/', $string, $matches);
            if(strlen($string) == strlen($matches[0])) {
                $end_char = '';
            }

            return rtrim($matches[0]) . $end_char;
        }

        public static function highlight($string) {
            $str = str_replace(array('&lt;', '&gt;'), array('<', '>'), $string);

            $str = str_replace(
                array('<?', '?>', '<%', '%>', '\\', '</script>'),
                array('phpopen', 'phpclose', 'aspopen', 'aspclose', 'backslashtmp', 'scriptclose'),
                $string);

            $str = '<?php ' . $str . ' ?>';

            $str = highlight_string($str, true);

            if(abs(PHP_VERSION) < 5) {
                $str = str_replace(array('<font ', '</font>'), array('<span ', '</span>'), $str);
                $str = preg_replace('/color="(.*?)"/', 'style="color: \\1"', $str);
            }

            $str = preg_replace(
                '/<span style="color: #([A-Z0-9]+)">&lt;\?php(&nbsp;| )/i',
                '<span style="color: #$1">',
                $str);
            $str = preg_replace(
                '/<span style="color: #[A-Z0-9]+">.*?)\?&gt;<\/span>\n<\/span>\n<\/code>/is',
                "$1</span>\n</span>\n</code>",
                $str);
            $str = preg_replace('/<span style="color: #[A-Z0-9]+"\><\/span>/i', '', $str);

            $str = str_replace(
                array('phpopen', 'phpclose', 'aspopen', 'aspclose', 'backslashtmp', 'scriptclose'),
                array('&lt;?', '?&gt;', '&lt;%', '%&gt;', '\\', '&lt;/script&gt;'),
                $str);

            return $str;

        }

        public static function remove_invisible_chars($str, $url_encoded = true) {
            $undisplayable = array();
            if($url_encoded) {
                $undisplayable[] = '/%0[08bcef]/';
                $undisplayable[] = '/%1[0-9a-f]/';
            }
            $undisplayable[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';

            do {
                $str = preg_replace($undisplayable, '', $str, -1, $count);
            } while($count);

            return $str;
        }

        public static function word_highlight(
            $str,
            $word,
            $tag_open = '<strong><b><i>',
            $tag_close = '</i></b></strong>') {
            if($str == '') {
                return '';
            }
            if($word != '') {
                return preg_replace('/(' . preg_quote($word, '/') . ')/i', $tag_open . "\\1" . $tag_close, $str);
            }

            return $str;
        }

        public static function censor($string, $disallow = null, $replace = '*', $append = false) {
            if(!$disallow) {
                $disallow = (self::$dw) ? self::$dw : explode(' ', self::DIRTY_WORDS);
            } else {
                if($append) {
                    $not_allowed = (self::$dw) ? self::$dw : explode(" ", self::DIRTY_WORDS);
                    if(!is_array($disallow)) {
                        $disallow = (array)$disallow;
                    }
                    $disallow = array_merge($disallow, $not_allowed);
//                    foreach($not_allowed as $wrd) {
//                        $disallow[] = $wrd;
//                    }
                }
            }
            $with_array = array_fill(0, sizeof($disallow), "*");

            foreach($disallow as $word) {
                if(preg_match("/" . $word . "/i", $string)) {
                    $string = preg_replace("/" . $word . "/i", str_repeat($replace, strlen($word)), $string);
                }
            }

            return $string;
        }

        public static function word_wrap($str, $limit = '80') {
            if(!is_numeric($limit)) {
                $limit = 80;
            }
            $str = preg_replace("| +|", " ", $str);

            if(strpos($str, "\r") !== false || preg_match("/\r/i", $str)) {
                $str = str_replace(array("\r\n", "\r"), "\n", $str);
            }
            $unwrap = array();
            if(preg_match_all("|(\{save\}.+?\{/save\})|s", $str, $matches)) {
                for($i = 0, $len = count($matches[0]); $i < $len; $i++) {
                    $unwrap[] = $matches[1][$i];
                    $str      = str_replace($matches[1][$i], "{{save" . $i . "}}", $str);
                }
            }
            $str = wordwrap($str, $limit, "\n", false);

            $output = '';
            foreach(explode("\n", $str) as $line) {
                if(strlen($line) <= $limit) {
                    $output .= $line . "\n";
                    continue;
                }
                $temp = '';
                while((strlen($line)) > $limit) {
                    if(preg_match("~\[url.+\]|://|www.~", $line)) {
                        break;
                    }
                    $temp .= substr($line, 0, $limit - 1);
                    $line = substr($line, $limit - 1);
                }
                if($temp != '') {
                    $output .= $temp . "\n" . $line;
                } else {
                    $output .= $line;
                }
                $output .= "\n";
            }
            if(count($unwrap) > 0) {
                foreach($unwrap as $key => $val) {
                    $output = str_replace("{{save" . $key . "}}", $val, $output);
                }
            }
            $output = str_replace(array('{save}', '{/save}'), '', $output);

            return $output;
        }

        public static function add_ellipsis($str, $length, $position = 1, $ellipsis = '&hellip;') {
            $str = trim(strip_tage($str));

            if(strlen($str) <= $length) {
                return $str;
            }
            $beginning = substr($str, 0, floor($length * $position));
            $position  = ($position > 1) ? 1 : $position;
            if($position == 1) {
                $end = substr($str, 0, -($length - strlen($beginning)));
            } else {
                $end = substr($str, -($length - strlen($beginning)));
            }

            return beginning . $ellipsis . $end;
        }

        public static function to_sentence_case($text) {
            if(LanguageUtil::is_valid_string($text)) {
                return preg_replace("/(?|([.?!]\\s+)(\\w)|^()(\\w))/e", '"$1".strtoupper("$2")', strtolower($text));
            } else {
                return null;
            }
        }

        public static function camel_case_to_words($text) {
            if(LanguageUtil::is_valid_string($text)) {
                return preg_replace("/(\\w)([A-Z])/", "$1 $2", $text);
            } else {
                return null;
            }
        }

        public static function underscores_to_words($text) {
            if(LanguageUtil::is_valid_string($text)) {
                return str_replace("_", " ", $text);
            } else {
                return null;
            }
        }

        public static function underscores_to_camel_case($text) {
            if(LanguageUtil::is_valid_string($text)) {
                return preg_replace("/(_(\\w))/e", 'strtoupper("$2")', $text);
            } else {
                return null;
            }
        }

        public static function camel_case_to_underscores($text) {
            if(LanguageUtil::is_valid_string($text)) {
                return preg_replace("/(\\w)([A-Z])/e", '"$1_".strtolower("$2")', $text);
            } else {
                return null;
            }
        }

        public static function words_to_camel_case($text) {
            if(LanguageUtil::is_valid_string($text)) {
                return preg_replace("/(\\s+(\\w))/e", 'strtoupper("$2")', strtolower($text));
            } else {
                return null;
            }
        }

        public static function words_to_underscores($text) {
            if(LanguageUtil::is_valid_string($text)) {
                return strtolower(preg_replace("/\\s+/", "_", $text));
            } else {
                return null;
            }
        }

        public static function to_title_case($text) {
            if(LanguageUtil::is_valid_string($text)) {
                $text_array = explode(" ", strtolower($text));
                for($i = 0, $num_words = sizeof($text_array); $i < $num_words; $i++) {
                    //capitalize the first and last word, and any not in the ignore string.
                    if($i == 0 || $i == $num_words - 1
                       || !in_array(
                            $text_array[$i],
                            explode(" ", LanguageUtil::TITLE_CASE_IGNORE))
                    ) {
                        $text_array[$i] = ucfirst($text_array[$i]);
                    }
                }

                return implode(" ", $text_array);
            } else {
                return null;
            }
        }

        public static function is_valid_string($string) {
            return ($string && is_string($string));
        }

        public static function to_string($var, $json_encode = false, $stringify_primitives = true) {
            if(is_null($var)) {
                return $stringify_primitives ? "NULL" : null;
            }
            if($var === false) {
                return $stringify_primitives ? "FALSE" : false;
            }
            if($var === true) {
                return $stringify_primitives ? "TRUE" : true;
            }
            if(is_resource($var)) {
                return "Resource of type " . get_resource_type($var);
            }
            if(is_array($var)) {
                return $json_encode ? json_encode($var) : print_r($var, true);
            }
            if(!is_scalar($var)) {
                //objects should use their __toString() method, or if they don't have one, json_encode themselves.
                if(get_class($var) == "DateTime"
                ) { //The DateTime class doesn't have a __toString() method defined for some reason.  Explicitly call DateTime->format() for them.  Also, convert the DateTime to UTC.
                    /** @var DateTime $var */
                    // Cloning incase of datetime because otherwise it gets converted to UTC and gets passed back to the task. DO NOT REMOVE!!!
                    $var = clone $var;
                    $var->setTimezone(new DateTimeZone("UTC"));

                    return DateTimeUtil::format($var, DateTimeUtil::DATETIME_FORMAT_MYSQL);
                }
                try { //check to see if the $var responds well to casting as a string.
                    return $var . "";
                } catch(Exception $e) {
                    if(is_object($var) && !$json_encode) { //if it's an object and we are going for text output

                        // Cloning incase of datetime because otherwise it gets converted to UTC and gets passed back to the task. DO NOT REMOVE!!!
                        $var = clone $var;

                        $string = "Object of type " . get_class($var) . "\n";
                        foreach($var as $key => $value) { //FIXME: This will throw an error due to infinite recursion of two objects contain themselves.
                            $string .= "\t[$key] => " . self::to_string($value, false) . "";
                        }

                        return $string;
                    } else { //otherwise just go straight encode
                        return $json_encode ? json_encode($var) : print_r($var, true);
                    }
                }
            } else {
                return $var . "";
            }
        }

        /**
         * @param Object|String $class_object
         *
         * @return String|null
         */

        public static function get_class_from_namespace($class_object) {
            if(is_object($class_object)) {
                return ArrayUtil::pop(explode("\\", get_class($class_object)));
            } else if(is_string($class_object)) {
                return ArrayUtil::pop(explode("\\", $class_object));
            }

            return null;
        }
    }