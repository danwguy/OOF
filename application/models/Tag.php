<?php


class Tag extends Model {

    public $id;
    public $name;

    public static $column_list = array('id', 'name');
    public static $editable_column_list = array('name');

    public static function get_table() {
        return 'tags';
    }

}