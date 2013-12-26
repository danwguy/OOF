<?php


class Security  {

    private $config;

    protected $_xss_hash = '';
    protected $_csrf_hash = '';
    protected $_csrf_expire = '';
    protected $_csrf_token_name = 'oof_csrf_token';
    protected $_csrf_cookie_name = 'oof_csrf_token';
    protected $_blocked_str = array(
        'document.cookie' => '[removed]',
        'document.write' => '[removed]',
        '.parentNode' => '[removed]',
        '.innerHTML' => '[removed]',
        'window.location' => '[removed]',
        '-moz-binding' => '[removed]',
        '<!--' => '&lt;!--',
        '-->' => '--&gt;',
        '<![CDATA[' => '&lt;![CDATA[',
        '<comment>' => '&lt;comment&gt;'
    );
    protected $_blocked_regex = array(
        'javascript\s*:',
        'expression\s*(\(|&\#40;)',
        'vbscript\s*:',
        'Redirect\s+302',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    );

    public function __construct() {
        if(Config::getItem('csrf', 'protection')) {
            foreach(array('csrf_expire', 'csrf_token_name', 'csrf_cookie_name') as $key) {
                if(false !== ($val = Config::getItem($key))) {
                    $this->{'_'.$key} = $val;
                }
            }
            if(Config::getItem('cookie', 'prefix')) {
                $this->_csrf_cookie_name = Config::getItem('cookie', 'prefix').$this->_csrf_cookie_name;
            }

            $this->_csrf_set_hash();
        }
    }

    protected function _csrf_set_hash() {
        if($this->_csrf_hash == '') {
            if(
                isset($_COOKIE[$this->_csrf_cookie_name]) &&
               preg_match("#[0-9a-f]{32}$#iS", $_COOKIE[$this->_csrf_cookie_name])
            ) {
                return $this->_csrf_hash = $_COOKIE[$this->_csrf_cookie_name];

            }
            return $this->_csrf_hash = md5(uniqid(rand(), true));
        }
        return $this->_csrf_hash;
    }

    public function csrf_show_error() {
        OOF::show_error("The action you are requesting is forbidden");
    }

    public function get_csrf_hash() {
        return $this->_csrf_hash;
    }

    public function clean($str, $image = false) {
        if(is_array($str)) {
            while(list($key) = each($str)) {
                $str[$key] = $this->clean($str[$key]);
            }
            return $str;
        }

        $str = LanguageUtil::remove_invisible_chars($str);

        $str = $this->_validate_entities($str);

        $str = rawurldecode($str);

        $str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, '_convert_attribute'), $str);
        $str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array($this, '_decode_entity'), $str);

        $str = LanguageUtil::remove_invisible_chars($str);

        if(strpos($str, "\t")) {
            $str = str_replace("\t", ' ', $str);
        }

        $convert = $str;

        $str = $this->_run_bocked($str);
        if($image) {
            $str = preg_replace('/<\?(php/i', "&lt;?\\1", $str);
        } else {
            $str = str_replace(array("<?", "?", ">"), array("lt;?", "?&gt;"), $str);
        }

        $find = array(
            'expression', 'javascript', 'vbscript', 'base64', 'script', 'applet',
            'document', 'alert', 'write', 'window', 'cookie'
        );

        foreach($find as $word) {
            $temp = '';
            for($i = 0, $len = strlen($word); $i < $len; $i++) {
                $temp .= substr($word, $i, 1)."\s*";
            }
            $str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', aray($this, '_compact_exploded_words'), $str);
        }

        do  {
            $original = $str;
            if(preg_match("/<a/i", $str)) {
                $str = preg_replace_callback("#<a\s+([^>]*?(>|$)#si", array($this, '_js_link_remove'), $str);
            }
            if(preg_match("/<img/i", $str)) {
                $str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", array($this, '_js_img_remove'),$str);
            }
            if(preg_match("/script/i", $str) || preg_match("/xss/i", '[removed]', $str)) {
                $str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
            }
        } while($original != $str);

        unset($original);

        $str = $this->_remove_excess_attributes($str, $image);

        $bad = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
        $str = preg_replace_callback('#<(/*\s*)('.$bad.')([^><]*)([><]*)#is', array($this, '_sanitize_bad_html'), $str);

        $str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&$41;", $str);

        if($image) {
            return ($str == $convert) ? true : false;
        }

        return $str;

    }

    public function xss_hash() {
        if($this->_xss_hash == '') {
            $this->_xss_hash = md5(time() + mt_rand(0, 1999999999));
        }

        return $this->_xss_hash;
    }

    public function sanitize_filename($str, $relative = false) {

        $bad = array(
            "../",
            "<!--",
            "-->",
            "<",
            ">",
            "'",
            '"',
            '&',
            '$',
            '#',
            '{',
            '}',
            '[',
            ']',
            '=',
            ';',
            '?',
            "%20",
            "%22",
            "%3c",		// <
            "%253c",	// <
            "%3e",		// >
            "%0e",		// >
            "%28",		// (
            "%29",		// )
            "%2528",	// (
            "%26",		// &
            "%24",		// $
            "%3f",		// ?
            "%3b",		// ;
            "%3d"		// =
        );

        if(!$relative) {
            $bad[] = '/';
            $bad[] = './';
        }

        $str = LanguageUtil::remove_invisible_chars($str, false);

        return stripslashes(str_replace($bad, '', $str));
    }

    public function entity_decode($str, $chars = 'UTF-8') {
        if(!stristr($str, '&')) {
            return $str;
        }

        $str = html_entity_decode($str, ENT_COMPAT, $chars);
        $str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);

        return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
    }

    protected function _compact_exploded_words($array) {
        return preg_replace('/\s+/s', '', $array[1]).$array[2];
    }

    protected function _remove_excess_attributes($str, $image) {
        $bad_attr = array('on\w*', 'style', 'xmlns', 'formaction');

        if($image) {
            unset($bad_attr[array_search('xmlns', $bad_attr)]);
        }

        do {
            $count = 0;
            $attributes = array();
            preg_match_all('/('.implode('|', $bad_attr).')\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is', $str, $matches, PREG_SET_ORDER);
            foreach($matches as $attr) {
                $attributes[] = preg_quote($attr[0], '/');
            }
            preg_match_all('/('.implode('|', $bad_attr).')\s*=\s*([^\s>]*)/is', $str, $matches, PREG_SET_ORDER);
            foreach ($matches as $attr)
            {
                $attributes[] = preg_quote($attr[0], '/');
            }

            // replace illegal attribute strings that are inside an html tag
            if (count($attributes) > 0)
            {
                $str = preg_replace('/(<?)(\/?[^><]+?)([^A-Za-z<>\-])(.*?)('.implode('|', $attributes).')(.*?)([\s><]?)([><]*)/i', '$1$2 $4$6$7$8', $str, -1, $count);
            }

        } while($count);

        return $str;
    }


}