<?php

namespace Mvc;

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath($_SERVER['document_root'] . DS . __DIR__) . DS);

/**
 * Instantiate the MVC pattern
 *
 * Following $pSetup array elements expected as parameter
 * @param 'caches_path' => '',
 * @param 'controllers_path' => '',
 * @param 'themes_path' => '',
 * @param 'modules_path' => '',
 */
function mvc_init($pSetup) {

	require './src/Tools.php';

	$default_setup = [
		'paths' => [
			'cache' => 'cache',
			'controllers' => 'controllers',
			'themes' => 'themes',
			'modules' => 'modules'
		],
		'default_theme' => 'default'
	];

	$pSetup = array_merge($default_setup, $pSetup);

	foreach ($pSetup as $key => $value) {
		$path = $value;
		if (!file_exists(ROOT . $path)) {
			mkdir(ROOT . $path, 0777, true);
		}
	}
}
