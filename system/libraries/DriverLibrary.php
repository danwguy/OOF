<?php


    class DriverLibrary {


        protected $_valid_drivers = array();
        protected $_lib_name;

        function __get($child) {
            if(!isset($this->_lib_name)) {
                $this->_lib_name = get_class($this);
            }
            $child_class = $this->_lib_name . ucfirst($child);

            $lib_name    = ucfirst($this->_lib_name);
            $driver_name = strtolower($child_class);
            $controller  = Loader::load('Controller', 'core');
            if(in_array($driver_name, array_map('strtolower', $this->_valid_drivers))) {
                if(!class_exists($child_class)) {
                    foreach($controller->load->get_package_paths(true) as $path) {
                        foreach(array(ucfirst($driver_name), $driver_name) as $class) {
                            $file_path = $path . 'libraries/' . $lib_name . '/drivers/' . $class . '.php';
                            if(file_exists($file_path)) {
                                include_once($file_path);
                                break;
                            }
                        }
                    }
                    if(!class_exists($child_class)) {
                        OOF::show_error("Unable to load the requested driver: " . $child_class);
                    }
                }
                $obj = new $child_class;
                $obj->decorate($this);
                $this->$child = $obj;

                return $this->$child;
            }
            OOF::show_error("Invalid driver request: " . $child_class);
        }

    }