<?php

namespace Mvc;

class Foundation {

	private static $instances = [];
	private $internal_data = [];

	public function __construct() {

	}

	public static function instance() {
		$class = get_called_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class;
		}
		return self::$instances[$class];
	}

	public function __set($name, $value) {
		$this->internal_data[$name] = $value;
	}

	public function __get($name) {
		if (isset($this->internal_data[$name]))
			return $this->internal_data[$name];
		else
			return FALSE;
	}

}
