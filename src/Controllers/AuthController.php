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
		Hook::register("setting.media.layout", function($pMedia) {
			$pMedia->addJs('ajax_forms');
		});
		$this->viewLayout();
	}

	public function ajaxLogin($params) {
		$login_hook_result = Hook::execute('auth.login', $params, function($sucess, $hook_results) {

				});



		if ($login_hook_result) {
			/* success fully loged in in */
		} else {
			$this->alert('error', 'Error', "Invalid username or password");
		}
	}

}
