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

require 'config_tools.php';

/**
 * Instantiate the MVC pattern
 *
 * Following $pSetup array elements expected as parameter
 * @param 'caches_path' => '',
 * @param 'controllers_path' => '',
 * @param 'themes_path' => '',
 * @param 'modules_path' => '',
 * @param 'default_theme' => '',
 * @param 'cookie_name' => '',
 * @param 'routes' => '[
 * 		'name' => 'route_name',
 * 		'pattern' => 'regex',
 * 		'defaults' => array(of key value to be passed) THIS SETTING OPTIONAL,
 * 	]',
 */
function mvc_init($pSetup = []) {

	$default_setup = [
		'namespace' => 'App',
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

	if (isset($pSetup['cookie_name'])) {
		Context::instance()->cookie = new Cookie($pSetup['cookie_name']);
	}

	Context::instance()->theme = Theme::instance();

	Context::instance()->view = SmartyView::instance();

	//Controller::execute(Router::getRoute());

	require 'config_smarty.php';
}
