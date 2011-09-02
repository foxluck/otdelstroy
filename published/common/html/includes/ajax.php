<?php
	require_once( WBS_DIR."kernel/classes/JSON.php" );
	$json = new Services_JSON();	
	
	function simple_ajax_encode($params) {
		header("Content-type: text/html; charset=utf-8");
		//$res;
		foreach ($params as $cKey => $cValue) {
			$res .= $cKey . ":" . $cValue . SIMPLE_AJAX_DELIMITER;
		}
		$res = substr($res, 0, strlen ($res) - strlen(SIMPLE_AJAX_DELIMITER ));
		return $res;
	}
	
	function simple_ajax_get_toolbar ($tpl, $preproc = null, $title = null) {
		if (!$title && $preproc)		
			$title = $preproc->get_template_vars(PAGE_TITLE);
		
		$tb = "";
		$tb .= "<div id='SubToolbar'><div>$title</div></div>";
		$tb .= "			<div id='ToolbarIn' style='height: 35px'><ul>\n";
		if ($preproc) {
			$tb .= simple_ajax_get_toolbar_content ($tpl, $preproc, $title);
		}
		$tb .= "			</ul></div>\n";
		return $tb;
	}
	
	function simple_ajax_get_toolbar_content ($tpl, $preproc = null, $title = null) {
		$params = array("smarty_include_tpl_file" => $tpl, 'smarty_include_vars' => $preproc->get_template_vars());
		ob_start();
		$preproc->_smarty_include($params);
		$tb .= ob_get_clean();
		
		return $tb;
	}
?>