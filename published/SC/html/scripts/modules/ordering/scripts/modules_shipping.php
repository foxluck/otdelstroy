<?php
set_query('safemode=','',true);

$moduleFiles = GetFilesInDirectory( './modules/shipping', 'php' );

foreach( $moduleFiles as $fileName )
	include( $fileName );

	
$shipping_module_id = isset($_GET['setting_up'])?intval($_GET['setting_up']):0;	
if ($shipping_module_id)
{
	if (isset($_POST) && count($_POST)>0)
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			RedirectSQ( 'safemode=yes' );
		}
	}

	$ModuleConfig = modGetModuleConfig($shipping_module_id);
	
	if($ModuleConfig['ModuleClassName']&&class_exists($ModuleConfig['ModuleClassName'],false)){
		
		$shipping_module = new $ModuleConfig['ModuleClassName']($shipping_module_id);
	}else{
		
		foreach( $moduleFiles as $fileName )
		{
			$module = null;
			$className = GetClassName( $fileName );
			if(!$className || !class_exists($className))continue;
			$module = new $className();

			if ( $module->get_id() == $shipping_module_id )
			{
				$shipping_module = $module;
				break;
			}
		}
	}

	if(!isset($shipping_module)||!is_object($shipping_module))return false;
	
	$constants = $shipping_module->settings_list();
	$settings = array();
	$controls = array();

	foreach( $constants as $constant ){
		
		$settings[]	 = settingGetSetting( $constant );
		$controls[] = settingCallHtmlFunction(  $constant );
	}
	
	if(isset($_POST['save'])){
		
		Redirect(set_query('__tt='));
	}
	
	$smarty->assign('settings', $settings );
	$smarty->assign('controls', $controls );

	$smarty->assign('shipping_module', $shipping_module );
	$smarty->assign('constant_managment', 1 );
}
else
{

	$shipping_configs = modGetAllInstalledModuleObjs(SHIPPING_RATE_MODULE);
	foreach($shipping_configs as $_Ind=>$_Conf){
	
		$shipping_configs[$_Ind] = array(
			'ConfigID' => $_Conf->get_id(),
			'ConfigName' => $_Conf->title,
			'ConfigClassName' => get_class($_Conf),
			);
	}
	$shipping_modules = array();
	$shipping_methods_by_modules = array();
	foreach( $moduleFiles as $fileName )
	{
		$className = GetClassName( $fileName );
		if(!$className || !class_exists($className))continue;
		
		$shippingModule = new $className();
		$shipping_modules[] = $shippingModule;
		$shipping_methods_by_modules[] = shGetShippingMethodsByModule( $shippingModule );
	}
	
	function cmpShObjs($a, $b) 
	{
	   return strcmp($a->title, $b->title);
	}

	usort($shipping_modules, 'cmpShObjs');

	if ( isset($_GET['install']) )
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			RedirectSQ( 'install=&safemode=yes' );
		}

		if(isset($shipping_modules[$_GET['install']]))$shipping_modules[$_GET['install']]->install();
		RedirectSQ( 'install=' );
	}

	if ( isset($_GET['uninstall']) )
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			RedirectSQ( 'uninstall=&safemode=yes' );
		}

		$ModuleConfig = modGetModuleConfig($_GET['uninstall']);
		if($ModuleConfig['ModuleClassName']){
			
			modUninstallModuleConfig($_GET['uninstall']);
		}else{
			
			foreach ($shipping_configs as $_tModConf){
				
				if($_tModConf['ConfigID']==(int)$_GET['uninstall']){
					
					$_tModConf = new $_tModConf['ConfigClassName']();
					$_tModConf->uninstall();
					break;
				}
			}
		}
		RedirectSQ('uninstall=');
	}
	
	$smarty->assign( 'shipping_modules', $shipping_modules );
	$smarty->assign( 'shipping_methods_by_modules', $shipping_methods_by_modules );
	$smarty->assign ( 'shipping_configs' ,  $shipping_configs);
}

$smarty->assign('admin_sub_dpt', 'modules_shipping.tpl.html');
?>