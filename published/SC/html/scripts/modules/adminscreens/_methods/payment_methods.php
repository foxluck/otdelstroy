<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
ClassManager::includeClass('PaymentModule');

function cmpPObjs($a, $b){
	
	if(strtolower(get_class($a)) == 'manualpayment')return 1;
	if(strtolower(get_class($b)) == 'manualpayment')return -1;
	if(strtolower(get_class($a)) == 'cmanualccprocessing')return -1;
	if(strtolower(get_class($b)) == 'cmanualccprocessing')return 1;
	return strcmp($a->title, $b->title);
}

class PaymentMethodsController extends ActionsController{
	
	function __filter_modules(&$payment_modules, $method_type = null){
		
			for($j_max = count($payment_modules)-1; $j_max>=0; $j_max--){

				if( !is_null($method_type) && $payment_modules[$j_max]->type != $method_type){
				
					unset($payment_modules[$j_max]);
				}elseif($payment_modules[$j_max]->language && sc_getSessionData('LANGUAGE_ISO3')!=$payment_modules[$j_max]->language){
					unset($payment_modules[$j_max]);
				}

			}
			
			usort($payment_modules, 'cmpPObjs');
	}
	
	function save_new_method(){

		$paymentModule = PaymentModule::getInstance($this->getData('module_id'));

		if(is_object($paymentModule)){
			$constants = $paymentModule->settings_list();
			$_POST['save'] = 1;		
			foreach( $constants as $constant ){
				settingCallHtmlFunction(  $constant );
			}
		}else{
			RedirectSQ('action=add_method');
		}
		
		if(LanguagesManager::ml_isEmpty('Name', $this->getData())){
			
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'action=add_method&mid='.$this->getData('module_id'), 'pmnt_empty_name', '', array('Fields' => 'Name','Data' => $this->getData()));
		}
		
	 	$PID = payAddPaymentMethod( $this->getData(), $this->getData(), $this->getData('Enabled'), payGetMaxSortOrder()+1, $this->getData(), $this->getData('module_id'), $this->getData('calculate_tax'),$this->getData('logo') );
	 	
