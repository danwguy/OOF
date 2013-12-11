<?php

/**
 * Class User
 * @method static User get() Returns User
 * @method static User retrieve_object() Returns User
 * @method static User retrieve_objects() Returns User
 */

class User extends Singleton {

//    use Singleton;

    public $id;
    public $email;
    public $first_name;
    public $last_name;
    public $title;
    public $position;
//    public static $instance;
    public $pass_hash;

    const NAME = 'user';
    const INVALID_EMAIL = 'You entered an invalid email';
    const INVALID_PASS = 'The password you entered does not match out records';
    const NON_ADMIN_USER = 'You must be an admin to access this area';

    public static $column_list = array('id', 'email', 'first_name', 'last_name', 'title', 'pass_hash');

    public function after_construction() {
        $this->position = new Job($this->title);
    }

    public static function login(array $data) {
        $user = User::retrieve_object(" WHERE email = '".$data['email']."'");
        if(!$user) {
            return array('success' => false, 'message' => self::INVALID_EMAIL);
        }
        if($user->pass_hash == sha1($data['password'])) {
            self::set($user);
            self::updateUser(array('ip_address' => $_SERVER['REMOTE_ADDR'], 'session_id' => session_id()));
            return array('success' => true, 'message' => 'You have been successfully logged in');
        } else {
            return array('success' => false, 'message' => self::INVALID_PASS);
        }
    }

    public static function updateUser(array $data) {
        $sql = "UPDATE ".self::get_table()."
                SET ";
        foreach($data as $key => $value) {
            $sql .= $key . " = '".$value."', ";
        }
        $sql = substr($sql, 0, -2);
        $user = User::get();
        $sql .= " WHERE id = '".$user->id."'";
        return DB::get()->execute($sql);
    }

    public static function validate($type, $user = null) {
        if(!$user) {
            $user = User::get();
        }
        return $user->position->position == $type;
    }

    public function __toString() {
        return 'user';
    }
    public static function get_table() {
        return 'user';
    }

    public static function get_primary_key() {
        return 'id';
    }

}