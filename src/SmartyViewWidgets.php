<?php

namespace Mvc;

class SmartyViewWidgets extends Foundation {

	private $smarty;

	public function register($smarty) {
		$this->smarty = $smarty;
		$theme = Context::instance()->theme;
		$files = array_merge(rdir($theme->getCurrentThemePath()), rdir(MVC_DEFAULT_TEMPLATES));
		$files = array_filter($files, function($item) {
			$matches = [];
			preg_match('/^template-(.*).html/', $item['name'], $matches);
			if ($matches) {
				return true;
			}
		});

		foreach ($files as $file) {
			preg_match('/^template-(.*).html/', $file['name'], $matches);
			$widget = str_replace('-', '_', $matches[1]);
			$smarty->registerPlugin('function', $widget, [$this, '_Widget_' . $widget]);
		}
	}

	public function __call($method, $args) {

		if (substr($method, 0, 8) == '_Widget_') {

			$widget = strtolower(str_replace('_Widget_', '', $method));
			$params = $args[0];
			$params['template'] = 'template-' . str_replace('_', '-', $widget);

			$attributes_ignore_list = ['class'];
			$attribute_html = "";

			foreach ($params as $key => $param) {
				if (!in_array($key, $attributes_ignore_list, false))
					$attribute_html .= "$key='$param' ";
			}

			$params['attributes'] = $attribute_html;

			return SmartyView::instance()->_SmartyView_Include_Function($params, $this->smarty);
		}
	}

}
