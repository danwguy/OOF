<?php


class Inflection  {

    protected $_plural = array(
        '/(quiz)$/i' => '$1zes',
        '/^(ox)$/i' => '$1en',
        '/([m|l])ouse$/i' => '$1ice',
        '/(matr|vert|ind)ix|ex$/i' => '$1ices',
        '/(x|ch|ss|sh)$/i' => '$1es',
        '/([^aeiouy]|qu)y$/i' => '$1ies',
        '/(hive)$/i' => '$1s',
        '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
        '/(shea|lea|loa|thie)f$/i' => "$1ves",
        '/sis$/i'                  => "ses",
        '/([ti])um$/i'             => "$1a",
        '/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
        '/(bu)s$/i'                => "$1ses",
        '/(alias)$/i'              => "$1es",
        '/(octop)us$/i'            => "$1i",
        '/(ax|test)is$/i'          => "$1es",
        '/(us)$/i'                 => "$1es",
        '/s$/i'                    => "s",
        '/$/'                      => "s"
    );

    protected $_singular = array(
        '/(quiz)zes$/i'             => "$1",
        '/(matr)ices$/i'            => "$1ix",
        '/(vert|ind)ices$/i'        => "$1ex",
        '/^(ox)en$/i'               => "$1",
        '/(alias)es$/i'             => "$1",
        '/(octop|vir)i$/i'          => "$1us",
        '/(cris|ax|test)es$/i'      => "$1is",
        '/(shoe)s$/i'               => "$1",
        '/(o)es$/i'                 => "$1",
        '/(bus)es$/i'               => "$1",
        '/([m|l])ice$/i'            => "$1ouse",
        '/(x|ch|ss|sh)es$/i'        => "$1",
        '/(m)ovies$/i'              => "$1ovie",
        '/(s)eries$/i'              => "$1eries",
        '/([^aeiouy]|qu)ies$/i'     => "$1y",
        '/([lr])ves$/i'             => "$1f",
        '/(tive)s$/i'               => "$1",
        '/(hive)s$/i'               => "$1",
        '/(li|wi|kni)ves$/i'        => "$1fe",
        '/(shea|loa|lea|thie)ves$/i'=> "$1f",
        '/(^analy)ses$/i'           => "$1sis",
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
        '/([ti])a$/i'               => "$1um",
        '/(n)ews$/i'                => "$1ews",
        '/(h|bl)ouses$/i'           => "$1ouse",
        '/(corpse)s$/i'             => "$1",
        '/(us)es$/i'                => "$1",
        '/s$/i'                     => ""
    );

    protected $_irregular = array(
        'move'   => 'moves',
        'foot'   => 'feet',
        'goose'  => 'geese',
        'sex'    => 'sexes',
        'child'  => 'children',
        'man'    => 'men',
        'tooth'  => 'teeth',
        'person' => 'people',
        'admin' => 'admin'
    );

    protected $_uncountable = array(
        'sheep',
        'fish',
        'deer',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment'
    );

    public function __construct() {
	    $this->_init();
    }

    public function pluralize($string) {

        if(in_array(strtolower($string), $this->_uncountable)) {
            return $string;
        }
//
//        foreach($irregularWords as $pattern => $result) {
//            $pattern = '/'.$pattern.'$/i';
//            if(preg_match($pattern, $string)) {
//                return preg_replace($pattern, $result, $string);
//            }
//        }

        foreach($this->_irregular as $pattern => $result) {
            $pattern = '/'.$pattern.'$/i';
            if(preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        foreach($this->_plural as $pattern => $result) {
            if(preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    public function singularize($string) {

        if(in_array(strtolower($string), $this->_uncountable)) {
            return $string;
        }

//        foreach ( $irregularWords as $result => $pattern )
//        {
//            $pattern = '/' . $pattern . '$/i';
//
//            if ( preg_match( $pattern, $string ) )
//                return preg_replace( $pattern, $result, $string);
//        }

        // check for irregular plural forms
        foreach ( $this->_irregular as $result => $pattern )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $string ) )
                return preg_replace( $pattern, $result, $string);
        }

        // check for matches using regular expressions
        foreach ($this->_singular as $pattern => $result )
        {
            if ( preg_match( $pattern, $string ) )
                return preg_replace( $pattern, $result, $string );
        }

        return $string;
    }

    public function pluralize_if($count, $string) {
        if($count == 1) {
            return "1 $string";
        } else {
            return $count . " " . $this->pluralize($string);
        }
    }

	protected function _init() {
		if(defined('ENVIRONMENT') && is_file(APP_PATH.'config/'.ENVIRONMENT.'/inflection.php')) {
			include(APP_PATH.'config/'.ENVIRONMENT.'/inflection.php');
		} else if(is_file(APP_PATH.'config/inflection.php')) {
			include(APP_PATH.'config/inflection.php');
		}
		if(isset($irregularWords) && is_array($irregularWords)) {
			$this->_irregular = array_merge($this->_irregular, $irregularWords);
		}
	}

}