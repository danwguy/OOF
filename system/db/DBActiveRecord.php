<?php


class DBActiveRecord extends DBDriver {

	public $ar_select = array();
	public $ar_distinct = false;
	public $ar_from = array();
	public $ar_join = array();
	public $ar_where = array();
	public $ar_like = array();
	public $ar_group_by = array();
	public $ar_having = array();
	public $ar_keys = array();
	public $ar_limit = false;
	public $ar_offset = false;
	public $ar_order = false;
	public $ar_order_by = array();
	public $ar_set = array();
	public $ar_wherein = array();
	public $ar_aliased_tables = array();
	public $ar_store_array = array();

	private $ar_caching = false;
	private $ar_cache_exists = array();
	private $ar_cache_select = array();
	private $ar_cache_from = array();
	private $ar_cache_join = array();
	private $ar_cache_where = array();
	private $ar_cache_like = array();
	private $ar_cache_group_by = array();
	private $ar_cache_having = array();
	private $ar_cache_order_by = array();
	private $ar_cache_set = array();

	protected $ar_no_escape = array();
	protected $ar_cache_no_escape = array();


    public function db_prefix($table = null) {
        if(!$table) {
            $this->display_error('db_table_name_required');
        }
        return $this->prefix.$table;
    }

    public function set_prefix($prefix) {
        $this->prefix = $prefix;
    }

	public function select($select = "*", $escape = null) {
		if(is_string($select)) {
			$select = explode(',', $select);
		}
		foreach($select as $v) {
			$v = trim($v);
			if($v != '') {
				$this->ar_select[] = $v;
				$this->ar_no_escape[] = $escape;
                $this->_cache($v, 'select', array('no_escape' => $escape));
			}
		}
		return $this;
	}

	public function select_min($select = "*", $alias = '') {
		return $this->_min_max_avg_sum($select, $alias, 'min');
	}

	public function select_max($select = "*", $alias = '') {
		return $this->_min_max_avg_sum($select, $alias, 'max');
	}

	public function select_avg($select = "*", $alias = '') {
		return $this->_min_max_avg_sum($select, $alias, 'avg');
	}

	public function select_sum($select = "*", $alias = '') {
		return $this->_min_max_avg_sum($select, $alias, 'sum');
	}

    public function from($table) {
        foreach((array)$table as $v) {
            if(strpos($v, ',') !== false) {
                foreach(explode(',', $v) as $va) {
                    $va = trim($va);
                    $this->_track_aliases($va);

                    $this->ar_from[] = $this->_protect_identifiers($va, true, null, false);
                    $this->_cache($this->_protect_identifiers($va, true, null, false), 'from');
                }
            } else {
                $v = trim($v);
                $this->_track_aliases($v);
                $this->ar_from[] = $this->_protect_identifiers($v, true, null, false);
                $this->_cache($this->_protect_identifiers($v, true, null, false), 'from');
            }
        }
        return $this;
    }

    public function distinct($val = true) {
        $this->ar_distinct = (is_bool($val)) ? $val : true;
        return $this;
    }

