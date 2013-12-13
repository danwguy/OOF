<?php

class ARDO extends TableObject {

    public $relates = array();
    public static $loaded = array();
    protected static $_current;

    public function retrieve($id = null) {
        if(isset($this->relates) && count($this->relates) > 0) {
            $relate = true;
        } else {
            $relate = false;
        }
        if(!$relate) {
            return parent::retrieve($id);
        }
        $base_table = $this->get_table();
        $used_tables[] = $this->get_table();
        $base_columns = $this->get_column_list();
        $sql = "SELECT ";
        $this->_pre_load();
        foreach($this->relates as $base => $class) {
            $next_classname = ucfirst($class);
            $columns[$class] = $next_classname::$column_list;
            $tables[$class] = $next_classname::get_table();
            $used_tables[] = $next_classname::get_table();
        }
        $sql .= implode(', ', array_map(function($column) use($base_table) {
            return $base_table . '.'.$column . ' as ' . $base_table . '_' . $column;
        }, $base_columns));
        foreach($columns as $class => $set) {
            $sql .= ', '.implode(', ', array_map(function($column) use($tables, $class) {
                    return $tables[$class].'.'.$column . ' as ' . $tables[$class] . '_'.$column;
                }, $set));
        }
        $sql .= "\r\n"." FROM ".$base_table;
        $first = true;
        for($i = 0, $len = count($this->relates); $i < $len; $i++) {
            $this_array = array_slice($this->relates, $i, 1);
            $old_class = key($this_array);
            $new_class = $this->relates[$old_class];
            $old_table = $old_class::get_table();
            $new_table = $new_class::get_table();

            $sql .= "\r\n"." INNER JOIN ". $new_table;
            if($first) {
                $sql .= "\r\n"." ON " . $base_table.".".static::get_primary_key(). " = " . $new_table.".".$base_table."_id";
                $first = false;
            } else {

                $sql .= "\r\n"." ON " . $old_table.".".$old_class::get_primary_key()." = ". $new_table.".".$old_table."_id";
            }
        }
        $sql .= "\r\n"." WHERE " . $base_table . '.' .static::get_primary_key()." = " . $id;
        $res = $this->db->fetch_all($sql);
        foreach($res as $data) {
            foreach($data as $k => $v) {
                foreach($this->relates as $old_class => $new_class) {
                    $old_table = $old_class::get_table();
                    $old_key = $old_class::get_primary_key();
                    $old_find = $old_table."_".$old_key;
                    $new_table = $new_class::get_table();
                    $new_key = $new_class::get_primary_key();
                    $new_find = $new_table."_".$new_key;
                    if(preg_match("/^(".$old_table."(_)*)(.*?)$/", $k, $matches)) {
                        $results[$old_class][$data[$old_find]][$matches[3]] = $v;
                    } else if(preg_match("/^(".$new_table."(_)*)(.*?)$/", $k, $match)) {
                        $results[$new_class][$data[$new_find]][$match[3]] = $v;
                    }
                }
            }
        }
        $first = true;
        $prev = '';
        foreach($results as $class => $data) {
            if($first) {
                $prev = $this->_relate($this, array_shift($data), false);
                $first = false;
            } else {
                $prev = $this->_relate($class, $data, $prev);
            }
        }
        return $prev;
    }


    protected function _relate($class, $data, $base = false) {
        if(!$base) {
            $class->set_class_variable($data);
            return $class;
        } else {
            if(is_array($base)) {
                if(is_array($data)) {
                    foreach($base as $obj) {
                        $point = $obj::get_table();
                        $pk = $obj::get_primary_key();
                        foreach($data as $stuff) {
                            if($obj->$pk == $stuff[$point.'_'.$pk]) {
                                $obj->$class = new $class($stuff);
                            }
                        }
                    }
                } else {
                    foreach($base as $obj) {
                        $point = $obj::get_table();
                        $pk = $obj::get_primary_key();
                        if($obj->$pk == $data[$point.'_'.$pk]) {
                            $obj->$class = new $class($data);
                        }
                    }
                }
            } else {
                if(is_array($data)) {
                    foreach($data as $array) {
                        $classes[] = new $class($array);
                    }
                } else {
                    $classes = new $class($data);
                }
                $base->$class = $classes;
                $base = $base->$class;
            }
        }
        return $base;
    }

    public function _pre_load() {
        $first = array_slice($this->relates, 0, 1);
        $class_name = ucfirst(array_shift($this->relates));
        foreach($first as $k => $v) {
            self::$loaded[$k] = $v;
        }
        $class = new $class_name();
        if(count($class->relates) > 0) {
            $class->_pre_load();
        }
        $this->relates = self::$loaded;
        return;

    }

} 