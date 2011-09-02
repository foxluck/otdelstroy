<?php
	class PDListLinkSubtype extends PDListSubtype {
		var $shortLink = "/photos";
		
		function PDListLinkSubtype (&$type) {
			$this->id = "Link";
			$this->embType = "link";
			$this->fields = array ();
			parent::PDListSubtype ($type);
		}
		
		function prepareConstructorPage (&$preproc) {
			parent::prepareConstructorPage ($preproc);
			
			global $pd_loc_str;
			global $language;
			
			$preproc->assign("pdStrings", $pd_loc_str[$language]);			
			$preproc->assign ("formFilename", PATH_WG_WIDGETS . "PDList/html/link_form.htm");
		}
		
		function prepare (&$preproc, &$widgetData) {
			$res = parent::prepare($preproc, $widgetData);
			$preproc->assign('widgetFilename', $res["tplFilename"]);
			if ($res["tplFilename"] == "pdlist_linkslideshow.htm")
				$res["tplFilename"] = PATH_WG_WIDGETS . "_common/public/html/empty_wrapper.htm";
			else
				$res["tplFilename"] = PATH_WG_WIDGETS . "_common/public/html/link_wrapperpd.htm";
				
			if ( file_exists(realpath(dirname(realpath(__FILE__)).'/../../../publicdata/'.$GLOBALS['DB_KEY'].'/attachments/pd/themes/default').'/main.css' ) ) {
			    $preproc->assign('frontendcss', '../publicdata/'.$GLOBALS['DB_KEY'].'/attachments/pd/themes/default/main.css');
			}
			else {
			    $preproc->assign('frontendcss', '../PD/source/templates/themes/default/main.css');
			}
				
			
			return $res;
		}
		
		function getEmbInfo ($widgetData) {
			return array("short_link" => $this->shortLink, "width" => "100%");
		}
		
		
	}
?>