    public function join($table, $cond, $type = '') {
        if($type != '') {
            $type = strtoupper(trim($type));
            if(!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }
        $this->_track_aliases($table);
        if(preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $match[1] = $this->_protect_identifiers($match[1]);
            $match[3] = $this->_protect_identifiers($match[3]);
            $cond = $match[1].$match[2].$match[3];
        }
        $join = $type.'JOIN '.$this->_protect_identifiers($table, true, null, false).' ON '.$cond;
        $this->ar_join[] = $join;
        $this->_cache($join, 'join');

        return $this;
    }

    public function where_in($key = null, $values = null) {
        return $this->_where_in($key, $values);
    }

    public function or_where_in($key = null, $values = null) {
        return $this->_where_in($key, $values, false, 'OR ');
    }

    public function or_where_not_in($key = null, $values = null) {
        return $this->_where_in($key, $values, true, 'OR ');
    }

    public function where($key, $value = null, $escape = true) {
        return $this->_where($key, $value, 'AND ', $escape);
    }

    public function or_where($key, $value = null, $escape = true) {
        return $this->_where($key, $value, 'OR ', $escape);
    }

    public function like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side);
    }

    public function not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side, 'NOT');
    }

    public function or_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side);
    }

    public function or_not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }

    public function having($key, $value = '', $escape = true) {
        return $this->_having($key, $value, 'AND ', $escape);
    }

    public function or_having($key, $value = '', $escape = true) {
        return $this->_having($key, $value, 'OR ', $escape);
    }

    public function limit($value, $offset = null) {
        $this->ar_limit = (int) $value;
        if($offset) {
            $this->ar_offset = (int) $offset;
        }
        return $this;
    }

    public function offset($offset) {
        $this->ar_offset = $offset;
        return $this;
    }

    public function order_by($orderby, $direction = '') {
        if(strtolower($direction) == 'random') {
            $orderby = '';
            $direction = $this->_rendom_keyword;
        } else if(trim($direction) != '') {
            $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), true)) ? ' '.$direction : ' ASC';
        }
        if(strpos($orderby, ',') !== false) {
            $temp = array();
            foreach(explode(',', $orderby) as $part) {
                $part = trim($part);
                if(!in_array($part, $this->ar_aliased_tables)) {
                    $part = $this->_protect_identifiers(trim($part));
                }
                $temp[] = $part;
            }
            $orderby = implode(', ', $temp);
        } else if($direction != $this->_random_keyword) {
            $orderby = $this->_protect_identifiers($orderby);
        }
        $orderby_statement = $orderby.$direction;
        $this->ar_order_by[] = $orderby.$direction;
        $this->_cache($orderby_statement, 'orderby');

        return $this;
    }

    public function group_by($by) {
        if(is_string($by)) {
            $by = explode(',', $by);
        }
        foreach($by as $val) {
            $val = trim($val);
            if($val != '') {
                $this->ar_group_by[] = $this->_protect_identifiers($val);
                $this->_cache($this->_protect_identifiers($val), 'groupby');
            }
        }
        return $this;
    }

    public function set($key, $value = '', $escape = true) {
        $key = $this->_object_to_array($key);
        if(!is_array($key)) {
            $key = array($key => $value);
        }
        foreach($key as $k => $v) {
            if(!$escape) {
                $this->ar_set[$this->_protect_identifiers($k)] = $v;
            } else {
                $this->ar_set[$this->_protect_identifiers($k, false, true)] = $this->escape($v);
            }
        }
        return $this;
    }

    public function get($table = null, $limit = null, $offset = null) {
        if($table) {
            $this->_track_aliases($table);
            $this->from($table);
        }
        if($limit) {
            $this->limit($limit, $offset);
        }
        $sql = $this->_compile_select();
        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }

    public function get_where($table = null, $where = null, $limit = null, $offset = null) {
        if($table) {
            $this->from($table);
        }
        if($where) {
            $this->where($where);
        }
        if($limit) {
            $this->limit($limit, $offset);
        }
        $sql = $this->_compile_select();

        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }

    public function count_all_results($table = null) {
        if($table) {
            $this->_track_aliases($table);
            $this->from($table);
        }
        $sql = $this->_compile_select($this->_count_string . $this->_protect_identifiers('numrows'));

        $query = $this->query($sql);
        $this->_reset_select();
        if($query->num_rows() == 0) {
            return 0;
        }
        $row = $query->row();
        return (int) $row->numrows;
    }

    public function insert($table = null, $set = null) {
        if($set) {
            $this->set($set);
        }
        if(count($this->ar_set) == 0) {
            if($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return false;
        }
        if(!$table) {
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return false;
            }
            $table = $this->ar_from[0];
        }
        $sql = $this->_inset($this->_protect_identifiers($table, true, null, false), array_keys($this->ar_set), array_values($this->ar_set));

        $this->_reset_write();
        return $this->query($sql);
    }

    public function insert_batch($table = null, $set = null) {
        if($set) {
            $this->set_insert_batch($set);
        }
        if(count($this->ar_set) == 0) {
            if($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return false;
        }
        if(!$table) {
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_use_set_table');
                }
                return false;
            }
            $table = $this->ar_from[0];
        }
        for($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {
            $sql = $this->_insert_batch($this->_protect_identifiers($table, true, null, false), $this->ar_keys, array_slice($this->ar_set, $i, 100));
            $this->query($sql);
        }
        $this->_reset_write();
        return true;
    }

    public function set_insert_batch($key, $value = null, $escape = true) {
        $key = $this->_object_to_array_batch($key);
        if(!is_array($key)) {
            $key = array($key => $value);
        }
        $keys = array_keys(current($key));
        sort($keys);

        foreach($key as $row) {
            if(count(array_diff($keys, array_keys($row))) > 0 || count(array_diff(array_keys($row), $keys)) > 0) {
                $this->ar_set[] = array();
                return;
            }
            ksort($row);
            if(!$escape) {
                $this->ar_set[] = '('.implode(',', $row).')';
            } else {
                $clean = array();
                foreach($row as $val) {
                    $clean[] = $this->escape($val);
                }
                $this->ar_set[] = '('.implode(',', $clean).')';
            }
        }
        foreach($keys as $k) {
            $this->ar_keys[] = $this->_protect_identifiers($k);
        }
        return $this;
    }

    public function replace($table = null, $set = null) {
        if($set) {
            $this->set($set);
        }
        if(count($this->ar_set) == 0) {
            if($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return false;
        }
        if(!$table) {
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_use_set_table');
                }
                return false;
            }
            $table = $this->ar_from[0];
        }
        $sql = $this->_replace($this->protect_identifiers($table, true, null, false), array_keys($this->ar_set), array_values($this->ar_set));
        $this->_reset_write();
        return $this->query($sql);
    }

    public function update($table = null, $set = null, $where = null, $limit = null) {
        $this->_merge_cache();
        if($set) {
            $this->set($set);
        }
        if(count($this->ar_set) == 0) {
            if($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return false;
        }
        if(!$table) {
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_use_set_table');
                }
                return false;
            }
            $table = $this->ar_from[0];
        }
        if($where) {
            $this->where($where);
        }
        if($limit) {
            $this->limit($limit);
        }

        $sql = $this->_update($this->_protect_identifiers($table, true, null, false), $this->ar_set, $this->ar_where, $this->ar_order_by, $this->ar_limit);
        $this->_reset_write();
        return $this->query($sql);
    }

    public function delete($table = null, $where = null, $limit = null, $reset_data = true) {
        $this->_merge_cache();
        if(!$table){
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return false;
            }
            $table = $this->ar_from[0];
        } else if(is_array($table)) {
            foreach($table as $single) {
                $this->delete($single, $where, $limit, false);
            }
            $this->_reset_write();
            return;
        } else {
            $table = $this->_protect_identifiers($table, true, null, false);
        }
        if($where) {
            $this->where($where);
        }
        if($limit) {
            $this->limit($limit);
        }
        if(count($this->ar_where) == 0 && count($this->ar_wherein) == 0 && count($this->ar_like) == 0) {
            if($this->db_debug) {
                return $this->display_error('db_de;_must_use_where');
            }
            return false;
        }
        $sql = $this->_delete($table);
        if($reset_data) {
            $this->_reset_write();
        }
        return $this->query($sql);
    }


    public function empty_table($table = null) {
        if(!$table) {
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return false;
            }
            $table = $this->ar_from[0];
        } else {
            $table = $this->_protect_identifiers($table, true, null, false);
        }
        $sql = $this->_delete($table);
        $this->_reset_write();
        return $this->query($sql);
    }

    public function truncate($table = null) {
        if(!$table) {
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return false;
            }
            $table = $this->ar_table[0];
        } else {
            $table = $this->_protect_identifiers($table, true, null, false);
        }
        $sql = $this->_truncate($table);
        $this->_reset_write();
        return $this->query($sql);
    }

    public function update_batch($table = null, $set = null, $index = null) {
        $this->_merge_cache();
        if(!$index) {
            if($this->db_debug) {
                return $this->display_error('db_must_use_index');
            }
            return false;
        }
        if($set) {
            $this->set_update_batch($set, $index);
        }
        if(count($this->ar_set) == 0) {
            if($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return false;
        }
        if(!$table) {
            if(!isset($this->ar_from[0])) {
                if($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return false;
            }
            $table = $this->ar_from[0];
        }
        for($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {
            $sql = $this->_update_batch($this->_protect_identifiers($table, true, null, false), array_slice($this->ar_set, $i, 100), $this->_protect_identifiers($index), $this->ar_where);
            $this->query($sql);
        }
        $this->_reset_write();

    }

    public function set_update_batch($key, $index = null, $escape = true) {
        $key = $this->_object_to_array_batch($key);
        if(!is_array($key)) {
            //error happened, let someone know
        }
        foreach($key as $k => $v) {
            $index_set = false;
            $clean = array();
            foreach($v as $k2 => $v2) {
                if($k2 == $index) {
                    $index_set = true;
                } else {
                    $not[] = $k2.'-'.$v2;
                }
                if($escape) {
                    $clean[$this->_protect_identifiers($k2)] = $this->escape($v2);
                } else {
                    $clean[$this->_protect_idenifiers($k2)] = $v2;
                }
            }
            if(!$index_set) {
                return $this->display_error('db_batch_missing_index');
            }

        }
    }

    public function start_cache() {
        $this->ar_caching = true;
    }

    public function stop_cache() {
        $this->ar_caching = false;
    }

    public function flush_cache() {
        $this->_reset_run(array(
                               'ar_cache_select' => array(),
                               'ar_cache_from' => array(),
                               'ar_cache_join' => array(),
                               'ar_cache_where' => array(),
                               'ar_cache_like' => array(),
                               'ar_cache_group_by' => array(),
                               'ar_cache_having' => array(),
                               'ar_cache_order_by' => array(),
                               'ar_cache_set' => array(),
                               'ar_cache_exists' => array(),
                               'ar_cache_no_escape' => array()
                          ));
    }



    protected function _where($key, $value = null, $type = 'AND ', $escape = null) {
        if(!is_array($key)) {
            $key = array($key => $value);
        }
        if(!is_bool($escape)) {
            $escape = $this->protect_identifiers;
        }

        foreach($key as $k => $v) {
            $prefix = (count($this->ar_where) == 0 && count($this->ar_cache_where) == 0) ? '' : $type;
            if(is_null($v) && !$this->_has_operator($k)) {
                $k .= ' IS NULL';
            }
            if(!is_null($v)) {
                if($escape === true) {
                    $k = $this->_protect_identifiers($k, false, $escape);
                    $v = ' '.$this->escape($v);
                }
                if(!$this->_has_operator($k)) {
                    $k .= ' = ';
                }
            } else {
                $k = $this->_protect_identifiers($k, false, $escape);
            }
            $this->ar_where[] = $prefix.$k.$v;
            $this->_cache($prefix.$k.$v, 'where');
        }
    }

    protected function _having($key, $value = '', $type = 'AND ', $escape = true) {
        if(!is_array($key)) {
            $key = array($key => $value);
        }
        foreach($key as $k => $v) {
            $prefix = (count($this->ar_having) == 0) ? '' : $type;
            if($escape) {
                $k = $this->_protect_identifiers($k);
            }
            if(!$this->_has_operator($k)) {
                $k .= ' = ';
            }
            if($v != '') {
                $v = ' '.$this->escape($v);
            }
            $this->ar_having[] = $prefix.$k.$v;
            $this->_cache($prefix.$k.$v, 'having');
        }
        return $this;
    }

    protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '') {
        if(!is_array($field)) {
            $field = array($field => $match);
        }
        foreach($field as $k => $v) {
            $k = $this->_protect_identifiers($k);
            $prefix = (count($this->ar_like) == 0) ? '' : $type;
            $v = $this->escape_like_str($v);
            if($side == 'none') {
                $like_statement = $prefix." $k $not LIKE '{$v}'";
            } else if($side == 'before') {
                $like_statement = $prefix." $k $not LIKE '%{$v}'";
            } else if($side == 'after') {
                $like_statement = $prefix." $k $not LIKE '{$v}%'";
            } else {
                $like_statement = $prefix." $k $not LIKE'%{$v}%'";
            }
            if($this->_like_escape_str != '') {
                $like_statement = $like_statement.sprintf($this->_like_escape_str, $this->_like_escape_chr);
            }
            $this->ar_like[] = $like_statement;
            $this->_cache($like_statement, 'like');
        }
        return $this;
    }

    protected function _where_in($key = null, $values = nul, $not = false, $type = 'AND ') {
        if($key === null || $values === null)  {
            return;
        }
        if(!is_array($values)) {
            $values = array($values);
        }
        $not = ($not) ? ' NOT' : '';
        foreach($values as $value) {
            $this->ar_wherein[] = $this->escape($value);
        }
        $prefix = (count($this->ar_where) === 0) ? '' : $type;
        $where_in = $prefix . $this->protect_identifiers($key).$not.' IN ('.implode(", ", $this->ar_wherein).")";
        $this->ar_where[] = $where_in;
        $this->_cache($where_in, 'where');
        $this->ar_wherein = array();
        return $this;
    }

	protected function _min_max_avg_sum($select = '', $alias = '', $type = 'max') {
		if(is_string($select) || $select == '') {
			$this->display_error('db_invalid_query');
		}
		$type = strtolower($type);
		if(!in_array($type, array('min', 'max', 'avg', 'sum'))) {
			OOF::show_error("Invalid function type: '.$type");
		}
		if($alias == '') {
			$alias = $this->_create_alieas_from_table(trim($select));
		}
		$sql = $type.'('.$this->_protect_identifiers(trim($select)).') AS '.$alias;
		$this->ar_select[] = $sql;
		if($this->ar_caching == true) {
			$this->ar_cache_select[] = $sql;
			$this->ar_cache_exists[] = 'select';
		}
		return $this;
	}

    protected function _track_aliases($table) {
        if(is_array($table)) {
            foreach($table as $single) {
                $this->_track_aliases($single);
            }
            return;
        }
        if(strpos($table, ',') !== false) {
            $table = preg_replace('/\s+AS\s+/i', ' ', $table);
            $table = trim(strrchr($table, " "));
            if(!in_array($table, $this->ar_aliased_tables)) {
                $this->ar_aliased_tables[] = $table;
            }
        }
    }

    protected function _compile_select($sql_override = false) {
        $this->_merge_cache();
        if($sql_override) {
            $sql = $sql_override;
        } else {
            $sql = (!$this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';
            if(count($this->ar_select) == 0) {
                $sql .= "*";
            } else {
                foreach($this->ar_select as $k => $v) {
                    $no_escape = isset($this->ar_no_escape[$k]) ? $this->ar_no_escape[$k] : null;
                    $this->ar_select[$k] = $this->_protect_identifiers($v, false, $no_escape);
                }
                $sql .= implode(', ', $this->ar_select);
            }
        }
        if(count($this->ar_from) > 0) {
            $sql .= "\nFROM ";
            $sql .= $this->_from_tables($this->ar_from);
        }
        if(count($this->ar_join) > 0) {
            $sql .= "\n";
            $sql .= implode("\n", $this->ar_join);
        }
        if(count($this->ar_where) > 0 || count($this->ar_like) > 0) {
            $sql .= "\nWHERE ";
        }
        $sql .= implode("\n", $this->ar_where);
        if(count($this->ar_like) > 0) {
            if(count($this->ar_where) > 0) {
                $sql .= "\nAND ";
            }
            $sql .= implode("\n", $this->ar_like);
        }
        if(count($this->ar_group_by) > 0) {
            $sql .= "\nGROUP BY ";
            $sql .= implode(", ", $this->ar_group_by);
        }
        if(count($this->ar_having) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $this->ar_having);
        }
        if(count($this->ar_order_by) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $this->ar_order_by);
            if($this->ar_order !== false) {
                $sql .= ($this->ar_order == 'desc') ? ' DESC' : ' ASC';
            }
        }
        if(is_numeric($this->ar_limit)) {
            $sql .="\n";
            $sql .= $this->_limit($sql, $this->ar_limit, $this->ar_offset);
        }
        return $sql;
    }

    protected function _object_to_array($obj) {
        if(!is_object($obj)) {
            return (is_array($obj)) ? $obj : array($obj);
        }
        $array = array();
        foreach(get_object_vars($obj) as $k => $v) {
            if(!is_object($v) && !is_array($v) && $k != '_parent_name') {
                $array[$k] = $v;
            }
        }
        return $array;
    }

    protected function _object_to_array_batch($obj) {
        if(!is_object($obj)) {
            return (is_array($obj)) ? $obj : array($obj);
        }
        $array = array();
        $out = get_object_vars($obj);
        $fields = array_keys($out);
        foreach($fields as $val) {
            if($val != '_parent_name') {
                $i = 0;
                foreach($out[$val] as $data) {
                    $array[$i][$val] = $data;
                    $i++;
                }
            }
        }
        return $array;
    }

    protected function _create_alias_from_table($table) {
        if(strpos($table, '.') !== false) {
            return(end(explode('.', $table)));
        }
        return $table;
    }

    protected function _reset_select()
    {
        $this->_reset_run(array(
            'ar_select'			=> array(),
            'ar_from'			=> array(),
            'ar_join'			=> array(),
            'ar_where'			=> array(),
            'ar_like'			=> array(),
            'ar_groupby'		=> array(),
            'ar_having'			=> array(),
            'ar_orderby'		=> array(),
            'ar_wherein'		=> array(),
            'ar_aliased_tables'	=> array(),
            'ar_no_escape'		=> array(),
            'ar_distinct'		=> FALSE,
            'ar_limit'			=> FALSE,
            'ar_offset'			=> FALSE,
            'ar_order'			=> FALSE,
        ));

    }

    protected function _reset_write()
    {
        $this->_reset_run(array(
            'ar_set'		=> array(),
            'ar_from'		=> array(),
            'ar_where'		=> array(),
            'ar_like'		=> array(),
            'ar_orderby'	=> array(),
            'ar_keys'		=> array(),
            'ar_limit'		=> FALSE,
            'ar_order'		=> FALSE
        ));
    }

    protected function _reset_run($items) {
        foreach($items as $i => $def_val) {
            if(!in_array($i, $this->ar_store_array)) {
                $this->$item = $def_val;
            }
        }
    }

    protected function _merge_cache() {
        if(count($this->ar_cache_exists) == 0) {
            return;
        }
        foreach($this->ar_cache_exists as $v) {
            $ar_variable = 'ar_'.$v;
            $ar_cache_var = 'ar_cache_'.$v;
            if(count($this->$ar_cache_var) == 0) {
                continue;
            }
            $this->$ar_variable = array_unique(array_merge($this->$ar_cache_var, $this->$ar_cache_variable));
        }
        if($this->_protect_identifiers === true && count($this->ar_cache_from) > 0) {
            $this->_track_aliases($this->ar_from);
        }
        $this->ar_no_escape = $this->ar_cache_no_escape;
    }


    protected function _cache($what, $type, $extra = array()) {
        if($this->ar_caching === true) {
            $cache = 'ar_cache_'.$type;
            $this->{$cache}[] = $what;
            $this->ar_cache_exists[] = $type;
            if(count($extra) > 0) {
                foreach($extra as $k => $v) {
                    $c = 'ar_cache_'.$k;
                    $this->{$c}[] = $v;
                }
            }
        }
    }


} 