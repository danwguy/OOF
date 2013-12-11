<?php


class Item extends Model {

    public $id;
    public $item_name;

    public static $column_list = array('id', 'item_name');

    public static function get_table() {
        return 'items';
    }

}