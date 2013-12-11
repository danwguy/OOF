<?php


class Product extends Model {

    public $hasOne = array('Category' => 'Category');
    public $hasManyAndBelongsToMany = array('Tag' => 'Tag');

    public $id;
    public $category_id;
    public $name;
    public $price;

    public static $column_list;
    public static $editable_column_list = array('category_id', 'name', 'price');


    public static function get_table() {
        return 'products';
    }

}