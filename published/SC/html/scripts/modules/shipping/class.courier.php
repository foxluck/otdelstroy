<?php
/**
 * @connect_module_class_name CourierShippingModule
 *
 */

class CourierShippingModule extends ShippingRateCalculator{

	var $DB_TABLE;
	
	/*
	Parent methods redifinition
	*/

	function allow_shipping_to_address($_Address){
		
		if($this->_getSettingValue('CONF_COURIER_COUNTRY')>0){
			
			if($_Address['countryID']!=$this->_getSettingValue('CONF_COURIER_COUNTRY'))return false;
			
			if($this->_getSettingValue('CONF_COURIER_ZONE')>0 && $_Address['zoneID']!=$this->_getSettingValue('CONF_COURIER_ZONE'))return false;
		}
		return true;
	}

	function calculate_shipping_rate($_Order, $_Address, $_ServID = 0){
		
		/**
		 * Calculate order amount without free shipping items
		 */
		include_once(DIR_CLASSES.'/class.shoppingcart.php');
		$cartEntry = new ShoppingCart();
		$cartEntry->loadCurrentCart();

		$order_amount = $cartEntry->calculateTotalPriceWithoutFreeShippingProducts();
		
		$dsc = dscCalculateDiscount( $order_amount, isset($_SESSION["log"])?$_SESSION["log"]:'');
		
		$order_amount = $order_amount - ($order_amount/100)*floatval($dsc["discount_percent"]);
		
		$Rate = $this->_getRate($order_amount);
		
		if(!isset($Rate['isPercent']) || $order_amount<=0)return 0;

		$Rate = $Rate['isPercent']?($order_amount*($Rate['rate']/100)):$Rate['rate'];
		return $Rate;
	}
	
	function uninstall($_ModuleConfigID = 0){
		
		ShippingRateCalculator::uninstall($_ModuleConfigID);
		
		if(!count(modGetModuleConfigs(get_class($this)))){
			
			//drop shipping rates table
			$sql = '
				DROP TABLE IF EXISTS '.CourierShippingModule::_getDBName().'
			';
		}else {
			
			$sql = '
				DELETE FROM '.CourierShippingModule::_getDBName().' WHERE module_id="'.$_ModuleConfigID.'"
			';
		}
		db_query($sql);
	}
	/*
	 Abstract methods redifinition
	 */
	function _InitVars(){
		
		parent::_initVars();
		$this->TemplatesDir = getcwd().'/modules/shipping/templates/';
		$this->DB_TABLE = $this->_getDBName();
		$this->title = COURIER_TTL;
		$this->description = COURIER_DSCR;
		
		$this->Settings = array(
			'CONF_COURIER_COUNTRY',
			'CONF_COURIER_ZONE',
			'CONF_COURIER_RATES'
			);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_COURIER_COUNTRY'] = array(
			'settings_value' 		=> CONF_COUNTRY, 
			'settings_title' 			=> COURIER_CFG_COUNTRY_TTL, 
			'settings_description' 	=> COURIER_CFG_COUNTRY_DSCR, 
			'settings_html_function' 	=> 'CourierShippingModule::setting_COUNTRY_SELECT(true,', 
			'sort_order' 			=> 20,
		);
		$this->SettingsFields['CONF_COURIER_ZONE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> COURIER_CFG_ZONE_TTL, 
			'settings_description' 	=> COURIER_CFG_ZONE_DSCR, 
			'settings_html_function' 	=> 'CourierShippingModule::setting_ZONE_SELECT('.$this->_getSettingRealName('CONF_COURIER_COUNTRY').',', 
			'sort_order' 			=> 30,
		);
		$this->SettingsFields['CONF_COURIER_RATES'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> COURIER_CFG_RATES_TTL, 
			'settings_description' 	=> COURIER_CFG_RATES_DSCR, 
			'settings_html_function' 	=> 'CourierShippingModule::_settingRates('.$this->ModuleConfigID.',', 
			'sort_order' 			=> 40,
		);
		
