<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 7/14/13
 * Time: 11:51 PM
 * To change this template use File | Settings | File Templates.
 */

class TipsTrick extends TableObject {

    public $id;
    public $language;
    public $title;
    public $content;
    public $category;
    public $created_on;
    public $modified_on;
    public $mode;
    public $status;

    public static $column_list = array('id', 'language', 'title', 'content', 'mode', 'status', 'created_on', 'modified_on');

    public function before_creation() {
        if(!isset($this->mode)) {
            $this->mode = 'live';
        }
        if(!isset($this->status)) {
            $this->status = 'active';
        }
    }


    public function after_construction() {
        $this->category = new Languages($this->language);
    }

    public static function getTutorials() {
        $objs = array();
        $sql = "SELECT ".implode(", ", self::$column_list). "
                FROM ".self::get_table();
        $results = DB::get()->fetch_all($sql);
        if($results) {
            foreach($results as $result) {
                $objs[] = new static($result);
            }
        }
        return $objs;
    }

    public static function getLanguages() {
        return Languages::retrieve_objects(" WHERE mode = ? AND status = ?", array('live', 'active'));
    }

    public static function get_table() {
        return 'tips_tricks';
    }

    public static function get_primary_key() {
        return 'id';
    }

}