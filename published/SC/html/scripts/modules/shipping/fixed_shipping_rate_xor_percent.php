<?php
/*
	shipping module

	calculates shipping rate as in Shop-Script PRO: retuns maximum between a fixed shipping rate and a percent of order amount
*/

/**
 * @connect_module_class_name CShippingModuleFixedXorPercent
 *
 */

class CShippingModuleFixedXorPercent  extends ShippingRateCalculator{
	
	function _initVars() //constructor
	{
		parent::_initVars();
		//$this->id = "shipping_fixed";
		$this->title = CSHIPPINGMODULEFIXEDXORPERCENT_TTL;
		$this->description = CSHIPPINGMODULEFIXEDXORPERCENT_DSCR;
		$this->sort_order = 0;
		
		$this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_FIXEDRATE';
		$this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_PERCENT';
	}

	function calculate_shipping_rate($order, $address) //core shipping rate calculation routine
		//returns float value in case of correct calculation, and error string in case of error
	{
		if(!count($this->_getShippingProducts($order)))return 0;
		return
			max(
				$this->_getSettingValue('CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_FIXEDRATE'),
				$order["order_amount"]*$this->_getSettingValue('CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_PERCENT')/100.0
			);
	}

	function install() //installation routine
	{

		$this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_FIXEDRATE'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> CSHIPPINGMODULEFIXEDXORPERCENT_CONF_FIXEDRATE_TTL, 
			'settings_description' 	=> CSHIPPINGMODULEFIXEDXORPERCENT_CONF_FIXEDRATE_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 2,
		);
		$this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_PERCENT'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> CSHIPPINGMODULEFIXEDXORPERCENT_CONF_PERCENT_TTL, 
			'settings_description' 	=> CSHIPPINGMODULEFIXEDXORPERCENT_CONF_PERCENT_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 2,
		);

		ShippingRateCalculator::install();
	}
}
?>