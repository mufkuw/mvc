<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mvc\Controllers;

use Mvc\{
	Controller,
	Theme,
	Hook,
	Cookie
};

/**
 * Authentication Controller
 *
 * @hook 'auth.login' ($username,$password,$remember_login,$area)=>($token,$user_full_name,$user_id)
 * @hook 'auth.signup' ($username,$password,$user_full_name,$area)=>($signup_success)
 *
 */
class AuthController extends Controller {

	/**
	 *
	 * @param type $referer where to redirect after login
	 * @param type $area login to the which area ex members area, vendors area, admin, area, customers area
	 *
	 */
	public function actionLogin($referer, $area) {

		Hook::register("setting.media.layout", function($pMedia) {
			$pMedia->addJs('ajax_forms');
		});

		$system_area	 = Cookie::instance()->area;
		$system_referer	 = Cookie::instance()->referer;
		$referer		 = isset($referer) && $referer != '' ? $referer : false;
		$area			 = isset($area) && $area != '' ? $area : false;


		$this->view->referer = $referer ? $referer : ($system_referer ? $system_referer : null);
		$this->view->area	 = $area ? $area : ($system_area ? $system_area : null);

		if (!$this->view->area) {
			throw new InvalidAuthenicationAreaException();
			die_error(0);
		}

		$area_template = $this->getCurrentTemplate() . ($this->view->area ? '_' . $this->view->area : '');

		if (Theme::instance()->getTemplate($area_template, FALSE))
			$this->viewLayout($area_template); //$area_template);
		else
			$this->viewLayout();
	}

	public function ajaxLogin($params) {

		try {
			Hook::eventRequired('auth.login');
		} catch (\Exception $exc) {
			$this->alert('error', 'Error', $exc->getMessage());
		}

		$login_hook_result = Hook::execute('auth.login', $params, function($sucess, $hook_results) use ($params) {
					$form				 = $params['params'];
					$is_login_proccesed	 = true;
					$is_login_proccesed	 = $is_login_proccesed && count($hook_results) > 0;
					$is_login_proccesed	 = $is_login_proccesed && $hook_results[0]['result'] != null;
					$is_login_proccesed	 = $is_login_proccesed && $hook_results[0]['result']['error'] == null;

					if ($is_login_proccesed) {
						unset(Cookie::instance()->referer);
						unset(Cookie::instance()->area);
						$auth	 = Cookie::instance()->auth;
						if (!$auth)
							$auth	 = [];

						$auth[$form['area']] = [
							'token'		 => $hook_results[0]['result']['token'],
							'user_name'	 => $hook_results[0]['result']['user_full_name'],
							'user_id'	 => $hook_results[0]['result']['user_id']
						];

						Cookie::instance()->auth = $auth;

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

	public function actionSignUp($referer, $area) {

		Hook::register("setting.media.layout", function($pMedia) {
			$pMedia->addJs('ajax_forms');
		});

		$system_area	 = Cookie::instance()->area;
		$system_referer	 = Cookie::instance()->referer;
		$referer		 = isset($referer) && $referer != '' ? $referer : false;
		$area			 = isset($area) && $area != '' ? $area : false;


		$this->view->referer = $referer ? $referer : ($system_referer ? $system_referer : null);
		$this->view->area	 = $area ? $area : ($system_area ? $system_area : null);

		if (!$this->view->area) {
			throw new InvalidSignUpAreaException();
			die_error(0);
		}

		$area_template = $this->getCurrentTemplate() . ($this->view->area ? '_' . $this->view->area : '');

		if (Theme::instance()->getTemplate($area_template, FALSE))
			$this->viewLayout($area_template); //$area_template);
		else
			$this->viewLayout();
	}

	public function ajaxSignUp($params) {
		try {
			Hook::eventRequired('auth.signup');
		} catch (\Exception $exc) {
			$this->alert('error', 'Error', $exc->getMessage());
		}
	}

}

class InvalidAuthenicationAreaException extends \Exception {

}

class InvalidSignUpAreaException extends \Exception {

}
