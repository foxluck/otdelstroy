<?php

	define('MSG_SUCCESS',1);
	define('MSG_ERROR',2);
	define('MSG_NOTIFY',3);

	class Message{

		var $Code;
		var $Message;
		var $Actions;
		var $Fields;
		var $Data;
		var $Exists = false;
		var $Type = null;

		function Message(){

			$this->Actions = array();
		}

		function addAction($Action){

			$this->Actions[] = $Action;
		}

		function ActionExists($Action){

			if(is_array($this->Actions)){

				return in_array($Action, $this->Actions);
			}else{

				return $Action==$this->Actions;
			}
		}

		function is_set(){

			return $this->Exists;
		}

		function isMessage($entry){

			return ($entry instanceof message)?true:false;
		}

		function getMessage(){

			return $this->Message;
		}

		function setMessage($Message){

			$this->Message = $Message;
		}

		/**
		 * Enter description here...
		 *
		 * @param unknown_type $Message
		 * @param unknown_type $Code
		 * @return Error
		 */
		static function &raiseMessage($Type, $Message, $Code = ''){

			$Msg = new Message();
			$Msg->Exists = true;
			$Msg->setMessage($Message);
			$Msg->Code = $Code;
			$Msg->Type = $Type;

			return $Msg;
		}

		static function &raiseCurrentMessage($Type, $MessageText, $Code = ''){

			$Register = &Register::getInstance();
			/*@var $Register Register*/

			$Message = &$Register->get(VAR_MESSAGE);
			$Message = &Message::raiseMessage($Type, $MessageText, $Code);

			return $Message;
		}

		static function raiseMessageRedirect($Type, $URL, $Message, $Code = '', $params = null){

			$Msg = &Message::raiseMessage($Type, $Message,$Code?$Code:getUniqueWDataID());
			if(!is_null($params)){
				foreach ($params as $k=>$v)
					$Msg->{$k} = $v;
			}
			storeWData($Msg->Code, $Msg);

			RedirectSQ('msg='.$Msg->Code,$URL);
		}

		static function raiseMessageRedirectSQ($Type, $URL, $Message, $Code = '', $params = null){

			Message::raiseMessageRedirect($Type, renderURL($URL), $Message, $Code, $params);
		}

		static function throwMessage($message, $type = MSG_ERROR){

			print '<pre>';
			print_r(array($message, $type));
			print '</pre>';
		}

		static function raiseAjaxMessage($type, $code=0, $message = '', $params = null){

			global $_RESULT;

			if(!is_array($_RESULT))$_RESULT = array();

			$_RESULT['_AJAXMESSAGE'] = array(
					'type' => $type,
					'message' => translate($message),
					'code' => $code,
					'params' => $params
			);
		}

		/**
		 * Static method load data from current message to smarty
		 *
		 * @param string | null $var - smarty variable name. if null load Data as assoc
		 */
		static function loadData2Smarty($var = null){

			$Register = &Register::getInstance();
			/*@var $Register Register*/

			$Message = &$Register->get(VAR_MESSAGE);
			/* @var $Message Message */
			if(!Message::isMessage($Message) || !$Message->is_set())return;

			$smarty = &$Register->get(VAR_SMARTY);
			/*@var $smarty Smarty*/

			if(is_null($var) && is_array($Message->Data) && count($Message->Data)){
				$smarty->assign($Message->Data);
				return true;
			}else{
				$smarty->assign($var, $Message->Data);
				return true;
			}
		}
	}
?>