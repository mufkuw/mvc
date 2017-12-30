<?php

namespace Mvc;

use Mvc\Controllers\AuthController;

abstract class Controller extends Foundation {

	protected $alerts		 = [];
	protected $view;
	protected $theme;
	protected $route;
	protected $cookie;
	protected $layout		 = 'layout';
	private static $rendered = [];
	protected $media;
	protected $breadcrumbs	 = [];

	public function __construct() {
		parent::__construct();

		Context::instance()->theme		 = Theme::instance();
		Context::instance()->view		 = SmartyView::instance();
		Context::instance()->controller	 = $this;
		$this->view						 = SmartyView::instance();
		$this->theme					 = Theme::instance();
		$this->cookies					 = Context::instance()->cookie;
		$this->route					 = Router::getRoute();
		$this->view->page_title			 = ucwords($this->route['controller'] . " " . $this->route['action']);
		$this->media					 = new Media('Layout');

		$this->media->addCss("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css", false);
		$this->media->addCss("https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css", false);
		$this->media->addJs("https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", false);
		$this->media->addJs("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js", false);
		$this->media->addJs("https://cdnjs.cloudflare.com/ajax/libs/1000hz-bootstrap-validator/0.11.9/validator.min.js", false);
	}

	//ajax actions function starts with ajax
	public function actionX($id, $params, $c) {

		$invalidAjax = !isset($c);  // if c is not passed as querystring param c=test
		$invalidAjax = $invalidAjax && !isset($params['action_id']);  //if ajax is not passed as url path /x/test
		$invalidAjax = $invalidAjax || (isset($params['action_id']) && !($c			 = $params['action_id']));   //assign $c if valid ajax.
		$invalidAjax = (!$invalidAjax && method_exists($this, 'ajax' . camel_from_split('_', ucwords($c), '_'))) || $invalidAjax;  //check medthod defined if valid ajax.


		$c = camel_from_split('_' . $c, '_');

		if ($invalidAjax) {
			print_pre('Invalid Ajax');
			die(0);
		}
		$params['params']	 = $params;
		$output				 = invoke_function([$this, 'ajax' . ucwords($c)], $params);

		header('Alerts : ' . json_encode($this->alerts));

		$this->viewJson($output);
	}

	public function alert($type = 'info', $title = 'Info', $message = '') {
		$this->alerts[] = [
			'type'		 => $type,
			'title'		 => $title,
			'message'	 => $message,
		];
	}

	public static function execute($route) {

		//posible controllers namespace defination to purpose the search

		$namespaces	 = array_merge(array("\\" . Context::instance()->setup['namespace'] . "\\Controllers"), array_reverse(Context::instance()->search_sequence_controllers_namespaces));
		$success	 = false;
		foreach ($namespaces as $namespace) {

			$controller	 = $namespace . '\\' . ucwords($route['controller']) . "Controller";
			//print_pre($controller);
			$method_name = 'action' . ucwords($route['action']);
			if (class_exists($controller, true)) {
				$controller			 = $controller::instance();
				$controller->route	 = $route;

				$method_names				 = [
					'action' . ucwords(strtolower($route['params']['request_method'])) . ucwords($route['action']),
					'action' . ucwords($route['action']),
					'action'
				];
				$route['params']			 = array_merge($route['params'], $_POST, $_GET);
				$route['params']['params']	 = $route['params'];

				foreach ($method_names as $method_name) {
					if (method_exists($controller, $method_name)) {
						if ($method_name === 'action') {
							$route['params'] = $route;
							invoke_function([$controller, $method_name], $route);
						} else
							invoke_function([$controller, $method_name], $route['params']);
						$success = true;
						break;
					}
				}
			}
			if ($success)
				break;
		}
		if (!$success)
			die_page_not_found();
	}

