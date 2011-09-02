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
			
		    foreach($this->__current_data as $key => $value)
		    {
		        if(preg_match("/^setting([a-z0-9_]+)/i", $key, $matches))
		        {
		            _setSettingOptionValue($matches[1], $value);
		        };
		    };
		    
		    _setSettingOptionValue('CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID', $this->getData('gwID'));
		    
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
		
		function ajax_get_gw_settings()
		{
		    $gw_id = $this->getData('gw_id');
		    
		    $GLOBALS['_RESULT'] = array(
		        'gw_settings' => $this->_getGWsettings($gw_id)
		    );
		    
		    die();
		}
		
		function _getGWsettings($gw_id)
		{
		    $gw_settings = array();
		    if($gw_id != 0)
		    {
    		    $gwObj = SMSMail::getInstance($gw_id);
    		    if(!$gwObj){
    		    	 return $gw_settings;
    		    }
    		    $gw_sets = $gwObj->settings_list();
    		    
    		    foreach($gw_sets as $gws)
    		    {
    		        $_info = settingGetSetting($gws);
    		        
    		        $gw_settings[] = array(
    		            'descr' => '<b>'.(defined($_info['settings_title']) ? constant($_info['settings_title']) : $_info['settings_title']).'</b><div class="small">'.(defined($_info['settings_description']) ? constant($_info['settings_description']) : $_info['settings_description']).'</div>'
    		           ,'form_field' => settingCallHtmlFunction($_info['settings_constant_name'])
    		        );
    		    };
		    };
		    
		    return $gw_settings;
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
			
			if(preg_match("/webasyst\.net$/i", $_SERVER["SERVER_NAME"])) //TODO: check WA version
			{
			    $this->_getWASMSSender();
			}
			else
			{
			    $smarty->assign('SMSGateways', SMSNotify_getSMSSenderConfigIDOptions(array('WASMSSender')));
			    $smarty->assign('GWSettings', $this->_getGWsettings(CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID));
			};
			
			$smarty->assign('SendPeriodHTML', settingCallHtmlFunction('CONF_SMSNOTIFY_SEND_PERIOD').sprintf(translate('sms_current_time'),date('H:i')));
			$smarty->assign('PhoneNumbersHTML', settingCallHtmlFunction('CONF_SMSNOTIFY_PHONES'));
			
			Message::loadData2Smarty();
			
			$smarty->assign('admin_sub_dpt', 'sms_wa.html');
		}
	}
	
	ActionsController::exec('SMSWAController');
?>