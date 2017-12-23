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
		$media = Media::instance();
		$file = $media->getJs($action_id);
		$content = file_get_contents($file);
		if ($file) {
			header("content-type: application/javascript");
			echo $content;
		}
	}

	public function actionCss($action_id) {
		$media = Media::instance();
		$file = $media->getCss($action_id);
		$content = file_get_contents($file);
		if ($file) {
			header('content-type: text/css');
			echo $content;
		}
	}

}