	public function render($template = null) {

		$output = '';
		try {
			if ($template) {

				$template = str_replace('.html', '', $template);

				if ($template == 'layout') {
					$output = "<!--Start Rendering $template -->"
							. $this->view->render($this->theme->getTemplate($template))
							. "<!--End $template -->";
				} else {

					$media = new Media($template);

					Hook::execute("setting.media.$template", ['pMedia' => $media]);

					$media->addJs($template . '_bootstrap', true);
					$media->addCss($template . '_bootstrap', true);

					self::$rendered[$template] = $template;

					$output = "<!--Start Rendering $template -->"
							. $media->renderCss()
							. $this->view->render($this->theme->getTemplate($template))
							. $media->renderJs()
							. "<!--End $template -->";
				}
			} else {

				$action		 = $this->getCurrentAction();
				$controller	 = $this->getCurrentController();

				if ($action != 'index' && $action != '')
					$action	 = '_' . $action;
				else
					$action	 = '';

				$template = $controller . $action;

				$media = new Media($template);

				Hook::execute("setting.media.$template", ['pMedia' => $media]);

				$media->addJs($template . '_bootstrap', true);
				$media->addCss($template . '_bootstrap', true);

				self::$rendered[$template] = $template;

				$output = "<!--Start Rendering $template -->"
						. $media->renderCss()
						. $this->view->render($this->theme->getTemplate($template))
						. $media->renderJs()
						. "<!--End $template -->";
			}
		} catch (Exception $e) {
			$ex = new Exception('Error loading ' . $template . ' because of ' . $e->getMessage(), 0, $e);
			throw $ex;
		}
		return Minify::html($output);
	}

	public function viewJson($object) {
		Cookie::instance()->save();
		header('Content-Type: application/json');
		echo json_encode($object, JSON_UNESCAPED_UNICODE);
	}

	public function view($template = null) {
		Cookie::instance()->save();
		echo $this->render($template);
	}

	public function viewLayout($template = null, $layout = null) {
		Cookie::instance()->save();
		echo $this->renderLayout($template, $layout);
	}

	public function renderLayout($template = null, $layout = null) {
		$output = '';

		$layout = !$layout ? $this->layout : $layout;

		$this->view->HEADER_HTML = $this->header();
		$this->view->FOOTER_HTML = $this->footer();

		$this->media->addJs('alerts_bootstrap');
		$this->media->addCss('alerts_bootstrap');

		$this->view->ALERTS = $this->renderAlerts();

		$this->view->BODY_HTML = $this->render($template);

		Hook::execute('setting.media.layout', ['pMedia' => $this->media]);

		self::$rendered['layout'] = 'layout';

		$this->view->JS_FILES	 = $this->media->renderJs();
		$this->view->CSS_FILES	 = $this->media->renderCss();

		$output = $this->render($layout);

		return $output;
	}

	public function renderAlerts() {
		$html = '';
		foreach ($this->alerts as $alert) {
			$html .= SmartyViewWidgets::instance()->_Widget_Alert($alert, null);
		}
		return $html;
	}

	private function getCurrentAction() {

		return $this->route['action'];
	}

	private function getCurrentController() {
		return $this->route['controller'];
	}

	public function getCurrentTemplate() {

		$trace		 = debug_backtrace_of('action');
		$action		 = '';
		$controller	 = '';

		if ($trace) {
			$action = strtolower(str_replace('action', '', $trace['function']));
			if (isset($trace['class'])) {
				$controller	 = explode('\\', $trace['class']);
				$controller	 = array_pop($controller);
				$controller	 = strtolower(str_replace('Controller', '', $controller));
			}
		}

		return str_replace('_index', '', $controller . '_' . $action);
	}

	public function header() {

	}

	public function footer() {

	}

	/**
	 * Authenticate user access at this point of execution
	 * @param string $access_code user access code set for users ex: 'CUSTOMER.LIST','CUSTOMER.ADD','CUSTOMER.UPDATE','CUSTOMER.REMOVE','CUSTOMER.VIEW'
	 * @param string $area area to be authenticate for access like member, customer, admin, vendor etc
	 *
	 */
	public function authenticate($access_code = null, $area = 'admin') {

		$referer = $_SERVER['REQUEST_URI'];

		if (!isset(Cookie::instance()->auth[$area])) {
			Auth::login($referer, $area);
		}
	}

	/**
	 * Logout  from authenticated area
	 * @param string $area area to be authenticate for access like member, customer, admin, vendor etc
	 *
	 */
	public function logout($area = 'admin') {
		if (isset(Cookie::instance()->auth[$area])) {
			Auth::logout($area);
		}
	}

	public static function getControllers($path) {
		$files	 = [];
		$files	 = array_merge($files, array_filter(rdir($path), function($a) {
					return preg_match_all('/\\\\controllers\\\\(.*)Controller\.php/', $a['dir'] . DS . $a['name']) > 0;
				}));
		return $files;
	}

}
