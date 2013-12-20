<?php


class LogExternal extends LoggingObject {

    public $id;
    public $target_url;
    public $data;
    public $result;
    public $error_number;
    public $error_message;
    public $opts;
    public $external_info;
    public $created_on;
    public $ended_on;
    public $request_duration;

    protected static $column_list = array(
        'id',
        'target_url',
        'data',
        'result',
        'error_number',
        'error_message',
        'opts',
        'external_info',
        'created_on',
        'ended_on',
        'request_duration'
    );

    public static function get_table() {
        return 'log_external_requests';
    }
} 