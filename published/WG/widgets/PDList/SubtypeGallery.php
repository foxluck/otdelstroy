<?php
	class PDListGallerySubtype extends PDListSubtype {
		var $shortLink = "/photos";
		
		function PDListGallerySubtype (&$type) {
			$this->id = "Gallery";
			$this->embType = "inplace";
			parent::PDListSubtype ($type);
			$this->commonFields = array_merge ($this->commonFields, array ("FILES", "FOLDERS", "COLUMNSCOUNT", "GLHEIGHT", "GLIMGWIDTH", "GLIMGHEIGHT", "GLONCLICK", "BODYBGCOLOR", "SHOWDESC"));
		}
		
		/*function prepareConstructorPage (&$preproc, &$pageState, $widgetData = array ()) {
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
				if (PEAR::isError($folderInfo)) {
					$preproc->assign ("filesErrorStr", $folderInfo->getMessage());
					break;
				}
				$preproc->assign ("folderName", $folderInfo["DF_NAME"]);
			}
			
			$filesIds = $widgetData["params"]["FILES"];
			if ($filesIds) {
				$filesList = $this->getFilesList($widgetData);
				$preproc->assign ("filesList", $filesList);
			}
			
			$preproc->assign("ddStrings", $dd_loc_str[$language]);			
		}*/
		
		function prepare (&$preproc, &$widgetData) {
			$widgetParams = $this->getRealParams($widgetData);
			
			$imgSize = PD_LARGE_THUMB_SIZE;
			$slwidth = $widgetParams["GLIMGWIDTH"];
			if ($slwidth <= 96 )
				$imgSize = 96;
			elseif ($slwidth <= 256 )
				$imgSize = 256;
			elseif ($slwidth <= 512 )
				$imgSize = 512;
			
			if ($this->pageState->getParam("mode") != "big")
				$widgetData["imgSize"] = $imgSize;
			
			$preproc->assign('imgSize', $imgSize);
			
			$res = parent::prepare($preproc, $widgetData);
			$preproc->assign('widgetFilename', "pdlist_gallery.htm");
			$res["tplFilename"] = PATH_WG_WIDGETS . "_common/public/html/inplace_wrapper.htm";
			
			
			return $res;			
		}
		
		function getEmbInfo ($widgetData) {
			$widgetParams = $this->getRealParams($widgetData);
			$width = ($widgetParams["COLUMNSCOUNT"] * (20+$widgetParams["GLIMGWIDTH"]))-20;
			return array("scrolling" => "AUTO", "width" => $width, "height" => $widgetParams["GLHEIGHT"]);
		}
	}
?>