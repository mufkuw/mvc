<?php

namespace Mvc;

use Mvc\Controllers\AuthController;

class Auth extends Foundation {

	public static function login($referer = null, $area = null) {
		Cookie::instance()->referer	 = $referer;
		Cookie::instance()->area	 = $area;

		header("location : /auth/login");
	}

	public static function logout($area = null) {
		$auth					 = Cookie::instance()->auth;
		unset($auth[$area]);
		print_pre($auth);
		Cookie::instance()->auth = $auth;
	}

}
