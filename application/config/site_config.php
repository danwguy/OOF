<?php

    $config = array(
        'return'                   => 'false',
        'class_names'              => array(
            'underscore_to_camel_case' => true
        ),
        'class_actions'            => array(
            'underscore_to_camel_case' => true
        ),
        'email'                    => array(
            'from'     => 'robert.mason.lti@gmail.com',
            'reply_to' => 'robert.mason.li@gmail.com',
            'html'     => true
        ),
        'database'                 => array(
            'active' => 'local',
            'local'  => array(
                'host'          => 'localhost',
                'user'          => 'root',
                'password'      => '',
                'db_name'       => 'framework',
                'db_type'       => 'mysql', //currently only mysql, mssql, or mysql_pdo are accepted
                'persistent'    => false,
                'prefix'        => null,
                'debug'         => true,
                'cache'         => false,
                'cache_dir'     => 'tmp/database/',
                'charset'       => 'utf8',
                'collate'       => 'utf8_general_ci',
                'auto_connect'  => true,
                'active_record' => true,
                'ARDO'          => true
            )
        ),
        'compress_output'          => false,
        'cache_path'               => '',
        'date_formatting'          => array(
            'log_file'      => 'Y-m-d H:i:s',
            'calendar_lang' => 'calendar',
            'screen_output' => 'l \the jS \of F Y \at h:i:s a', //experimental this is not in use yet
            'db_storage'    => 'Y-m-d H:i:s'
        ),
        'debug'                    => array(
            'production'  => array(
                'show_debug'      => false,
                'start_minimized' => false,
                'show_file'       => false
            ),
            'development' => array(
                'show_debug'      => true,
                'start_minimized' => true,
                'show_file'       => true
            )
        ),
        'allowed_url_chars'        => 'a-z 0-9~%.:_\-',
        'xss_filter'               => false,
        'underscore_to_camel_case' => true,
        'paginate_limit'           => 5,
        'site_name'                => 'Robert Mason Home',
        'cache_path'               => 'tmp/cache',
        'uri_protocol'             => 'AUTO',
        'index_page'               => 'index.php',
        'language'                 => 'english',
        'class_prefix'             => '',
        'allow_get'                => true,
        'enable_query_strings'     => false,
        'log_path'                 => '',
        'cache_path'               => '',
        'encryption_key'           => '',
        'session'                  => array(
            'cookie_name'     => 'oof_session',
            'expire'          => 7200,
            'expire_on_close' => false,
            'use_database'    => false,
            'table_name'      => 'oof_sessions',
            'encrypt_cookie'  => false,
            'match_ip'        => false,
            'match_useragent' => true,
            'update_time'     => 300
        ),
        'cookie'                   => array(
            'prefix' => '',
            'domain' => '',
            'path'   => '/',
            'secure' => false
        ),
        'xss_filter'               => false,
        'csrf'                     => array(
            'protection'  => false,
            'token_name'  => 'csrf_test',
            'cookie_name' => 'csrf_cookie',
            'expire'      => 7200
        ),
        'compress_output'          => false, //Not used right now
        'rewrite_short_tags'       => false,
        'use_template'             => true,
        //E-Commerce stuff in this section
        'shopping_cart'            => array(
            'save_to_database' => true,
            'save_to_session'  => false,
            'charge_tax'       => true,
            'tax_amount'       => 8.5, //==8.5% or .085 (roughly 8 cents on the dollar
            'currency'         => 'USD',
            'convert_to_local' => true
        ),
        'logging'                  => array(
            'enable'           => true, //turn on logging = true, turn off logging = false
            'path'             => 'tmp/logs', //relative to root
            'log_sql'          => false, //should we log all sql queries that are tun?
            'log_all_requests' => false, //should we log all page/class requests?
            'log_to_database'  => false,
            //if logging is enabled should we log to db? if set to true must define table too...
            //'db_log_table' => 'logs'
            'log_to_files'     => true, //if logging is enabled are we logging to files?
            /*
             * what should we log?
             * 0 = No error logging;
             *          ^Even if logging is enabled
             * 1 = Error messages;
             *          ^including php errors
             * 2 = Debug logging;
             *          ^pretty much everything going on will be logged to debug.log. Careful this can get real big
             *           real fast. If you enable this level you should check and clear out your debug.log file at
             *           least once a day
             * 3 = Information messages;
             *          ^Information about what is going on, like 2 except no errors, or debug messages, just info
             * 4 = All messages;
             *          ^Log ALL the things! this will log absolutely everything. You thought debug filled up fast, go for it
             *           enable this one, those files are going to be huge, but you'll get a lot of info. You should go
             *           through and clean out the log files a few times a day
             *
             * If you are running this live I would recommend either 0 or 1. For a dev site I would still recommend keeping
             * this relatively low and enable the debug output instead, but it's your call.
             */
            'logging_level'    => 1,
            'log_files'        => array(
                'debug.log',
                'error.log',
                'sql.log',
                'email.log'
            )
        )
    );
