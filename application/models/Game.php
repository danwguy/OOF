<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 9/20/13
 * Time: 4:36 PM
 * To change this template use File | Settings | File Templates.
 */

class Game extends TableObject {

    public $id;
    public $name;
    public $active;
    public $coming_soon;
    public $description;
    public $image;
    public $play_page;

    public static $column_list = array('id', 'name', 'active', 'coming_soon', 'description', 'image', 'play_page');
    public static $editable_column_list = array('name', 'active', 'coming_soon', 'description', 'image', 'play_page');

    public static function get_table() {
        return 'games';
    }

    public static function get_primary_key() {
        return 'id';
    }

}