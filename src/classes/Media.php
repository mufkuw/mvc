<?php

namespace Mvc;

define('JS_TEMPLATE', "<script src='{MEDIA}' type='text/javascript'></script>");
define('CSS_TEMPLATE', "<link href='{MEDIA}' rel='stylesheet' type='text/css'/>");

class MediaTypes {

	const JS	 = 'js';
	const CSS	 = 'css';

}

class Media extends Foundation {

	private $media_files = [];
	private $scope		 = '';

	public function __construct($scope = '') {
		$this->media_files['js']	 = [];
		$this->media_files['css']	 = [];
		$this->scope				 = $scope;
	}

	private function addMedia($type, $media, $validate = true, $module = '') {
		$theme = Theme::instance();
		if ($media) {
			if ($type == 'js') {
				$media_path = $this->getJs($media, $module);
			} else if ($type == 'css') {
				$media_path = $this->getCss($media, $module);
			}

			if ($media != $media_path) {
				$validate	 = false;
				$media_path	 = "/media/$type/$media.$type";
			}



			if (($validate && realpath(ROOT . $media_path)) || !$validate) {
				$this->media_files[$type][]	 = $media_path;
				$this->media_files[$type]	 = array_unique($this->media_files[$type]);
			}

			//print_pre($this->media_files);

			return true;
		}
	}

	public function addJs($media, $validate = true, $module = '') {
		return $this->addMedia('js', $media, $validate, $module);
	}

	public function addCss($media, $validate = true, $module = '') {
		return $this->addMedia('css', $media, $validate, $module);
	}

	private function renderMedia($type) {
		$media_template = [
			'js'	 => JS_TEMPLATE
			, 'css'	 => CSS_TEMPLATE
		];

		$html = '';

		foreach ($this->media_files[$type] as $media) {
			$html .= str_replace('{MEDIA}', $media, $media_template[$type]);
		}

		return "<!--Start $this->scope ($type) -->"
				. $html
				. "<!--End $this->scope ($type) -->";
	}

	public function renderJs() {
		return $this->renderMedia('js');
	}

	public function renderCss() {
		return $this->renderMedia('css');
	}

	private function getMedia($type, $file, $module = '') {

		$theme = Theme::instance();

		if (!$file)
			return $file;


		$search_paths	 = array();
		$search_paths[]	 = $file;
		$search_paths[]	 = $theme->getCurrentThemePath() . '' . $type . '/' . $file;
		$search_paths[]	 = $theme->getDefaultThemePath() . '' . $type . '/' . $file;
		$search_paths[]	 = MVC_MEDIA . '' . $type . DS . $file;
		$search_paths[]	 = ROOT . Context::instance()->setup['modules_path'] . $module . DS . $type . DS . $file;

		foreach ($search_paths as $path) {

			if (realpath($path))
				return $path;
			if (realpath($path . '.' . $type))
				return $path . '.' . $type;
		}

		return $file;
	}

	public function getJs($file, $module = '') {
		return $this->getMedia('js', $file, $module);
	}

	public function getCss($file, $module = '') {
		return $this->getMedia('css', $file, $module);
	}

}
