<?php

namespace Mvc;

class Controller extends Foundation {

	protected $alerts = [];
	protected $view;
	protected $theme;
	protected $route;
	protected $cookie;
	private static $rendered = [];
	protected $media;
	protected $breadcrumbs = [];

	public function __construct() {
		parent::__construct();

		$this->view = new SmartyView();
		$this->theme = new Theme();
		$this->context()->controller = $this;
		$this->cookies = $this->context()->cookies;
	}

	//ajax actions function starts with ajax
	public function actionX($id, $params, $c) {

		$invalidAjax = !isset($c);  // if c is not passed as querystring param c=test
		$invalidAjax = $invalidAjax && !isset($params['action_id']);  //if ajax is not passed as url path /x/test
		$invalidAjax = $invalidAjax || (isset($params['action_id']) && !($c = $params['action_id']));   //assign $c if valid ajax.
		$invalidAjax = (!$invalidAjax && method_exists($this, 'ajax' . camel_from_split('_', ucwords($c), '_'))) || $invalidAjax;  //check medthod defined if valid ajax.


		$c = camel_from_split('_' . $c, '_');

		if ($invalidAjax) {
			print_pre('Invalid Ajax');
			die(0);
		}
		$params['params'] = $params;
		$output = Invoker::invoke([$this, 'ajax' . ucwords($c)], $params);

		header('Alerts : ' . json_encode($this->alerts));

		$this->viewJson($output);
	}

	public function alert($type = 'info', $title = 'Info', $message = '') {
		$this->alerts[] = [
			'type' => $type,
			'title' => $title,
			'message' => $message,
		];
	}

	public static function execute($route) {

		$controller = ucwords($route['controller']) . "Controller";
		$method_name = 'action' . ucwords($route['action']);

		if (class_exists($controller, true)) {
			$controller = new $controller;
			$method_name = 'action' . ucwords($route['action']);
			if (method_exists($controller, $method_name)) {
				Invoker::invoke([$controller, $method_name], $route);
			} else {
				die_page_not_found();
			}
		} else {
			die_page_not_found();
		}
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

				$action = $this->getCurrentAction();
				$controller = $this->getCurrentController();

				if ($action != 'index' && $action != '')
					$action = '_' . $action;
				else
					$action = '';

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

		$this->view->HEADER_SETTINGS = $this->headerSettings();
		$this->view->HEADER_HTML = $this->header();
		$this->view->FOOTER_HTML = $this->footer();
		$this->view->ALERTS = $this->renderAlerts();

		$this->view->BODY_HTML = $this->render($template);

		Hook::execute('setting.media.layout', ['pMedia' => $this->media]);

		self::$rendered['layout'] = 'layout';

		$this->view->JS_FILES = $this->media->renderJs();
		$this->view->CSS_FILES = $this->media->renderCss();

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
		$traces = debug_backtrace(2, 0);
		$action = '';
		foreach ($traces as $trace) {
			if (substr($trace['function'], 0, 6) == 'action') {
				$action = strtolower(str_replace('action', '', $trace['function']));
				break;
			}
		}

		if ($action == '')
			$action = $this->route['action'];

		return $action;
	}

	private function getCurrentController() {
		return $this->route['controller'];
	}

}
