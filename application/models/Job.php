<?php

class Job extends TableObject {

    public $id;
    public $position;
    public $description;

    public static $column_list = array('id', 'position', 'description');

    public static function get_table() {
        return 'positions';
    }

    public static function get_primary_key() {
        return 'id';
    }

}