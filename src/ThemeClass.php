<?php

class Theme extends Foundation {

	private $current_theme = 'default';

	public function __construct() {
		$this->current_theme = Context::instance()->setup['default_theme'];
	}

	public function set($theme = 'default') {
		$this->current_theme = $theme;
	}

	public function getTemplate($file) {
		$search_paths = [];
		$search_paths[] = $file;
		$search_paths[] = $file . ' . html';


		foreach ($search_paths as $path) {

			$full_path = realpath(Context::instance()->setup['themes_path'] . $this->current_theme . ds . $path);

			if ($full_path && file_exists($full_path))
				return $full_path;
			else
				throw new TemplateNotFoundException($file);
		}
	}

}

class TemplateNotFoundException extends Exception {

	public function __construct($template) {
		parent::__construct("The templete you are trying to fetch does not exists $template", 0, null);
	}

}
