<?php

namespace Mvc;

class Minify extends Foundation {

	public static function html($html) {

		$html = preg_replace('/<!--[^>]*-->/', '', $html);

		return str_replace(array("\n", "\r", "\t"), '', $html);
	}

	public static function css($minify) {
		/* remove comments */
		$minify = preg_replace('/\/\*[^*]*\*\/|\/\*[^~]*\*\/|\/\/.+/', '', $minify);

		/* remove tabs, spaces, newlines, etc. */
		$minify	 = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $minify);
		$minify	 = str_replace(': ', ':', $minify);
		$minify	 = str_replace(' {', '{', $minify);

		return $minify;
	}

	public static function js($minify) {
		/* remove comments */
		$minify = preg_replace('/\/\*[^*]*\*\/|\/\*[^~]*\*\/|\/\/.+/', '', $minify);

		/* remove tabs, spaces, newlines, etc. */
		$minify = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $minify);

		return $minify;
	}

}
