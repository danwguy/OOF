<?php

class Page extends TableObject {

    public $id;
    public $content;
    public $linked_menu;
    public $mode;
    public $status;

    public static $column_list = array('id', 'content', 'linked_menu', 'mode', 'status');

    public static function get_table() {
        return 'pages';
    }

    public static function get_primary_key() {
        return 'id';
    }

}