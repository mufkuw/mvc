<?php

namespace Mvc;

function print_pre($var, $return = false) {
	$res = '<pre>' . print_r($var, true) . '</pre>';
	if ($return)
		return $res;
	else
		echo $res;
}

function utf8_encode_deep(&$input) {
	if (is_string($input)) {
		$input = iconv('Arabic_CI_AS', 'utf-8', ($input));
//$input = utf8_encode($input);
	} else if (is_array($input)) {
		foreach ($input as &$value) {
			utf8_encode_deep($value);
		}

		unset($value);
	} else if (is_object($input)) {
		$vars = array_keys(get_object_vars($input));

		foreach ($vars as $var) {
			utf8_encode_deep($input->$var);
		}
	}
}

function raw_json_encode($input, $flags = 0) {
	$fails = implode('|', array_filter(array(
		'\\\\',
		$flags & JSON_HEX_TAG ? 'u003[CE]' : '',
		$flags & JSON_HEX_AMP ? 'u0026' : '',
		$flags & JSON_HEX_APOS ? 'u0027' : '',
		$flags & JSON_HEX_QUOT ? 'u0022' : '',
	)));
	$pattern = "/\\\\(?:(?:$fails)(*SKIP)(*FAIL)|u([0-9a-fA-F]{4}))/";
	$callback = function ($m) {
		return html_entity_decode("&#x$m[1];", ENT_QUOTES, 'UTF-8');
	};
	return preg_replace_callback($pattern, $callback, json_encode($input, $flags));
}

function rdir($dir, $include_dir = false) {
	$results = array();
	if (!realpath($dir))
		return $results;
	$files = scandir($dir);
	foreach ($files as $key => $value) {
		$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
		if (!($value == '.' || $value == '..')) {
			if (!is_dir($path) || $include_dir)
				$results[] = ['dir' => realpath($dir), 'name' => $value];
			if (is_dir($path))
				$results = array_merge($results, rdir($path));
		}
	}
	return $results;
}

function camel_split($string) {
	if (!$string)
		return false;
	$s = preg_split('/(?=[A-Z]\w+)/', $string);
	return $s;
}

function camel_join($string, $glue, $include_first_element = true) {
	$array = camel_split($string);
	if (!$include_first_element)
		array_shift($array);
	return implode($array, $glue);
}

function camel_from_split($string, $delimiter = '_') {
	$splits = explode($delimiter, strtolower($string));

	$string = '';
	for ($i = 0; $i < count($splits); $i++) {
		if ($i == 0)
			$string .= $splits[$i];

		if ($i > 0 && $i < count($splits) - 1)
			$string .= ucwords($splits[$i]);

		if ($i + 1 == count($splits)) {
			if (strlen($splits[$i]) == 2)
				$string .= strtoupper($splits[$i]);
			else
				$string .= ucwords($splits[$i]);
		}
	}

	return $string;
}

function print_table($data, $return = false) {
	if (is_array($data) && count($data) > 0) {
		$s = "<table cellspacing='0px' cellpadding='10px' >";

		$columns = array_keys($data[0]);
		$rows = array_values($data);
		$s .= '<tr>';
		foreach ($columns as $column)
			$s .= "<td>$column</td>";


		$s .= '</tr>';


		foreach ($rows as $row) {
			$s .= '<tr>';
			foreach ($row as $col)
				$s .= "<td>$col</td>";
			$s .= '</tr>';
		}

		return print_pre($s, $return);
	}
}

function getMethodName($name, $prefix, $sufix) {
	$validName = ((strlen($name) > (strlen($prefix) + strlen($sufix))) && (substr($name, 0, strlen($prefix)) == $prefix) && (substr($name, -strlen($sufix)) == $sufix));
	return ($validName ? substr($name, strlen($prefix), -strlen($sufix)) : false );
}

function getMethodNameRegEX($name, $pPattern) {
	$matches = [];
	preg_match_all($pPattern, $name, $matches);

	if ($matches[0] && count($matches) > 1) {
		$arr = [];
		array_shift($matches);
		foreach ($matches as $match) {
			$arr[] = $match[0];
		}

		return $arr;
	} else
		return false;
}

function is_json($string) {
	return ((is_string($string) &&
			(is_object(json_decode($string)) ||
			is_array(json_decode($string))))) ? true : false;
}

class StopWatch {

	private static $startTimes = array();

	public static function start($timerName = 'default') {
		self::$startTimes[$timerName] = microtime(true);
	}

	public static function elapsed($timerName = 'default') {
		return microtime(true) - self::$startTimes[$timerName];
	}

	public static function elapsedSpan($timerName = 'default') {
		$r = self::elapsed($timerName);
		self::$startTimes[$timerName] = microtime(true);
		return number_format($r * 1000, 0) . 'ms';
	}

}

function isenglish($string) {
	$re = '/([A-Za-z0-9].*)/';
	preg_match_all($re, $string, $matches);
	if ($matches[0])
		return true;
	else
		return false;
}

function file_get_php_classes($filepath) {
	$php_code = file_get_contents($filepath);
	$classes = get_php_classes($php_code);
	return $classes;
}

function get_php_classes($php_code) {
	$classes = array();
	$tokens = token_get_all($php_code);
	$count = count($tokens);
	for ($i = 2; $i < $count; $i++) {
		if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
			$class_name = $tokens[$i][1];
			$classes[] = $class_name;
		}
	}
	return $classes;
}

function convert_numbers_units($str) {

	$ret = ['value' => 1, 'unit' => ''];

	$re = '/(?:^[\d.,]+)|(?:\w+)/';
	preg_match_all($re, $str, $matches);
	if (isset($matches[0]) && isset($matches[0][0])) {
		if (is_numeric($matches[0][0])) {
			$ret['value'] = $matches[0][0];
		} else {
			$ret['unit'] = $matches[0][0];
			$ret['value'] = 0;
		}

		if (isset($matches[0][1])) {
			$ret['unit'] = $matches[0][1];
		}
	}
	return $ret;
}

function condition_or($variable, ...$values) {
	$return = false;
	foreach ($values as $value) {
		$return = $return || ($variable == $value);
		if ($return)
			break;
	}
	return $return;
}

function condition_and($variable, ...$values) {
	$return = true;
	foreach ($values as $value) {
		$return = $return && ($variable == $value);
		if (!$return)
			break;
	}
	return $return;
}

function invoke_function($pCallable, $pArgs = null) {
	$params = [];
	if (is_array($pCallable))
		$params = (new \ReflectionMethod($pCallable[0], $pCallable[1]))->getParameters();
	else if (is_a($pCallable, 'Closure'))
		$params = (new \ReflectionFunction($pCallable))->getParameters();

	$receiving_array = [];

//arranging param array as per receving parameters by the function
	foreach ($params as $param)
		$receiving_array[$param->getName()] = &$pArgs[$param->getName()];

//adding remaining params pased by the call but not recevied
	foreach ($pArgs as $key => $arg)
		if (!array_key_exists($key, $receiving_array))
			$receiving_array[$key] = &$arg;

	return call_user_func_array($pCallable, $receiving_array);
}

function die_page_not_found() {
	header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
	die();
}

/*
 * Translator Provider
 */

function l($string) {
	return $string;
}
