<?php


    class ArrayUtil {

        public static function elements(array $elems, array $data, $missing = false) {
            $ret_array = array();
            foreach($elems as $elem) {
                if(isset($data[$elem]) && $data[$elem] != '') {
                    $ret_array[$elem] = $data[$elem];
                } else {
                    $ret_array[$elem] = $missing;
                }
            }
            return $ret_array;
        }

        public static function element($search, array $data, $missing = false) {
            return (isset($data[$search]) && $data[$search] != '') ? $data[$search] : $missing;
        }

        protected static function _obj_to_array_omit($obj, $omit) {
            $arr = array();
            foreach(get_object_vars($obj) as $k => $v) {
                if($k != $omit) {
                    $arr[$k] = $v;
                }
            }
            return $arr;
        }

        public static function object_to_array($obj, $omit = null, $recursive = false) {
            if(!is_object($obj)) {
                if(!is_array($obj)) {
                    return array($obj);
                }
                return $obj;
            }
            $array = array();
            if($omit) {
                $obj = self::_obj_to_array_omit($obj, $omit);
            } else {
                $obj = get_object_vars($obj);
            }
            foreach($obj as $k => $v) {
                if(is_object($v) && $recursive) {
                    $array[k] = self::object_to_array($v, $omit, $recursive);
                } else {
                    $array[$k] = $v;
                }
            }
            return $array;
        }


        public static function transpose(array $array) {
            $transposed_array = array();
            if ($array) {
                foreach ($array as $row_key => $row) {
                    if (is_array($row) && !empty($row)) { //check to see if there is a second dimension
                        foreach ($row as $column_key => $element) {
                            $transposed_array[$column_key][$row_key] = $element;
                        }
                    } else {
                        $transposed_array[0][$row_key] = $row;
                    }
                }
            }
            return $transposed_array;
        }

        public static function pop(array $array) {
            return array_pop($array);
        }

        public static function shift(array $array) {
            return array_shift($array);
        }

        public static function get_value(array &$array, $key) {
            return isset($array[$key]) ? $array[$key] : null;
        }

        public static function array_keys_recursive(array $array) {
            $key_array = array();
            array_walk_recursive($array, function ($value, $key) use (&$key_array) {
                    $key_array[] = $key;
                });
            return $key_array;
        }

        public static function flatten(array $array, $key_prefix = null) {
            $flattened_array = array();
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $flattened_array = array_merge($flattened_array, ArrayUtil::flatten($value, ($key_prefix ? $key_prefix."_" : "").$key));
                } else {
                    $flattened_array = array_merge($flattened_array, array(($key_prefix ? $key_prefix."_" : "").$key => $value));
                }
            }
            return $flattened_array;
        }

        //same as php's http_build_query function except that it actually includes fields that are empty/null
        public static function http_build_query(array $array, $key_prefix = null) {
            $flattened_array = array();
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $flattened_array = array_merge($flattened_array, ArrayUtil::http_build_query($value, ($key_prefix ? $key_prefix."[" : "").$key.($key_prefix ? "]" : "")));
                } else {
                    $flattened_array = array_merge($flattened_array, array(($key_prefix ? $key_prefix."[" : "").$key.($key_prefix ? "]" : "") => $value));
                }
            }
            if ($key_prefix) {
                return $flattened_array;
            } else {
                $query_array = array();
                foreach ($flattened_array as $key => $value) {
                    $query_array[] = urlencode($key)."=".urlencode(LanguageUtil::to_string($value, false, false));
                }
                return implode("&", $query_array);
            }
        }

        public static function unflatten(array $array, $delimiter) {
            $unflattened_array = array();
            foreach ($array as $key => $value) {
                $key_list = explode($delimiter, $key);
                $first_key = array_shift($key_list);
                if (sizeof($key_list) > 0) { //does it go deeper, or was that the last key?
                    $subarray = ArrayUtil::unflatten(array(implode($delimiter, $key_list) => $value), $delimiter);
                    foreach ($subarray as $subarray_key => $subarray_value) {
                        $unflattened_array[$first_key][$subarray_key] = $subarray_value;
                    }
                } else {
                    $unflattened_array[$first_key] = $value;
                }
            }
            return $unflattened_array;
        }

        //Given an array of objects, returns the same array with keys equal to field indicated
        public static function key_by_object_field(array $objects_array, $field) {
            $keyed_array = array();
            if ($objects_array) {
                foreach ($objects_array as $object) {
                    $keyed_array[LanguageUtil::to_string($object->$field, true)] = $object;
                }
            }
            return $keyed_array;
        }

        public static function key_by_array_field(array $array_array, $field) {
            $keyed_array = array();
            if ($array_array) {
                foreach ($array_array as $array) {
                    $keyed_array[LanguageUtil::to_string($array[$field], true)] = $array;
                }
            }
            return $keyed_array;
        }

        public static function extract_object_field(array $objects_array, $field, $unique = false) {
            $field_array = array();
            if ($objects_array) {
                foreach ($objects_array as $key => $object) {
                    $field_array[$key] = $object->$field;
                }
            }
            if ($unique) {
                $field_array = array_unique($field_array);
            }
            return $field_array;
        }

        public static function group_by_value(array $array) {
            $grouped_array = array();
            if ($array) {
                foreach ($array as $key => $value) {
                    $grouped_array[LanguageUtil::to_string($value, true)][] = $key;
                }
            }
            return $grouped_array;
        }

        public static function group_by_object_field(array $objects_array, $group_by_field) {
            $grouped_array = array();
            if ($objects_array) {
                foreach ($objects_array as $key => $object) {
                    $grouped_array[LanguageUtil::to_string($object->$group_by_field, true)][$key] = $object;
                }
            }
            return $grouped_array;
        }

        public static function group_by_array_key(array $array_array, $group_by_key) {
            $grouped_array = array();
            if ($array_array) {
                foreach ($array_array as $key => $array) {
                    $grouped_array[LanguageUtil::to_string($array[$group_by_key], true)][$key] = $array;
                }
            }
            return $grouped_array;
        }

        /**
         * @param array      $values          An array of initial values to be weighted.  Keys will be preserved for the output array.
         * @param array      $weights         An array of weights to give each of the values.  Weights must be non-negative.  Weights are relative to one another, any weight array will be equivalent to itself multiplied by any non-zero scalar.
         * @param float|null $round_precision If specified, all values in the output array will be rounded to the nearest multiple of $round_precision.  If rounding of the values would produce values which had a total different than the sum of the unrounded values, then the difference is rounded to the precision specified, and that is distributed among the values in order of weight.
         *
         * @return array An array of weighted values with the keys from the input $values array.
         * @throws Exception if the size of $values and $weights arrays are not equal or the weights array contains a negative or non-numeric value.
         */
        public static function weighted_values(array $values, array $weights, $round_precision = null) {
            if (sizeof($values) != sizeof($weights)) {
                throw new Exception("The size of the value and weight arrays must be equal.");
            }
            if (!array_reduce($weights, function ($non_negative_number, $value) {
                    return $non_negative_number && is_numeric($value) && $value >= 0;
                }, true)
            ) {
                throw new Exception("The weights array must contain only non-negative numbers.");
            }
            $weights = array_combine(array_keys($values), $weights); //set the keys of the $weights array to those of the $values array

            $total_weight = array_sum($weights);
            if (!$total_weight) {
                //if the total weight is 0, return the $values array with each element set to a value of 0;
                return array_combine(array_keys($values), array_fill(0, sizeof($values), 0));
            }

            $weighted_array = array();
            $raw_weighted_array_sum = 0;
            if ($values) {
                foreach ($values as $key => $value) {
                    $weighted_value = $value*$weights[$key]/$total_weight;
                    if ($round_precision === null) {
                        $weighted_array[$key] = $weighted_value;
                    } else {
                        $rounded_weighted_value = NumberUtil::round($weighted_value, $round_precision);
                        $weighted_array[$key] = $rounded_weighted_value;
                        $raw_weighted_array_sum += $weighted_value;
                    }
                }
                if ($round_precision !== null) { //if we were rounding, then we need to make sure the sum is correct.
                    $total_weighted_values = array_sum($weighted_array);
                    $unallocated = $raw_weighted_array_sum-$total_weighted_values;
                    $rounded_unallocated = NumberUtil::round($unallocated, $round_precision);
                    if ($rounded_unallocated) {
                        arsort($weights); //sort the weights array by size descending maintaining keys
                        $allocation_size = $round_precision*($rounded_unallocated > 0 ? 1 : -1);
                        if ($rounded_unallocated) {
                            foreach ($weights as $key => $weight) {
                                $weighted_array[$key] += $allocation_size;
                                $rounded_unallocated -= $allocation_size;
                                if (!$rounded_unallocated) {
                                    break; //stop as soon as all the leftover amount has all been allocated.
                                }
                            }
                        }
                    }
                }
            }
            return $weighted_array;
        }

        //Unsets any array keys whose value, when run through the callback function, does not return true, similar to php's array_filter function, but recursive.  Arrays inside the input array are filtered out BEFORE their internal elements are checked.
        public static function filter_recursive(array $array, callable $callback, $remove_empty_arrays = true) {
            if ($array) {
                foreach ($array as $key => $value) {
                    if (!call_user_func($callback, $value)) {
                        unset($array[$key]);
                    } else {
                        if (is_array($value)) {
                            $array[$key] = self::filter_recursive($value, $callback, $remove_empty_arrays);
                            if ($remove_empty_arrays) {
                                if ($array[$key] === array()) {
                                    unset($array[$key]);
                                }
                            }
                        }
                    }
                }
            }
            return $array;
        }

        public static function shuffle_assoc(array $array) {
            if(!$array || !is_array($array)) {
                return false;
            }
            $keys = array_keys($array);
            shuffle($keys);
            $random = array();
            foreach($keys as $key) {
                $random[$key] = $array[$key];
            }
            return $random;
        }

        public static function shuffle_assoc_ref(array &$array) {
            if(!$array || !is_array($array)) {
                return false;
            }
            $keys = array_keys($array);
            shuffle($keys);
            $new = array();
            foreach($keys as $key) {
                $new[$key] = $array[$key];
            }
            $array = $new;
            return true;
        }

        //Given an array of objects, sorts them by the field indicated in $sort_by
        public static function sort_objects(array &$objects_array, $sort_by, $sort_order = "desc") {
            $sort_order = strtolower($sort_order);
            uasort($objects_array, function ($a, $b) use ($sort_by, $sort_order) {
                    if ($a->$sort_by == $b->$sort_by) {
                        return 0;
                    } else {
                        $comparison = $a->$sort_by > $b->$sort_by;
                        if ($sort_order == "desc") {
                            return $comparison ? -1 : 1;
                        } else {
                            return $comparison ? 1 : -1;
                        }
                    }
                });
        }

        public static function filter(array $array, $where_clause) {
            if(!$array) {
                return array();
            }
            $optional_preamble_regex = "\\s*(?i:WHERE\\s*)?";
            $boolean_operators_regex = "(?i:AND)"; //TODO: Adding support for OR would be awesome here.  Maybe at a later date
            $operators_list_regex = "=|!=|<=|>=|<|>|NOT IN|not in|IN|in|NOT LIKE|not like|LIKE|like|IS NOT|is not|IS|is"; //IS NOT needs to be listed before IS, <= and >= need to be listed before < and >
            $field_name_regex = "\\w+";
            $value_regex = "\\'.*?\\'|\\(.*?\\)|\\S*";
            $comparison_regex = "($field_name_regex)\\s*($operators_list_regex)\\s*($value_regex)";
            preg_match_all("/$optional_preamble_regex(?:\\s*($boolean_operators_regex)?\\s*$comparison_regex?)/", $where_clause, $comparisons, PREG_SET_ORDER);

            if ($comparisons) {
                foreach ($comparisons as $comparison) {
                    if ($array) { //stop immediately if the array gets filtered down to nothing.
                        $boolean = $comparison[1];
                        $field = $comparison[2];
                        $operator = strtoupper($comparison[3]);
                        $value = trim(trim($comparison[4]), "'\""); //trim spaces and quotes //TODO: right now this doesn't allow for allowing actual quotes at the start or end of values

                        //TODO: Add logic to make sure input array contains objects
                        //if the object doesn't have the field in question, then we just remove it from the array.  It can't match the criteria if it doesn't exist.
                        // Don't want to remove things with null if we're trying to filter on null objects
                        if (strtolower($value) !== "null") {
                            $array = array_filter($array, function ($object) use ($field) {
                                    return isset($object[$field]);
                                });
                        }
                        if ($array) { //the filtering above might have emptied the array.  Check again to make sure that it still has something in it.
                            $sample_value = ArrayUtil::pop($array)[$field];
                            if (is_a($sample_value, "DateTime")) { //check to see if it's an object first so it doesn't try to autoload primative values as though they were a class.
                                if ($value == "NOW()") {
                                    $value = new DateTime();
                                } else {
                                    try {
                                        $value = new DateTime($value);
                                    } catch (Exception $e) {
                                    }
                                }
                            }

                            switch ($operator) {
                                case "=":
                                    $array = array_filter($array, function ($object) use ($field, $value) {
                                            return $object[$field] == $value;
                                        });
                                    break;
                                case "!=":
                                    $array = array_filter($array, function ($object) use ($field, $value) {
                                            return $object[$field] != $value;
                                        });
                                    break;
                                case "<":
                                    $array = array_filter($array, function ($object) use ($field, $value) {
                                            return $object[$field] < $value;
                                        });
                                    break;
                                case ">":
                                    $array = array_filter($array, function ($object) use ($field, $value) {
                                            return $object[$field] > $value;
                                        });
                                    break;
                                case "<=":
                                    $array = array_filter($array, function ($object) use ($field, $value) {
                                            return $object[$field] <= $value;
                                        });
                                    break;
                                case ">=":
                                    $array = array_filter($array, function ($object) use ($field, $value) {
                                            return $object[$field] >= $value;
                                        });
                                    break;
                                case "in":
                                case "not in":
                                case "IN":
                                case "NOT IN":
                                    preg_match_all("/(?:^\\s*\\()?\\s*(?|[\"'](.*?)[\"']|([^,]*?))\\s*(?:,|\\)\\s*$)/", $value, $values);
                                    $values = $values[1]; //ignore the full regex captures, only get the values from the capturing subgroup of interest
                                    $array = array_filter($array, function ($object) use ($field, $values, $operator) {
                                            $in = in_array($object[$field], $values);
                                            return !($operator == "IN" xor $in);
                                        });
                                    break;
                                case "like":
                                case "not like":
                                case "LIKE":
                                case "NOT LIKE":
                                    $value = str_replace("%", ".*?", preg_quote($value, "/"));
                                    $array = array_filter($array, function ($object) use ($field, $value, $operator) {
                                            $like = preg_match("/$value/", $object[$field]);
                                            return !($operator == "LIKE" xor $like);
                                        });
                                    break;
                                case "is":
                                case "is not":
                                case "IS":
                                case "IS NOT":
                                    $array = array_filter($array, function ($object) use ($field, $value, $operator) {
                                            $value = strtolower($value);
                                            switch ($value) {
                                                case "array":
                                                case "bool":
                                                case "callable":
                                                case "double":
                                                case "float":
                                                case "int":
                                                case "integer":
                                                case "long":
                                                case "null":
                                                case "numeric":
                                                case "object":
                                                case "real":
                                                case "resource":
                                                case "scalar":
                                                case "string":
                                                    $is = call_user_func("is_".$value, $object[$field]);
                                                    break;
                                                case "true":
                                                    $is = (bool)$object[$field];
                                                    break;
                                                case "false":
                                                    $is = !(bool)$object[field];
                                                    break;
                                                case "set":
                                                    $is = isset($object[$field]);
                                                    break;
                                                case "empty":
                                                    $is = empty($object[$field]);
                                                    break;
                                                default:
                                                    return false;
                                            }
                                            return !($operator == "IS" xor $is);
                                        });
                                    break;
                                default:
                                    return false;
                            }
                        }
                    }
                }
            }
            return $array;

        }

        //TODO: Add a buttload of output messages for all the ways this could error so you know what you did wrong
        //filters an array of objects based on values of $object->$filter_field.  Will only retain an object if
        public static function filter_objects(array $objects_array, $where_clause) {
            if (!$objects_array) {
                return array(); //empty arrays should immediately exit
            }
            $optional_preamble_regex = "\\s*(?i:WHERE\\s*)?";
            $boolean_operators_regex = "(?i:AND)"; //TODO: Adding support for OR would be awesome here.  Maybe at a later date
            $operators_list_regex = "=|!=|<=|>=|<|>|NOT IN|not in|IN|in|NOT LIKE|not like|LIKE|like|IS NOT|is not|IS|is"; //IS NOT needs to be listed before IS, <= and >= need to be listed before < and >
            $field_name_regex = "\\w+";
            $value_regex = "\\'.*?\\'|\\(.*?\\)|\\S*";
            $comparison_regex = "($field_name_regex)\\s*($operators_list_regex)\\s*($value_regex)";
            preg_match_all("/$optional_preamble_regex(?:\\s*($boolean_operators_regex)?\\s*$comparison_regex?)/", $where_clause, $comparisons, PREG_SET_ORDER);

            if ($comparisons) {
                foreach ($comparisons as $comparison) {
                    if ($objects_array) { //stop immediately if the array gets filtered down to nothing.
                        $boolean = $comparison[1];
                        $field = $comparison[2];
                        $operator = strtoupper($comparison[3]);
                        $value = trim(trim($comparison[4]), "'\""); //trim spaces and quotes //TODO: right now this doesn't allow for allowing actual quotes at the start or end of values

                        //TODO: Add logic to make sure input array contains objects
                        //if the object doesn't have the field in question, then we just remove it from the array.  It can't match the criteria if it doesn't exist.
                        // Don't want to remove things with null if we're trying to filter on null objects
                        if (strtolower($value) !== "null") {
                            $objects_array = array_filter($objects_array, function ($object) use ($field) {
                                    return isset($object->$field);
                                });
                        }
                        if ($objects_array) { //the filtering above might have emptied the array.  Check again to make sure that it still has something in it.
                            $sample_value = ArrayUtil::pop($objects_array)->$field;
                            if (is_object($sample_value) && is_a($sample_value, "DateTime")) { //check to see if it's an object first so it doesn't try to autoload primative values as though they were a class.
                                if ($value == "NOW()") {
                                    $value = new DateTime();
                                } else {
                                    try {
                                        $value = new DateTime($value);
                                    } catch (Exception $e) {
                                    }
                                }
                            }

                            switch ($operator) {
                                case "=":
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value) {
                                            return $object->$field == $value;
                                        });
                                    break;
                                case "!=":
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value) {
                                            return $object->$field != $value;
                                        });
                                    break;
                                case "<":
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value) {
                                            return $object->$field < $value;
                                        });
                                    break;
                                case ">":
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value) {
                                            return $object->$field > $value;
                                        });
                                    break;
                                case "<=":
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value) {
                                            return $object->$field <= $value;
                                        });
                                    break;
                                case ">=":
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value) {
                                            return $object->$field >= $value;
                                        });
                                    break;
                                case "in":
                                case "not in":
                                case "IN":
                                case "NOT IN":
                                    preg_match_all("/(?:^\\s*\\()?\\s*(?|[\"'](.*?)[\"']|([^,]*?))\\s*(?:,|\\)\\s*$)/", $value, $values);
                                    $values = $values[1]; //ignore the full regex captures, only get the values from the capturing subgroup of interest
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $values, $operator) {
                                            $in = in_array($object->$field, $values);
                                            return !($operator == "IN" xor $in);
                                        });
                                    break;
                                case "like":
                                case "not like":
                                case "LIKE":
                                case "NOT LIKE":
                                    $value = str_replace("%", ".*?", preg_quote($value, "/"));
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value, $operator) {
                                            $like = preg_match("/$value/", $object->$field);
                                            return !($operator == "LIKE" xor $like);
                                        });
                                    break;
                                case "is":
                                case "is not":
                                case "IS":
                                case "IS NOT":
                                    $objects_array = array_filter($objects_array, function ($object) use ($field, $value, $operator) {
                                            $value = strtolower($value);
                                            switch ($value) {
                                                case "array":
                                                case "bool":
                                                case "callable":
                                                case "double":
                                                case "float":
                                                case "int":
                                                case "integer":
                                                case "long":
                                                case "null":
                                                case "numeric":
                                                case "object":
                                                case "real":
                                                case "resource":
                                                case "scalar":
                                                case "string":
                                                    $is = call_user_func("is_".$value, $object->$field);
                                                    break;
                                                case "true":
                                                    $is = (bool)$object->$field;
                                                    break;
                                                case "false":
                                                    $is = !(bool)$object->$field;
                                                    break;
                                                case "set":
                                                    $is = isset($object->$field);
                                                    break;
                                                case "empty":
                                                    $is = empty($object->$field);
                                                    break;
                                                default:
                                                    return false;
                                            }
                                            return !($operator == "IS" xor $is);
                                        });
                                    break;
                                default:
                                    return false;
                            }
                        }
                    }
                }
            }
            return $objects_array;

        }

        public static function random(array $data) {
            return $data[array_rand($data)];
        }
    }