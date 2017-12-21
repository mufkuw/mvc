<?php

namespace Mvc;

class Theme extends Foundation {

	private $current_theme = 'default';

	public function __construct() {
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
		$search_paths = [];
		$search_paths[] = $file;
		$search_paths[] = $file . '.html';

		foreach ($search_paths as $path) {

			$this_path = realpath($path);
			$mvc_path = realpath(MVC_DEFAULT_TEMPLATES . $path);
			$full_path_default = realpath(Context::instance()->theme->getDefaultThemePath() . $path);
			$full_path = realpath(Context::instance()->theme->getCurrentThemePath() . $path);

			if ($this_path && file_exists($this_path))
				return $this_path;
			else if ($full_path && file_exists($full_path))
				return $full_path;
			else if ($full_path_default && file_exists($full_path_default))
				return $full_path_default;
			else if ($mvc_path && file_exists($mvc_path))
				return $mvc_path;
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
