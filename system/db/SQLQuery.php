<?php


    class SQLQuery extends DB {


        protected $_dbHandle;
        protected $_result;
        protected $_query;
        protected $_table;
        protected $_model;

        protected $_describe = array();

        protected $_orderBy;
        protected $_order;
        protected $_extraConditions;
        protected $_hO;
        protected $_hM;
        protected $_hMABTM;
        protected $_page;
        protected $_limit;

        public function where($field, $value) {
            $this->_extraConditions .=
                '`' . $this->_model . '`.`' . $field . '` = \'' . mysql_real_escape_string($value) . '\' AND ';
        }

        public function like($field, $value) {
            $this->_extraConditions .=
                '`' . $this->_model . '`.`' . $field . '` LIKE \'%' . mysql_real_escape_string($value) . '%\' AND ';
        }

        public function showHasOne() {
            $this->_hO = 1;
        }

        public function showHasMany() {
            $this->_hM = 1;
        }

        public function showHMABTM() {
            $this->_hMABTM = 1;
        }

        public function setLimit($limit) {
            $this->_limit = $limit;
        }

        public function setPage($page) {
            $this->_page = $page;
        }

        public function orderBy($orderBy, $order = 'ASC') {
            $this->_orderBy = $orderBy;
            $this->_order   = $order;
        }

        public function search() {

            global $inflect;

            $from            = '`' . $this->_table . '` as `' . $this->_model . '` ';
            $conditions      = '\'1\'=\'1\' AND ';
            $conditionsChild = '';
            $fromChild       = '';

            if($this->_hO == 1 && isset($this->hasOne)) {

                foreach($this->hasOne as $alias => $model) {
                    $table         = strtolower($this->inflect->pluralize($model));
                    $singularAlias = strtolower($alias);
                    $from .= 'LEFT JOIN `' . $table . '` as `' . $alias . '` ';
                    $from .= 'ON `' . $this->_model . '`.`' . $singularAlias . '_id` = `' . $alias . '`.`id`  ';
                }
            }

            if($this->id) {
                $conditions .= '`' . $this->_model . '`.`id` = \'' . mysql_real_escape_string($this->id) . '\' AND ';
            }

            if($this->_extraConditions) {
                $conditions .= $this->_extraConditions;
            }

            $conditions = substr($conditions, 0, -4);

            if(isset($this->_orderBy)) {
                $conditions .= ' ORDER BY `' . $this->_model . '`.`' . $this->_orderBy . '` ' . $this->_order;
            }

            if(isset($this->_page)) {
                $offset = ($this->_page - 1) * $this->_limit;
                $conditions .= ' LIMIT ' . $this->_limit . ' OFFSET ' . $offset;
            }

            $this->_query = 'SELECT * FROM ' . $from . ' WHERE ' . $conditions;
            #echo '<!--'.$this->_query.'-->';
            $this->_result = mysql_query($this->_query, $this->_dbHandle);
            $result        = array();
            $table         = array();
            $field         = array();
            $tempResults   = array();
            $numOfFields   = mysql_num_fields($this->_result);
            for($i = 0; $i < $numOfFields; ++$i) {
                array_push($table, mysql_field_table($this->_result, $i));
                array_push($field, mysql_field_name($this->_result, $i));
            }
            if(mysql_num_rows($this->_result) > 0) {
                while($row = mysql_fetch_row($this->_result)) {
                    for($i = 0; $i < $numOfFields; ++$i) {
                        $tempResults[$table[$i]][$field[$i]] = $row[$i];
                    }

                    if($this->_hM == 1 && isset($this->hasMany)) {
                        foreach($this->hasMany as $aliasChild => $modelChild) {
                            $queryChild      = '';
                            $conditionsChild = '';
                            $fromChild       = '';

                            $tableChild         = strtolower($this->inflect->pluralize($modelChild));
                            $pluralAliasChild   = strtolower($this->inflect->pluralize($aliasChild));
                            $singularAliasChild = strtolower($aliasChild);

                            $fromChild .= '`' . $tableChild . '` as `' . $aliasChild . '`';

                            $conditionsChild .= '`' . $aliasChild . '`.`' . strtolower($this->_model) . '_id` = \''
                                                . $tempResults[$this->_model]['id'] . '\'';

                            $queryChild = 'SELECT * FROM ' . $fromChild . ' WHERE ' . $conditionsChild;
                            #echo '<!--'.$queryChild.'-->';
                            $resultChild = mysql_query($queryChild, $this->_dbHandle);

                            $tableChild       = array();
                            $fieldChild       = array();
                            $tempResultsChild = array();
                            $resultsChild     = array();

                            if(mysql_num_rows($resultChild) > 0) {
                                $numOfFieldsChild = mysql_num_fields($resultChild);
                                for($j = 0; $j < $numOfFieldsChild; ++$j) {
                                    array_push($tableChild, mysql_field_table($resultChild, $j));
                                    array_push($fieldChild, mysql_field_name($resultChild, $j));
                                }

                                while($rowChild = mysql_fetch_row($resultChild)) {
                                    for($j = 0; $j < $numOfFieldsChild; ++$j) {
                                        $tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
                                    }
                                    array_push($resultsChild, $tempResultsChild);
                                }
                            }

                            $tempResults[$aliasChild] = $resultsChild;

                            mysql_free_result($resultChild);
                        }
                    }


                    if($this->_hMABTM == 1 && isset($this->hasManyAndBelongsToMany)) {
                        foreach($this->hasManyAndBelongsToMany as $aliasChild => $tableChild) {
                            $queryChild      = '';
                            $conditionsChild = '';
                            $fromChild       = '';

                            $tableChild         = strtolower($this->inflect->pluralize($tableChild));
                            $pluralAliasChild   = strtolower($this->inflect->pluralize($aliasChild));
                            $singularAliasChild = strtolower($aliasChild);

                            $sortTables = array($this->_table, $pluralAliasChild);
                            sort($sortTables);
                            $joinTable = implode('_', $sortTables);

                            $fromChild .= '`' . $tableChild . '` as `' . $aliasChild . '`,';
                            $fromChild .= '`' . $joinTable . '`,';

                            $conditionsChild .= '`' . $joinTable . '`.`' . $singularAliasChild . '_id` = `'
                                                . $aliasChild . '`.`id` AND ';
                            $conditionsChild .= '`' . $joinTable . '`.`' . strtolower($this->_model) . '_id` = \''
                                                . $tempResults[$this->_model]['id'] . '\'';
                            $fromChild = substr($fromChild, 0, -1);

                            $queryChild = 'SELECT * FROM ' . $fromChild . ' WHERE ' . $conditionsChild;
                            #echo '<!--'.$queryChild.'-->';
                            $resultChild = mysql_query($queryChild, $this->_dbHandle);

                            $tableChild       = array();
                            $fieldChild       = array();
                            $tempResultsChild = array();
                            $resultsChild     = array();

                            if(mysql_num_rows($resultChild) > 0) {
                                $numOfFieldsChild = mysql_num_fields($resultChild);
                                for($j = 0; $j < $numOfFieldsChild; ++$j) {
                                    array_push($tableChild, mysql_field_table($resultChild, $j));
                                    array_push($fieldChild, mysql_field_name($resultChild, $j));
                                }

                                while($rowChild = mysql_fetch_row($resultChild)) {
                                    for($j = 0; $j < $numOfFieldsChild; ++$j) {
                                        $tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
                                    }
                                    array_push($resultsChild, $tempResultsChild);
                                }
                            }

                            $tempResults[$aliasChild] = $resultsChild;
                            mysql_free_result($resultChild);
                        }
                    }

                    array_push($result, $tempResults);
                }

                if(mysql_num_rows($this->_result) == 1 && $this->id != null) {
                    mysql_free_result($this->_result);
                    $this->clear();

                    return ($result[0]);
                } else {
                    mysql_free_result($this->_result);
                    $this->clear();

                    return ($result);
                }
            } else {
                mysql_free_result($this->_result);
                $this->clear();

                return $result;
            }

        }

        public function __describe() {
            $cache = Cache::get_instance();

            $this->_describe = $cache->get('describe' . $this->_table);

            if(!$this->_describe) {
                $query         = 'DESCRIBE ' . $this->_table;
                $this->_result = $this->_describe = $this->fetch_all($query);
                mysql_free_result($this->_result);
                $cache->set('describe' . $this->_table, $this->_describe);
            }
            foreach($this->_describe as $field) {
                $this->$field = null;
            }

        }

        public function clear() {
            foreach($this->_describe as $field) {
                $this->$field = null;
            }

            $this->_orderby         = null;
            $this->_extraConditions = null;
            $this->_hO              = null;
            $this->_hM              = null;
            $this->_hMABTM          = null;
            $this->_page            = null;
            $this->_order           = null;
        }

        public function totalPages() {
            if($this->_query && $this->_limit) {
                $pattern       = '/SELECT (.*?) FROM (.*)LIMIT(.*)/i';
                $replacement   = 'SELECT COUNT(*) FROM $2';
                $countQuery    = preg_replace($pattern, $replacement, $this->_query);
                $this->_result = mysql_query($countQuery, $this->_dbHandle);
                $count         = mysql_fetch_row($this->_result);
                $totalPages    = ceil($count[0] / $this->_limit);

                return $totalPages;
            } else {
                /* Error Generation Code Here */
                return -1;
            }
        }


    }