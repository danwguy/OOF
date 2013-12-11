<?php


class Captcha extends Singleton {

    public $word;
    public $time;
    public $image;
    public $img_path;
    public $img_url;
    public $img_width;
    public $img_height;
    public $font_path;
    public $expiration;



    protected static $defaults = array(
        'word' => '',
        'img_path' => 'tmp\captcha',
        'img_url' => 'tmp/captcha',
        'img_width' => '150',
        'img_height' => '30',
        'font_path' => '',
        'expiration' => 7200
    );

    protected static $pool = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";



    public function construct() {

    }

    public function create($data = null, $img_path = null, $img_url = null, $font_path = null) {

        if(! @is_dir($img_path)) {
            $this->img_path = ROOT . DS . 'tmp' . DS . 'captcha' . DS;
        }

        if(!extension_loaded('gd')) {
            return false;
        }

        foreach(self::$defaults as $piece => $value) {
            if(!$data && !is_array($data)) {
                if(is_null($this->$piece)) {
                    $this->$piece = $value;
                }
            } else {
                $this->$piece = (!isset($data[$piece])) ? $value : $data[$piece];
            }
        }
        $path = explode(DS, $this->img_path);
        $base = explode(DS, ROOT);
        if(isset($path[0]) && isset($base[0])) {
            if(!$path[0] == $base[0]) {
                $this->img_path = ROOT . DS . $this->img_path;
            }
        } else {
            $this->img_path = ROOT . DS . $this->img_path;
        }

        $urls = explode('/', $this->img_url);
        $base_urls = explode('/', BASE_PATH);
        if(isset($urls[0]) && isset($base_urls[0])) {
            if($urls[0] != $base_urls[0]) {
                $this->img_url = BASE_PATH .'/' . $this->img_url;
            }
        } else {
            $this->img_url = BASE_PATH .'/' . $this->img_url;
        }

        if(substr($this->img_path, strlen($this->img_path) - 1, strlen($this->img_path)) != DS) {
            $this->img_path .= DS;
        }
        if(substr($this->img_url, strlen($this->img_url) - 1, strlen($this->img_url)) != '/' || substr($this->img_url, strlen($this->img_url) - 1, strlen($this->img_url)) != DS) {
            $this->img_url .= '/';
        }
        list($usec, $sec) = explode(" ", microtime());
        $now = (float)$usec + (float)$sec;

        $base_dir = ROOT . DS . self::$defaults['img_path'] . DS;

        $current_dir = opendir($base_dir);

        while($filename = @readdir($current_dir)) {
            if($filename != "." && $filename != ".." && $filename != "index.html") {
                $name = str_replace(".jpg", "", $filename);
                if(($name + $this->expiration) < $now) {
                    @unlink($base_dir.$filename);
                }
            }
        }

        @closedir($current_dir);

        if($this->word == '') {
            for($i = 0; $i < 8; $i++) {
                $this->word .= substr(self::$pool, mt_rand(0, strlen(self::$pool) - 1), 1);
            }
        }

        $length = strlen($this->word);
        $angle = ($length >= 6) ? mt_rand(-($length - 6), ($length - 6)) : 0;
        $x_axis = mt_rand(6, (360 / $length) - 16);
        $y_axis = ($angle >= 0) ? mt_rand($this->img_height, $this->img_width) : mt_rand(6, $this->img_height);

        if(function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($this->img_width, $this->img_height);
        } else {
            $im = imagecreate($this->img_width, $this->img_height);
        }

        $bg_color = imagecolorallocate($im, 255, 255, 255);
        $border_color = imagecolorallocate($im, 153, 102, 102);
        $text_color = imagecolorallocate($im, 204, 153, 153);
        $grid_color = imagecolorallocate($im, 255, 182, 182);

        ImageFilledRectangle($im, 0, 0, $this->img_width, $this->img_height, $bg_color);

        $theta = 1;
        $theta_c = 7;
        $radius = 16;
        $circles = 20;
        $points = 32;

        for($i = 0; $i < ($circles * $points) - 1; $i++) {
            $theta = $theta + $theta_c;
            $rad = $radius * ($i / $points);
            $x = ($rad * cos($theta)) + $x_axis;
            $y = ($rad * sin($theta)) + $y_axis;
            $theta = $theta + $theta_c;
            $rad1 = $radius * (($i + 1) / $points);
            $x1 = ($rad1 * cos($theta)) + $x_axis;
            $y1 = ($rad1 * sin($theta)) + $y_axis;
            imageline($im, $x, $y, $x1, $y1, $grid_color);
            $theta = $theta - $theta_c;
        }

        $use_font = ($this->font_path != '' && file_exists($this->font_path) && function_exists('imagettftext')) ? true : false;

        if(!$use_font) {
            $font_size = 5;
            $x = mt_rand(0, $this->img_width / ($length / 3));
            $y = 0;
        } else {
            $font_size = 16;
            $x = mt_rand(0, $this->img_width / ($length / 1.5));
            $y = $font_size + 2;
        }

        for($i = 0; $i < strlen($this->word); $i++) {
            if(!$use_font) {
                $y = mt_rand(0, $this->img_height / 2);
                imagestring($im, $font_size, $x, $y, substr($this->word, $i, 1), $text_color);
                $x += ($font_size * 2);
            } else {
                $y = mt_rand($this->img_height / 2, $this->img_height - 3);
                imagettftext($im, $font_size, $angle, $x, $y, $text_color, $this->font_path, substr($this->word, $i, 1));
                $x += $font_size;
            }
        }

        imagerectangle($im, 0, 0, $this->img_width - 1, $this->img_height - 1, $border_color);

        $img_name = $now.'.jpg';
        ImageJPEG($im, $this->img_path.$img_name);

        $this->image = '<img src="'.$this->img_url.$img_name.'" width="'.$this->img_width.'px" height="'.$this->img_height.'px" style="border: 0;" />';

        ImageDestroy($im);


    }

}