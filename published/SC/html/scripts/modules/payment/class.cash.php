<?php
/**
 * Invisible for administration
 */
/**
 * @connect_module_class_name PaymentModuleCash
 * @package DynamicModules
 * @subpackage Payment
 */

class PaymentModuleCash extends PaymentModule 
{
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/cash1.gif';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 			= PAYMENTMODULECASH_TTL;
		$this->description 	= PAYMENTMODULECASH_DSCR;
		$this->Settings 		= array(
			'CONF_PAYMENTMODULECASH_CURRENCY',
			);
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_PAYMENTMODULECASH_CURRENCY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> PAYMENTMODULECASH_CFG_CURRENCY_TTL, 
			'settings_description' 	=> PAYMENTMODULECASH_CFG_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
	}
	
	function payment_form_html(){
		
		if( $this->_getSettingValue('CONF_PAYMENTMODULECASH_CURRENCY') != $_SESSION['current_currency'] && $this->_getSettingValue('CONF_PAYMENTMODULECASH_CURRENCY')){
			
			currSetCurrentCurrency($this->_getSettingValue('CONF_PAYMENTMODULECASH_CURRENCY'));
			Redirect(set_query('__tt='));
		}
		return '';
	}
}
?>