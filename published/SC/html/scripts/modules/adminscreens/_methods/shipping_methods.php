<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
function cmpPObjs($a, $b){
	if(strtolower(get_class($a)) == 'customshipping')return -1;
	if(strtolower(get_class($b)) == 'customshipping')return 1;
	return strcmp($a->title, $b->title);
}

class ShippingMethodsController extends ActionsController{

	function __filter_modules(&$modules){
		$language = sc_getSessionData('LANGUAGE_ISO3');
		foreach($modules as $id=>&$module){
			/*@var $module ShippingRateCalculator*/
			if($module->language &&($language != $module->language)){
				unset($modules[$id]);
			}
		}
		usort($modules, 'cmpPObjs');
	}

	function save_new_method(){

		$refresh = $this->existsData('save');
		$moduleEntry = ShippingRateCalculator::getInstance($this->getData('module_id'));

		if(is_object($moduleEntry)){
			$constants = $moduleEntry->settings_list();
			$_POST['save'] = $refresh?'refresh':1;
			foreach( $constants as $constant ){
				settingCallHtmlFunction(  $constant );
			}
		}

		if($refresh){

			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'action=add_method', 'aa', '', array('name' => 'aaa', 'Data' => $this->getData()));
		}else{
			if(LanguagesManager::ml_isEmpty('Name', $this->getData())){

				Message::raiseMessageRedirectSQ(MSG_ERROR, 'action=add_method', 'shp_empty_name', '', array('Fields' => 'Name','Data' => $this->getData()));
			}
				
			$SID = shAddShippingMethod( $this->getData(), $this->getData(), $this->getData('Enabled'), shGetMaxSortOrder()+1, $this->getData('module_id'), $this->getData(), $this->getData('logo'));
			unsetWData('__SHP_INSTALL_CONFIG_ID');
				
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?did=', 'msg_information_saved');
		}
	}

	function add_method(){

		renderURL('action=add_method', '', true);

		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		$PostVars = &$Register->get(VAR_POST);

		$module_id = $this->getData('mid');

		if(isset($PostVars['mid'])){
			$module_id = $PostVars['mid'];
			renderURL('mid='.$module_id, '', true);
		}

		if($module_id === ''){/* Choose module */
				
			$modules = modGetModuleObjects(GetFilesInDirectory( DIR_MODULES.'/shipping', 'php' ));
			$this->__filter_modules($modules);

			$smarty->assign('modules', $modules);
			$smarty->assign('step', 'module');
			$smarty->assign('step_num', 1);
		}else{ /* Settings */
				
			$fill_default_data = isset($PostVars['mid']);
			$modules = modGetModuleObjects(GetFilesInDirectory( DIR_MODULES.'/shipping', 'php' ));
			$this->__filter_modules($modules);
			$moduleEntry = &$modules[$module_id];
			$default_data = array('Enabled' => 1);
				
			if(is_object($moduleEntry)){

				/* @var $moduleEntry ShippingRateCalculator*/
				if(!issetWData('__SHP_INSTALL_CONFIG_ID')){
					$moduleEntry->install();
					storeWData('__SHP_INSTALL_CONFIG_ID', $moduleEntry->getModuleConfigID());
				}else{

					$r_module = ShippingRateCalculator::getInstance(loadWData('__SHP_INSTALL_CONFIG_ID'));
					if(get_class($moduleEntry) != get_class($r_module)){

						if(is_object($r_module))$r_module->uninstall();
						$moduleEntry->install();
						storeWData('__SHP_INSTALL_CONFIG_ID', $moduleEntry->getModuleConfigID());
					}
				}

				settingDefineConstants();
				$moduleEntry->load(loadWData('__SHP_INSTALL_CONFIG_ID'));
				if($fill_default_data){
						
					$default_data[LanguagesManager::ml_getLangFieldName('Name')] = $moduleEntry->method_title;
					$default_data[LanguagesManager::ml_getLangFieldName('description')] = $moduleEntry->method_description;
					$default_data['logo'] = $moduleEntry->default_logo;
				}
				$constants = $moduleEntry->settings_list();

				$settings = array();
				$controls = array();
					
				foreach( $constants as $constant ){
						
					$settings[]	= settingGetSetting( $constant );
					$controls[]	= settingCallHtmlFunction(  $constant );
					
				}
				
				$smarty->assign('settings', $settings );
				$smarty->assign('controls', $controls );

				$smarty->assign('module_id', $moduleEntry->getModuleConfigID());
				$smarty->assign('moduleEntry', $moduleEntry);
			}

			if($fill_default_data)
			$smarty->assign('data', $default_data);
			Message::loadData2Smarty('data');
			$smarty->assign('step', 'settings');
			$smarty->assign('step_num', 2);
		}
		$smarty->assign('admin_sub_dpt', 'add_shipping_method.html');
	}

	function save_method(){

		safeMode(true);

		$refresh = $this->existsData('save');
		$SID = $this->getData('sid');

		$shipping_method = shGetShippingMethodById($SID);
		if(!$shipping_method)RedirectSQ();

		$shippingModule = ShippingRateCalculator::getInstance($shipping_method['module_id']);

		if(is_object($shippingModule)){
			$constants = $shippingModule->settings_list();
			$_POST['save'] = $refresh?'refresh':1;
			foreach( $constants as $constant ){
				settingCallHtmlFunction(  $constant );
			}
		}

		if($refresh){

			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'action=edit_method', '', '', array('name' => 'refresh', 'Data' => $this->getData()));
		}else{

			if(LanguagesManager::ml_isEmpty('Name', $this->getData())){

				Message::raiseMessageRedirectSQ(MSG_ERROR, 'action=edit_method', 'shp_empty_name', '', array('Fields' => 'Name','Data' => $this->getData()));
			}

			shUpdateShippingMethod($SID, $this->getData(), $this->getData(), $this->getData('Enabled'), $shipping_method['sort_order'], $shipping_method['module_id'], $this->getData(), $this->getData('logo'));

			Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?did=', 'msg_information_saved', '');
		}
	}

	function edit_method(){

		renderURL('action=edit_method', '', true);
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$SID = $this->getData('sid');

		$shipping_method = shGetShippingMethodById($SID);
		if(!$shipping_method)RedirectSQ();

		$shippingModule = ShippingRateCalculator::getInstance($shipping_method['module_id']);
		/* @var $shippingModule ShippingRateCalculator */

		if(is_object($shippingModule)){
			$constants = $shippingModule->settings_list();
				
			$shippingModule->_initSettingFields();
			$settings = array();
			$controls = array();

			foreach( $constants as $constant ){
				$settings[]	= settingGetSetting( $constant );
				$controls[] = settingCallHtmlFunction(  $constant );
			}
			
			if(method_exists($shippingModule,'getCustomProperties')){
				$_settings = $shippingModule->getCustomProperties();
				foreach($_settings as $_setting_item){
					$settings[] = $_setting_item;
					$controls[] = $_setting_item['control'];
				}
			}

			
			$smarty->assign('settings', $settings );
			$smarty->assign('controls', $controls );
			$smarty->assign('moduleEntry', $shippingModule);
		}

		$smarty->assign('SID', $SID);
		$smarty->assign('shipping_method', $shipping_method);
		Message::loadData2Smarty('shipping_method');
		$smarty->assign('admin_sub_dpt', 'edit_shipping_method.html');
	}

	function save_order(){


		$scan_result = scanArrayKeysForID($_POST, 'priority');
		$sql = '
			UPDATE ?#SHIPPING_METHODS_TABLE SET sort_order=? WHERE SID=?
		';

		foreach ($scan_result as $SID=>$scan_info){
				
			db_phquery($sql, $scan_info['priority'], $SID);
		}

		Message::raiseAjaxMessage(MSG_SUCCESS, '', 'order_saved');
		die;
	}

	function delete_method(){

		$SID = $this->getData('sid');
		$method = shGetShippingMethodById($SID);

		if(!$method)RedirectSQ();

		shDeleteShippingMethod($SID);

		$moduleEntry = ShippingRateCalculator::getInstance($method['module_id']);
		/* @var $moduleEntry ShippingRateCalculator*/
		if(!is_object($moduleEntry))RedirectSQ();

		$moduleEntry->uninstall();

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'shp_method_removed');
	}

	function main(){

		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$shipping_methods = shGetAllShippingMethods( false);

		$smarty->assign('shipping_methods', $shipping_methods);
		$smarty->assign('admin_sub_dpt', 'shipping_methods.html');
	}
}

ActionsController::exec('ShippingMethodsController');
?>