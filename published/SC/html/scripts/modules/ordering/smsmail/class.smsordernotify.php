<?php
require_once(DIR_CLASSES.'/class.virtual.smsmail.php');
require_once(DIR_MODULES.'/ordering/smsmail/class.smsnotify.php');

class SMSOrderNotify extends Module {
	
	var $SingleInstallation = true;
	
	function callFromInstallConfig(){
		
		$PricelistDivision = new Division();
		$PricelistDivision->setName('SMS-�����������');
		$PricelistDivision->setParentID(DivisionModule::getDivisionIDByUnicKey('admin_orders'));
		$PricelistDivision->setEnabled(1);
		$PricelistDivision->save();
		$PricelistDivision->addInterface($this->getConfigID().'_bsms_order_notify');
		
		$OrderingModule = ModulesFabric::getModuleObjByKey('Ordering');
		$OrderingModule->registerInterface2Interface('successful_ordering', $this->getConfigID(), 'successful_ordering_notify');
	}
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'bsms_order_notify' => array(
				'name' => 'SMS-уведомления о заказе - настройки (администрирование)',
				'method' => 'methodBSMSOrderNotify',
				),
			'successful_ordering_notify' => array(
				'name' => 'SMS-уведомление об заказе (администрирование)',
				'method' => 'methodSuccessfulOrderingNotify',
				),
		);
	}
	
	function methodSuccessfulOrderingNotify($_OrderID){
		
		$order = ordGetOrder($_OrderID);
		if (!CONF_BACKEND_SAFEMODE){
			
			$SMSNotify = new SMSNotify();
			$res = $SMSNotify->onEvent('new_order',array('OrderAmount'=>sprintf('%s %0.2f',$order["currency_code"],(float)($order["currency_value"]*(float)$order["order_amount"])), 'OrderNumber'=>$order['orderID_view']));
		}
	}
	
	function methodBSMSOrderNotify(){
		
		global $smarty;
		$xREQUEST_URI = set_query('&install=&setting_up=&uninstall=&enableSMSNotify=&msg=&disableSMSNotify=');
		
		$moduleFiles = GetFilesInDirectory( DIR_MODULES."/ordering/smsmail", "php" );
		
		foreach( $moduleFiles as $fileName )
			require_once( $fileName );
		
		$ModuleObjects = modGetModuleObjects($moduleFiles);
		$MSGInfo = array('status'=>0,'message'=>'');
		
		if(isset($_GET['msg'])){
			
			switch($_GET['msg']){
				case 1:
					$MSGInfo['status'] = 1;
					$MSGInfo['message'] = MSG_INFORMATION_SAVED;
					break;
				case 2:
					if(!isset($_SESSION['ERROR_MSG']))break;
					$MSGInfo['status'] = 2;
					$MSGInfo['message'] = $_SESSION['ERROR_MSG']['error'];
					$PeriodErrorHTML = $_SESSION['ERROR_MSG']['html'];
					unset($_SESSION['ERROR_MSG']);
					break;
			}
		}
		
		if(isset($_POST['SAVE_NOTIFY_SETTINGS'])){
			
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
			}
		
			settingCallHtmlFunction(  'CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID' );
			$Ret = settingCallHtmlFunction(  'CONF_SMSNOTIFY_SEND_PERIOD' );
			settingCallHtmlFunction(  'CONF_SMSNOTIFY_PHONES' );	
			if(is_array($Ret)){
				
				session_register('ERROR_MSG');
				$_SESSION['ERROR_MSG'] = $Ret;
			}
			Redirect( set_query('&msg='.(is_array($Ret)?'2':'1'), $xREQUEST_URI) );
		}
		if(isset($_GET['disableSMSNotify'])){
			
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
			}
		
			$NotifyObject = new SMSNotify();
			if($NotifyObject->is_installed()){
			
				_setSettingOptionValue('CONF_SMSNOTIFY_ENABLED',0);
			}
			Redirect( $xREQUEST_URI );
		}
		if(isset($_GET['enableSMSNotify'])){
			
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
			}
		
			$NotifyObject = new SMSNotify();
			if(!$NotifyObject->is_installed()){
				
				$NotifyObject->install();
			}
			_setSettingOptionValue('CONF_SMSNOTIFY_ENABLED',1);
			Redirect( $xREQUEST_URI );
		}
		if ( isset($_GET["install"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
			}
		
			$ModuleObjects[ (int)$_GET["install"] ]->install();
			Redirect( $xREQUEST_URI );
		}
		if ( isset($_GET["uninstall"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
			}
		
			
			$ModuleConfig = modGetModuleConfig($_GET["uninstall"]);
			if($ModuleConfig['ModuleClassName']){
				
				modUninstallModuleConfig($_GET["uninstall"]);
			}
			Redirect( $xREQUEST_URI );
		}
		$notify_module_id = isset($_GET['setting_up'])?intval($_GET['setting_up']):0;	
		if ($notify_module_id){
			
			$xREQUEST_URI = set_query('&setting_up='.$notify_module_id, $xREQUEST_URI);
			
			if (isset($_POST) && count($_POST)>0)
			{
				if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
				{
					Redirect( "admin.php?dpt=modules&sub=payment&setting_up=".$notify_module_id."&safemode=yes" );
				}
			}
		
			$ModuleObject = null;
			
			$ModuleConfig = modGetModuleConfig($notify_module_id);
			
			if($ModuleConfig['ModuleClassName']){
				
				$ModuleObject = new $ModuleConfig['ModuleClassName']($notify_module_id);
			}
		
			$constants = $ModuleObject->settings_list();
			$settings = array();
			$controls = array();
		
			foreach( $constants as $constant )
			{
				$settings[]	= settingGetSetting( $constant );
				$controls[]	= settingCallHtmlFunction(  $constant );
				$smarty->assign("settings", $settings );
				$smarty->assign("controls", $controls );
			}
		
		//	$ModuleObject->sendSMS('Hello', '79055132663');
			$smarty->assign("ModuleObject", $ModuleObject );
			$smarty->assign("constant_managment", 1);
		}else{
		
			$ModuleConfigs = array();
			$NotifyObject = new SMSNotify();
			
			$_TC = count($ModuleObjects)-1;
			for (; $_TC>=0; $_TC--){
			
				$_tMConfigs = modGetModuleConfigs(get_class($ModuleObjects[$_TC]));
				if(!count($_tMConfigs))continue;
				$ModuleConfigs = array_merge($ModuleConfigs, $_tMConfigs);
			}
			
			$smarty->assign('ModuleObjects', $ModuleObjects);
			$smarty->assign('ModuleConfigs', $ModuleConfigs);
			$smarty->assign('SMSNotifyEnabled', $NotifyObject->is_installed()&&CONF_SMSNOTIFY_ENABLED);
			if($NotifyObject->is_installed()){
				
				$smarty->assign('ConfigIDHTML', settingCallHtmlFunction(  'CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID' ));
				$smarty->assign('SendPeriodHTML', isset($PeriodErrorHTML)?$PeriodErrorHTML:settingCallHtmlFunction(  'CONF_SMSNOTIFY_SEND_PERIOD' ));
				$smarty->assign('PhoneNumbersHTML', settingCallHtmlFunction(  'CONF_SMSNOTIFY_PHONES' ));
			}
		}
		
		$smarty->assign('xREQUEST_URI', $xREQUEST_URI);
		$smarty->assign('MSGInfo', $MSGInfo);
		$smarty->assign('admin_sub_dpt', 'custord_smsmail.tpl.html');
	}
}
?>