<?php

namespace Mvc;

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

if (!defined('ROOT'))
	define('ROOT', realpath($_SERVER['DOCUMENT_ROOT']) . DS);

if (!defined('DEBUG'))
	define('DEBUG', 1);

error_reporting(0);

register_shutdown_function(function() {
	$e = error_get_last();
	if ($e)
		echo'<pre>ERROR<BR>' . $e['message'] . '<BR><BR>FILE<BR>' . $e['file'] . '(' . $e['line'] . ')</pre>';
});

set_error_handler(function($errno, $errstr, $errfile, $errline, $class) {

	$error_names = [
		E_ERROR => 'E_ERROR'
		, E_WARNING => 'E_WARNING'
		, E_PARSE => 'E_PARSE'
		, E_NOTICE => 'E_NOTICE'
		, E_CORE_ERROR => 'E_CORE_ERROR'
		, E_CORE_WARNING => 'E_CORE_WARNING'
		, E_COMPILE_ERROR => 'E_COMPILE_ERROR'
		, E_COMPILE_WARNING => 'E_COMPILE_WARNING'
		, E_USER_ERROR => 'E_USER_ERROR'
		, E_USER_WARNING => 'E_USER_WARNING'
		, E_USER_NOTICE => 'E_USER_NOTICE'
		, E_STRICT => 'E_STRICT'
		, E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR'
		, E_DEPRECATED => 'E_DEPRECATED'
		, E_USER_DEPRECATED => 'E_USER_DEPRECATED'
	];



	$ignore_errors = [];

	if (!DEBUG) {
		$ignore_errors = [E_WARNING, E_NOTICE, E_CORE_WARNING, E_USER_WARNING, E_USER_NOTICE, E_RECOVERABLE_ERROR, E_DEPRECATED, E_USER_DEPRECATED];
	}

	if (!in_array($errno, $ignore_errors, true))
		echo'<pre>' . $error_names[$errno] . '<BR>' . $errstr . '<BR><BR>FILE<BR>' . $errfile . '(' . $errline . ')</pre>';

	return true;
});

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

/**
 * Instantiate the MVC pattern
 *
 * Following $pSetup array elements expected as parameter
 * @param 'caches_path' => '',
 * @param 'controllers_path' => '',
 * @param 'themes_path' => '',
 * @param 'modules_path' => '',
 * @param 'default_theme' => '',
 * @param 'routes' => '[
 * 		'name' => 'route_name',
 * 		'pattern' => 'regex',
 * 		'defaults' => array(of key value to be passed) THIS SETTING OPTIONAL,
 * 	]',
 */
function mvc_init($pSetup = []) {

	$default_setup = [
		'cache_path' => 'cache',
		'controllers_path' => 'controllers',
		'themes_path' => 'themes',
		'modules_path' => 'modules',
		'default_theme' => 'default'
	];

	$pSetup = array_merge($default_setup, $pSetup);

	foreach ($pSetup as $key => $value) {
		if (strpos($key, 'path') > 0) {
			$path = $value;
			if (!file_exists(ROOT . $path)) {
				mkdir(ROOT . $path, 0777, true);
			}
		}
	}

	$default_theme_path = ROOT . $pSetup['themes_path'] . DS . $pSetup['default_theme'];
	if (!file_exists($default_theme_path)) {
		mkdir($default_theme_path, 0777, true);
	}

	if (isset($pSetup['routes'])) {
		foreach ($pSetup['routes'] as $route) {
			if (isset($route['name']) && $route['pattern']) {
				$route_defaults = isset($route['defaults']) ? $route['defaults'] : null;
				Router::addRoute($route['name'], $route['pattern'], $route_defaults);
			}
		}
	}


	Context::instance()->setup = $pSetup;
	Context::instance()->route = Router::getRoute();
}
