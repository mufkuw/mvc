<?php

use Mvc\{
	Context,
	Router,
	Controller,
	Cookie,
	Theme,
	SmartyView
};

error_reporting(0);

register_shutdown_function(function() {

	$e = error_get_last();
	if ($e)
		echo'<pre>ERROR<BR>' . $e['message'] . ' < BR><BR>FILE<BR>' . $e['file'] . ' ( ' . $e['line'] . ' ) </pre>';
});

set_error_handler(function($errno, $errstr, $errfile, $errline, $class) {

	$error_names = [
		E_ERROR				 => 'E_ERROR'
		, E_WARNING			 => 'E_WARNING'
		, E_PARSE				 => 'E_PARSE'
		, E_NOTICE			 => 'E_NOTICE'
		, E_CORE_ERROR		 => 'E_CORE_ERROR'
		, E_CORE_WARNING		 => 'E_CORE_WARNING'
		, E_COMPILE_ERROR		 => 'E_COMPILE_ERROR'
		, E_COMPILE_WARNING	 => 'E_COMPILE_WARNING'
		, E_USER_ERROR		 => 'E_USER_ERROR'
		, E_USER_WARNING		 => 'E_USER_WARNING'
		, E_USER_NOTICE		 => 'E_USER_NOTICE'
		, E_STRICT			 => 'E_STRICT'
		, E_RECOVERABLE_ERROR	 => 'E_RECOVERABLE_ERROR'
		, E_DEPRECATED		 => 'E_DEPRECATED'
		, E_USER_DEPRECATED	 => 'E_USER_DEPRECATED'
	];



	$ignore_errors = [];

	if (!DEBUG) {
		$ignore_errors = [E_WARNING, E_NOTICE, E_CORE_WARNING, E_USER_WARNING, E_USER_NOTICE, E_RECOVERABLE_ERROR, E_DEPRECATED, E_USER_DEPRECATED];
	}

	if (!in_array($errno, $ignore_errors, true))
		echo'<pre>' . $error_names[$errno] . '<BR>' . $errstr . '<BR><BR>FILE<BR>' . $errfile . '(' . $errline . ')</pre>';

	return true;
});


if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

if (!defined('ROOT'))
	define('ROOT', realpath($_SERVER['DOCUMENT_ROOT']) . DS);
/*
  define('MVC_ROOT', __DIR__ . DS);
  define('MVC_CONTROLERS', MVC_ROOT . 'controllers' . DS);
  define('MVC_TEMPLATES', MVC_ROOT . 'templates' . DS);
  define('MVC_MEDIA', MVC_ROOT . 'media' . DS);

  define('MVC_TEMPLATES_EXT', '.html');


  if (!defined('DEBUG'))
  define('DEBUG ', 1);
 */
require 'config_tools.php';

/**
 * Instantiate the MVC pattern
 *
 * Following $pSetup array elements expected as parameter
 * @param 'namespace' => 'App',
 * @param 'cache_path' => 'cache',
 * @param 'controllers_path' => 'controllers',
 * @param 'themes_path' => 'themes',
 * @param 'modules_path' => 'modules',
 * @param 'default_theme' => 'default',
 * @param 'cookie_name' => 'your_choice_name',
 * @param 'routes' => '[
 * 'name' => 'routename',
 * 'pattern' => 'regex',
 * 'defaults' => [
 * controller => ''
 * action => ''
 * action_id => ''
 * module => ''
 * module_controller => ''
 * module_action => ''
 * module_action_id => ''
 * ],
 * ]',
 */
function mvc_init($pSetup = []) {

	$default_setup = [
		'namespace'			 => 'App',
		'cache_path'		 => 'cache',
		'controllers_path'	 => 'controllers',
		'themes_path'		 => 'themes',
		'modules_path'		 => 'modules',
		'default_theme'		 => 'default',
		'auto_dispatch'		 => 0
	];

	$pSetup = array_merge($default_setup, $pSetup);

	foreach ($pSetup as $key => $valu e) {
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

	Context::instance()->search_sequence_controllers_namespaces[]	 = '\\Mvc\\Controllers';
	Context::instance()->search_sequence_templates[]				 = MVC_TEMPLATES;

	if (isset($pSetup['cookie_name'])) {
		Context::instance()->cookie = Cookie::instance($pSetup['cookie_name']);
	} else {
		Context::instance()->cookie = Cookie::instance();
	}

	if ($pSetup['auto_dispatch'] && boolval($pSetup['auto_dispatch

     '])) {
		Router::disptach();
	}
}
