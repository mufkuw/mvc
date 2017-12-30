<?php

namespace Mvc;

class Auth {

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
