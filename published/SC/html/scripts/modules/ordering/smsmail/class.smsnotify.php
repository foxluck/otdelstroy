<?php
//require_once(DIR_CLASSES.'/class.virtual.module.php');

function SMSNotify_getSMSSenderConfigIDOptions($exclude = array()){

	$SMSMailObjects = modGetAllInstalledModuleObjs(SMSMAIL_MODULE,true);
	$excludeAdditional = $exclude;
	foreach ($SMSMailObjects as $SMSMailObject){
		$excludeAdditional[] = get_class($SMSMailObject);
	}
	
	$FullSMSMailObjects = modGetModuleObjects(GetFilesInDirectory(DIR_MODULES.'/ordering/smsmail/','php'),$excludeAdditional);
	
	foreach ($FullSMSMailObjects as $SMSMailObject){
		$SMSMailObject->install();
		$SMSMailObjects[] = $SMSMailObject;
	}
	
	$Options = array(array('title'=>'-', 'value'=>'0'));
	$_TC = count($SMSMailObjects);
	for($_i=0;$_i<$_TC;$_i++)
	{
		if(in_array(get_class($SMSMailObjects[$_i]), $exclude))
		{
		    continue;
		};
		$Options[] = array(
			'title' => $SMSMailObjects[$_i]->getTitle(),
			'value' => $SMSMailObjects[$_i]->getModuleConfigID(),
			);
	}
	return $Options;
}

function SMSNotify_setting_PERIOD($_SettingID){
	
	$SettingConstName = settingGetConstNameByID($_SettingID);
	if (@$_POST['action'] == 'save' && isset($_POST[$SettingConstName.'_from']) && isset($_POST[$SettingConstName.'_till'])) {
		
		$dfrom = explode(':', $_POST[$SettingConstName.'_from']);
		$dtill = explode(':', $_POST[$SettingConstName.'_till']);
		if(!isset($dfrom[1]))
			$dfrom[1] = '';
		if(!isset($dtill[1]))
			$dtill[1] = '';
		if(	$dfrom[0]>23 || $dfrom[0]<0 || $dfrom[1]>59 || $dfrom[1]<0 ||
			$dtill[0]>23 || $dtill[0]<0 || $dtill[1]>59 || $dtill[1]<0 ||
			!preg_match('/^\d\d\:\d\d$/',$_POST[$SettingConstName.'_from']) 
			|| !preg_match('/^\d\d\:\d\d$/',$_POST[$SettingConstName.'_till'])){
			
			return array(
				'error'=>'sms_notify_error_period',
				'html'=>'
	<table>
		<tr>
			<td><input name="'.$SettingConstName.'_from" value="'.xHtmlSpecialChars($_POST[$SettingConstName.'_from']).'" type="text" size="5" /></td>
			<td> - </td>
			<td><input name="'.$SettingConstName.'_till" value="'.xHtmlSpecialChars($_POST[$SettingConstName.'_till']).'" type="text" size="5" /></td>
		</tr>
		<tr>
			<td>HH:MM</td>
			<td></td>
			<td>HH:MM</td>
		</tr>
	</table>
		'
			);
		}
		_setSettingOptionValueByID($_SettingID, $_POST[$SettingConstName.'_from'].'-'.$_POST[$SettingConstName.'_till']);
	}
	
	$SettingConstVal = _getSettingOptionValueByID($_SettingID);
	@list($FromTime,$TillTime) = explode('-',$SettingConstVal);
	return '
	<table>
		<tr>
			<td><input name="'.$SettingConstName.'_from" value="'.xHtmlSpecialChars($FromTime).'" type="text" size="5" /></td>
			<td> - </td>
			<td><input name="'.$SettingConstName.'_till" value="'.xHtmlSpecialChars($TillTime).'" type="text" size="5" /></td>
		</tr>
		<tr>
			<td>HH:MM</td>
			<td></td>
			<td>HH:MM</td>
		</tr>
	</table>
		';
}

