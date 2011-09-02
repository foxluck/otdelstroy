<?php

	function smarty_block_csscombine($params, $content, &$smarty) {
		if (!$content) {
			return '';
		}
		$dir = WBS_DIR . "published";   
		
		$url = Url::get('');
		$file = $dir . preg_replace("/\\?.*$/i", "", mb_substr($params['file'], mb_strlen($url)));
		$file = str_replace(".css", ".min.css", $file);
		$files = explode("\n", $content);
		if (defined('DEVELOPER') && DEVELOPER) {
			$result = "";
			foreach ($files as $f) {
				$f = trim($f);
				if ($f) {
					$result .= '<link rel="stylesheet" type="text/css" href="' . $f . '" />'."\n";
				}
			}
			return $result;
		} elseif (!file_exists($file)) {
			$combine = "";
			foreach ($files as $f) {
				$f = trim($f);
				if ($f) {
					$f = mb_substr($f, mb_strlen($url));
					$f = preg_replace("/\\?.*$/i", "", $f);
					$combine .= file_get_contents($dir.$f)."\n";
				}
			}
			file_put_contents($file, $combine);
		}
		return '<link rel="stylesheet" type="text/css" href="' . str_replace(".css", ".min.css", $params['file']) . '" />';
	}
?>