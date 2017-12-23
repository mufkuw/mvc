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
use Mvc\Cookie;

//  Event auth.login with paramameter names to process $params, $username, $password, $remember_login

class AuthController extends Controller {

	public function actionLogin($referer) {
		Hook::register("setting.media.layout", function($pMedia) {
			$pMedia->addJs('ajax_forms');
		});
		$system_referer		 = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : FALSE;
		$referer			 = isset($referer) && $referer != '' ? $referer : false;
		$this->view->referer = $referer ? $referer : $system_referer ? $system_referer : null;
		$this->viewLayout();
	}

	public function ajaxLogin($params) {
		$login_hook_result = Hook::execute('auth.login', $params, function($sucess, $hook_results) {
					if (!$hook_results[0]['result']['error']) {
						Cookie::instance()->auth	 = true;
						Cookie::instance()->token	 = $hook_results[0]['result']['token'];
						return true;
					} else {
						return false;
					}
				});

		if ($login_hook_result) {
			$this->alert('success', 'Success', "Redirecting...");
		} else {
			$this->alert('error', 'Error', "Invalid username or password");
		}
	}

}
