<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 7/21/13
 * Time: 11:00 PM
 * To change this template use File | Settings | File Templates.
 */

class Languages extends TableObject {

    public $id;
    public $name;
    public $relates_to;

    public static $column_list = array('id', 'name', 'relates_to');

    public static function get_table() {
        return 'languages';
    }

    public static function get_primary_key() {
        return 'id';
    }

}