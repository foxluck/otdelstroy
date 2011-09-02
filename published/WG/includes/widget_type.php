<?php
	class WidgetType {
		var $data;
		var $mainFile = "widget.htm";
		var $id;
		var $strings;
		var $name;
		var $fieldsData;
		var $subtypes;
		var $applications;
		var $rights;
		
		function WidgetType () {
			$this->init ();
		}
		
		function init () {
			// Load widget html and localization
			global $language;
			$localizationPath = sprintf( "%s/%s/widgets/" . $this->id . "/localization/", WBS_PUBLISHED_DIR, WG_APP_ID );
			$widgetAllStrings = loadLocalizationStrings( $localizationPath, strtolower($this->id) );
			$this->strings = &$widgetAllStrings[$language];
			if (empty($this->strings))
				$this->strings = &$widgetAllStrings[LANG_ENG];
			
			$this->name = $this->strings["widget_name_long"];
			
			foreach ($this->fieldsData as $cKey => $cField) {
				if (isset ($this->strings["sparam_" . strtolower($cKey) . "_default"])) {
					$cField["default"] = $this->strings["sparam_" . strtolower($cKey) . "_default"];
				}
				$this->fieldsData[$cKey] = $cField;
			}
						
		}
		
		function display ($widgetData) {
			$preproc = $this->getPreprocessor ();
			global $wgStrings;
			
			$subtypeObj = &$this->subtypes[$widgetData["WST_ID"]];
			$emptyStrings = null;
			$pageState = &new PageState ($emptyStrings);
			$subtypeObj->pageState = &$pageState;
			$res = $subtypeObj->prepare ($preproc, $widgetData);
			if (PEAR::isError($res))
				die ($res->getMessage());
			
			$widgetParams = $subtypeObj->getRealParams($widgetData);
			
			$preproc->assign ("widgetData", $widgetData);
			$preproc->assign ("widgetParams", $widgetParams);
			$preproc->assign ("wgStrings", $wgStrings);
			$preproc->assign ("widgetStrings", $this->strings);
			$preproc->assign ("params", $pageState->params);
			$preproc->assign ("subtypeObj", $subtypeObj);
			if (defined("BASE_SRC"))
				$preproc->assign ("BASE_SRC", BASE_SRC);				
			if (defined("WG_SRC"))
				$preproc->assign ("WG_SRC", WG_SRC);				
			
			$prepareRes = $res;
			
			$embInfo = $subtypeObj->getEmbInfo($widgetData);
			$preproc->assign("embInfo", $embInfo);
			$tplFilename = (empty($prepareRes["tplFilename"])) ? $this->mainFile : $prepareRes["tplFilename"];
			if ($pageState->hasErrors()) {
				$preproc->assign ("errorStr", $pageState->getErrorStr());
				$preproc->assign ("errorFields", $pageState->errorFields);
			}
			
			if ($pageState->getParam("mode") == "igoogle") {
				$fullEmbInfo = $this->getWidgetEmbInfo($widgetData, $subtypeObj->id);
				$info = $subtypeObj->getIGoogleInfo();
				$info["height"] = $fullEmbInfo["height"];
				header("Content-type: text/xml");
				print '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
				$src = $subtypeObj->getWidgetSrc($widgetData);
				$preproc->assign("src", $src);
				$preproc->assign("info", $info);
				$preproc->display ("../../../html/cssbased/igoogle.htm");
				exit;
			} elseif ($pageState->getParam("mode") == "typepad") {
				$fullEmbInfo = $this->getWidgetEmbInfo($widgetData, $subtypeObj->id);
				$info = $subtypeObj->getIGoogleInfo();
				$params["service_key"] = "52616e646f6d4956f82e12fcb88c01e454448d80f587b6dea7c6bdddf5ff572ca0b749f19f71f6b88d3a46bb7f993382";
				$params["service_name"] = "WebAsyst";
				$params["service_url"] = "http://www.webasyst.net/";
				$params["long_name"] = $info["description"];
				$params["short_name"] = "wa_subscriber";
				$params["content"] = $fullEmbInfo["realCode"];
				$params["return_url"] = "http://www.webasyst.com/";
				
				redirectBrowser( PAGE_WG_TYPEPAD_INSTALL, $params);
				exit;			
			} else {
				$preproc->display ($tplFilename);
			}
		}
		
		
		
		function isError ($error) {
			if (PEAR::isError($error)) {
				$this->pageState->addError ($error);
				return true;
			}
			return false;
		}
		
		function getPublicPath () {
			if(!$this->id)
				return "";
			return PATH_WG_WIDGETS . "/" . $this->id . "/public/";
		}
		
		function getHTMLPath () {
			if(!$this->id)
				return "";
			return PATH_WG_WIDGETS . "/" . $this->id . "/html/";
		}
		
		function getPreprocessor () {
			global $kernelStrings;
			global $language;
			
			$path = $this->getPublicPath ();
			$preproc =  new php_preprocessor("", $kernelStrings, $language, WG_APP_ID, true );
			$preproc->template_dir = $path;
			return $preproc;
		}
		
		function addSubtype(&$subtype) {
			$this->subtypes[$subtype->id] = &$subtype;
		}
		
		function getFormFilename () {
			return PATH_WG_WIDGETS . "/" . $this->id . "/html/form.htm";
		}
		
		var $defaultEmbWidth = 200;
		var $defaultEmbHeight = 200;
		
		function getWidgetEmbInfo ($widgetData, $subtypeId = "") {
			
			$scrolling = "NO";
			$widthAdd = 25;
			$heightAdd = 30;
			$styleStr = "";
			
			if ($subtypeId && $this->subtypes[$subtypeId]) {
				$subtypeObj = $this->subtypes[$subtypeId];
				$widgetParams = $subtypeObj->getRealParams($widgetData);
				$subEmbInfo = $subtypeObj->getEmbInfo ($widgetData);
				
				$width = (!empty($widgetParams["WIDTH"])) ? $widgetParams["WIDTH"] : $this->defaultEmbWidth;
				$height = (!empty($widgetParams["HEIGHT"])) ? $widgetParams["HEIGHT"] : $this->defaultEmbHeight;
				
				if (!empty($subEmbInfo["width"]))
					$width = $subEmbInfo["width"];
				if (!empty($subEmbInfo["height"]))
					$height = $subEmbInfo["height"];
				if (!empty($subEmbInfo["scrolling"]))
					$scrolling = $subEmbInfo["scrolling"];
				if (isset($subEmbInfo["widthAdd"]))
					$widthAdd = $subEmbInfo["widthAdd"];
				if (isset($subEmbInfo["heightAdd"]))
					$heightAdd = $subEmbInfo["heightAdd"];
				if (!empty($subEmbInfo["min_width"]) && $subEmbInfo["min_width"] > $width)
					$width = $subEmbInfo["min_width"];
				
				if (isset($subEmbInfo["style"]))
					$styleStr = 'style="' . $subEmbInfo["style"] . '"';
			} else {
				$widgetParams = $widgetData["params"];
				$width = (!empty($widgetParams["WIDTH"])) ? $widgetParams["WIDTH"] : $this->defaultEmbWidth;
				$height = (!empty($widgetParams["HEIGHT"])) ? $widgetParams["HEIGHT"] : $this->defaultEmbHeight;
			}
			
			$src = $subtypeObj->getWidgetSrc ($widgetData);
			
			if (is_numeric($width))
				$width += $widthAdd;
			if (is_numeric($height))
				$height += $heightAdd;
				
			if ($widgetData) {
				$info["src"] = $src;
				$info["previewSrc"] = $subtypeObj->getWidgetSrc ($widgetData, "mode=preview");
				$info["previewEditSrc"] = $subtypeObj->getWidgetSrc ($widgetData, "mode=previewEdit");
				$info["editSrc"] = $subtypeObj->getWidgetSrc ($widgetData, "mode=edit");
				$info["typepadSrc"] = $subtypeObj->getWidgetSrc ($widgetData, "mode=typepad");
				$info["igoogleSrc"] = rawurlencode($subtypeObj->getWidgetSrc ($widgetData, "mode=igoogle"));
			} else {
				global $language;
				$info["previewSrc"] = PAGE_WG_SWIDGET . "?typeId=" . $this->id . "&subtypeId=" . $subtypeId . "&mode=preview&language=" . $language ;
			}
				
			$info["width"] = $width;
			$info["height"] = $height;
			$info["realCode"] = '<iframe allowtransparency="true" scrolling="'.$scrolling.'" width="'.$width.'" height="'.$height.'" frameborder="0" src="'.$src.'" '.$styleStr.'></iframe>';
			$info["code"] = htmlspecialchars($info["realCode"]);
			return $info;
		}
	}	
	
	class WidgetSubtype {
		var $type;
		var $desc;
		var $name;
		var $commonFields;
		var $pageState;
		var $onlyForFolders = false;
		
		function WidgetSubtype (&$type) {
			$this->type = &$type;
			if (!empty($type->strings["subtype_" . strtolower($this->id) . "_name"]))
				$this->name = $type->strings["subtype_" . strtolower($this->id) . "_name"];
			if (!empty($type->strings["subtype_" . strtolower($this->id) . "_desc"]))
				$this->desc = $type->strings["subtype_" . strtolower($this->id) . "_desc"];
		}
		
		function getRealParams ($widgetData) {
			$widgetParams = $widgetData["params"];
			$fieldsData = $this->getFieldsData($widgetData);
			foreach ($fieldsData as $cParam => $cData) {
				if (!in_array($cParam, array_keys($widgetParams)) && @$cData['default']) {
					$widgetParams[$cParam] = $cData['default'];
				}
			}
			return $widgetParams;			
		}
		
		function getFields () {
			if (!$this->fields)
				$this->fields = array ();
			return array_unique(array_merge($this->commonFields, $this->fields));
		}
		
		function getWidgetSrc ($widgetData, $addStr = "") {
			global $DB_KEY;
			$subEmbInfo = $this->getEmbInfo ($widgetData);
			if ($subEmbInfo["short_link"] && onWebasystServer()) {
				
				if ((isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS']) == 'on')) || $_SERVER['SERVER_PORT'] == 443) {
			    	$pageProtocol = 'https://'; 
			    } else {
			    	$pageProtocol = 'http://';
			    }
				$host = empty($_SERVER['HTTP_X_REAL_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_X_REAL_HOST'];
				$src = $pageProtocol . $host . $subEmbInfo["short_link"] . "/" . $widgetData["WG_FPRINT"];
				if ($addStr)
					$src .= "/" . base64_encode($addStr);
			}
			else {
				$src= getLinkPrefix(3) . "/WG/show.php?q=" . base64_encode($DB_KEY) . "-" . $widgetData["WG_FPRINT"] ;
				if ($addStr)
					$src .= "&" . $addStr;
			}
			//$src .=  '&WG=' . $widgetData['WST_ID'];
			return $src;
		}
		
		function getWidgetSrcInfo ($widgetData) {
			$res = array ();
			global $DB_KEY;

			$subEmbInfo = $this->getEmbInfo ($widgetData);
			if ($subEmbInfo["short_link"] && onWebasystServer()) {
				$host = empty($_SERVER['HTTP_X_REAL_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_X_REAL_HOST'];
				$res["prefix"] = "http://" . $host . $subEmbInfo["short_link"] . "/";
				$src = $res["prefix"] . $widgetData["WG_FPRINT"];
				if ($addStr)
					$src .= "/" . base64_encode($addStr);
			}
			else {
				$res["prefix"] = getLinkPrefix(3) . "/WG/show.php?q=" . base64_encode($DB_KEY);
				$src= $res["prefix"] . "-" . $widgetData["WG_FPRINT"];
				if ($addStr)
					$src .= "&" . $addStr;
			}
			$res["address"] = $src;
			$res["fprint"] = $widgetData["WG_FPRINT"];
			
			return $res;
		}
		
		
		
		function getFParams () {
		}
		
		
		function prepare (&$preproc, &$widgetData) {
			if ($this->embType == "link")
				$res['tplFilename'] = PATH_WG_WIDGETS . "_common/public/html/link_wrapper.htm";
			else
				$res['tplFilename'] = PATH_WG_WIDGETS . "_common/public/html/inplace_wrapper.htm";
			$preproc->assign('widgetFilename', "widget.htm");
			return $res;
		}
		
		function prepareConstructorPage (&$preproc) {
			$preproc->assign("subtypeObj", $this);			
		}
		
		function prepareFieldsValues (&$params) {
		}
		
		function checkFieldsValues(&$params) {
		}
		
		function getEmbInfo ($widgetData) {
			return array ();
		}
		
		function getFieldsData () {
			$fields = $this->getFields ();
			$result = array ();
			foreach ($this->type->fieldsData as $cKey => $cData) {
				if (!in_array ($cKey, $fields))
					continue;
				if (isset ($this->type->strings[strtolower($this->id) . "_sparam_" . strtolower($cKey) . "_default"]))
						$cData["default"] = $this->type->strings[strtolower($this->id) . "_sparam_" . strtolower($cKey) . "_default"];
				$this->fieldsData[$cKey] = $cData;
				
				$result[$cKey] = $cData;
			}
			return $result;
		}
		
		function db_getListFromQuery ($sqlStr, $key = "") {
			$qr = db_query( $sqlStr);
			if ( PEAR::isError($qr)) 
				return $qr;
			
			$result = array ();
			while ($row = db_fetch_array($qr)) {
				if ($key && isset($row[$key]))
					$result[$row[$key]] = $row;
				else
					$result[] = $row;
			}
			
			db_free_result($qr);
			return $result;
		}
		
		function convertCreateParams($params) {
			return $params;
		}
		
		function prepareMailer (&$mailer, $widgetData, $sendData) {
			
		}	
		
		function getIGoogleInfo () {
			if ($this->type->strings["subtype_inplace_igoogle_title"]) {
				$info = array (
					"title" => $this->type->strings["subtype_inplace_igoogle_title"],
					"description" => $this->type->strings["subtype_inplace_igoogle_description"]
				);
			} else {
				$info = array (
					"title" => $this->name,
					"description" => $this->desc
				);				
			}
			return $info;
		}
		
		
		function sendToUsers ($widgetData, $sendData) {
			
			global $currentUser;
			$bodyTemplate = $this->getEmailBody ($widgetData, $sendData);
			$countTo = sendEmailToContacts($currentUser, $sendData["to"], $sendData["subject"], $bodyTemplate);
			
			return $countTo;
		}
		
		function createNewWidget ($user, $params) {
			return false;
		}
	}
?>