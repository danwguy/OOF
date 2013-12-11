<?php

    /**
     * handles the conversion between russian and cyrillic to latin
     * can be easily upgraded to handle any language by adding the language
     * to the database
     *
     * @author Robert Mason
     */

    class Transliteration extends Component
    {

        public $asCharMap;
        public static $sNumberRegex = '/^\$?(\d{1,3}[ ,.]?)*(\[.,]\d{0,2})?$/';
        public $sLang = 'russian'; //default language when no option passed
        public static $asColumnList = array(
            'id',
            'russian_char',
            'belarusian_char',
            'kazahk_char',
            'ukrain_char',
            'latin_char'); //list of columns in db
        public static $oInstance; // static instance for singleton pattern
        public static $asCharMapStatic = array();
        public static $aLanguageMap = array(
            'RU' => 'russian',
            'BY' => 'belarusian',
            'UA' => 'ukrain',
            'KK' => 'kazahk',
            'LA' => 'latin'
        );


        /**
         * @return TranslitModel
         */
        public static function get()
        {
            if (!isset(self::$oInstance))
            {
                self::$oInstance = new self(true);
            }
            return self::$oInstance;
        }

        public static function needsTranslation($sValue)
        {
            return !preg_match(self::$sNumberRegex, $sValue, $matches);
        }

        /**
         * @param null|string $sChar
         * @param null|string $sLang
         * @internal param string $char if a string is passed it will return the
         * latin character, if true, it will build map array
         * @internal param string $lang string representation of language to be
         * translated, i.e. 'russian', 'cyrillic'
         */
        public function __construct($sChar = null, $sLang = null)
        {
            if ($sChar)
            {
                if (is_string($sChar))
                {
                    $this->translate($sChar, $sLang);
                }
                else
                {
                    $this->buildCharMap();
                }
            }
        }
        public static function getLanguageFromCode($sAbbr)
        {
            return (isset(self::$aLanguageMap[$sAbbr])) ? self::$aLanguageMap[$sAbbr] : false;
        }


        /**
         * @return array returns the mapping array
         */
        public function buildCharMap()
        {
            $oConnection = Yii::app()->db;
            $aResults = $oConnection->createCommand()
                ->select(implode(", ", self::$asColumnList))
                ->from(self::get_table())
                ->queryAll();
            return $this->buildMapArray($aResults);
        }

        /**
         * @param array $aResults
         * @internal param array $results results from the database,
         * or an array of your choosing to create the character map from
         * @return array the mapped array
         */
        public function buildMapArray($aResults = array())
        {
            if (!$aResults)
            {
                return $this->buildCharMap();
            }
            else
            {
                foreach ($aResults as $aResult)
                {
                    $this->asCharMap['russian'][] = $aResult['russian_char'];
                    $this->asCharMap['belorusian'] = $aResult['belarusian_char'];
                    $this->asCharMap['kazahk'] = $aResult['kazahk_char'];
                    $this->asCharMap['ukrain'] = $aResult['ukrain_char'];
                    $this->asCharMap['latin'][] = $aResult['latin_char'];
                }
            }
            return self::$asCharMapStatic = $this->asCharMap;
        }

        public function translate($sChar, $sLang = null)
        {
            if ($sChar == ' ')
            {
                return ' ';
            }
            $sLang = ($sLang) ? : $this->sLang;
            $iIndex = $this->findChar($sChar, $sLang);
            return ($iIndex !== false)
                ? $this->asCharMap['latin'][$iIndex]
                : false;
        }

        /**
         * @param string $sChar
         * @param string $sLang
         * @internal param string $char
         * @internal param string $lang
         * @return bool|int
         * Finds the index of the character in the array map and returns
         * makes getting the latin equivalent easy
         */
        public function findChar($sChar, $sLang)
        {
            $xFound = false;
            if(!$this->asCharMap) {
                $this->buildCharMap();
            }
            for ($i = 0, $l = count($this->asCharMap[$sLang]);$i < $l;$i++)
            {
                if ($this->asCharMap[$sLang][$i] == $sChar)
                {
                    $xFound = $i;
                }
            }
            return $xFound;
        }

        /**
         * @param string $sString
         * @param null $sLang
         * @internal param string $string
         * @internal param string $lang
         * @return string latin equivalent
         */
        public function translateString($sString, $sLang = null)
        {
            $sLatinString = '';
            $asStringArray = $this->strToArray($sString);
            foreach ($asStringArray as $sChar)
            {
                if ($sChar == ' ')
                {
                    $sLatinString .= ' ';
                }
                else if (is_int($sString) || is_float($sString))
                {
                    $sLatinString .= $sString;
                }
                else
                {
                    $sLatinString .= $this->translate($sChar, $sLang);
                }
            }
            return $sLatinString;
        }

        /**
         * @param $sString
         * @internal param string $string
         * @return array
         * Breaks apart strings with multibyte characters into an array
         */
        public function strToArray($sString)
        {
            return preg_split('/(?<!^)(?!$)/u', $sString);
        }

        public function setLanguage($sLanguage)
        {
            $this->sLang = $sLanguage;
        }

        public function getLanguage()
        {
            return $this->sLang;
        }

        public function init()
        {
            $cache = Yii::app()->cache->get('transliterate');
            if(!$cache)
            {
                $this->buildCharMap();
                Yii::app()->cache->set('transliterate', $this->asCharMap, 0);
            }
        }

        public function writeTranslation($sField, $sValue, $iId) {
            $sql = "UPDATE `user_address`
                    SET `global_".$sField."` = '".$sValue."'
                    WHERE `user_id` = '".$iId."'";
            return Yii::app()->db->createCommand($sql)->execute();
        }


        /**
         * @return string name of the table to get the map from
         */
        protected static function get_table()
        {
            return 'transliterate';
        }

    }
