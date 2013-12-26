<?php


class Debug {

    const DIV_START = "<div class='debug'>";
    const DIV_END = "</div>";

    public static $content = array();
    public static $count = 0;

    public static function setContent($debug, $key = null) {
         if($key) {
             self::$content[$key] = $debug;
         } else {
             self::$content[] = $debug;
         }
        self::$count += 1;
        return true;
    }

    public static function removeContent($key) {
        if(isset(self::$content[$key])) {
            unset(self::$content[$key]);
        }
        return true;
    }

    public static function hasContent() {
        return (count(self::$content) > 0);
    }

    public static function getDebugContent($return_html = true) {
        $content = array();
        if(count(self::$content) > 0) {
            $out = self::DIV_START;
            foreach(self::$content as $k => $data) {
                $content[$k] = $data;
                $out .= "\r\n"."      ";
                $out .= (is_array($data)) ? implode("\r\n"."      ", $data) : $data;
            }
            $out .= "\r\n" . self::DIV_END;
        } else {
            $out = '';
        }
        return ($return_html) ? $out : $content;
    }

    public static function getJscript($minimize = true) {
        $str = "<script type='text/javascript' src='system/assets/js/errors.js'></script>"."\r\n".
                "<script type='text/javascript'>";
        $str .= ($minimize)
            ? "$(document).ready(function() { jsDebug.startMinimized = true; jsDebug.init(); });"
            : "$(document).ready(function() { jsDebug.startMinimized = false; jsDebug.init(); });";
        $str .= "</script>";
        return $str;
    }

} 