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

		$this->registerPlugin('function', 'l', [$this, '_SmartyView_LanguageTranslationProvider_Function']);
		$this->registerPlugin('function', 'hook', [$this, '_SmartyView_Hook_Function']);
		$this->registerPlugin('function', 'inc', [$this, '_SmartyView_Include_Function']);

		$this->registerPlugin('function', 'hidden', [$this, '_SmartyView_Hidden_Function']);

		$this->registerPlugin('modifier', 'null', [$this, '_SmartyView_Null_Modifier']);


//		$this->registerPlugin('function', 'html_widget', [$this, 'Mvc\_SmartyView_Widget_Function']);
//		$this->registerPlugin('function', 'alert', [$this, '_SmartyView_Widget_Alert_Function']);


		SmartyViewWidgets::instance()->register($this);
	}

	function _SmartyView_Hook_Function($param, &$smarty) {
		if (!isset($param['event']))
			return '';

		$param['params'] = $param;
		$event = $param['event'];

		return Hook::execute($event, $param, function($success, $hook_results) use ($event) {
					$html = '';
					foreach ($hook_results as $result) {
						if (!$result['error'])
							$html .= $result['result'];
					}

					return "<!--Rendering Hook $event-->"
							. $html
							. "<!--End Rendering Hook $event-->";
				});
	}

	function _SmartyView_LanguageTranslationProvider_Function($param, &$smarty) {
		return $param['s'];
	}

	function _SmartyView_Include_Function($params, &$smarty) {

		$view = $smarty;

		$theme = Theme::instance();

		$path = $theme->getTemplate($params['template']);

		if (!$path)
			return '';

		$tpl = $view->createTemplate($path);

		foreach ($params as $key => $param)
			$tpl->assign($key, $param);

		$html = $tpl->fetch();

		foreach ($params as $key => $param)
			$view->$key = null;

		return $html;
	}

	function _SmartyView_Null_Modifier(&$string, $value = "") {
		if (isset($string))
			return $string;
		else
			return $value;
	}

	function _SmartyView_Hidden_Function($params, &$smarty) {
		$html = '';
		foreach ($params as $key => $value) {
			if (isset($params['model']))
				$name_key = $params['model'] . '[' . $key . ']';
			else
				$name_key = $key;

			$html .= "<INPUT type=hidden id=$key name=$name_key value=$value>";
		}

		return $html;
	}

	function _SmartyView_Widget_Function($params, &$smarty) {

	}

	function _SmartyView_Widget_Alert_Function($params, &$smarty) {

	}

	public function __call($name, $arguments) {
		return call_user_func_array([$this->smarty, $name], $arguments);
	}

}
