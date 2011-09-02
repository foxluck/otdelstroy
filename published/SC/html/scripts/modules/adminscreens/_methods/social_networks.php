<?php
/**
 *
 * @author WebAsyst Team
 *
 */
class SocialNetworks extends ActionsController
{
	private function __getModules($module_id = null, $save = false)
	{
			
		static $modules = array();
		$module_language = sc_getSessionData('LANGUAGE_ISO3');

		//VKontakte section
		if(($module_id == null || $module_id =='vkontaktepayment')&&!isset($modules['vkontaktepayment'])){
			include_once(DIR_MODULES.'/payment/class.vkontaktepayment.php');
			$moduleEntryVK = new VKontaktePayment();
			if(!$moduleEntryVK->language||($moduleEntryVK->language == $module_language)){
				$is_installed = true;
				if(!$moduleEntryVK->is_installed()){
					$is_installed = false;
					$moduleEntryVK->install();
					_setSettingOptionValue('CONF_VKONTAKTE_ENABLED', 0);
				}
				list($VKontakteInfo) = modGetModuleConfigs('vkontaktepayment');
				$moduleEntryVK->ModuleConfigID = $VKontakteInfo['ConfigID'];
				$modules['vkontaktepayment'] = array(
						'module_id' => 'vkontaktepayment',
						'moduleEntry' => &$moduleEntryVK,
						'enabled' => $is_installed && CONF_VKONTAKTE_ENABLED,
						'enabled_constant' => 'CONF_VKONTAKTE_ENABLED',
						'description' => $moduleEntryVK->description
				);
			}
		}

		//FaceBook section
		if(($module_id == null || $module_id =='facebookpayment')&&!isset($modules['facebookpayment'])){
			include_once(DIR_MODULES.'/payment/class.facebookpayment.php');
			$moduleEntryFB = new FacebookPayment();
			if(!$moduleEntryFB->language||($moduleEntryFB->language == $module_language)){
				$is_installed = true;
				if(!$moduleEntryFB->is_installed()){
					$is_installed = false;
					$moduleEntryFB->install();
					_setSettingOptionValue('CONF_FACEBOOK_ENABLED', 0);
				}
				list($FBInfo) = modGetModuleConfigs('facebookpayment');
				$moduleEntryFB->ModuleConfigID = $FBInfo['ConfigID'];
				$modules['facebookpayment'] = array(
						'module_id' => 'facebookpayment',
						'moduleEntry' => &$moduleEntryFB,
						'enabled' => $is_installed && defined('CONF_FACEBOOK_ENABLED')&&constant('CONF_FACEBOOK_ENABLED'),
						'enabled_constant' => 'CONF_FACEBOOK_ENABLED',
						'description' => $moduleEntryFB->description
				);
			}
		}

		foreach ($modules as $_k=>$_module){
			if(!is_null($module_id) && $save && $_k == $module_id){
				$_POST['save'] = 1;
			}else{
				unset($_POST['save']);
			}

			$constants = $_module['moduleEntry']->settings_list();
			$modules[$_k]['settings'] = array();
			$modules[$_k]['controls'] = array();

			if(method_exists($_module['moduleEntry'],'getCustomProperties')){
				$_settings = $_module['moduleEntry']->getCustomProperties();
				foreach($_settings as $_setting_item){
					$modules[$_k]['settings'][] = $_setting_item;
					$modules[$_k]['controls'][] = $_setting_item['control'];
				}
			}
			
			$setting = null;
			$control = null;
			$moduleSettings = $_module['moduleEntry']->SettingsFields;
			$modules[$_k]['has_controls'] = false;
			foreach( $constants as $constant ){
				if($constant == $_module['enabled_constant'])continue;
				$modules[$_k]['has_controls'] = true;
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

	function main()
	{
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */
		$smarty->assign('modules', $this->__getModules());
		$smarty->assign('sub_template', DIR_TPLS.'/backend/social_networks.html');

	}
}
ActionsController::exec('SocialNetworks');
?>