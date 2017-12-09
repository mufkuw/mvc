<?php

namespace Mvc;

class SmartyView extends Foundation {

	private $smarty;

	/**
	 * @return SmartyView
	 */
	function __construct() {

		$this->smarty = New \Smarty;

		$cache_path = Context::instance()->cache_path;

		$this->smarty->setTemplateDir($cache_path . '/smarty/template/');
		$this->smarty->setCompileDir($cache_path . '/smarty/compiled/');
		$this->smarty->setCacheDir($cache_path . '/smarty/cache/');
		$this->smarty->setConfigDir($cache_path . '/smarty/configs/');

		//$this->smarty->caching = true;

		$this->plugin_setup();
	}

	public function __set($name, $value) {
		$this->smarty->assign($name, $value);
		parent::__set($name, $value);
	}

	public function view($template) {
		$this->smarty->display($template);
	}

	public function render($template) {

		/* ob_start();
		  $this->display($template);
		  $output = ob_get_clean(); */

		return $this->smarty->fetch($template);
	}

	public function plugin_setup() {

	}

	public function __call($name, $arguments) {
		return call_user_func_array([$this->smarty, $name], $arguments);
	}

}
