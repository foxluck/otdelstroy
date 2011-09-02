<?php
	class PageState {
		var $fatalError;
		var $errorStr;
		var $errors;
		var $errorFields;
		var $params;
		var $vars;
		var $kernelStrings;
		var $language;
		var $appStrings;
		
		function PageState (&$kernelStrings) {
			$this->fatalError = false;
			
			global $_GET;
			global $_POST;
			global $language;
			$this->params = array_merge($_GET, $_POST);
			$this->vars = array ();
			$this->language = $language;
			$this->kernelStrings = &$kernelStrings;
		}
		
		function addError ($error, $isFatal = false) {
			if ($isFatal)
				$this->fatalError = true;
			if (PEAR::isError($error)) {
				$this->errorFields[] = $error->getUserInfo ();
				$this->errors[] = $error->getMessage();
				return $error;
			} else {
				$this->errors[] = $error;
				return PEAR::raiseError($error);
			}
		}
		
		function fatalError ($error) {
			return $this->addError ($error,true);			
		}
		
		function hasErrors () {
			return sizeof ($this->errors);
		}
		
		function getErrorStr () {
			if (!$this->errors)
				return "";
			$errorStr = join ("<BR>", $this->errors);
			return $errorStr;
		}
		
		function getParam ($name) {
			if (!empty($this->params[$name]))
				return $this->params[$name];
			return "";
		}
		
		
		
	}
	
?>