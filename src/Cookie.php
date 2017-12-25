<?php

namespace Mvc;

define('COOKIE_PASS', '3+88KyHc?Pe@?WE');
define('COOKIE_KEY', 'ME&ss97+emn5WE-');
define('COOKIE_IV', 'km=7uRt4');

class Cookie {

	private $data		 = array();
	private $cookie_name = '';
	private $password	 = '&N&6C[q%';
	private static $blowfish;
	private static $instance;

	public function __construct($cookie_name) {
		$this->cookie_name	 = $cookie_name;
		self::$blowfish		 = new BlowFish(COOKIE_KEY, COOKIE_IV);
		$this->load($this->cookie_name);
	}

	public function __destruct() {
		$this->save($this->cookie_name);
	}

	public function set($prop, $value) {
		return $this->__set($prop, $value);
	}

	public function get($prop) {
		return $this->__get($prop);
	}

	public function __set($prop, $value) {
		$this->data[$prop] = $value;
	}

	public function __get($prop) {
		if (array_key_exists($prop, $this->data)) {
			return $this->data[$prop];
		} else
			return null;
	}

	public function __unset($prop) {
		unset($this->data[$prop]);
	}

	public function __isset($prop) {
		return isset($this->data[$prop]);
	}

	private function save($cookie_name) {
		$cookie = '';

		/* Serialize cookie content */
		if (isset($this->data['checksum'])) {
			unset($this->data['checksum']);
		}

		foreach ($this->data as $key => $value) {
			$cookie_value	 = '';
			if (is_array($value))
				$cookie_value	 = json_encode($value);
			else
				$cookie_value	 = $value;

			$cookie .= $key . '|' . $cookie_value . '�';
		}

		$cookie .= 'checksum|' . crc32(COOKIE_PASS . $cookie);


		if ($cookie) {
			$content = self::$blowfish->encrypt($cookie);
			$time	 = time() + 1728000;
		} else {
			$content = 0;
			$time	 = 1;
		}

		return setcookie($cookie_name, $content, $time, '/');
	}

	private function load($cookie_name) {

		if (isset($_COOKIE[$this->cookie_name])) {
			/* Decrypt cookie content */
			$content = self::$blowfish->decrypt($_COOKIE[$this->cookie_name]);

			//printf("\$content = %s<br />", $content);

			/* Get cookie checksum */
			$tmpTab					 = explode('�', $content);
			array_pop($tmpTab);
			$content_for_checksum	 = implode('�', $tmpTab) . '�';
			$checksum				 = crc32($this->password . $content_for_checksum);
			//printf("\$checksum = %s<br />", $checksum);

			/* Unserialize cookie content */
			$tmpTab = explode('�', $content);
			foreach ($tmpTab as $keyAndValue) {
				$tmpTab2 = explode('|', $keyAndValue);
				if (count($tmpTab2) == 2) {
					if (is_json($tmpTab2[1]))
						$this->data[$tmpTab2[0]] = json_decode($tmpTab2[1], true);
					else
						$this->data[$tmpTab2[0]] = $tmpTab2[1];
				}
			}
			/* Blowfish fix */
			if (isset($this->data['checksum'])) {
				$this->data['checksum'] = (int) ($this->data['checksum']);
			}
			//printf("\$this->_content['checksum'] = %s<br />", $this->_content['checksum']);
			//die();

			/* Check if cookie has not been modified */
			if (!isset($this->data['checksum']) || $this->data['checksum'] != $checksum) {
				//$this->logout();
			}

			if (!isset($this->data['date_add'])) {
				$this->data['date_add'] = date('Y-m-d H:i:s');
			}
		} else {
			$this->data['date_add'] = date('Y-m-d H:i:s');
		}
	}

	public function debug() {
		print_pre($this->data);
	}

	public static function instance($cookie_name = null) {
		if (!$cookie_name) {
			!$cookie_name = 'KyHc?Pe@?WE';
		}
		if (!self::$instance)
			self::$instance = new Cookie($cookie_name);

		return self::$instance;
	}

}
