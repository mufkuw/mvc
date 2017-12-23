<?php

namespace Mvc;

class Controller extends Foundation implements IApi {

	protected $alerts		 = [];
	protected $view;
	protected $theme;
	protected $route;
	protected $cookie;
	private static $rendered = [];
	protected $media;
	protected $breadcrumbs	 = [];

	public function __construct() {
		parent::__construct();

		$this->view						 = SmartyView::instance();
		$this->theme					 = Theme::instance();
		Context::instance()->controller	 = $this;
		$this->cookies					 = Context::instance()->cookie;
		$this->route					 = Router::getRoute();
		$this->view->page_title			 = ucwords($this->route['controller'] . " " . $this->route['action']);
		$this->media					 = new Media('Layout');

		$this->media->addCss("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css", false);
		$this->media->addCss("https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css", false);
		$this->media->addJs("https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", false);
		$this->media->addJs("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js", false);
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
		$search_path = [
			Context::instance()->setup['namespace'] . '\\Controllers', //search with App\Controllers
			Context::instance()->setup['namespace'], // search with App\
			'Mvc\\Controllers', //search with Mvc\Controllers
			'' //search directly without namespace
		];

		$success = false;

		foreach ($search_path as $namespace) {
			$controller	 = $namespace . '\\' . ucwords($route['controller']) . "Controller";
			$method_name = 'action' . ucwords($route['action']);
			if (class_exists($controller, true)) {
				$controller = new $controller;

				$method_names				 = [
					'action' . ucwords(strtolower($route['params']['request_method'])) . ucwords($route['action']),
					'action' . ucwords($route['action']),
					'action'
				];
				$route['params']			 = array_merge($route['params'], $_POST, $_GET);
				$route['params']['params']	 = $route['params'];
				foreach ($method_names as $method_name) {
					print_pre([$controller, $method_name]);
					if (method_exists($controller, $method_name)) {
						invoke_function([$controller, $method_name], $route['params']);
						$success = true;
						break;
					}
				}
			}
		}
		if (!$success)
			die_page_not_found();
	}

	public function viewJson($object) {
		header('Content-Type: application/json');
		//utf8_encode_deep($object);
		//echo raw_json_encode($object);

		echo json_encode($object, JSON_UNESCAPED_UNICODE);
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

	public function view($template = null) {
		echo $this->render($template);
	}

	public function renderLayout($template = null) {
		$output = '';

		$layout = 'layout';

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

	public function viewLayout($template = null) {
		echo $this->renderLayout($template);
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

	public function header() {

	}

	public function footer() {

	}

}
