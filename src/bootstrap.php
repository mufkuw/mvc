<?php

define('MVC_ROOT', __DIR__ . DS);
define('MVC_CONFIG', MVC_ROOT . 'config' . DS);
define('MVC_CONTROLLERS', MVC_ROOT . 'controllers' . DS);
define('MVC_CLASSES', MVC_ROOT . 'classes' . DS);
define('MVC_FOUNDATION_CLASS', MVC_CLASSES . 'Foundation.php');
define('MVC_TEMPLATES', MVC_ROOT . 'templates' . DS);
define('MVC_MEDIA', MVC_ROOT . 'media' . DS);
define('MVC_TEMPLATES_EXT', '.html');

require MVC_CONFIG . "startup_scripts.php";
require_once MVC_FOUNDATION_CLASS;

//preloading classes
(function() {
	$json_file	 = MVC_CONFIG . 'class_index.json';
	$classes	 = json_read($json_file);
	if (!$classes) {
		$classes = preload(MVC_CLASSES, MVC_CONTROLLERS);
		json_write($classes, $json_file);
	} else {
		foreach ($classes as $class) {
			require_once $class;
		}
	}
})();

use Mvc\{
	Context,
	Router,
	Controller,
	Cookie,
	Theme,
	SmartyView
};

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

	Context::instance()->search_sequence_controllers_namespaces[]	 = '\\Mvc\\Controllers';
	Context::instance()->search_sequence_templates[]				 = MVC_TEMPLATES;

	if (isset($pSetup['cookie_name'])) {
		Context::instance()->cookie = Cookie::instance($pSetup['cookie_name']);
	} else {
		Context::instance()->cookie = Cookie::instance();
	}

	if ($pSetup['auto_dispatch'] && boolval($pSetup['auto_dispatch'])) {
		Router::disptach();
	}
}
