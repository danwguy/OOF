<?php


class BreadCrumbs extends Singleton {

    public $home;
    public $trail;

    const HOME = 'Home';

    public function construct() {
        if(!isset($this->home)) {
            $this->home = 'Home';
        }
    }

    public function add($link) {
        $this->trail .= '->'.$link;
    }

    public function remove($link) {
        $parts = explode("->", $this->trail);
        if($parts) {
            $i = 0;
            foreach(parts as $word) {
                if($word == $link) {
                    unset($parts[$i]);
                }
                $i++;
            }
            $this->trail = implode("->", $parts);
        }
        return $this->trail;
    }

    public function build(array $data = array()) {
        $this->trail = self::HOME;
        if($data && !empty($data)) {
            foreach($data as $link) {
                $this->trail .= "->".$link;
            }
        }
        return $this->trail;
    }

}