function SMSNotify_setting_Phones($_SettingID){
	
	$SettingConstName = settingGetConstNameByID($_SettingID);
	
	if (@$_POST['action'] == 'save' && isset($_POST[$SettingConstName])){
		
		$_ind = 0;
		$savePhones = array();
		foreach ($_POST[$SettingConstName] as $_ind=>$_val){
			
			if($_POST[$SettingConstName][$_ind] && $_ind<15 && !in_array($_POST[$SettingConstName][$_ind], $savePhones) && $_POST[$SettingConstName][$_ind]!=translate('sms_new_phone_number')){
				$savePhones[] = $_POST[$SettingConstName][$_ind];
			}
		}
		
		_setSettingOptionValueByID($_SettingID, implode(',', $savePhones));
	}
	
	$SettingConstVal = _getSettingOptionValueByID($_SettingID);
	$Phones = explode(',', $SettingConstVal);
	$HTML= '';
	foreach ($Phones as $_Phone){
		
		if($_Phone)
		$HTML .= '<input name="'.$SettingConstName.'[]" value="'.xHtmlSpecialChars($_Phone).'" type="text" /><br />';
	}
	$HTML .= '<hr /><input name="'.$SettingConstName.'[]" rel="'.translate('sms_new_phone_number').'" value="'.translate('sms_new_phone_number').'" type="text" class="input_message" /><br />';
	return $HTML;
}

class SMSNotify extends virtualModule {
	
	var $SingleInstall = true;
	
	function _initVars(){

		$this->title = 'SMS-Уведомления';
		
		$this->Settings = array(
			'CONF_SMSNOTIFY_ENABLED',
			'CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID',
			'CONF_SMSNOTIFY_SEND_PERIOD',
			'CONF_SMSNOTIFY_PHONES',
			);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_SMSNOTIFY_ENABLED'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> '', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_SELECT_BOX(SMSNotify_getSMSSenderConfigIDOptions(),', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSNOTIFY_SEND_PERIOD'] = array(
			'settings_value' 		=> '00:00-23:59', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'SMSNotify_setting_PERIOD(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSNOTIFY_PHONES'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'SMSNotify_setting_Phones(', 
			'sort_order' 			=> 1,
		);
	}

	function onEvent($_Event, $_Params){
		
		if(!$this->is_installed())return -1;
		if(!$this->_getSettingValue('CONF_SMSNOTIFY_ENABLED')) return -2;
		switch ($_Event){
			case 'new_order':
				$_tSmarty = new ViewSC();
				$_tSmarty->assign('OrderNumber', $_Params['OrderNumber']);
				$_tSmarty->assign('OrderAmount', $_Params['OrderAmount']);
				return $this->notify($_tSmarty->fetch('backend/smsmail_neworder.html'));
		}
	}
	
	function notify($_Message){
		
		$SMSsender = SMSMail::getInstance($this->_getSettingValue('CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID'));
				
		$Phones = explode(',', $this->_getSettingValue('CONF_SMSNOTIFY_PHONES'));
	
		if(preg_match('/^\d\d\:\d\d\-\d\d\:\d\d$/', $this->_getSettingValue('CONF_SMSNOTIFY_SEND_PERIOD'))){

			@list($FromTime,$TillTime) = explode('-', $this->_getSettingValue('CONF_SMSNOTIFY_SEND_PERIOD'));
			
			$FromTime = explode(':', $FromTime);
			$FromTime = $FromTime[0]*60+$FromTime[1];
			
			$TillTime = explode(':',$TillTime);
			$TillTime = $TillTime[0]*60+$TillTime[1];
			
			$CurrentTime = date("H")*60+date("i");
			
			if($TillTime>=$FromTime){
				if(!($TillTime>=$CurrentTime&&$CurrentTime>=$FromTime))return -3;
			}
			else {
				if($CurrentTime<$FromTime&&$CurrentTime>$TillTime)return -4;
			}
		}
		
		if(!$SMSsender)return -5;
		if(!count($Phones))return -6;
		
		$SMSsender->sendSMS($_Message, $Phones);
		
		return 1;
	}
}
?>