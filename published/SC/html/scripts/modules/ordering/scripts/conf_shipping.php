<?php
	set_query('save_successful=&safemode=', '', true);
	
	$moduleFiles = GetFilesInDirectory( './modules/shipping', 'php' );
	
	foreach( $moduleFiles as $fileName )
		include( $fileName );
	
	if (isset($_GET['save_successful'])) //show successful save confirmation message
		$smarty->assign('configuration_saved', 1);
	
	if (isset($_GET['delete'])) //delete shipping type
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			RedirectSQ( 'delete=&safemode=yes' );
		}
		shDeleteShippingMethod($_GET['delete']);
		RedirectSQ( 'delete=' );
	}
	
	if (isset($_POST['save_shipping'])){ //save shipping and payment types

		safeMode(true);
	
		$values = scanArrayKeysForID($_POST, array( 'Enabled', 'Name_\w{2}', 'description_\w{2}', 'email_comments_text_\w{2}', 'module', 'sort_order' ) );
		foreach( $values as $key => $value ){
			
			shUpdateShippingMethod($key, $value, $value, isset($value['Enabled'])?1:0, $value['sort_order'], $value['module'], $value );
		}
	
		if ( !LanguagesManager::ml_isEmpty('Name', $_POST) ){
			
			shAddShippingMethod( $_POST, $_POST, 
						isset($_POST['Enabled'])?1:0, (int)$_POST['sort_order'],
						$_POST['module'], $_POST );
		}
	
		RedirectSQ( 'save_successful=yes' );
	
	}
	
	
	/**
	 * get all installed module objects
	 */
	$smarty->assign( 'shipping_types', shGetAllShippingMethods() );
	$smarty->assign( 'shipping_modules', modGetAllInstalledModuleObjs(SHIPPING_RATE_MODULE) );
	
	
	//set sub-department template
	$smarty->assign('admin_sub_dpt', 'conf_shipping.tpl.html');
?>