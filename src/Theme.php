<?php

namespace Mvc;

class Theme extends Foundation {

	private $current_theme = 'default';

	public function __construct() {
		parent::__construct();
		$this->current_theme = Context::instance()->setup['default_theme'];
	}

	public function set($theme = 'default') {
		$this->current_theme = $theme;
	}

	public function getCurrentThemePath($pNeedLogicalPath = false) {

		if ($pNeedLogicalPath)
			return str_replace('\\', '/', Context::instance()->setup['themes_path'] . DS . $this->current_theme . DS);
		else
			return ROOT . Context::instance()->setup['themes_path'] . DS . $this->current_theme . DS;
	}

	public function getDefaultThemePath($pNeedLogicalPath = false) {
		if ($pNeedLogicalPath)
			return str_replace('\\', '/', Context::instance()->setup['themes_path'] . DS . Context::instance()->setup['default_theme'] . DS);
		else
			return ROOT . Context::instance()->setup['themes_path'] . DS . Context::instance()->setup['default_theme'] . DS;
	}

	public function getTemplate($file, $throw_exception = true) {
		$search_paths = [$file, $this->getCurrentThemePath() . $file, $this->getDefaultThemePath() . $file];
		foreach (array_reverse(Context::instance()->search_sequence_templates) as $search_sequence) {
			$search_paths[] = $search_sequence . $file;
		}

		print_pre($search_paths);

		foreach ($search_paths as $path) {
			print_pre([file_exists($path . MVC_TEMPLATES_EXT), $path . MVC_TEMPLATES_EXT]);
		}

		if ($throw_exception)
			throw new TemplateNotFoundException($file);
		else
			return false;
	}

}

class TemplateNotFoundException extends \Exception {

	public function __construct($template) {
		parent::__construct("The templete you are trying to fetch does not exists $template", 0, null);
	}

}
