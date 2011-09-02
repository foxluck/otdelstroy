<?php
	class DDListLinkSubtype extends DDListSubtype {
		var $shortLink = "/files";
		
		function DDListLinkSubtype (&$type) {
			$this->id = "Link";
			$this->embType = "link";
			$this->fields = array ();
			parent::DDListSubtype ($type);
		}
		
		function prepareConstructorPage (&$preproc) {
			parent::prepareConstructorPage ($preproc);
			
			global $dd_loc_str;
			global $language;
			
			$preproc->assign("ddStrings", $dd_loc_str[$language]);			
			$preproc->assign ("formFilename", PATH_WG_WIDGETS . "DDList/html/link_form.htm");
		}
		
		function prepare (&$preproc, &$widgetData) {
			global $DB_KEY;
			global $_GET;
			if (!isset($_POST["sendData"])) {
				if (onWebasystServer() && $this->shortLink) {
					redirectBrowser($this->getWidgetSrc($widgetData) . "/../../DD/2.0/wg_link.php", array("fp" => $widgetData["WG_FPRINT"]));
				}
				else
					redirectBrowser("../DD/2.0/wg_link.php", array("fp" => $widgetData["WG_FPRINT"], "DB_KEY" => base64_encode($DB_KEY)));
			}
			
			
			$res = parent::prepare($preproc, $widgetData);
			
			$preproc->assign('widgetFilename', "ddlist_link.htm");
			$res["tplFilename"] = PATH_WG_WIDGETS . "_common/public/html/link_wrapper.htm";
			return $res;
		}
		
		function getEmbInfo ($widgetData) {
			return array("short_link" => $this->shortLink, "width" => "100%");
		}
		
		
	}
?>