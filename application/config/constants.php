<?php

	/*
	 * file and directory permissions
	 * constants
	 */

define('PERMISSION_FILE_READ', 0644);
define('PERMISSION_FILE_WRITE', 0666);
define('PERMISSION_DIR_READ', 0755);
define('PERMISSION_DIR_WRITE', 0777);


	/*
	 * These are for reading and writing files
	 * all are appended with the b flag so that
	 * none of the files are will be translated
	 * i.e. translating line endings from \n or \r
	 * to \r\n
	 */
define('FILE_READ', 'rb');
define('FILE_READ_WRITE', 'r+b');
define('FILE_WRITE_CREATE_TRUNCATE', 'wb');
define('FILE_READ_WRITE_CREATE_TRUNCATE', 'w+b');
define('FILE_WRITE_END_CREATE', 'ab');
define('FILE_READ_WRITE_END_CREATE', 'a+b');
define('FILE_CREATE_WRITE', 'xb');
define('FILE_CREATE_READ_WRITE', 'x+b');
define('FILE_WRITE_CREATE_PREPEND', 'cb');
define('FILE_READ_WRITE_CREATE_PREPEND', 'c+b');

define('LOGGING_FILE_PATH', TMP_PATH.'logs/');