		$shipping_methods = shGetAllShippingMethods();
	 	foreach( $shipping_methods as $shipping_method ){
	 		
			if(!$this->getData('ShippingMethodsToAllow_'.$shipping_method['SID']))continue;
			paySetPaymentShippingMethod( $PID, $shipping_method['SID'] );
		}
		unsetWData('__PAY_INSTALL_CONFIG_ID');
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?did=&mt=', 'msg_information_saved');
	}
	
	function add_method(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		$PostVars = &$Register->get(VAR_POST);
		
		$method_type = $this->getData('mt');
		$module_id = $this->getData('mid');
		$module_class = $this->getData('mclass');
		
		if(isset($PostVars['mt'])){
			$method_type = $PostVars['mt'];
			renderURL('mt='.$method_type, '', true);
		}
		if(isset($PostVars['mid'])){
			$module_id = $PostVars['mid'];
			renderURL('mid='.$module_id, '', true);
		}
		if(isset($PostVars['module_id'])){
			$module_id = $PostVars['module_id'];
			renderURL('mid='.$module_id, '', true);
		}
		if(isset($PostVars['mclass'])){
			$module_class = $PostVars['mclass'];
			renderURL('mclass='.$module_class, '', true);
		}
		$module_language = sc_getSessionData('LANGUAGE_ISO3');
		
		if(!$method_type){/* Choose method type */
			
			$paymtd_types = array(
				PAYMTD_TYPE_CC => array('title'=>'pmnt_paymtd_cc_description','count'=>0),
				PAYMTD_TYPE_ONLINE => array('title'=>'pmnt_paymtd_online_description','count'=>0),
				PAYMTD_TYPE_MANUAL => array('title'=>'pmnt_paymtd_manual_description','count'=>0),
				);
			//$payment_modules = modGetModuleObjects(GetFilesInDirectory( DIR_MODULES.'/payment', 'php','class\..+' ));
			$payment_modules = PaymentModule::getModules($module_language);

			//$this->__filter_modules($payment_modules);
			foreach ($payment_modules as $_module){
				/*@var $_module PaymentModule */
				if($_module->type&&isset($paymtd_types[$_module->type])){
					$paymtd_types[$_module->type]['count']++;
				}
			}
			
			//unset($paymtd_types[PAYMTD_TYPE_REPLACE]);

			$smarty->assign('paymtd_types', $paymtd_types);
			$smarty->assign('step', 'type');
			$smarty->assign('step_num', 1);
		}elseif(!$module_class){/* Choose module */
			
			//$payment_modules = modGetModuleObjects(GetFilesInDirectory( DIR_MODULES.'/payment', 'php' ));
			//$this->__filter_modules($payment_modules, $method_type);
			
			$payment_modules = PaymentModule::getModules($module_language,$method_type);
			$more_modules = false;
			if(!SystemSettings::is_hosted()&&($method_type == PAYMTD_TYPE_CC)){
				$more_modules = true;
				foreach($payment_modules as &$payment_module){
					if(strtolower(get_class($payment_module)) == 'cauthorizenetaim'){
							$more_modules = false;
						break;
					}
				}
				
			}
			$smarty->assign('more_modules',$more_modules);

			$smarty->assign('payment_modules', $payment_modules);
			$smarty->assign('method_type_const', 'pmnt_mtdtype_title_'.$method_type);
			$smarty->assign('step', 'module');
			$smarty->assign('step_num', 2);
		}else{/* Settings */
			$fill_default_data = isset($PostVars['mclass']);
			//$payment_modules = modGetModuleObjects(GetFilesInDirectory( DIR_MODULES.'/payment', 'php' ));
			//$this->__filter_modules($payment_modules, $method_type);
			
			//PaymentModule::getm
			if($module_id){
				$paymentModule = PaymentModule::getInstance($module_id);
			}else{
			
				$payment_modules = PaymentModule::getModules($module_language,$method_type,$module_class);
				if(count($payment_modules)){
					$paymentModule = $payment_modules[0];
				}
				//$paymentModule = &$payment_modules[$module_id];
				$shipping_methods = shGetAllShippingMethods();
				$default_data = array('calculate_tax' => 1, 'Enabled' => 1);
				if($fill_default_data){
					foreach ($shipping_methods as $_method)
						$default_data['ShippingMethodsToAllow_'.$_method['SID']] = 1;
				}
			}
			if(is_object($paymentModule)){
				
				/* @var $paymentModule PaymentModule*/
				if(!issetWData('__PAY_INSTALL_CONFIG_ID')){
					$paymentModule->install();
					storeWData('__PAY_INSTALL_CONFIG_ID', $paymentModule->getModuleConfigID());
				}else{

					$r_pay = PaymentModule::getInstance(loadWData('__PAY_INSTALL_CONFIG_ID'));
					if(get_class($paymentModule) != get_class($r_pay)){
						if(is_object($r_pay))
						$r_pay->uninstall();
						$paymentModule->install();
						storeWData('__PAY_INSTALL_CONFIG_ID', $paymentModule->getModuleConfigID());
					}
				}
				
				settingDefineConstants();
				$paymentModule->load(loadWData('__PAY_INSTALL_CONFIG_ID'));
				
				if($fill_default_data){
					
					$default_data[LanguagesManager::ml_getLangFieldName('Name')] = $paymentModule->method_title; 
					$default_data[LanguagesManager::ml_getLangFieldName('description')] = $paymentModule->method_description;
					$default_data['logo'] =  $paymentModule->default_logo;
				}
				
				$constants = $paymentModule->settings_list();
				
				$settings = array();
				$controls = array();
			
				foreach( $constants as $constant ){
					
					$settings[]	= settingGetSetting( $constant );
					$controls[]	= settingCallHtmlFunction(  $constant );
					
				}
				
				if(method_exists($paymentModule,'getCustomProperties')){
					$_settings = $paymentModule->getCustomProperties();
					foreach($_settings as $_setting_item){
						$settings[] = $_setting_item;
						$controls[] = $_setting_item['control'];
					}
				}
				$smarty->assign('settings', $settings );
				$smarty->assign('controls', $controls );
					
				Message::loadData2Smarty('data');
				
				$smarty->assign('module_id', $paymentModule->getModuleConfigID());
				$smarty->assign('moduleEntry', $paymentModule);
			}
			
			if($fill_default_data){
				$smarty->assign('data', $default_data);
			}
			$smarty->assign('ShippingMethodsToAllow', $shipping_methods);
			$smarty->assign('step', 'settings');
			$smarty->assign('step_num', 3);
		}
		$smarty->assign('admin_sub_dpt', 'add_payment_method.html');
	}
	
	function save_method(){
		
		safeMode(true);
		
		$PID = $this->getData('pid');
		
		$payment_method = payGetPaymentMethodById($PID);
		if(!$payment_method)RedirectSQ();
		
		$paymentModule = PaymentModule::getInstance($payment_method['module_id'], PAYMENT_MODULE);

		if(is_object($paymentModule)){
			$constants = $paymentModule->settings_list();
			$_POST['save'] = 1;		
			foreach( $constants as $constant ){
				settingCallHtmlFunction(  $constant );
			}
		}
		
		if(LanguagesManager::ml_isEmpty('Name', $this->getData())){
			
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'action=edit_method', 'pmnt_empty_name', '', array('Fields' => 'Name','Data' => $this->getData()));
		}

		payUpdatePaymentMethod( $PID, $this->getData(), $this->getData(), $this->getData('Enabled'), $payment_method['sort_order'], $payment_method['module_id'], $this->getData(), $this->getData('calculate_tax'), $this->getData('logo'));
		payResetPaymentShippingMethods($PID);
		$shipping_methods = shGetAllShippingMethods();
		
		foreach( $shipping_methods as $shipping_method ){
			
			if(!$this->getData('ShippingMethodsToAllow_'.$shipping_method['SID']))continue;
			paySetPaymentShippingMethod( $PID, $shipping_method['SID'] );
		}
			
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}
	
	function edit_method(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		
		$PID = $this->getData('pid');
		
		$payment_method = payGetPaymentMethodById($PID);
		if(!$payment_method)RedirectSQ();
		
		$payment_method['ShippingMethodsToAllow'] = _getShippingMethodsToAllow($PID);
		
		$paymentModule = PaymentModule::getInstance($payment_method['module_id']);
		if(is_object($paymentModule)){
			$constants = $paymentModule->settings_list();
			$settings = array();
			$controls = array();
		
			foreach( $constants as $constant )
			{
				$setting = settingGetSetting( $constant );
				if(defined($setting['settings_title'])){
					$setting['settings_title'] = constant($setting['settings_title']);
				}
				if(defined($setting['settings_description'])){
					$setting['settings_description'] = constant($setting['settings_description']);
				}
				
				$settings[]	= $setting;
				$controls[]	= settingCallHtmlFunction(  $constant );
				
			}
			if(method_exists($paymentModule,'getCustomProperties')){
				$_settings = $paymentModule->getCustomProperties();
				foreach($_settings as $_setting_item){
					$settings[] = $_setting_item;
					$controls[] = $_setting_item['control'];
				}
			}
			
			$smarty->assign('settings', $settings );
			$smarty->assign('controls', $controls );
			$smarty->assign('moduleEntry', $paymentModule);
		}
		
		$smarty->assign('PID', $PID);
		$smarty->assign('payment_method', $payment_method);
		Message::loadData2Smarty('payment_method');
		$smarty->assign('admin_sub_dpt', 'edit_payment_method.html');
	}
	
	function save_order(){
		
		
		$scan_result = scanArrayKeysForID($_POST, 'priority');
		$sql = '
			UPDATE ?#PAYMENT_TYPES_TABLE SET sort_order=? WHERE PID=?
		';
		
		foreach ($scan_result as $PID=>$scan_info){
			
			db_phquery($sql, $scan_info['priority'], $PID);
		}
		
		Message::raiseAjaxMessage(MSG_SUCCESS, '', 'order_saved');
		die;
	}
	
	function delete_method(){
		
		$PID = $this->getData('pid');
		$payment_method = payGetPaymentMethodById($PID);
		if(!$payment_method)RedirectSQ();
		
		payDeletePaymentMethod($PID);
		
		$paymentModule = PaymentModule::getInstance($payment_method['module_id'], PAYMENT_MODULE);
		/* @var $paymentModule PaymentModule*/
		if(!is_object($paymentModule))RedirectSQ();
		
		$paymentModule->uninstall();
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'pmnt_method_removed');
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$payment_methods = payGetAllPaymentMethods( false, false);

		$smarty->assign('payment_methods', $payment_methods);
		$smarty->assign('admin_sub_dpt', 'payment_methods.html');
	}
}

ActionsController::exec('PaymentMethodsController');
?>