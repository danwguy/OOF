<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 9/2/13
 * Time: 11:52 AM
 * To change this template use File | Settings | File Templates.
 */

class Category extends Model {

    public $hasMany = array('Product' => 'Product');
    public $hasOne = array('Parent' => 'Category');

    public $id;
    public $name;
    public $parent_id;

    public static $column_list = array('id', 'name', 'parent_id');
    public static $editable_column_list = array('name', 'parent_id');

    public static function get_table() {
        return 'categories';
    }

}