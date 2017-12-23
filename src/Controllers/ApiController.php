<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mvc\Controllers;

use Mvc\{
	Controller,
	Context
};

class ApiController extends Controller {

	public function action($params, $action, $action_id) {
		$api_controller	 = $action;
		$api_action		 = $action_id;

		//posible controllers namespace defination to purpose the search
		$search_path = [
			Context::instance()->setup['namespace'] . '\\Controllers', //search with App\Controllers
			Context::instance()->setup['namespace'], // search with App\
			'Mvc\\Controllers', //search with Mvc\Controllers
			'' //search directly without namespace
		];

		$success = false;

		foreach ($search_path as $namespace) {
			$params['controller']	 = $api_controller;
			$params['action']		 = 'x';



			$controller = $namespace . '\\' . ucwords($api_controller) . "Controller";
			if (class_exists($controller, true)) {
				$success	 = true;
				$controller	 = new $controller;
				invoke_function([$controller, 'actionX'], ['params' => $params]);
			}
		}
		if (!$success)
			die_page_not_found();
	}

}
