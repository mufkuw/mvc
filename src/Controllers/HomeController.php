<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mvc\Controllers;

use Mvc\{
	Controller,
	Hook,
	Cookie,
	Auth
};

class HomeController extends Controller {

	public function actionIndex() {

		$this->authenticate(null, 'customer');
		$this->view->page_title	 = 'Home';
		$this->view->page_icon	 = 'icomoon-home-2';
		Hook::register("setting.media.home", function($pMedia) {

		});

		$this->viewLayout();
	}

	public function actionLogout() {
		Cookie::instance()->debug();
		$this->logout('customer');
		Cookie::instance()->debug();
	}

}
