<?php
/*
	sample shipping module
*/

/**
 * @connect_module_class_name CShippingModuleFixed
 *
 */
class CShippingModuleFixed extends ShippingRateCalculator{
	
	function _initVars(){
		
		parent::_initVars();
		$this->title = CSHIPPINGMODULEFIXED_TITLE;
		$this->description = CSHIPPINGMODULEFIXED_DESCRIPTION;
		$this->sort_order = 0;
		
		$this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATE_SHIPPINGRATE';
	}

	function calculate_shipping_rate($order, $address) {
		if(count($this->_getShippingProducts($order))){
			return $this->_getSettingValue('CONF_SHIPPING_MODULE_FIXEDRATE_SHIPPINGRATE');
		}else{
			return 0;
		}
	}

	function install(){

		$this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATE_SHIPPINGRATE'] = array(
			'settings_value' 		=> '10', 
			'settings_title' 			=> CSHIPPINGMODULEFIXED_CONF_SHIPPINGRATE_TITLE, 
			'settings_description' 	=> CSHIPPINGMODULEFIXED_CONF_SHIPPINGRATE_DESCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 2,
		);

		ShippingRateCalculator::install();
	}
}

?>