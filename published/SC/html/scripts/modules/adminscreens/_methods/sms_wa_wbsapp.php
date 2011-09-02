<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	if(class_exists('ProductWidgetsController',false))return;
	
	require_once(DIR_MODULES.'/ordering/smsmail/class.smsnotify.php');
	require_once(DIR_MODULES.'/ordering/smsmail/class.wasmssender.php');
	
	class SMSWAController extends ActionsController {
		
		function save(){
			
			$Ret = settingCallHtmlFunction('CONF_SMSNOTIFY_SEND_PERIOD');
			settingCallHtmlFunction('CONF_SMSNOTIFY_PHONES');	
			if(is_array($Ret)){
				
				session_register('ERROR_MSG');
				$_SESSION['ERROR_MSG'] = $Ret;
				$MSGInfo['message'] = $_SESSION['ERROR_MSG']['error'];
				$PeriodErrorHTML = $_SESSION['ERROR_MSG']['html'];
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $Ret['error'], '', array('Data' => array('SendPeriodHTML' => $Ret['html'])));
			}
			
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
		}
		
		function _getWASMSSender(){
			
			$configs = modGetModuleConfigs('WASMSSender');
			if(!count($configs)){
				
				$smssenderEntry = new WASMSSender();
				$smssenderEntry->install();
			}else{
				
				$smssenderEntry = SMSMail::getInstance($configs[0]['ConfigID']);
				/*@var $smssenderEntry WASMSSender*/
			}
			
			if(CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID != $smssenderEntry->getModuleConfigID()){
				_setSettingOptionValue('CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID', $smssenderEntry->getModuleConfigID());
			}
			return $smssenderEntry;
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			
			$NotifyObject = new SMSNotify();
			if(!$NotifyObject->is_installed()){
				
				$NotifyObject->install();
			}
			_setSettingOptionValue('CONF_SMSNOTIFY_ENABLED',1);
			
			$this->_getWASMSSender();
			
			$smarty->assign('SendPeriodHTML', settingCallHtmlFunction('CONF_SMSNOTIFY_SEND_PERIOD'));
			$smarty->assign('PhoneNumbersHTML', settingCallHtmlFunction('CONF_SMSNOTIFY_PHONES'));
			
			Message::loadData2Smarty();
			
			$smarty->assign('admin_sub_dpt', 'sms_wa.html');
		}
	}
	
	ActionsController::exec('SMSWAController');
?>