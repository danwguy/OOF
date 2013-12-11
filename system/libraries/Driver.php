<?php
/**
 * Created by JetBrains PhpStorm.
 * User: robert
 * Date: 10/10/13
 * Time: 8:13 AM
 * To change this template use File | Settings | File Templates.
 */

class Driver {

	protected $_parent;

	private $_methods = array();
	private $_props = array();

	private static $_reflections = array();

	public function decorate($parent) {
		$this->_parent = $parent;
		$class = get_class($parent);

		if(!isset(self::$_reflections[$class])) {
			$r = new ReflectionObject($parent);
			foreach($r->getMethods() as $method) {
				if($method->isPublic()) {
					$this->_methods[] = $method->getName();
				}
			}
			foreach($r->getProperties() as $prop) {
				if($prop->isPublic()) {
					$this->_props[] = $prop->getName();
				}
			}
			self::$_reflections[$class] = array($this->_methods, $this->_props);
		} else {
			list($this->_methods, $this->_props) = self::$_reflections[$class];
		}
	}

	public function __get($var) {
		if(in_array($var, $this->_props)) {
			return $this->_parent->$var;
		}
	}

	public function __call($method, $args = array()) {
		if(in_array($method, $this->_methods)) {
			return call_user_func_array(array($this->_parent, $method), $args);
		}
		$trace = debug_backtrace();
		ExceptionHandler::handle(E_ERROR, "no such method ".$method, $trace[1]['file'], $trace[1]['line']);
		exit;
	}

	public function __set($var, $val) {
		if(in_array($var, $this->_props)) {
			$this->_parent->$var = $val;
		}
	}

}