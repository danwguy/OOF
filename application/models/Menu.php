<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 7/29/13
 * Time: 11:13 PM
 * To change this template use File | Settings | File Templates.
 */

class Menu extends TableObject {

    public $id;
    public $title;
    public $mode;
    public $status;
    public $content;
    public $link;

    public static $column_list = array('id', 'title', 'mode', 'status', 'link');


    public static function get_table() {
        return 'menu';
    }

    public static function get_primary_id() {
        return 'id';
    }

}