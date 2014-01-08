<?php


    class HTML {


        const BR           = "<br />";
        const HR           = "<hr />";
        const NBSP         = "&nbsp;";
        const KEYWORDS = "break, continue, do, else, for, if, return, while, foreach, abstract, bool, extends, final, finally, implements, null, false, true, throw, function, const, static, public, private, protected, class, int, float, double, array, die, exit, goto, require, require_once, include, echo, print_r, var_dump, print, global, \$_POST, \$_REQUEST, \$_GET, \$_SERVER, switch, case, break, next, end, self";

        protected static $link_tag = array('rel' => 'stylesheet', 'type' => 'text/css');

        public $config;
        public $base;
        public $ob_level;

        private $js = array();
        private $_image_path;
        private $_js_path;
        private $_css_path;
        private $_highlight_included = false;
        private static $_static_method_regex = '/[a-zA-Z0-9_]+\:\:(?<!\s)/';
        private static $_inline_comment_regex = '~\s*\/\/(.*?)$~m';
        private static $_block_comment_regex = '#/\*.+?\*/#s';
        private static $_doc_comment_regex = '#/\*\*.+?\*/#s';
        private static $_property_regex = '/(?<=->)[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^(]*/';
        private static $_method_regex = '/(->|::)(\s*[a-zA-Z0-9_]+)(\s*\()/';
        private static $_variable_regex = '/(?<=\$)[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
        private static $_punctuation_regex = '/[,!=@#%^&*(){}\.\-><:;]+/';
        private static $_string_regex = '/[\"\']\s*(.*?)\s*[\"\']/';
        private static $_function_regex = '/function\s*([a-zA-z0-9_]+)/';
        private static $_const_regex = '/(?:["\'][^"\']+["\']|([A-Z0-9_\\.]+\b))/';

        public function __construct() {
            $this->config      = Loader::load('Config', 'core');
            $this->base        = $this->config->item('base_url');
            $this->_image_path = $this->base . 'img/';
            $this->_js_path    = $this->base . 'js/';
            $this->_css_path   = $this->base . 'css/';
            $this->ob_level = ob_get_level();
        }

        public function shortenUrls($data) {
            $data = preg_replace_callback(
                '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@',
                array(get_class($this), '_fetchTinyUrl'),
                $data);

            return $data;
        }

        public function sanitize($data) {
            return mysql_real_escape_string($data);
        }

        public function link($text, $path, $prompt = null, $confirmMessage = "Are you sure?") {
            $path = str_replace(' ', '-', $path);
            $href = $this->_prep_link($path);
            if($prompt) {
                $data =
                    '<a href="javascript:void(0);" onclick="javascript:jumpTo(\'' . $href . '\', \'' . $confirmMessage
                    . '\')">' . $text . '</a>';
            } else {
                $data = '<a href="' . $href . '">' . $text . '</a>';
            }

            return $data;
        }

        protected function _prep_link($link, $type = null, $omit = false) {
            $prep = '';
            if(strpos($link, '://') === false) {
                $prep = $this->base;
            }
            if($type) {
                if(!$omit) {
                    if(substr($link, -strlen($type)) != $type) {
                        $link = $link . '.' . $type;
                    }
                }
                if(substr($link, 0, strlen($type) + 1) != $type . '/') {
                    $prep = $prep . $type . '/';
                }
            }

            return $prep . $link;
        }

        public function includeJs($filename) {
            $ret = '';
            if(is_array($filename)) {
                foreach($filename as $name) {
                    $src = $this->_prep_link($name, 'js');
                    $ret .= "\r\n" . "<script type='text/javascript' src='" . $src . "'></script>";
                }
            } else {
                $src = $this->_prep_link($filename, 'js');
                $ret .= "<script type='text/javascript' src='" . $src . "'></script>";
            }

            return $ret;
        }

        public function includeCss($filename) {
            $ret = '';
            if(is_array($filename)) {
                foreach($filename as $name) {
                    $src = $this->_prep_link($name, 'css');
                    $ret .= "\r\n" . "<link href='" . $src . "' rel='stylesheet' />";
                }
            } else {
                $src = $this->_prep_link($filename, 'css');
                $ret .= '<link href="' . $src . '" rel="stylesheet" />';
            }

            return $ret;
        }

        public function br($num = 1) {
            $ret = '';
            for($i = 0; $i < $num; $i++) {
                $ret .= self::BR;
            }

            return $ret;
        }

        public function heading($text, $level = 1, $attr = '') {
            return "<h" . $level . " " . $attr . ">" . $text . "</h" . $level . ">";
        }

        public function link_tag() {
            $args = func_get_args();
            $args = array_shift($args);
            if(!$args) {
                return '';
            }
            $base     = "<link ";
            $defaults = self::$link_tag;
            if(is_array($args)) {
                foreach($args as $key => $val) {
                    if(in_array($key, array_keys($defaults))) {
                        unset($defaults[$key]);
                    }
                    if($key == 'href') {
                        $src = $this->_prep_link($val);
                        $base .= ' ' . $key . '="' . $src . '" ';
                    } else {
                        $base .= ' ' . $key . '="' . $val . '" ';
                    }
                }
                if(!empty($defaults)) {
                    foreach($defaults as $prop => $value) {
                        $base .= ' ' . $prop . '="' . $value . '" ';
                    }
                }
                $base .= " />";
            } else {
                $num = func_num_args();
                if($num == 1) {
                    $src = $this->_prep_link($args);
                    $base .= ' href="' . $src . '" ';
                    foreach($defaults as $prop => $value) {
                        $base .= $prop . '="' . $value . '" ';
                    }
                    $base .= ' />';
                } else if($num > 1) {
                    $args = func_get_args();
                    $src  = $this->_prep_link($args[0]);
                    $base .= 'href="' . $src . '" ';
                    if(isset($args[1])) {
                        $base .= 'rel="' . $args[1] . '" ';
                    }
                    if(isset($args[2])) {
                        $base .= 'type="' . $args[2] . '" ';
                    }
                    $base .= ' />';
                }
            }

            return $base;
        }

        public function nbs($num = 1) {
            $base = '';
            for($i = 0; $i < $num; $i++) {
                $base .= ' ' . self::NBSP;
            }

            return $base;
        }

        public function meta() {
            $num = func_num_args();
            if($num == 3) {
                $args = func_get_args();

                return $this->make_meta_tag($args[0], $args[1], $args[2]);
            } else if($num == 2) {
                $args = func_get_args();

                return $this->make_meta_tag($args[0], $args[1]);
            } else if($num == 1) {
                if(is_array(array_shift(func_get_args()))) {
                    $args = array_shift(func_get_args());
                    if(isset($args[0])) {
                        $tags = '';
                        foreach($args as $array) {
                            $tags .= "\r\n" . $this->make_meta_tag(
                                                   $array['name'],
                                                   $array['content'],
                                                   (isset($array['type'])) ? $array['type'] : 'name');
                        }
                    } else {
                        $tags = $this->make_meta_tag(
                                     $args['name'],
                                     $args['content'],
                                     (isset($args['type'])) ? $args['type'] : 'name');
                    }
                }

                return $tags;
            }

            return '';
        }

        public function make_meta_tag($name, $content, $type = 'name') {
            if(!$type == 'name') {
                $final = '<meta http-equiv="' . $name . '" content="' . $content . '" />';
            } else {
                $final = '<meta name="' . $name . '" content="' . $content . '" />';
            }

            return $final;
        }

        public function meta_tag() {
            $num   = func_num_args();
            $final = '';
            if($num == 1) {
                $args = array_shift(func_get_args());
                if(is_array($args)) {
                    if(isset($args[1])) {
                        $base = '<meta ';
                        $end  = ' />';
                        foreach($args as $array) {
                            $final .= ' ' . $base;
                            foreach($array as $prop => $value) {
                                $final .= $prop . '="' . $value . '" ';
                            }
                            $final .= $end;
                        }
                    } else {
                        $final .= '<meta ';
                        foreach($args as $prop => $val) {
                            $final .= $prop . '="' . $val . '" ';
                        }
                        $final .= '/>';
                    }
                }
            } else if($num == 2) {
                $args = func_get_args();
                $final .= '<meta name="' . $args[0] . '" content="' . $args[1] . '" />';
            } else if($num == 3) {
                $args = func_get_args();
                $final .= '<meta ';
                if($args[2] == 'equiv') {
                    $final .= 'http-equiv="' . $args[0] . '" content="' . $args[1] . '" />';
                } else if($args[2] == 'name') {
                    $final .= 'name="' . $args[0] . '" content="' . $args[1] . '" />';
                }
            }

            return $final;
        }

        public function ul(array $list, array $attribs = array()) {
            return $this->make_list('ul', $list, $attribs);
        }

        public function make_list($type = 'ul', array $list, array $attribs = array()) {
            $ret = "<" . $type . " ";
            if($attribs && !empty($attribs)) {
                foreach($attribs as $prop => $value) {
                    $ret .= $prop . '="' . $value . '" ';
                }
            }
            $ret .= '>';
            if($list) {
                foreach($list as $base => $text) {
                    $ret .= "\r\n" . '<li>';
                    if(is_array($text)) {
                        $ret .= $base . "\r\n";
                        $ret .= $this->ul($text);
                        $ret .= "\r\n" . "</li>";
                    } else {
                        $ret .= $text . '</li>';
                    }
                }
            }
            $ret .= '</' . $type . '>';

            return $ret;
        }

        public function ol(array $list, array $attribs = array()) {
            return $this->make_list('ol', $list, $attribs);
        }

        public function doctype() {
            $num     = func_num_args();
            $doctype = '';
            if($num == 0) {
                $doctype = 'html';
            } else {
                switch(func_get_arg(0)) {
                    case 'xhtml11':
                        $doctype = 'PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"';
                        break;
                    case 'xhtml1-strict':
                        $doctype = 'PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"';
                        break;
                    case 'xhtml1-trans':
                        $doctype = 'PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"';
                        break;
                    case 'xhtml1-frame':
                        $doctype = 'PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd"';
                        break;
                    case 'html5':
                        $doctype = 'html';
                        break;
                    case 'html4-strict':
                        $doctype = 'PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"';
                        break;
                    case 'html4-trans':
                        $doctype = 'PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"';
                        break;
                    case 'html4-frame':
                        $doctype = 'PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd"';
                        break;
                    default:
                        $doctype = 'html';
                        break;
                }
            }

            return '<!DOCTYPE ' . $doctype . '>';
        }

        public function parse_html($content) {
            return $this->rewrite_img_tags($content);
        }

        public function rewrite_img_tags($content) {
            $matches     = array();
            $replaced    = $content;
            $img_pattern = "/<img\s*(.+?\s*)=\s*[\"'](.+?)[\"']\s*(.+?\s*)=\s*[\"'](.+?)[\"']\s*(.+?\s*)=\s*[\"'](.+?)[\"']\s*(.+?\s*)=\s*[\"'](.+?)[\"']\s*(\/\\>)/i";
            preg_match($img_pattern, $content, $matches);
            if($matches) {
                $total     = count($matches);
                $img_array = array();
                for($i = 0; $i < $total; $i++) {
                    if(isset($matches[$i + 2])) {
                        if($matches[$i + 1] == 'src') {
                            $img                         = array_pop(explode("/", $matches[$i + 2]));
                            $img_array[$matches[$i + 1]] = $img;
                        } else {
                            $img_array[$matches[$i + 1]] = $matches[$i + 2];
                        }
                        $i++;
                    }
                }
                $replacement = self::img($img_array);
                $replaced    = str_replace($matches[0], $replacement, $content);
            }

            return $replaced;
        }

        public function img() {
            $args = func_get_args();
            $args = array_shift($args);
            $num  = func_num_args();
            $img  = "<img ";
            $attr = '';
            if($num == 0) {
                return '';
            }
            if($num == 1) {
                if(is_array($args)) {
                    foreach($args as $key => $val) {
                        if($key == 'src') {
                            $src = $this->_prep_link($val, 'img', true);
                            $img .= ' ' . $key . '="' . $src . '" ';
                        } else {
                            $img .= ' ' . $key . '="' . $val . '" ';
                        }
                    }
                    $img .= " />";
                } else {
                    $src = $this->_prep_link($args, 'img', true);
                    $img .= "src='" . $src . "' />";
                }
            }

            return $img;
        }

        public function clean($str) {
            return strip_tags($str);
        }

        public function highlight($content, $language, $return = true) {
            if(strtolower($language) == 'php') {
                return $this->_syntaxHighlight($content, $return);
            }
            return $content;
        }

        public function setupJsVars() {
            $const = get_defined_constants(true);
            $out   = '<script>';
            if(isset($const['user'])) {
                foreach($const['user'] as $def => $val) {
                    $out .= ' var ' . $def . ' = "' . str_replace("\\", "\\\\", $val) . '";' . "\r\n";
                }
            }
            $out .= '</script>';

            return $out;
        }

        private function _fetchTinyUrl($url) {
            $ch      = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url-' . $url[0]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data = curl_exec($ch);
            curl_close($ch);

            return '<s href="' . $data . '" target="_blank">' . $data . '</a>';
        }

        private function _syntaxHighlight($content, $return) {
            $keywords = explode(", ", self::KEYWORDS);
            $keywords_regex = "/(".implode("|", $keywords).")(\s+|::|\(|~)/";
            $content = preg_replace_callback(self::$_const_regex, function($matches) {
                if(count($matches) >= 2) {
                    return "~~~CONST~~~".$matches[1]."~~~FINCONST~~~";
                } else {
                    return $matches[0];
                }
            }, $content);
            $content = preg_replace_callback(self::$_static_method_regex, function($matches) {
                return '~~~STATIC~~~'.substr($matches[0], 0, -2).'~~~FINSTATIC~~~::';
            }, $content);
            $content = preg_replace_callback(self::$_inline_comment_regex, function($matches) {
                return '~~~COM~~~'.$matches[0].'~~~FINCOM~~~';
            }, $content);
            $content = preg_replace_callback(self::$_doc_comment_regex, function($matches) {
                return '~~~DOC~~~'.substr($matches[0], 3).'~~~FINDOC~~~';
            }, $content);
            $content = preg_replace_callback(self::$_block_comment_regex, function($matches) {
                return '~~~COM~~~'.$matches[0].'~~~FINCOM~~~';
            }, $content);
            $content = preg_replace_callback(self::$_property_regex, function($matches) {
                return (substr($matches[0], -1) != '(') ? "~~~PROP~~~".$matches[0]."~~~FINPROP~~~" : $matches[0];
            }, $content);
            $content = preg_replace_callback(self::$_method_regex, function($matches){
                return $matches[1]."~~~METHOD~~~".$matches[2]."~~~FINMETHOD~~~".(isset($matches[3]) ? $matches[3] : '');
            }, $content);
            $content = preg_replace_callback(self::$_variable_regex, function($matches) {
                return (substr($matches[0], -2) == "->")
                    ? "~~~VAR~~~".substr($matches[0], 0, -2)."~~~FINVAR~~~"."->"
                    : "~~~VAR~~~".$matches[0]."~~~FINVAR~~~";
            }, $content);
            $content = preg_replace_callback(self::$_punctuation_regex, function($matches) {
                return (is_numeric($matches[0])) ? $matches[0] : "~~~PUN~~~".$matches[0]."~~~FINPUN~~~";
            }, $content);
            $content = preg_replace_callback(self::$_string_regex, function($matches) {
                return "~~~STR~~~".$matches[0]."~~~FINSTR~~~";
            }, $content);
            $content = preg_replace_callback(self::$_function_regex, function($matches) {
                return "function ~~~FUN~~~".$matches[1]."~~~FINFUN~~~";
            }, $content);
            $content = preg_replace_callback($keywords_regex, function($matches) {
                return "~~~KEY~~~".((substr($matches[0], -1) == '~') ? $matches[1]."~~~FINKEY~~~~" : $matches[1]."~~~FINKEY~~~");
            }, $content);

            $base = "<span class='";
            $end = "'>";
            $fin = "</span>";
            $stat = $base . "static".$end;
            $prop = $base."property".$end;
            $com = $base."com".$end;
            $doc = $base."doc".$end.'/**';
            $finprop = $finmethod = $finvar = $finpun = $finstring = $finfunction = $finconst = $finstat = $fincom = $findoc = $fin;
            $method = $base."method".$end;
            $var = $base."var".$end;
            $pun = $base."punctuation".$end;
            $string = $base."str".$end;
            $function = $base."function".$end;
            $keyword = $base."keyword".$end;
            $const = $base."const".$end;
            $finkeyword = $fin ." ";
            $replace = array(
                '~~~STATIC~~~',
                '~~~PROP~~~',
                '~~~COM~~~',
                '~~~DOC~~~',
                '~~~METHOD~~~',
                '~~~VAR~~~',
                '~~~PUN~~~',
                '~~~STR~~~',
                '~~~FUN~~~',
                '~~~KEY~~~',
                '~~~CONST~~~'
            );
            $replace_with = array(
                $stat,
                $prop,
                $com,
                $doc,
                $method,
                $var,
                $pun,
                $string,
                $function,
                $keyword,
                $const
            );
            $replace_2 = array(
                '~~~FINSTATIC~~~',
                '~~~FINPROP~~~',
                '~~~FINCOM~~~',
                '~~~FINDOC~~~',
                '~~~FINMETHOD~~~',
                '~~~FINVAR~~~',
                '~~~FINPUN~~~',
                '~~~FINSTR~~~',
                '~~~FINFUN~~~',
                '~~~FINKEY~~~',
                '~~~FINCONST~~~'
            );
            $replace_with_2 = array(
                $finstat,
                $finprop,
                $fincom,
                $findoc,
                $finmethod,
                $finvar,
                $finpun,
                $finstring,
                $finfunction,
                $finkeyword,
                $finconst
            );
            $content = str_replace($replace, $replace_with, $content);
            $content = str_replace($replace_2, $replace_with_2, $content);
            $content = "<pre class='highlight'>".str_replace("~", "", $content)."</pre>";
            $content = str_replace('$', '<span class="var">$</span>', $content);
            if(!$this->_highlight_included) {
                if(file_exists(PUBLIC_PATH.'css/highlight.css')) {
                    $ret = "<link rel='stylesheet' href='".PUBLIC_PATH."css/highlight.css' />";
                } else {
                    $ret = "<link rel='stylesheet' href='system/assets/css/highlight.css' />";
                }
                $ret .= "<script type='text/javascript' src='system/assets/js/highlight.js'></script>";
                $this->_highlight_included = true;
            }
            $ret .= "\r\n".$content;
            if($return) {
                return $ret;
            } else {
                $out = Loader::load('Output');
                $out->append_output($ret);
                return;
            }
        }

    }