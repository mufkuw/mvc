<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mvc\Controllers;

use Mvc\Controller;
use Mvc\Theme;
use Mvc\Hook;
use Mvc\Media;

class MediaController extends Controller {

	public function actionJs($action_id) {
		$media	 = Media::instance();
		$file	 = $media->getJs($action_id);
		$content = file_get_contents($file);
		if ($file) {
			ob_start();
			echo $this->minify_js($content);
			$expires = 60 * 60 * 24;
			header("Content-type: x-javascript");
			header('Content-Length: ' . ob_get_length());
			header('Cache-Control: max-age=' . $expires . ', must-revalidate');
			header('Pragma: public');
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . 'GMT');
			ob_end_flush();
		}
	}

	public function actionCss($action_id) {
		$media	 = Media::instance();
		$file	 = $media->getCss($action_id);
		$content = file_get_contents($file);
		if ($file) {

			ob_start();
			echo $this->minify_css($content);
			$expires = 60 * 60 * 24;
			header('content-type: text/css');
			header('Content-Length: ' . ob_get_length());
			header('Cache-Control: max-age=' . $expires . ', must-revalidate');
			header('Pragma: public');
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . 'GMT');
			ob_end_flush();
		}
	}

}
