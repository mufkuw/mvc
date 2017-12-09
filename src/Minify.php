<?php

namespace Mvc;

class Minify extends Foundation {

	public static function html($html) {
		return str_replace(array("\n", "\r", "\t"), '', $html);
	}

}
