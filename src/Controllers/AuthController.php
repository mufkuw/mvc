<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mvc\Controllers;

use Mvc\Controller;
use Mvc\Theme;
use Mvc\Hook;

class AuthController extends Controller {
	/*
	  Event auth.login with paramameter names to process $params, $username, $password, $remember_login
	 */

	public function actionLogin($params, $username, $password, $remember_login) {
		if ($params['request_method'] == 'GET') {
			$this->viewLayout('login');
		} else {
			$is_login = Hook::execute('auth.login', $params, function($sucess, $hook_results) {
						return $hook_results[0]['result'];
					});

			if ($is_login) {
				/* success fully loged in in */
			} else {
				$this->alert('error', 'Invaid username or password');
				$this->viewLayout('login');
			}
		}
	}

}
