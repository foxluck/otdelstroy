<?php
/*
	sample shipping module
*/


/**
 * @connect_module_class_name CShippingModuleFixedAndPercent
 *
 */

class CShippingModuleFixedAndPercent  extends ShippingRateCalculator{
	
	function _initVars() //constructor
	{
		parent::_initVars();
		$this->title = CSHIPPINGMODULEFIXEDANDPERCENT_TITLE;
		$this->description = CSHIPPINGMODULEFIXEDANDPERCENT_DESCR;
		$this->sort_order = 0;
		
		$this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_FIXEDRATE';
		$this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_PERCENT';
	}

	function calculate_shipping_rate($order, $address) {
		
		if(!count($this->_getShippingProducts($order)))return 0;
		
		return $this->_getSettingValue('CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_FIXEDRATE') + $order["order_amount"]*$this->_getSettingValue('CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_PERCENT')/100.0;
	}

	function install() //installation routine
	{

		$this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_FIXEDRATE'] = array(
			'settings_value' 		=> '10', 
			'settings_title' 			=> CSHIPPINGMODULEFIXEDANDPERCENT_CONF_FIXEDRATE_TTL, 
			'settings_description' 	=> CSHIPPINGMODULEFIXEDANDPERCENT_CONF_FIXEDRATE_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 2,
		);
		$this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_PERCENT'] = array(
			'settings_value' 		=> '10', 
			'settings_title' 			=> CSHIPPINGMODULEFIXEDANDPERCENT_CONF_PERCENT_TTL, 
			'settings_description' 	=> CSHIPPINGMODULEFIXEDANDPERCENT_CONF_PERCENT_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 2,
		);

		ShippingRateCalculator::install();
	}
}
?>
