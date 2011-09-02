<?php
/**
 * FacebookPayment not payment implementation
 *
 * @connect_module_class_name FacebookPayment
 * @package DynamicModules
 * @subpackage Payment
 * @author WebAsyst Team
 * @see http://developers.facebook.com/setup/
 *
 */
class FacebookPayment extends PaymentModule
{
	var $type = PAYMTD_TYPE_REPLACE;
	var $default_logo = './images_common/facebook/facebook.ico';
	var $SingleInstall = true;

	function _initVars()
	{

		parent::_initVars();
		$this->title 		= FACEBOOK_TTL;
		$this->description 	= FACEBOOK_DSCR;
		$this->sort_order 	= 0;
		$this->Settings = array(
			"CONF_FACEBOOK_ENABLED",
		//	"CONF_FACEBOOK_APP_ID",
			"CONF_FACEBOOK_LIKE_URL",
			"CONF_FACEBOOK_HELLO",
		);
	}

	function _initSettingFields()
	{
	/*	$this->SettingsFields['CONF_FACEBOOK_APP_ID'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> FACEBOOK_CFG_APP_ID_TTL, 
			'settings_description' 		=> FACEBOOK_CFG_APP_ID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 				=> 1,
		);*/
		$this->SettingsFields['CONF_FACEBOOK_LIKE_URL'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> 'FACEBOOK_CFG_LIKE_URL_TTL', 
			'settings_description' 		=> 'FACEBOOK_CFG_LIKE_URL_DSCR', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 				=> 1,
		);
		$this->SettingsFields['CONF_FACEBOOK_HELLO'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> 'FACEBOOK_CFG_HELLO_TTL', 
			'settings_description' 		=> 'FACEBOOK_CFG_HELLO_DSCR', 
			'settings_html_function' 	=> 'setting_TEXT_AREA(', 
			'sort_order' 				=> 2,
		);
	}

	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array(
			'settings_title'=>FACEBOOK_CFG_STOREURL_TTL,
			'settings_description'=>FACEBOOK_CFG_STOREURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getStoreUrl())
			.'">',
			);
			return $customProperties;
	}


	function after_processing_html( $orderID )
	{
		return false;

	}
	
	private function getStoreUrl()
	{
		$scURL = str_replace(array("http://","https://"),array('',''), trim( BASE_WA_URL ));
		if(SystemSettings::is_hosted()){
			$scURL .= 'shop/';
		}
		return "http://".$scURL.(MOD_REWRITE_SUPPORT?'facebook/':'?store_mode=facebook');
	}
}
?>