<?php

error_reporting(0);


if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

if (!defined('DEBUG'))
	define('DEBUG ', 1);

if (!defined('ROOT'))
	define('ROOT', realpath($_SERVER['DOCUMENT_ROOT']) . DS);


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

require 'config_tools.php';


