<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	class CheckoutReplacementController extends ActionsController {
		
		function __getModules($module_id = null, $save = false){
			
			static $modules = array();
			
			if(($module_id == null || $module_id =='googlecheckout2')&&!isset($modules['googlecheckout2'])){
				include_once(DIR_MODULES.'/payment/class.googlecheckout2.php');			
				$moduleEntryGC = new GoogleCheckout2();
				$is_installed = true;
				if(!$moduleEntryGC->is_installed()){
					$is_installed = false;
					$moduleEntryGC->install();
					_setSettingOptionValue('CONF_GOOGLECHECKOUT2_ENABLED', 0);
				}
				$modules['googlecheckout2'] = array(
					'module_id' => 'googlecheckout2',
					'moduleEntry' => &$moduleEntryGC,
					'enabled' => $is_installed && CONF_GOOGLECHECKOUT2_ENABLED,
					'enabled_constant' => 'CONF_GOOGLECHECKOUT2_ENABLED',
					'description' => $moduleEntryGC->description
					);
			}
			
			if(($module_id == null || $module_id =='ppexpresscheckout')&&!isset($modules['ppexpresscheckout'])){
				include_once(DIR_MODULES.'/payment/class.ppexpresscheckout.php');
				$moduleEntryPPE = new PPExpressCheckout();
				$is_installed = true;
				if(!$moduleEntryPPE->is_installed()){
					$is_installed = false;
					$moduleEntryPPE->install();
					_setSettingOptionValue('CONF_PPEXPRESSCHECKOUT_ENABLED', 0);
				}
				$modules['ppexpresscheckout'] = array(
					'module_id' => 'ppexpresscheckout',
					'moduleEntry' => &$moduleEntryPPE,
					'enabled' => $is_installed && CONF_PPEXPRESSCHECKOUT_ENABLED,
					'enabled_constant' => 'CONF_PPEXPRESSCHECKOUT_ENABLED',
					'description' => $moduleEntryPPE->description
					);
			}

			foreach ($modules as $_k=>$_module){
				if(!is_null($module_id) && $save && $_k == $module_id)$_POST['save'] = 1;
				else unset($_POST['save']);
			
				$constants = $_module['moduleEntry']->settings_list();
				$modules[$_k]['settings'] = array();
				$modules[$_k]['controls'] = array();
				$setting = null;
				$control = null;
				$moduleSettings = $_module['moduleEntry']->SettingsFields;
				foreach( $constants as $constant ){
					
					if($constant == $_module['enabled_constant'])continue;
					$modules[$_k]['settings'][]	= settingGetSetting( $constant );
					$modules[$_k]['controls'][]	= settingCallHtmlFunction(  $constant );
				}
				
			}
			
			return is_null($module_id)?$modules:$modules[$module_id];
		}
		
		function save_module_settings(){
			
			$module = $this->__getModules($this->getData('module_id'), true);
			if(!isset($module['moduleEntry']) || !is_object($module['moduleEntry']))RedirectSQ();;
			
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
		}
		
		function change_module_state(){
			
			$module = $this->__getModules($this->getData('module_id'));
			if(!isset($module['moduleEntry']) || !is_object($module['moduleEntry']))die;
			
			if($this->getData('enable') == '1'){
				
				if(!$module['moduleEntry']->is_installed())
					$module['moduleEntry']->install();
				
				_setSettingOptionValue($module['enabled_constant'], 1);
			}else{
				
				_setSettingOptionValue($module['enabled_constant'], 0);
			}
			die;
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			$smarty->assign('modules', $this->__getModules());
			$smarty->assign('admin_sub_dpt', 'checkout_replacement.html');
		}
	}
	
	ActionsController::exec('CheckoutReplacementController');
?>