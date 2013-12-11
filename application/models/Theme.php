<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 8/14/13
 * Time: 4:44 PM
 * To change this template use File | Settings | File Templates.
 */

class Theme extends TableObject {

    public $id;
    public $description;
    public $css_class;

    public static $column_list = array('id', 'title', 'description', 'css_class');

    public static function get_table() {
        return 'themes';
    }

    public static function get_primary_key() {
        return 'id';
    }

}