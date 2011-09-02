<?php
	$Register = &Register::getInstance();
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	
	/**
	 * Modules uninstalling
	 */
	if(isset($PostVars['delete_configs'])){
		
		if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
			Redirect('safemode=yes');
		}
		
		$r_config_id = array_keys(scanArrayKeysForID($PostVars, 'config_id'));
		
		foreach ($r_config_id as $config_id){
			
			$Module = PaymentModule::getInstance($config_id);
			if(is_null($Module)){
				continue;
			}
			$Module->uninstall();
		}
		RedirectSQ();
	}
	
	$moduleFiles = GetFilesInDirectory( './modules/payment', 'php' );
	
	foreach( $moduleFiles as $fileName )
		include( $fileName );
	
	$payment_module_id = isset($_GET['setting_up'])?intval($_GET['setting_up']):0;
	if ($payment_module_id)
	{
		if (isset($_POST) && count($_POST)>0&&CONF_BACKEND_SAFEMODE){//this action is forbidden when SAFE MODE is ON
			RedirectSQ( 'safemode=yes' );
		}
	
		$payment_module = null;
		
		$ModuleConfig = modGetModuleConfig($payment_module_id);
		
		if($ModuleConfig['ModuleClassName']&&class_exists($ModuleConfig['ModuleClassName'],false)){
			
			$payment_module = new $ModuleConfig['ModuleClassName']($payment_module_id);
		}else{
			
			foreach( $moduleFiles as $fileName )
			{
				$module = null;
				$className = GetClassName( $fileName );
				if(!$className)continue;
				$module = new $className();
				if ( $module->get_id() == $payment_module_id )
				{
					$payment_module = $module;
					break;
				}
			}
		}
	
		if(!isset($payment_module)||!is_object($payment_module))return false;
		
		$constants = $payment_module->settings_list();
		$settings = array();
		$controls = array();
	
		foreach( $constants as $constant )
		{
			$settings[]	= settingGetSetting( $constant );
			$controls[]	= settingCallHtmlFunction(  $constant );
			$smarty->assign('settings', $settings );
			$smarty->assign('controls', $controls );
		}
	
		$smarty->assign('payment_module', $payment_module );
		$smarty->assign('constant_managment', 1);
	}else{
	
		$payment_modules = array();
		$payment_methods_by_modules = array();
		$payment_configs = modGetAllInstalledModuleObjs(PAYMENT_MODULE);

		foreach($payment_configs as $_Ind=>$_Conf){
		
			$payment_configs[$_Ind] = array(
				'ConfigID' => $_Conf->get_id(),
				'ConfigName' => $_Conf->title,
				'ConfigClassName' => get_class($_Conf),
				);
		}
		
		foreach( $moduleFiles as $fileName ){
			
			$className = GetClassName( $fileName );
			if(!$className || !class_exists($className))continue;
				
			$paymentModule = new $className();
			$payment_modules[] = $paymentModule;
			$payment_methods_by_modules[] = payGetPaymentMethodsByModule( $paymentModule );
		}
		
		function cmpPObjs($a, $b){
		   return strcmp($a->title, $b->title);
		}
	
		usort($payment_modules, 'cmpPObjs');

		/**
		 * Module installing
		 */
		if(isset($GetVars['install'])){
			
			if(CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
				RedirectSQ( 'install=&safemode=yes' );
			}
	
			if(!isset($payment_modules[(int)$GetVars['install']]))RedirectSQ('install=');
			
			$Module = &$payment_modules[(int)$GetVars['install']];
			/* @var $Module virtualModule */
			$Module->install();
			RedirectSQ('install=&setting_up='.$Module->getModuleConfigID());
		}
	
		$smarty->assign('payment_modules', $payment_modules );
		$smarty->assign('payment_methods_by_modules', $payment_methods_by_modules );
		$smarty->assign ( 'payment_configs' ,  $payment_configs);
	}
	
	$smarty->assign('admin_sub_dpt', 'modules_payment.tpl.html');
?>