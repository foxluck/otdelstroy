<?php
	class WidgetsPageBuilder extends PageBuilder {
		
		var $typeManager;
		
		function init () {
			$this->typeManager = new WidgetTypeManager ($this->dataManager->kernelStrings, $this->dataManager->appStrings);
			$this->listPageURL = PAGE_WG_TYPES;
		}
		
		
		function actionListPage () {
			$btnIndex = getButtonIndex( array("addbtn", "cancelbtn", "backbtn"), $this->pageState->params );
			
			switch ($btnIndex) {
				case 0:
					$params = array ("action" => ACTION_NEW, "FAPP_ID" => $this->pageState->params["FAPP_ID"], "CF_ID" =>  $this->pageState->params["CF_ID"],"WT_ID" => $this->pageState->params["WT_ID"],"WST_ID" => $this->pageState->params["WST_ID"]);
					//$oparams = array ();
					//if (isset($this->pageState->params["outParams"])) {
					//	
					//}
					redirectBrowser( PAGE_WG_ADDMODWIDGET, $params);
					break;
				case 1:
					redirectBrowser( PAGE_WG_TYPES, array ());
					break;
				case 2:
					$backUrl = $this->pageState->params["FAPP_BACK_URL"];
					if (!$backUrl)
						$backUrl = PAGE_WG_TYPES;
					redirectBrowser( $backUrl, array ());
					break;
			}
		}
		
		function assignListData () {
			global $currentUser;
			
			$fapp = $this->pageState->params["app"];
			$widgetsSubtypes = getAppWidgetsSubtypes ($fapp, 0);
			if (PEAR::isError($widgetsSubtypes))
				return $this->pageState->fatalError ("Cannot get types: " . $widgetsSubtypes->getMessage());
			
			$widgets = $this->dataManager->getWidgetsForSubtypes($widgetsSubtypes, "WG_DESC ASC");
			if (PEAR::isError($widgets))
				return $this->pageState->fatalError ("Cannot get widgets: " . $widgets->getMessage());
				
			foreach ($widgets as $cKey => $cWidget) {
				if (empty($cWidget["WG_DESC"]))
					$cWidget["WG_DESC"] = "widget #" . $cWidget["WG_ID"];
				$widgets[$cKey] = $cWidget;
			}
			
			$this->preproc->assign ("widgetsSubtypes", $widgetsSubtypes);
			$this->preproc->assign ("widgets", $widgets);
		}
		
		function getPreprocessor () {
			global $language;
			global $templateName;
			$appId = ($fappid = $this->pageState->getParam("FAPP_ID")) ? $fappid : WG_APP_ID;
			$path =  sprintf( "%s/%s/html/cssbased", WBS_PUBLISHED_DIR, WG_APP_ID );
			$obj = new php_preprocessor( $templateName, $this->kernelStrings, $language, $appId, false);
			$obj->template_dir = $path;
			return $obj;
		}
		
		
		function prepareTypesPage () {
			$this->pageTitle = $this->appStrings["ty_page_title"];
			
			if (PEAR::isError($typeObjects = $this->typeManager->getObjsList ()))
				return $this->pageState->fatalError ("Cannot get types: " . $typeObjects->getMessage ());
			
			$types = array ();
			foreach ($typeObjects as $cKey => $cObj) {
				$cType["WT_ID"] = $cObj->id;
				$cType["name"] = $cObj->name;
				$cType["widgetsURL"] = prepareURLStr( PAGE_WG_WIDGETS, array ("WT_ID" => (base64_encode($cType["WT_ID"]))) );
				$cType["shortDesc"] = $cObj->strings["type_widgetsform_short_desc"];
				$types[$cKey] = $cType;
			}
			
			
			
			$this->preproc->assign ("types", $types);
		}
		
		function actionItemPage () {
			$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL, "deletebtn", "installtpbtn", "saveclosebtn"), $this->pageState->params );
			$redirects = array ();
			switch ($btnIndex) {
				case 0 :
					$res = parent::actionItemPage ();
					if (PEAR::isError($res))
						return $res;
					if ($this->pageState->params["action"] == ACTION_NEW) {
						redirectBrowser( PAGE_WG_ADDMODWIDGET, array ("action" => ACTION_EDIT, "FAPP_ID" => $this->pageState->params["FAPP_ID"], "WG_ID" => base64_encode($this->pageState->vars["WG_ID"])));						
					}
				break;
				case 1 :
					// Cancel
					$params = array ("FAPP_ID" => $this->pageState->params["FAPP_ID"], "WT_ID" => base64_encode($this->pageState->params["fields"]["WT_ID"]));
					redirectBrowser( PAGE_WG_WIDGETS, $params);
					break;
					return parent::actionItemPage ();
				case 2:
					// Delete
					$id = base64_decode($this->pageState->params["WG_ID"]);
					if (!$this->pageState->params["fields"]["WT_ID"]) {
						$row = $this->dataManager->get ($id);
						$this->pageState->params["fields"]["WT_ID"] = $row["WT_ID"];						
					}
					$this->pageState->params["fields"]["WG_ID"] = $id;
						
					$params = array ("FAPP_ID" => $this->pageState->params["FAPP_ID"], "WT_ID" => base64_encode($this->pageState->params["fields"]["WT_ID"]));
					$redirects["deletebtn"] = array ("url" => PAGE_WG_WIDGETS, "params" => $params);
					break;
				case 3 :
					// Install to typepad
					redirectBrowser( PAGE_WG_TYPEPAD_INSTALL, $this->pageState->params);
					break;
				case 4 :
					// Save and close
					$res = $this->saveItem ();
					if (PEAR::isError($res)) {
						$this->pageState->addError($res);
						return $res;
					}
					$params = array ("FAPP_ID" => $this->pageState->params["FAPP_ID"], "WT_ID" => base64_encode($this->pageState->params["fields"]["WT_ID"]));
					redirectBrowser( PAGE_WG_WIDGETS, $params);
					break;
			}
			return parent::actionItemPage ($redirects);
		}
		
		function getSubtypesList (&$typeObj) {
			$result = array ();
			foreach ($typeObj->subtypes as $cObj) {
				$row["WST_ID"] = $cObj->id;
				$row["name"] = $cObj->name;
				$row["desc"] = $cObj->desc;
				$result[] = $row;
			}
			return $result;			
		}
		
		function prepareViewWidgetPage () {
			/*global $wbs_languages;
			$this->preproc->assign ("WBS_LANGUAGES", 	$wbs_languages);
			
			$WG_ID = base64_decode($this->getParam("WG_ID"));
			$res = $this->prepareItemPage ($WG_ID, "vwwg");
			
			$item = $this->pageState->vars["itemData"];
			
			if ($this->isError($typeObj = $this->typeManager->getObj($item["WT_ID"]), true))
				return $typeObj;
			
			$widgetEmbInfo = $typeObj->getWidgetEmbInfo ($item, $item["WST_ID"]);
			
			$subtypeObj = &$typeObj->subtypes[$item["WST_ID"]];
			
			
			if ($item)
				$subtypeObj->prepareViewPage ($this->preproc, $this->pageState, $item);
			
			$this->preproc->assign ("embInfo", $widgetEmbInfo);
			$this->preproc->assign ("widgetStrings", $typeObj->strings);*/
			
			$this->prepareWidgetPage ();
		}
		
		
		function prepareWidgetPage () {
			global $wbs_languages;
			$this->preproc->assign ("WBS_LANGUAGES", 	$wbs_languages);
			
			$action = $this->getParam("action");
			$WG_ID = base64_decode($this->getParam("WG_ID"));
			
			$res = $this->prepareItemPage ($WG_ID, "amwg");
			
			if ($action == ACTION_NEW) {
				$WT_ID = base64_decode($this->getParam("WT_ID"));
				$WST_ID = base64_decode($this->getParam("WST_ID"));
				global $language;
				$selectedLang = $language;
				$item = null;
			} else {
				$item = $this->pageState->vars["itemData"];
				$selectedLang = $item["WG_LANG"];
				if ($this->isError($item)) {
					return $item;
				}
				$WT_ID = $item["WT_ID"];
				$WST_ID = $item["WST_ID"];
			}
			
			if (!$WT_ID)
				return $this->pageState->fatalError ("Empty type (WT_ID)");
			
			if (!$WST_ID)
				return $this->pageState->fatalError ("Empty type (WST_ID)");
			
			if ($this->isError($typeObj = $this->typeManager->getObj($WT_ID), true))
				return $typeObj;
			
			global $currentUser;
			global $host_applications;
			if ($typeObj->rights) {
				$haveRights = true;
				User::set($currentUser);
				foreach ($typeObj->rights as $cRight) {
					if (!User::hasAccess($cRight["app_id"], Rights::FUNCTIONS, $cRight["name"])) {
						$haveRights = false;
						break;
					}
				}
				if (!$haveRights) {
					$this->preproc->assign ("haventRights", true);
					$this->pageTitle = $this->appStrings["message_title"];
					return $this->pageState->fatalError ($this->appStrings["widgets_noright_error"]);
				}
			}
			if ($typeObj->applications) {
				$appOk = true;
				if (array_diff ($typeObj->applications, $host_applications)) {
				
					$this->preproc->assign ("haventRights", true);
					$this->pageTitle = $this->appStrings["message_title"];
					return $this->pageState->fatalError ($this->appStrings["widgets_noapp_error"]);
				}
			}
			
			
			
			$subtypeObj = &$typeObj->subtypes[$WST_ID];
			if (!$subtypeObj)
				return $this->pageState->fatalError ("Empty subtype object");
			
			$widgetStrings = &$typeObj->strings;
			$this->pageState->vars["widgetStrings"] = &$widget->strings;
			
			global $wgStrings;
			$this->preproc->assign('wgStrings', $wgStrings);
			//$this->pageTitle = ($action == ACTION_NEW) ? $wgStrings["amwg_addpage_title"] : $wgStrings["amwg_modpage_title"];
			$this->pageTitle = $typeObj->strings["type_widget_createname"];
			if ($subtypeObj->name)
				 $this->pageTitle = $subtypeObj->name ;
			
			$fieldsData = $subtypeObj->getFieldsData ($item);
			
			$widgetEmbInfo = $typeObj->getWidgetEmbInfo ($item, $WST_ID);
			
			$createdFrom = "";
			if ($item) {
				$subtypeObj->prepareConstructorPage ($this->preproc, $this->pageState, $item);
				$createdFrom = $item["WG_CREATED_FROM"];
			}
			else
				$subtypeObj->prepareConstructorPage ($this->preproc, $this->pageState);
			
			if (	$this->pageState->getParam("WG_CREATED_FROM"))
				$createdFrom = $this->pageState->getParam("WG_CREATED_FROM");
			if (!empty($this->pageState->params["fields"]["WG_CREATED_FROM"]))
				$createdFrom = $this->pageState->params["fields"]["WG_CREATED_FROM"];
			
			
			$widgetLinkInfo  = $subtypeObj->getWidgetSrcInfo($item);
			
			$this->preproc->assign ("widgetUrlStart", $widgetLinkInfo["prefix"]);
			$this->preproc->assign ("widgetUrlHash", $widgetLinkInfo["fprint"]);
			$this->preproc->assign ("canChangeUrl", onWebasystServer());
			
			$this->preproc->assign ("fieldsData", $fieldsData);
			$this->preproc->assign ("selectedLang", $selectedLang);
			$this->preproc->assign ("fields", !empty($this->pageState->params["fields"]) ? $this->pageState->params["fields"] : array());
			$this->preproc->assign ("WT_ID", $WT_ID);
			$this->preproc->assign ("WST_ID", $WST_ID);
			$this->preproc->assign ("widgetIdEnc", base64_encode($item["WG_ID"]));
			$this->preproc->assign ("WG_CREATED_FROM", $createdFrom);
			$this->preproc->assign ("action", $action);
			$this->preproc->assign ("itemName", $typeObj->name);
			$this->preproc->assign ("subtypeName", $subtypeObj->name);
			$this->preproc->assign ("subtypeDesc", $subtypeObj->desc);
			$this->preproc->assign ("widgetStrings", $widgetStrings);
			$this->preproc->assign ("widgetHTMLPath", $subtypeObj->type->getHTMLPath ());
			$this->preproc->assign ("embInfo", $widgetEmbInfo);
		}
		
		function prepareEditPage () {
			if (defined("BASE_SRC"))
				$this->preproc->assign ("BASE_SRC", BASE_SRC);				
			if (defined("WG_SRC"))
				$this->preproc->assign ("WG_SRC", WG_SRC);
		}
		
	}
	
?>