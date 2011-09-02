<?php

    function smarty_block_jscombine($params, $content, &$smarty) {
        if (!$content) {
            return '';
        }
        $dir = WBS_DIR . "published";
        $url = Url::get('');
        $file = $dir . preg_replace("/\\?.*$/i", "", mb_substr($params['file'], mb_strlen($url)));
        $file = str_replace(".js", ".min.js", $file);
        $files = explode("\n", $content);
        if (defined('DEVELOPER') && DEVELOPER) {
            $result = "";
            foreach ($files as $f) {
                $f = trim($f);
                if ($f) {
                    $result .= '<script type="text/javascript" src="' . $f . '"></script>'."\n";
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
        return '<script type="text/javascript" src="' . str_replace(".js", ".min.js" . (defined('REVISION') ? "?r".REVISION : ""), $params['file']) . '"></script>';
    }

?>