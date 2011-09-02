<?php
	class DDListInplaceSubtype extends DDListSubtype {
		var $shortLink = "/files";
		
		function DDListInplaceSubtype (&$type) {
			$this->id = "Inplace";
			$this->embType = "inplace";
			parent::DDListSubtype ($type);
			$this->commonFields = array_merge ($this->commonFields, array ("TITLEBGCOLOR", "TITLECOLOR", "BODYBGCOLOR", "WIDTH", "HEIGHT", "VIEWMODE", "FILEICON", "SHOWTITLES", "SHOWDESC", "SHOWSIZE", "SHOWDATE", "SHOWDOWNLOADLINK", "SHOWBORDER", "SORTING"));
		}
		
		function prepareConstructorPage (&$preproc, &$pageState, $widgetData = array ()) {
			include_once("../../../DD/dd.php");
			
			parent::prepareConstructorPage ($preproc);
			global $kernelStrings;
			global $language;
			global $dd_loc_str;
			global $dd_treeClass;
			global $currentUser;
			
			$folderID = $widgetData["params"]["FOLDERS"];
			if ($folderID) {
				$folderInfo = $dd_treeClass->getFolderInfo( $folderID, $kernelStrings );
				if ($res = PEAR::isError($folderInfo)) {
					$preproc->assign ("filesErrorStr", $folderInfo->getMessage());
					return $res;
				}
				$preproc->assign ("folderName", $folderInfo["DF_NAME"]);
			}
			
			$filesIds = $widgetData["params"]["FILES"];
			if ($filesIds) {
				$filesList = $this->getFilesList($widgetData);
				$preproc->assign ("filesList", $filesList);
			}
			
			$preproc->assign("ddStrings", $dd_loc_str[$language]);			
		}
		
		function prepare (&$preproc, &$widgetData) {
			$res = parent::prepare($preproc, $widgetData);
			$preproc->assign('widgetFilename', "ddlist_inplace.htm");
			$res["tplFilename"] = PATH_WG_WIDGETS . "_common/public/html/inplace_wrapper.htm";
			return $res;			
		}
		
		function getEmbInfo ($widgetData) {
			return array("short_link" => $this->shortLink, "scrolling" => "AUTO", "widthAdd" => 50);
		}
	}
?>