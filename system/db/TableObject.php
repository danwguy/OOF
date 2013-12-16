<?php


    class TableObject {

        protected static $column_list = array();
        protected static $editable_column_list = array();
        private static $status_list = array('active', 'inactive', 'ended', 'archived');
        private static $mode_list = array('uninitialized', 'live', 'deleted', 'suggested_for_deletion', 'unknown');
        public $creation_id;
        public $duplicate_key;
	    public $config;
	    public $inflect;

        protected $_dbHandle;
        protected $_result;
        protected $_query;
        protected $_table;

        protected $_describe = array();

        protected $_orderBy;
        protected $_order;
        protected $_extraConditions;
        protected $_hO;
        protected $_hM;
        protected $_hMABTM;
        protected $_page;
        protected $_limit;
	    protected $_cache;
        protected $_ardo;

        /*
        * HOOKS
        */

        protected function before_construction() {
        } //run at start of __construct() (but after the db object has been added)
        protected function after_construction() {
        } //run at the end of __construct()
        protected function before_creation() {
        } //run at the start of create()
        protected function after_creation() {
        } //run at the end of create()
        protected function before_retrieval() {
        } //run at the start of retrieve()
        protected function after_retrieval() {
        } //run at the end of retrieve()
        protected function before_saving() {
        } //run at the start of save()
        protected function after_saving() {
        } //run at the end of save()
        protected function before_deletion() {
        } //run at the start of delete()
        protected function after_deletion() {
        } //run at the end of delete()

        /*
        * Primary operation functions
        */

        //Takes as an argument either an id number to retrieve from the db, an array of field values, or another object to clone.
        public function __construct() {
            $this->_initialize_class_objects();
            $class = get_class($this);
            $this->before_construction();
            if(func_num_args() == 1) {
                $arg = func_get_arg(0);
                if(is_scalar($arg)) {
                    $this->retrieve(intval($arg));
                } else if(is_array($arg)) {
                    $this->set_class_variables($arg);
                } else if(get_class($this) == get_class($arg)) {
                    $this->set_class_variables($arg);
                }
            } else if(func_num_args() > 1) {
                throw new Exception ("Unable to create $class.  The constructor for a $class requires either an integer primary key, array of properties and values, or another $class as a singular argument.".(isset($arg) ? "The argument given was ".var_export($arg, true) : "No argument was given"));
            }
            $this->after_construction();
        }

        //Sets the fields of $this to match those in the associative $data array
        protected function set_class_variables($data) { //should only be called from the constructor (or via retrieve()), not by outside classes.
            if ($data) {
                foreach ($data as $key => $value) {
                    $this->$key = $value;
                }
            }
        }

        protected function _initialize_class_objects() {
            $this->config = Loader::load('Config', 'core');
            $this->inflect = Loader::load('Inflection', 'core');
            $this->_cache = new Cache(array('adapter' => 'FileCache'));
            $d_to_the_b_c = $this->config->item('database');
            $active = $d_to_the_b_c['active'];
            $db = $d_to_the_b_c[$active];
            $this->_ardo = (isset($db['ARDO']) && $db['ARDO']) ? new ARDO() : false;
        }

        public function insert(array $data) {
            foreach($data as $key => $value) {
                $this->$key = $value;
            }
            return $this->create();
        }

        public function create() {
            DB::get()->begin_trans();
            try {
                $this->before_creation();
                $columns = $this->get_column_list();
                $primary_key = static::get_primary_key();
                $sql = "INSERT INTO ".static::get_table()." SET ";
                $set_values = array();
                $params = array();
                foreach ($columns as $column) {
                    switch ($column) {
                        case $primary_key:
                            if ($this->$column != null) {
                                $set_values[] = "$primary_key = ?";
                                $params[] = $this->$column;
                            }
                            break;
                        case "created_on":
                            $set_values[] = "created_on = ".($this->created_on ? "'".DateTimeUtil::format($this->created_on, DateTimeUtil::DATETIME_FORMAT_MYSQL)."'" : "NOW()");
                            break;
                        case "modified_on":
                            //do nothing, MySQL will auto-update this column with the CURRENT_TIMESTAMP
                            break;
                        default:
                            $set_values[] = "$column = ?";
                            $params[] = $this->$column;
                            break;
                    }
                }
                $sql .= implode(", ", $set_values);
                $sql .= " ON DUPLICATE KEY UPDATE $primary_key=LAST_INSERT_ID($primary_key)";
                DB::get()->execute($sql, $params);
                $this->duplicate_key = DB::get()->affected_rows() == 1 ? false : true;
                $this->creation_id = DB::get()->last_insert_id() ? : false;
                $this->$primary_key = $this->$primary_key ? : $this->creation_id; //if there isn't a primary key set make it the creation id
                $this->after_creation();
                DB::get()->commit_trans();
                return $this->creation_id;
            } catch (Exception $e) {
                DB::get()->rollback_trans();
                throw $e;
            }
        }

        //either matches a scalar on primary_key = id or takes an assoc array where each key value pair is evaluated for equality
        public function retrieve($id_or_conditions) {
            $this->before_retrieval();
            if($this->_ardo && count($this->relates) > 0) {
                return $this->_ardo->retrieve($id_or_conditions);
            }
            $columns = $this->get_column_list();
            $sql = "SELECT ".implode(", ", $columns)." FROM ".static::get_table()." WHERE ";
            if (is_array($id_or_conditions)) {
                $sql .= implode(" AND ", array_map(function ($key, $value) {
                                               return "$key = ?";
                                           }, array_keys($id_or_conditions), $id_or_conditions));
            } else {
                $sql .= static::get_primary_key()." = ?";
            }
            $sql .= " LIMIT 1";
            $params = (array)$id_or_conditions;
            $result = DB::get()->fetch_one($sql, $params);
            if ($result) {
                $this->set_class_variables($result);
            } else {
                return false; //this is a badness that should be handled by the caller
            }
            $this->after_retrieval();
            return $this;
        }

        public static function retrieve_multiple(array $ids_or_conditions) {
            $associative_input = array_reduce(array_keys($ids_or_conditions), function ($assoc_found, $key) {
                    return ($assoc_found or !is_numeric($key));
                }, false);
            $class = get_called_class();
            $columns = $class::get_column_list();
            $sql = "SELECT ".implode(", ", $columns)." FROM ".static::get_table()." WHERE ";
            if ($associative_input) {
                $sql .= implode(" AND ", array_map(function ($key, $value) {
                                               return "$key = ?";
                                           }, array_keys($ids_or_conditions), $ids_or_conditions));
            } else {
                $sql .= static::get_primary_key()." IN (".implode(", ", array_fill(0, sizeof($ids_or_conditions), "?")).")";
            }
            $params = $ids_or_conditions;
            $result = DB::get()->fetch_all($sql, $params);
            $objects = array();
            if ($result) {
                foreach ($result as $row) {
                    $objects[] = new $class($row);
                }
            } else {
                return false; //this is a badness that should be handled by the caller
            }
            return $objects;
        }

        public function save($editable_columns_only = true) {
            DB::get()->begin_trans();
            try {
                $columns = $editable_columns_only ? $this->get_editable_column_list() : $this->get_column_list();
                $primary_key = static::get_primary_key();
                if (!empty($columns)) {
                    $this->before_saving();
                    $sql = "UPDATE ".static::get_table()." SET ";
                    $set_values = array();
                    foreach ($columns as $column) {
                        $set_values[] = "$column = ?";
                    }
                    $sql .= implode(", ", $set_values);
                    $sql .= " WHERE $primary_key = ?";
                    $params = array();
                    foreach ($columns as $column) {
                        $params[] = $this->$column;
                    }
                    $params[] = $this->$primary_key;
                    $result = DB::get()->execute($sql, $params);
                    $this->after_saving();
                    DB::get()->commit_trans();
                    return $result;
                } else {
                    throw new Exception("The class ".LanguageUtil::get_class_from_namespace($this)." does not have any editable fields.  As such, it can not be saved.");
                }
            } catch (Exception $e) {
                DB::get()->rollback_trans();
                throw $e;
            }
        }

        function where($field, $value = null) {
            if(is_array($field)) {
                foreach($field as $key => $value) {
                    $this->_extraConditions .= '`'.$this->_model.'`.`'.$key.'` = "'.mysql_real_escape_string($value).'" AND ';
                }
            } else {
                $this->_extraConditions .= '`'.$this->_model.'`.`'.$field.'` = \''.mysql_real_escape_string($value).'\' AND ';
            }
        }

        function like($field, $value) {
            $this->_extraConditions .= '`'.$this->_model.'`.`'.$field.'` LIKE \'%'.mysql_real_escape_string($value).'%\' AND ';
        }

        function showHasOne() {
            $this->_hO = 1;
        }

        function showHasMany() {
            $this->_hM = 1;
        }

        function showHMABTM() {
            $this->_hMABTM = 1;
        }

        function setLimit($limit) {
            $this->_limit = $limit;
        }

        function setPage($page) {
            $this->_page = $page;
        }

        function orderBy($orderBy, $order = 'ASC') {
            $this->_orderBy = $orderBy;
            $this->_order = $order;
        }

        function search() {

            $db = DB::get();
            $class = get_class($this);
            $inflect = Inflection::get_instance();

            $from = '`'.$this->_table.'` as `'.$this->_model.'` ';
            $conditions = '\'1\'=\'1\' AND ';

            if ($this->_hO == 1 && isset($this->hasOne)) {

                foreach ($this->hasOne as $alias => $model) {
                    $table = strtolower($this->inflect->pluralize($model));
                    $singularAlias = strtolower($alias);
                    $from .= 'LEFT JOIN `'.$table.'` as `'.$alias.'` ';
                    $from .= 'ON `'.$this->_model.'`.`'.$singularAlias.'_id` = `'.$alias.'`.`id`  ';
                }
            }

            if ($this->id) {
                $conditions .= '`'.$this->_model.'`.`id` = \''.mysql_real_escape_string($this->id).'\' AND ';
            }

            if ($this->_extraConditions) {
                $conditions .= $this->_extraConditions;
            }

            $conditions = substr($conditions,0,-4);

            if (isset($this->_orderBy)) {
                $conditions .= ' ORDER BY `'.$this->_model.'`.`'.$this->_orderBy.'` '.$this->_order;
            }

            if (isset($this->_page)) {
                $offset = ($this->_page-1)*$this->_limit;
                $conditions .= ' LIMIT '.$this->_limit.' OFFSET '.$offset;
            }

            $columns = $this->get_column_list();
            array_walk($columns, function(&$item1, $key, $prefix) {
                    $item1 = '`'.$prefix.'`.`'.$item1.'`';
                }, $this->_model);
            $this->_query = 'SELECT '.implode(", ", $columns).' FROM '.$from.' WHERE '.$conditions;
            $this->_result = $db->fetch_all($this->_query);
            $num = $db->num_results($this->_result);
            $result = array();
            if($this->_result) {
                $tempResults = array();
                $tempResults[$this->_model] = $class::construct_objects_from_rows($this->_result);
                foreach($tempResults[$class] as $class_obj) {



                    if ($this->_hM == 1 && isset($this->hasMany)) {
                        foreach ($this->hasMany as $aliasChild => $modelChild) {
                            $queryChild = '';
                            $conditionsChild = '';
                            $fromChild = '';

                            $tableChild = strtolower($this->inflect->pluralize($modelChild));
                            $pluralAliasChild = strtolower($this->inflect->pluralize($aliasChild));
                            $singularAliasChild = strtolower($aliasChild);

                            $fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`';

                            $conditionsChild .= '`'.$aliasChild.'`.`'.strtolower($this->_model).'_id` = \''.$class_obj->id.'\'';

                            /** @var $column_list array */
                            $childColumns = $modelChild::$column_list;
                            $queryChild =  'SELECT ' . implode(", ", $childColumns) . ' FROM '.$fromChild.' WHERE '.$conditionsChild;
                            #echo '<!--'.$queryChild.'-->';

                            $resultChild = $db->fetch_all($queryChild);
                            $tempResultsChild = array();
                            if ($db->num_results($resultChild) > 0) {
                                $num += $db->num_results($resultChild);

                                $tempResultsChild = $modelChild::construct_objects_from_rows($resultChild);

                                $tempResults[$aliasChild] = $tempResultsChild;
                            }

                        }
                    }


                    if ($this->_hMABTM == 1 && isset($this->hasManyAndBelongsToMany)) {
                        foreach ($this->hasManyAndBelongsToMany as $aliasChild => $tableChild) {
                            $queryChild = '';
                            $conditionsChild = '';
                            $fromChild = '';

                            $tableChild = strtolower($this->inflect->pluralize($tableChild));
                            $pluralAliasChild = strtolower($this->inflect->pluralize($aliasChild));
                            $singularAliasChild = strtolower($aliasChild);

                            $sortTables = array($this->_table,$pluralAliasChild);
                            sort($sortTables);
                            $joinTable = implode('_',$sortTables);

                            $fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`,';
                            $fromChild .= '`'.$joinTable.'`,';

                            $conditionsChild .= '`'.$joinTable.'`.`'.$singularAliasChild.'_id` = `'.$aliasChild.'`.`id` AND ';
                            $conditionsChild .= '`'.$joinTable.'`.`'.strtolower($this->_model).'_id` = \''.$class_obj->id.'\'';
                            $fromChild = substr($fromChild,0,-1);

                            $childColumns = $aliasChild::$column_list;
                            array_walk($childColumns, function(&$item1, $key, $prefix) {
                                    $item1 = '`'.$prefix.'`.`'.$item1.'`';
                                }, $aliasChild);
                            $queryChild =  'SELECT ' . implode(", ", $childColumns) . ' FROM '.$fromChild.' WHERE '.$conditionsChild;
                            #echo '<!--'.$queryChild.'-->';

                            $resultChild = $db->fetch_all($queryChild);
                            $tempResultsChild = array();
                            if ($db->num_results($resultChild) > 0) {
                                $num += $db->num_results($resultChild);
                                $tempResultsChild = $aliasChild::construct_objects_from_rows($resultChild);
                            }

                            $tempResults[$aliasChild] = $tempResultsChild;
                        }
                    }

                    $result = $tempResults;
                }

                if($num == 1 && $this->id != null) {
                    $this->clear();
                    return(array_shift($result[$this->_model]));
                } else {
                    $this->clear();
                    return($result);
                }
            } else {
                $this->clear();
                return $result;
            }

        }

        protected function _describe() {
            $db = DB::get();

            $this->_describe = $this->cache->get('describe'.$this->_table);

            if (!$this->_describe) {
                $this->_describe = array();
                $query = 'DESCRIBE '.$this->_table;
                $this->_result = mysql_query($query, $db->conn);
                while ($row = mysql_fetch_row($this->_result)) {
                    array_push($this->_describe,$row[0]);
                }

                mysql_free_result($this->_result);
                $this->cache->save('describe'.$this->_table,$this->_describe, 999999999);
            }

            foreach ($this->_describe as $field) {
                $this->$field = null;
            }
	        $class = get_class($this);
	        $class::$column_list = $this->_describe;
        }

        public function clear() {
            foreach($this->_describe as $field) {
                $this->$field = null;
            }

            $this->_orderby = null;
            $this->_extraConditions = null;
            $this->_hO = null;
            $this->_hM = null;
            $this->_hMABTM = null;
            $this->_page = null;
            $this->_order = null;
        }

        public function delete() {
            DB::get()->begin_trans();
            try {
                $this->before_deletion();
                $primary_key = static::get_primary_key();
                if (is_numeric($this->$primary_key)) {
                    $sql = "DELETE FROM ".static::get_table()."
                        WHERE $primary_key = ?";
                    $params = array($this->$primary_key);
                    $results = DB::get()->execute($sql, $params);
                    $this->after_deletion();
                    DB::get()->commit_trans();
                    return $results;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                DB::get()->rollback_trans();
                throw $e;
            }
        }

        /**
         * @param array $data
         *
         * @return TableObject|mixed
         */
        public static function build(array $data) {
            $table_object = new static($data);
            $table_object->create();
            return $table_object;
        }

        public function refresh() {
            $class = get_called_class();
            $object = new $class($this->id);
            $this->set_class_variables($object);
        }

        public static function construct_objects_from_rows(array $rows) {
            $objects = array();
            if ($rows) {
                foreach ($rows as $row) {
                    $objects[$row[static::get_primary_key()]] = new static($row);
                }
            }
            return $objects;
        }

        public static function construct_object_from_row(array $row) {
            return new static($row);
        }

        /*
        * Functions to get objects or ids
        */


        public function selectAll() {
            $class = get_class($this);
            return $class::retrieve_objects("");
        }

        public function select($id) {
            $class = get_class($this);
            return $class::retrieve_object(" WHERE ".static::get_primary_key()." = ?", array($id));
        }

        public function remove($id) {
            $class = get_class($this);
            $class = new $class($id);
            return $class->delete();
        }

        /**
         * @param      $where_clause
         * @param null $params
         *
         * @return mixed
         */
        public static function retrieve_object($where_clause, $params = null) {
            if ($where_clause) {
                $where_clause = preg_match("/(?i)^\\s*WHERE\\s/", $where_clause) != 0 ? " $where_clause" : " WHERE $where_clause";
            }
            $columns = implode(", ", static::get_column_list());
            $sql = "SELECT $columns FROM ".static::get_table().$where_clause;
            $row = DB::get()->fetch_one($sql, $params);
            return static::construct_object_from_row($row);
        }

        public static function retrieve_object_id($where_clause, $params = null) {
            if ($where_clause) {
                $where_clause = preg_match("/(?i)^\\s*WHERE\\s/", $where_clause) != 0 ? " $where_clause" : " WHERE $where_clause";
            }
            $sql = "SELECT ".static::get_primary_key()." FROM ".static::get_table().$where_clause;
            $row = DB::get()->fetch_one($sql, $params);

            if ($row) {
                return array_shift($row);
            }
            return null;
        }

        /**
         * @param      $where_clause
         * @param null $params
         *
         * @return mixed
         */
        public static function retrieve_objects($where_clause, $params = null) {
            if ($where_clause) {
                $where_clause = preg_match("/(?i)^\\s*WHERE\\s/", $where_clause) != 0 ? " $where_clause" : " WHERE $where_clause";
            }
            $columns = implode(", ", static::get_column_list());
            $sql = "SELECT $columns FROM ".static::get_table().$where_clause;
            $rows = DB::get()->fetch_all($sql, $params);
            return static::construct_objects_from_rows($rows);
        }

        public static function retrieve_object_ids($where_clause, $params = null) {
            if ($where_clause) {
                $where_clause = preg_match("/(?i)^\\s*WHERE\\s/", $where_clause) != 0 ? " $where_clause" : " WHERE $where_clause";
            }
            $sql = "SELECT ".static::get_primary_key()." FROM ".static::get_table().$where_clause;
            $rows = DB::get()->fetch_all($sql, $params);

            $ids = array();
            if ($rows) {
                foreach ($rows as $row) {
                    $ids[] = array_shift($row);
                }
            }
            return $ids;
        }

        public static function retrieve_all_objects() {
            return static::retrieve_objects("WHERE mode='live' ORDER BY id");
        }

        public static function retrieve_all_object_ids() {
            return static::retrieve_object_ids("WHERE mode='live' ORDER BY id");
        }

        //this actually does the work when the other two above are called
        public static function get_related($name, $arguments) {
            $name = strtolower($name); //convert the function call name into all lowercase for easier regex matching

            //check the $name against accepted forms, capturing target class and pluralization, and make sure an argument is supplied
            if (preg_match("/^get_related_(.*?)(ies|s)?_?(ids?)?$/", $name, $matches) && !empty($arguments)) {
                //class names don't have underscores, so remove them and CamelCase
                $target_class = str_replace("_", " ", $matches[1]);
                $target_class = ucwords($target_class);
                $target_class = str_replace(" ", "", $target_class);
                $target_class = "\\FluxCRM\\Models\\$target_class";

                //if the class is an -ies pluralization, unpluralize back to -y
                if (isset($matches[2]) && $matches[2] == "ies") {
                    $target_class .= "y";
                }

                //foreign key needs depluralization as well, also add _id to the end
                $fk = preg_replace(array("/s$/", "/ies$/"), array("", "y"), static::get_table())."_id";
                if (property_exists($target_class, $fk)) { //check to see that the $target_class has properly named foreign key
                    //$arguments will be an array of actual arguments, with $arguments[0] being the first.  Unpack arrays inside of it.
                    if (is_array($arguments[0]) && sizeof($arguments) == 1) {
                        $arguments = $arguments[0];
                    }
                    //if a bunch of ids are given, SQL in the style of WHERE IN
                    if (($num_args = sizeof($arguments)) > 1) {
                        $sql = "WHERE $fk IN (";
                        $sql .= implode(", ", array_fill(0, $num_args, "?"));
                        $sql .= ")";
                    } //otherwise just a simple WHERE =
                    else {
                        $sql = "WHERE $fk = ?";
                    }
                    //figure out if you should get ids or whole objects
                    if (!isset($matches[3])) {
                        return $target_class::retrieve_objects($sql, $arguments);
                    } else {
                        return $target_class::retrieve_object_ids($sql, $arguments);
                    }
                }
            }
            //if we fall through to here, then the call doesn't match a known style
            throw new Exception(get_called_class()." does not have a defined function named $name.");
        }

        public static function get_enum_values($table, $column) {
            $sql = "SHOW COLUMNS FROM $table LIKE ?";
            $params = array($column);
            $result = DB::get()->fetch_all($sql, $params);
            $type_definition = $result[0]['Type'];
            preg_match_all("/(?:'(.*?)')/", $type_definition, $enum_values);
            return $enum_values[1];
        }

        /*
        * Status Setting Methods
        */

        //this to just a convenience function for the functions below, it should
        //not be called directly, but only via a call of activate(), inactivate(),
        //end(), or archive().  This allows children to override or extend those
        //methods as needed with confidence that thier specific behaviour will be used.
        protected static function set_status($new_status, $class, $ids) {
            if (!empty($ids)) { //not an empty array
                $sql = "UPDATE ".$class::get_table()." SET status = ?";
                foreach (self::$status_list as $status) {
                    if (property_exists($class, $status."_on")) {
                        if ($status == $new_status) {
                            $sql .= ", ".$status."_on = NOW()";
                        } else {
                            $sql .= ", ".$status."_on = NULL";
                        }
                    }
                }
                if (($num_ids = sizeof($ids)) > 1) {
                    $sql .= " WHERE ".$class::get_primary_key()." IN (";
                    $sql .= implode(", ", array_fill(0, $num_ids, "?"));
                    $sql .= ")";
                } else {
                    $sql .= " WHERE ".$class::get_primary_key()." = ?";
                }
                $params = array_merge(array($new_status), (array)$ids);
                return DB::get()->execute($sql, $params);
            } else {
                return false;
            }
        }

        public static function activate($object_ids) {
            return static::set_status("active", get_called_class(), $object_ids);
        }

        public static function inactivate($object_ids) {
            return static::set_status("inactive", get_called_class(), $object_ids);
        }

        public static function end($object_ids) {
            return static::set_status("ended", get_called_class(), $object_ids);
        }

        public static function archive($object_ids) {
            return static::set_status("archived", get_called_class(), $object_ids);
        }

        public static function call_status_func($new_status, $object_ids) {
            switch ($new_status) {
                case "ended":
                    static::end($object_ids);
                    break;
                case "active":
                    static::activate($object_ids);
                    break;
                case "inactive":
                    static::inactivate($object_ids);
                    break;
                case "archived":
                    static::archive($object_ids);
                    break;
                default:
                    throw new Exception("Unable to change status to $new_status. $new_status is not an allowed status value");
            }
        }

        /*
        * Mode Setting Methods
        */

        //this to just a convenience function for the functions below, it should
        //not be called directly, but only via a call of enliven, delete,
        //uninitialize, suggest_for_deletion, unknow.  This allows children to
        //override or extend those methods as needed with confidence that thier
        //specific behaviour will be used.
        protected static function set_mode($new_mode, $class, $ids) {
            if (!empty($ids)) { //not an empty array
                $sql = "UPDATE ".$class::get_table()." SET mode = ?";
                foreach (self::$mode_list as $mode) {
                    if (property_exists($class, $mode."_on")) {
                        if ($mode == $new_mode) {
                            $sql .= ", ".$mode."_on = NOW()";
                        } else {
                            $sql .= ", ".$mode."_on = NULL";
                        }
                    }
                }
                if (($num_ids = sizeof($ids)) > 1) {
                    $sql .= " WHERE ".$class::get_primary_key()." IN (";
                    $sql .= implode(", ", array_fill(0, $num_ids, "?"));
                    $sql .= ")";
                } else {
                    $sql .= " WHERE ".$class::get_primary_key()." = ?";
                }
                $params = array_merge(array($new_mode), (array)$ids);
                return DB::get()->execute($sql, $params);
            } else {
                return false;
            }
        }

        public static function uninitialize($object_ids) {
            return static::set_mode("uninitialized", get_called_class(), $object_ids);
        }

        public static function enliven($object_ids) {
            return static::set_mode("live", get_called_class(), $object_ids);
        }

        //TODO: rename this to just "delete" once the non-static delete() method can be refactored to this one
        public static function mode_delete($object_ids) {
            return static::set_mode("deleted", get_called_class(), $object_ids);
        }

        public static function suggest($object_ids) {
            return static::set_mode("suggested_for_deletion", get_called_class(), $object_ids);
        }

        public static function unknow($object_ids) {
            return static::set_mode("unknown", get_called_class(), $object_ids);
        }

        public static function call_mode_func($new_mode, $object_ids) {
            switch ($new_mode) {
                case "uninitialized":
                    static::uninitialize($object_ids);
                    break;
                case "live":
                    static::enliven($object_ids);
                    break;
                case "deleted":
                    static::mode_delete($object_ids);
                    break;
                case "suggested_for_deletion":
                    static::suggest($object_ids);
                    break;
                case "unknown":
                    static::unknow($object_ids);
                    break;
                default:
                    throw new Exception("Unable to change mode to $new_mode. $new_mode is not an allowed mode value");
            }
        }

        /*
         * Inherited Meta-data returning functions
         */

        public static function get_column_list() {
            return static::$column_list;
        }

        protected static function get_editable_column_list() {
            return static::$editable_column_list;
        }

        public static function get_primary_key() {
            return "id";
        }

        public function get_primary_key_value() {
            $class = get_class($this);
            $primary_key_name = $class::get_primary_key();
            return $this->$primary_key_name;
        }

        protected static function get_table() {
            return static::get_table();
        }
    }
