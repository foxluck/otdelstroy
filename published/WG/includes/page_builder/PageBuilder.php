<?php
	class PageBuilder {
		
		var $appId;
		var $kernelStrings;
		var $appStrings;
		var $dataManager;
		var $pageState;
		var $pageName;
		var $pageTitle;
		var $ajaxVersion;
		
		function PageBuilder($appId, &$dataManager) {
			$this->appId = $appId;
			$this->dataManager = &$dataManager;
			$this->kernelStrings = &$dataManager->kernelStrings;
			$this->appStrings = &$dataManager->appStrings;
			$this->init ();
			
			$this->pageState = new PageState ($this->kernelStrings);
			$this->pageState->appStrings = &$this->appStrings;
			$this->preproc = $this->getPreprocessor ();
		}
		
		function init () {
		}
		
		function isError ($error, $isFatal = false) {
			if (PEAR::isError($error)) {
				$this->pageState->addError ($error, $isFatal);
				return true;
			}
			return false;
		}
		
		function prepareListPage ($pageName) {
			$this->assignListData ();
			$this->pageName = $pageName;
			if (!$this->pageTitle)
				$this->pageTitle = $this->appStrings[strtolower($pageName) . "_page_title"];
		}
		
		
		function saveItem () {
			$action = $this->pageState->params["action"];
			$preparedData = prepareArrayToStore( $this->pageState->params["fields"] );
			$res = true;
			if ($action == ACTION_NEW) {
				$res = $this->dataManager->add( $preparedData);
				if (!PEAR::isError($res))
					$this->pageState->vars[$this->dataManager->keyField] = $res;
			}
			else {
				$key = $preparedData[$this->dataManager->keyField];
				if ($key)
					$res = $this->dataManager->update( $key, $preparedData);
				else
					$res = PEAR::raiseError ("Haven't item key in fields");
			}
			return $res;
		}
		
		
		
		function actionItemPage ($redirects = array ()) {
			$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL, "deletebtn"), $this->pageState->params );
			$action = $this->pageState->params["action"];
			
			switch ($btnIndex) {
				case 0 :				
					$res = $this->saveItem ();
					if ($this->isError ($res))
						return $res;
					break;		
				case 1 :
					// Cancel
					redirectBrowser( $this->listPageURL, array ());			
					break;
				case 2:
					// Delete
					$preparedData = prepareArrayToStore( $this->pageState->params["fields"] );
					$key = $preparedData[$this->dataManager->keyField];
					if ($key)
						$this->dataManager->delete($key);
					else
							$res = PEAR::raiseError ("Haven't item key in fields");
					if (@$redirects["deletebtn"])
						redirectBrowser( $redirects["deletebtn"]["url"], $redirects["deletebtn"]["params"]);
					else
						redirectBrowser( $this->listPageURL, array ());
					break;
			}
		}
		
		
		
		function prepareItemPage ($itemKey, $pageName) {
			$action = $this->pageState->params["action"];
			
			if ($action == ACTION_NEW) {
				$this->pageTitle = $this->appStrings[strtolower($pageName) . "_addpage_title"];
			}
			elseif ($action == ACTION_EDIT) {
				$this->pageTitle = $this->appStrings[strtolower($pageName) . "_modpage_title"];
				$item = $this->dataManager->get ($itemKey);
				$this->preproc->assign ("itemData", $item);
				
				$this->pageState->vars["itemData"] = $item;
			} else {
				if ($itemKey) {
					$item = $this->dataManager->get ($itemKey);
					$this->preproc->assign ("itemData", $item);
					$this->pageState->vars["itemData"] = $item;
				}
				$this->pageTitle = $this->appStrings[strtolower($pageName) . "_page_title"];				
			}
		}
		
		function assignListData () {
			$dataList = $this->dataManager->getList();
			if (PEAR::isError($dataList)) {
				$this->pageState->fatalError ($dataList->getMessage());
				return;
			}
			$this->preproc->assign ("DATA_LIST", $data);
		}
		
		
		function getPreprocessor () {
			global $language;
			global $templateName;
			return new php_preprocessor( $templateName, $this->kernelStrings, $language, $this->appId );
		}
		
		function getParam ($name) {
			if (!empty($this->pageState->params[$name]))
				return $this->pageState->params[$name];
			return false;
		}
		
		function setAjaxVersion ($params) {
			$this->ajaxVersion = $params;
		}
		
		
		
		function displayPage ($templateFilename) {
			$this->preproc->assign(PAGE_TITLE, $this->pageTitle );
			$this->preproc->assign( "kernelStrings", $this->kernelStrings );
			$this->preproc->assign( strtolower($this->appId) . "Strings", $this->appStrings);
			$this->preproc->assign( "params", $this->pageState->params);
			
			if ($this->pageState->fatalError)
				$this->preproc->assign ("fatalError", $this->pageState->fatalError);
			if ($this->pageState->hasErrors()) {
				$this->preproc->assign ("errorStr", $this->pageState->getErrorStr());
				$this->preproc->assign ("errorFields", $this->pageState->errorFields);
			}
			
			if ($this->preproc->get_template_vars('ajaxAccess') && isset ($this->ajaxVersion["tpl"])) {
				require_once( "../../../common/html/includes/ajax.php" );
				$ajaxRes = array ();
				if ($this->ajaxVersion["toolbar_tpl"])
					$ajaxRes["toolbar"] = simple_ajax_get_toolbar ($this->ajaxVersion["toolbar_tpl"], $this->preproc);
				if ($this->ajaxVersion["toolbar_content_tpl"])
					$ajaxRes["toolbarContent"] = simple_ajax_get_toolbar_content ($this->ajaxVersion["toolbar_content_tpl"], $this->preproc);
				$ajaxRes["rightContent"] = $this->preproc->fetch( $this->ajaxVersion["tpl"]);
				print simple_ajax_encode($ajaxRes);
			} else 
				$this->preproc->display ($templateFilename );
		}
	}
?>