<?php

namespace Mvc;

class Router {

	private static $routes = array();

	private static function route($name, $pattern, $defaults = NULL) {
		$matches	 = null;
		$returnValue = preg_match_all('\'\\{(.*?)\\}\'', $pattern, $matches);
		$count		 = null;
		$returnValue = preg_replace('\'\\{(.*?)\\}\'', '(\\w+)', $pattern, -1, $count);

		//$pattern = $returnValue ;
		$pattern = $returnValue;
		$pattern = str_replace('/', '\/', $pattern);
		$pattern = str_replace('.', '\.', $pattern);

		self::$routes[$name] = ['pattern' => $pattern, 'keys' => $matches[1], 'defaults' => $defaults ? $defaults : []];
	}

	private static function parseroute($uri = NULL, $route_keys = null) {
		$uri = urldecode($uri);
		if (strpos($uri, '?') > 0) {
			$re = "/([\\w\\.]+)[\\?\\/]|(\\w+)\\=([^&]*)/";

			preg_match_all($re, $uri, $matches);

			//print_pre($matches);

			$route_values = array_values($matches[1]);

			//array combine

			$length = count($route_keys) > count($route_values) ? count($route_keys) : count($route_values);

			for ($i = 0; $i < $length; $i++) {
				if (isset($route_keys[$i]))
					$route_array[$route_keys[$i]]	 = isset($route_values[$i]) ? $route_values[$i] : '';
				else
					$route_array[]					 = isset($route_values[$i]) ? $route_values[$i] : '';
			}

			$route = array_slice($route_array, 0, count($route_keys));

			$route_params = array_slice($route_array, count($route_keys));

			$param_keys		 = array_values(($matches[2]));
			$param_values	 = array_values(($matches[3]));

			$param = array_combine($param_keys, $param_values);

			$param = array_merge($route_params, $param);

			$route['params'] = $param;

			return $route;
		}
		else {
			$re = "/([^\\/^?]+)/";
			preg_match_all($re, $uri, $matches);

			$route_values = $matches[1];

			$length = count($route_keys) > count($route_values) ? count($route_keys) : count($route_values);

			for ($i = 0; $i < $length; $i++) {
				if (isset($route_keys[$i]))
					$route_array[$route_keys[$i]]	 = isset($route_values[$i]) ? $route_values[$i] : '';
				else
					$route_array[]					 = isset($route_values[$i]) ? $route_values[$i] : '';
			}

			$route			 = array_slice($route_array, 0, count($route_keys));
			$route_params	 = array_slice($route_array, count($route_keys));

			$route['params'] = $route_params;

			return $route;
		}
	}

	public static function getRoute($url = null) {

		if (!$url)
			$url = substr($_SERVER['REQUEST_URI'], 1);

		Router::route('auto_default_module_3', 'mod{module}/{module_controller}/{module_action}/{module_action_id}', ['controller' => 'module', 'action' => 'index', 'module_action' => 'index']);
		Router::route('auto_default_module_2', 'mod{module}/{module_controller}/{module_action}', ['controller' => 'module', 'action' => 'index', 'module_action' => 'index']);
		Router::route('auto_default_module_1', 'mod{module}/{module_controller}', ['controller' => 'module', 'action' => 'index', 'module_action' => 'index']);
		Router::route('auto_default_module_0', 'mod{module}', ['controller' => 'module', 'action' => 'index', 'module_action' => 'index']);
		Router::route('auto_default_3', '{controller}/{action}/{action_id}', ['controller' => 'home', 'action' => 'index']);
		Router::route('auto_default_2', '{controller}/{action}', ['controller' => 'home', 'action' => 'index']);
		Router::route('auto_default_1', '{controller}', ['controller' => 'home', 'action' => 'index']);
		Router::route('auto_default_0', '', ['controller' => 'home', 'action' => 'index']);

		foreach (self::$routes as $name => $options) {

			$re = "/^" . $options['pattern'] . '/';

			if (preg_match($re, explode('?', $url)[0], $params)) {


				array_shift($params);

				$options_keys = array_unique(array_merge($options['keys'], array_keys($options['defaults'])));

				$route = self::parseroute($url, $options_keys);

				$params = array_combine($options['keys'], $params);

				$params_defaults = array_merge($params, $options['defaults']);

				$params = array_merge($params_defaults, $params);

				if (array_key_exists('controller', $params) && array_key_exists('action', $params)) {
					$controller_class	 = $params['controller'];
					$action				 = $params['action'];

					$route_params = $params;

					unset($route_params['controller']);
					unset($route_params['action']);

					$route_params['request_method'] = $_SERVER['REQUEST_METHOD'];

					$params['params'] = array_merge($route_params, $route['params']);

					return $params;
				}
			}
		}

		if ($route && array_key_exists('controller', $route) && array_key_exists('action', $route)) {
			$controller_class	 = $route['controller'];
			$action				 = $route['action'];
			return $route;
		}

		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
		die(0);
	}

	public static function addRoute($name, $pattern, $defaults = NULL) {
		self::route($name, $pattern, $defaults);
	}

	public static function dispatch($route = null) {
		if (!$route)
			$route = self::getRoute();
		Controller::execute($route);
	}

}
