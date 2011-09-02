<?php
/**
 * @connect_module_class_name InterShipperModule
 *
 */

define('INTERSHIPPER_CARRIERS_SETTINGS_TBL' , DBTABLE_PREFIX.'_intershipper_carriers');

class InterShipperModule extends ShippingRateCalculator{

	//var $language = 'eng';
	
	var $carriers;
	var $classes;
	var $default_logo = 'http://www.webasyst.net/collections/design/shipping-icons/intershipper.gif';
	function _initVars(){
		
		parent::_initVars();
		$this->title = INTERSHIPPERMODULE_TTL;
		$this->description = INTERSHIPPERMODULE_DSCR;
		
		$this->Settings[] = 'CONF_INTERSHIPPER_USERNAME';
		$this->Settings[] = 'CONF_INTERSHIPPER_PASSWORD';
		$this->Settings[] = 'CONF_INTERSHIPPER_CARRIERS';
		$this->Settings[] = 'CONF_INTERSHIPPER_CLASSES';
		$this->Settings[] = 'CONF_INTERSHIPPER_SHIPMETHOD';
		$this->Settings[] = 'CONF_INTERSHIPPER_SHMOPTION';
		$this->Settings[] = 'CONF_INTERSHIPPER_PACKAGING';
		$this->Settings[] = 'CONF_INTERSHIPPER_CONTENTS';
		$this->Settings[] = 'CONF_INTERSHIPPER_INSURANCE';
		$this->Settings[] = 'CONF_INTERSHIPPER_USD';
		$this->Settings[] = 'CONF_INTERSHIPPER_COUNTRY';
		$this->Settings[] = 'CONF_INTERSHIPPER_POSTAL';
		$this->Settings[] = 'CONF_INTERSHIPPER_STATE';
		$this->Settings[] = 'CONF_INTERSHIPPER_CITY';
		
		$this->carriers = array(
			array('id' => '1','code' => 'ARB','name' => 'AirBorne'),
			array('id' => '2','code' => 'DHL','name' => 'DHL World Wide Express'),
			array('id' => '3','code' => 'FDX','name' => 'Federal Express'),
			array('id' => '4','code' => 'UPS','name' => 'United Parcel Service'),
			array('id' => '5','code' => 'USP','name' => 'U.S. Postal Service'),
//		array('id' => '6','code' => 'CAN','name' => 'Canada Post'),
		);

		$this->classes = array(
			array('id' => '0','code' => '1DY','descr' => '1st Day'),
			array('id' => '1','code' => '2DY','descr' => '2nd Day'),
			array('id' => '2','code' => '3DY','descr' => '3rd Day'),
			array('id' => '3','code' => 'GND','descr' => 'Ground'),
		);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_INTERSHIPPER_SHMOPTION'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_SHMOPTION_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_SHMOPTION_DSCR, 
			'settings_html_function' 	=> 'InterShipperModule::settingSHMOption('.$this->_getSettingRealName('CONF_INTERSHIPPER_SHIPMETHOD').',', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_CARRIERS'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_CARRIERS_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_CARRIERS_DSCR, 
			'settings_html_function' 	=> 'InterShipperModule::settingCarriers('.$this->getModuleConfigID().',', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_USD'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_USD_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_USD_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_USERNAME'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_USERNAME_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_USERNAME_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_INSURANCE'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_INSURANCE_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_INSURANCE_DSCR, 
			'settings_html_function' 	=> 'InterShipperModule::settingInsurance(', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_PASSWORD'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_PASSWORD_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_PASSWORD_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_CLASSES'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_CLASSES_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_CLASSES_DSCR, 
			'settings_html_function' 	=> 'setting_CHECKBOX_LIST(InterShipperModule::getClasses4List(),', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_SHIPMETHOD'] = array(
			'settings_value' 		=> 'DRP',
			'settings_title' 			=> INTERSHIPPER_CFG_SHIPMETHOD_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_SHIPMETHOD_DSCR, 
			'settings_html_function' 	=> 'InterShipperModule::settingShipMethod(', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_PACKAGING'] = array(
			'settings_value' 		=> 'BOX',
			'settings_title' 			=> INTERSHIPPER_CFG_PACKAGING_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_PACKAGING_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(InterShipperModule::getPackaging4Select(),', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_CONTENTS'] = array(
			'settings_value' 		=> 'OTR',
			'settings_title' 			=> INTERSHIPPER_CFG_CONTENTS_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_CONTENTS_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(InterShipperModule::getContents4Select(),', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_COUNTRY'] = array(
			'settings_value' 		=> CONF_COUNTRY,
			'settings_title' 			=> INTERSHIPPER_CFG_COUNTRY_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_COUNTRY_DSCR, 
			'settings_html_function' 	=> 'setting_COUNTRY_SELECT(true,', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_POSTAL'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_POSTAL_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_POSTAL_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
		);
		$this->SettingsFields['CONF_INTERSHIPPER_STATE'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_STATE_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_STATE_DSCR, 
			'settings_html_function' 	=> 'setting_ZONE_SELECT('.$this->_getSettingRealName('CONF_INTERSHIPPER_COUNTRY').', array("mode"=>"notdef"),',
		);
		$this->SettingsFields['CONF_INTERSHIPPER_CITY'] = array(
			'settings_title' 			=> INTERSHIPPER_CFG_CITY_TTL, 
			'settings_description' 	=> INTERSHIPPER_CFG_CITY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
		);
		
		$this->_installCarriersSettingTable();
	}

	function _prepareQuery(&$_Services,  $order, $address){
		
		$Query = array();
		$Query['Version'] = '2.0.3.0';
		list($Query['ShipmentID'], $Query['QueryID']) = $this->_generateShipmentID($Query);
		$Query['Username'] = $this->_getSettingValue('CONF_INTERSHIPPER_USERNAME');
		$Query['Password'] = $this->_getSettingValue('CONF_INTERSHIPPER_PASSWORD');
		
		$Classes = $this->getAvailableClasses();
		$ClassesCount = 0;
		foreach ($Classes as $_Ind=>$_Class){
			
			$ClassesCount++;
			$Query['ClassCode'.$ClassesCount] = $_Class['code'];
		}
		$Query['TotalClasses'] = $ClassesCount;
		$Query['DeliveryType'] = 'COM';
		$Query['ShipMethod'] = $this->_getSettingValue('CONF_INTERSHIPPER_SHIPMETHOD');

		$Query['OriginationCountry'] = cnGetCountryById($this->_getSettingValue('CONF_INTERSHIPPER_COUNTRY'));
		$Query['OriginationCountry'] = $Query['OriginationCountry']['country_iso_2'];
		
		$Query['OriginationState'] = znGetSingleZoneById($this->_getSettingValue('CONF_INTERSHIPPER_STATE'));
		$Query['OriginationState'] = $Query['OriginationState']['zone_code'];
		
		$Query['OriginationCity'] = $this->_getSettingValue('CONF_INTERSHIPPER_CITY');
		$Query['OriginationPostal'] = $this->_getSettingValue('CONF_INTERSHIPPER_POSTAL');

		$Query['DestinationCountry'] = cnGetCountryById($address['countryID']);
		$Query['DestinationCountry'] = $Query['DestinationCountry']['country_iso_2'];
		
		$Query['DestinationState'] = znGetSingleZoneById($address['zoneID']);
		$Query['DestinationState'] = $Query['DestinationState']['zone_code'];
		
		if (isset($address['city']))
			$Query['DestinationCity'] = $address['city'];
		if (isset($address['zip']))
			$Query['DestinationPostal'] = $address['zip'];

		$Query['Currency'] = 'USD';
		$Query['SortBy'] = 'Carrier';
		$Query['TotalPackages'] = 1;

		$Query['Weight1'] = $this->_getOrderWeight($order);
//		if(!$Query['Weight1']) return '';
		$Query['Weight1'] = $this->_convertMeasurement($Query['Weight1'], CONF_WEIGHT_UNIT, 'LBS');
		$Query['WeightUnit1'] = 'LB';
		
		$Query['DimensionalUnit1'] = 'IN';
		
		$Query['Packaging1'] = $this->_getSettingValue('CONF_INTERSHIPPER_PACKAGING');
		$Query['Contents1'] = $this->_getSettingValue('CONF_INTERSHIPPER_CONTENTS');
		$Query['Cod1'] = 0;
		
		$Perc = 0;
		if(preg_match('|([0-9]*\.?[0-9]*)\%|',$this->_getSettingValue('CONF_INTERSHIPPER_INSURANCE'), $Perc)){

			if(isset($Perc[1])){
				
				$Query['Insurance'] = ceil($this->_convertCurrency($order['order_amount']*$Perc[1]/100, 0, $this->_getSettingValue('CONF_INTERSHIPPER_USD'))*100);
			}
		}else{
			
			$Query['Insurance'] = ceil($this->_convertCurrency($this->_getSettingValue('CONF_INTERSHIPPER_INSURANCE'), 0, $this->_getSettingValue('CONF_INTERSHIPPER_USD'))*100);
		}
		
		$CarriersCount = 0;
		$Carriers = $this->getAvailableServices();
		foreach ($Carriers as $_CarrierID=>$_Carrier){
			
			$CarriersCount++;
			$CarrierSettings = $this->_getCarrierSettings($_Carrier['id']);
			$Query['CarrierCode'.$CarriersCount] = $_Carrier['code'];
			$Query['CarrierAccount'.$CarriersCount] = $CarrierSettings['account'];
			$Query['CarrierInvoiced'.$CarriersCount] = (int)$CarrierSettings['invoiced'];
		}
		$Query['TotalCarriers'] = $CarriersCount;
		
		if(in_array($this->_getSettingValue('CONF_INTERSHIPPER_SHMOPTION'), array_keys($this->_getShipMethodOptions($this->_getSettingValue('CONF_INTERSHIPPER_SHIPMETHOD'))))){
			
			$Query['TotalOptions'] = 1;
			$Query['OptionCode1'] = $this->_getSettingValue('CONF_INTERSHIPPER_SHMOPTION');
		}
		
		return $Query;
	}

	function _sendQuery($_Query){

		$xmlResponce = '';
		$url = 'www.intershipper.com';
		$uri = '/Interface/Intershipper/XML/v2.0/HTTP.jsp?';
		$_TC = 0;
		foreach ($_Query as $_Key=>$_Val){
			
			$uri .= ($_TC?'&':'').$_Key.'='.urlencode($_Val);
			$_TC++;
		}
		
		$fp = fsockopen ($url, 80, $errno, $errstr, 20);
		if (!$fp) {

			$this->_writeLogMessage(0, "Socket error: $errstr ($errno)");
			return '';
		} 

		fputs ($fp, "GET $uri HTTP/1.0\r\nHost: $url\r\n\r\n");
		
		while ($data = fread($fp, 4096)) {
	
	 		$xmlResponce .= $data;
		}
		fclose($fp);
		
 		$xmlResponce = preg_replace('/^.*\r?\n\r?\n/s', "", $xmlResponce);
 		
		return $xmlResponce;
	}
	
	function _parseXMLAnswer($_XMLAnswer){
		
		$Rates = array();
		$xmlNodes = new xmlNodeX();
		$xmlNodes->renderTreeFromInner($_XMLAnswer);
		
		$xmlErrors = $xmlNodes->xPath('/shipment/error');
		$_TC = count($xmlErrors)-1;
		for(;$_TC>=0;$_TC--){
			
			$this->_writeLogMessage(0, "Error: ".$xmlErrors[$_TC]->getData());
		}
		
		$xmlNodes = $xmlNodes->xPath('/shipment/package/quote');
		$_TC = count($xmlNodes);
		$Carrier2Ind = array();
		$Carriers = $this->getAvailableServices();
		foreach ($Carriers as $_Carrier){
			
			$Carrier2Ind[$_Carrier['code']] = $_Carrier['id'];
		}
		
		$classcode2id = array();
		$classes = $this->getAvailableClasses();
		foreach ($classes as $class){
			
			$classcode2id[$class['code']] = $class['id'];
		}
		
		for ($_j=0; $_j<$_TC; $_j++){
			
			$ShID = $_j;
			
			$Rate = array(
				'name' => '',
				'id' => 0,
				'rate' => '',
				);
			$xnQuote = &$xmlNodes[$_j];
			/* @var $xnQuote xmlNodeX */
			
			$xnCarrier = &$xnQuote->getFirstChildByName('carrier');
			$Rate['name'] = $xnCarrier->getChildData('name');
			
			$xnClass = &$xnQuote->getFirstChildByName('class');
			$Rate['name'] .= ' '.$xnClass->getChildData('name');
			
			$xnService = &$xnQuote->getFirstChildByName('service');
			$Rate['name'] .= ' '.$xnService->getChildData('name');
			
			$xnRate = &$xnQuote->getFirstChildByName('rate');
			
			$Rate['rate'] = $this->_convertCurrency($xnRate->getChildData('amount')/100, $xnRate->getChildData('currency'), 0);
			
			$carrier_code = $xnCarrier->getChildData('code');
			$class_code = $xnClass->getChildData('code');
			
			$Rate['id'] = isset($Rates[$Carrier2Ind[$carrier_code]])?$classcode2id[$class_code]:0;
			$Rates[$Carrier2Ind[$carrier_code]][] = $Rate;
		}
		return $Rates;
	}

	/**
	 * Generate shipment ID
	 *
	 * @param array $_Services
	 * @param array $order
	 * @param array $address
	 */
	function _generateShipmentID(&$_Query){
		
		$Return = array('ShipmentID'=>113, 'QueryID'=>113);
		return array($Return['ShipmentID'],$Return['QueryID']);
	}
	
	function _getClasses(){
		
		return $this->classes;
	}
	
	function getClasses4List(){
		
		$moduleEntry = new InterShipperModule(); 
		$Classes = $moduleEntry->_getClasses();
		$Return = array();
		foreach ($Classes as $_Ind=>$_Class){
			
			$Return[$_Class['id']] = $_Class['descr'];
		}
		return $Return;
	}

	function classIsAvailable($class){
		
		return $this->_getSettingValue('CONF_INTERSHIPPER_CLASSES')&pow(2, $class['id']);
	}
	
	function getShipMethods4Select(){
		
		return 'Drop-Off At Carrier Location:DRP,Schedule A Special Pickup:PCK,Regularly Scheduled Pickup:SCD';
	}
	
	function getPackaging4Select(){
		
//		return 'Customer-supplied Box:BOX,Carrier Box:CBX,Carrier Pak:CPK,Carrier Envelope:ENV,Media Mail:MEM,Carrier Tube:TUB';
		return 'Customer-supplied Box:BOX,Carrier Envelope:ENV,Media Mail:MEM,Carrier Tube:TUB';
	}
	
	function getContents4Select(){
		
		return 'Accessible Hazmat:AHM,Inaccessible Hazmat:IHM,Liquid:LQD,Other:OTR';
	}
	
	function settingInsurance($_SettingID){
		
		$ConstantName = settingGetConstNameByID($_SettingID);
		if ( isset($_POST['save']) && isset($_POST['setting'.$ConstantName]) ){
			
			$_POST['setting'.$ConstantName] = preg_replace('/[^0-9]*([0-9]*\.?[0-9]*\%?).*/i','$1', $_POST['setting'.$ConstantName]);
		}

		return setting_TEXT_BOX(0, $_SettingID);
	}
	
	function settingCarriers($_ModuleID, $_SettingID){
		
		$Mod = new InterShipperModule($_ModuleID);
		$boxDescriptions = array();
		$Carriers = $Mod->_getCarriers();
		foreach ($Carriers as $_CarrierID=>$_Carrier){
			
			if(isset($_POST['save']) && is_array($_POST['fCARRIER_SETTINGS'][$_CarrierID])){
				
				if(!isset($_POST['fCARRIER_SETTINGS'][$_CarrierID]['invoiced']))$_POST['fCARRIER_SETTINGS'][$_CarrierID]['invoiced'] = 0;
				$Mod->_setCarrierSettings($_CarrierID, $_POST['fCARRIER_SETTINGS'][$_CarrierID]);
			}
			$CarrierSettings = $Mod->_getCarrierSettings($_CarrierID);
			$boxDescriptions[$_CarrierID] = '<strong>'.$_Carrier['name'].'</strong>
				<div style="padding-left:20px;">
					<table>
						<tr>
							<td align="right">
								'.INTERSHIPPER_TXT_CARRIER_ACCOUNT.'
							</td>
							<td valign="top">
								<input name="fCARRIER_SETTINGS['.$_CarrierID.'][account]" type="text" value="'.xHtmlSpecialChars($CarrierSettings['account']).'" />
							</td>
						</tr>
						<tr><td colspan="2"><div class="divider_grey"></div></td></tr>
						<tr>
							<td align="right">
								'.INTERSHIPPER_TXT_CARRIER_INVOICED.'
							</td>
							<td valign="top">
								<input name="fCARRIER_SETTINGS['.$_CarrierID.'][invoiced]" value="1" type="checkbox" style="margin:0px;padding:0px;"'.($CarrierSettings['invoiced']?' checked="checked"':'').' />
							</td>
						</tr>
					</table>
				</div>
				';
		}
		return setting_CHECKBOX_LIST($boxDescriptions, $_SettingID);
	}
	
	function _getCarriers(){
		
		return $this->carriers;
	}
	
	function _installCarriersSettingTable(){
		
		if(!db_table_exists(INTERSHIPPER_CARRIERS_SETTINGS_TBL)){
			
			$sql = '
				CREATE TABLE '.INTERSHIPPER_CARRIERS_SETTINGS_TBL.' 
				(module_id INT UNSIGNED NOT NULL, carrierID INT, account VARCHAR(50), invoiced BOOL, KEY(module_id, carrierID))
			';
			db_query($sql);
		}
	}
	
	function _setCarrierSettings($_CarrierID, $_Settings){
		
		if(!count($_Settings)) return null;
		
		$sql = '
			SELECT 1 FROM '.INTERSHIPPER_CARRIERS_SETTINGS_TBL.'
			WHERE module_id="'.xEscapeSQLstring($this->getModuleConfigID()).' "
			AND carrierID="'.xEscapeSQLstring($_CarrierID).'"
		';
		if(is_array(db_fetch_row(db_query($sql)))){
			
			$rSet = array();
			foreach ($_Settings as $_Column=>$_Value){
			
				$rSet[] = '`'.xEscapeSQLstring($_Column).'`="'.xEscapeSQLstring($_Value).'"';	
			}
			$sql = '
				UPDATE '.INTERSHIPPER_CARRIERS_SETTINGS_TBL.'
				SET '.implode(', ', $rSet).'
				WHERE module_id="'.xEscapeSQLstring($this->getModuleConfigID()).' "
				AND carrierID="'.xEscapeSQLstring($_CarrierID).'"
			';
		}else {
			
			$sql = '
				INSERT INTO '.INTERSHIPPER_CARRIERS_SETTINGS_TBL.'
				(module_id, carrierID,`'.implode('`, `', xEscapeSQLstring(array_keys($_Settings))).'`)
				VALUES("'.xEscapeSQLstring($this->getModuleConfigID()).'","'.xEscapeSQLstring($_CarrierID).'","'.implode('", "', xEscapeSQLstring($_Settings)).'")
			';
		}
		db_query($sql);
	}
	
	function _getCarrierSettings($_CarrierID){
		
		$sql = '
			SELECT account, invoiced FROM '.INTERSHIPPER_CARRIERS_SETTINGS_TBL.'
			WHERE module_id="'.xEscapeSQLstring($this->getModuleConfigID()).' "
			AND carrierID="'.xEscapeSQLstring($_CarrierID).'"
		';
		return db_fetch_row(db_query($sql));
	}

	function getShippingServices(){
		
		return InterShipperModule::_getCarriers();
	}
	
	function settingSHMOption($_ShipMethod, $_SettingID){
		
		$Options = translate("str_not_defined").':';
		
		$rOpt = InterShipperModule::_getShipMethodOptions($_ShipMethod);
		
		foreach ($rOpt as $_Key=>$_Descr){
			
			$Options .= ','.$_Descr.':'.$_Key;
		}
		
		return setting_SELECT_BOX($Options, $_SettingID);
	}
	
	function _getShipMethodOptions($_ShipMethod){
		
		switch ($_ShipMethod){
			case 'DRP':
				return array(
					'ADD' => 'Additional Handling',
					'SDD' => 'Saturday Delivery',
					'PDD' => 'Proof of Delivery',
				);
			case 'PCK':
			case 'SCD':
				return array(
					'ADP' => 'Additional Handling',
					'PDP' => 'Proof of Delivery',
					'SDP' => 'Saturday Pickup',
				);
			default:
				return array();
		}
		
	}
	
	function settingShipMethod($_SettingID){
		
		return setting_SELECT_BOX(InterShipperModule::getShipMethods4Select(), $_SettingID).'&nbsp;<input name="save" value="'.translate("btn_select").'" type="submit">';
	}
	
	/**
	 * Shipping services
	 *
	 * @param array $service
	 * @return bool
	 */
	function serviceIsAvailable($service){
		
		return $this->_getSettingValue('CONF_INTERSHIPPER_CARRIERS')&pow(2, $service['id']);
	}
	
	function _getRates(&$_Services,  $order, $address){
		$cachedRates = Storage::getInstance(__CLASS__);
		$address_ = md5(serialize($address));
		$order_ = md5(serialize($order));
		if(($address_ === $cachedRates->getData('address'))
			&&($order_ === $cachedRates->getData('order'))
			&&($_Services ===$cachedRates->getData('services_'))){
			$result = $cachedRates->getData('result');
			$_Services = $cachedRates->getData('services');
		}else{
			$cachedRates->clean();
			$cachedRates->setData('services_',$_Services);
			$result = parent::_getRates($_Services,  $order, $address);
			//TODO: cache only success result
			if(count($_Services)){
				$cachedRates->setData('address',$address_);
				$cachedRates->setData('order',$order_);
				$cachedRates->setData('result',$result);
				$cachedRates->setData('services',$_Services);
			}
		}
		return $result;
	}
}
?>