		if(!db_table_exists($this->DB_TABLE)){
			
			$sql = '
				CREATE TABLE '.$this->DB_TABLE.' 
				(module_id INT UNSIGNED NOT NULL, orderAmount FLOAT, rate FLOAT, isPercent BOOL, KEY (module_id))
			';
			db_query($sql);
		}
		
	}

	/*
	current object methods
	*/
	function _getDBName(){
		
		return  DBTABLE_PREFIX.'_courier_rates';
	}
	
	function _settingRates($_ModuleConfigID){
		
		$smarty = new ViewSC();
		$Courier = new CourierShippingModule($_ModuleConfigID);
		$Rates = array();
		
		if (isset($_GET['delete_rate'])){
			
			$Courier->_deleteRate($_GET['delete_rate']);
			Redirect(set_query('delete_rate='));
		}
		
		if(isset($_POST['save'])){
			
			$_Rates = array();
			$_Amounts = array();
			foreach ($_POST['fORDER_AMOUNTS'] as $_Ind=>$_Amount){
				
				if((float)$_Amount<=0 || (float)$_POST['fRATES'][$_Ind]<=0 || in_array($_Amount, $_Amounts) )continue;
				
				$_Rate = array();
				$_Rate['rate'] = preg_replace('/([0-9]+)\%/','$1', $_POST['fRATES'][$_Ind]);
				
				if($_Rate['rate']!=$_POST['fRATES'][$_Ind]){
					
					$_Rate['isPercent'] = 1;
				}else {
					
					$_Rate['isPercent'] = 0;
				}
				$_Rate['orderAmount'] = $_Amount;
				$_Amounts[] = $_Amount;
				$_Rates[] = $_Rate;				
			}
			$Courier->_saveRates($_Rates);
		}
		
		if(!count($Rates))$Rates = $Courier->_getRates();
		$smarty->hassign('Rates', $Rates);
		return $smarty->fetch($Courier->TemplatesDir.'courier.tpl.html');
	}
	
	function _saveRates($_Rates){
		
		$sql = '
			DELETE FROM `'.$this->DB_TABLE.'`
			WHERE module_id = "'.xEscapeSQLstring($this->ModuleConfigID).'"
		';
		db_query($sql);
		
		foreach ($_Rates as $_Rate){
			
			$sql = '
				INSERT `'.$this->DB_TABLE.'`
				(module_id, `'.implode('`, `', xEscapeSQLstring(array_keys($_Rate))).'`)
				VALUES("'.xEscapeSQLstring($this->ModuleConfigID).'", "'.implode('", "', xEscapeSQLstring($_Rate)).'")
			';
			db_query($sql);
		}
	}
	
	function _getRates(){
		
		$Rates = array();
		$sql = '
			SELECT orderAmount, rate, isPercent
			FROM '.$this->DB_TABLE.'
			WHERE module_id="'.xEscapeSQLstring($this->ModuleConfigID).'"
			ORDER BY orderAmount ASC
		';
		$Result = db_query($sql);
		while ($_Row = db_fetch_row($Result)) {
			
			$Rates[] = $_Row;
		}
		return $Rates;
	}
	
	function _deleteRate($_Amount){
		
		$sql = '
			DELETE FROM '.$this->DB_TABLE.'
			WHERE  module_id="'.xEscapeSQLstring($this->ModuleConfigID).'" AND orderAmount="'.xEscapeSQLstring($_Amount).'"
		';
		db_query($sql);
	}
	
	function _getRate($_Amount){
		
		$sql = '
			SELECT  rate, isPercent FROM '.$this->DB_TABLE.'
			WHERE  module_id="'.xEscapeSQLstring($this->ModuleConfigID).'"
			AND orderAmount>'.floor($_Amount).'
			ORDER BY orderAmount ASC
			LIMIT 1
		';
		return db_fetch_row(db_query($sql));
	}

	function setting_COUNTRY_SELECT($_ShowButton, $_SettingID = null){
		
		if(!isset($_SettingID)){
			
			$_SettingID = $_ShowButton;
			$_ShowButton = false;
		}
		
		$Options = array(
			array("title"=>translate("str_any_country"), "value"=>0)
			);
		$CountriesNum = 0;
		$Countries = cnGetCountries( array('raw data'=>true), $CountriesNum );
		foreach ($Countries as $_Country){
			
			$Options[] = array("title"=>$_Country['country_name'], "value"=>$_Country['countryID']);
		}
		return '<nobr>'.setting_SELECT_BOX($Options, $_SettingID).($_ShowButton?'&nbsp;&nbsp;<input type="submit" name="save" value="'.translate("btn_select").'" />':'').'</nobr>';
	}
	
	function setting_ZONE_SELECT($_CountryID, $_SettingID){
		
		$Zones = znGetZones($_CountryID);
		
		$Options = array(
			array("title"=>translate("str_any_region"), "value"=>0)
			);
		
		if(!count($Zones) && $_CountryID){
			setting_SELECT_BOX($Options, $_SettingID);
			return translate("str_regions_notdefined").'<input type="hidden" name="setting_'.settingGetConstNameByID($_SettingID).'" value="0" />';
		}
		
		foreach ($Zones as $_Zone){
			
			$Options[] = array("title"=>$_Zone['zone_name'], "value"=>$_Zone['zoneID']);
		}
		return setting_SELECT_BOX($Options, $_SettingID);
	}

}
?>