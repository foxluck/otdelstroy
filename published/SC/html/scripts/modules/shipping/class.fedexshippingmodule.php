<?php
/**
 * @connect_module_class_name fedexShippingModule
 *
 */

class fedexShippingModule extends ShippingRateCalculator{

	//var $language = 'eng';
	
	var $DestAddress 	= array();
	var $ShippingServices;
	var $default_logo = 'http://www.webasyst.net/collections/design/shipping-icons/fedex.gif';

	function fedexShippingModule($mod_id = 0){
		
		$this->LogFile = DIR_TEMP.'/fedex_errors.log';
		parent::__construct($mod_id);
	}
	
	/*
	 Abstract methods redifinition
	 */
	
	function _parseXMLAnswer($_Data){
		
		if(!is_array($_Data))return array();
	
		$Rates 		= array();
		
		foreach($_Data as $_ServID=>$_D){

			$_D = $this->_parseData($_D);
			if(isset($_D[2]) && $this->_getSettingValue('CONF_SHIPPING_FEDEX_ENABLE_ERROR_LOG')){
			
				$this->_writeLogMessage(0, 'FedEx error: '.$_D[2].(isset($_D[3])?' ('.$_D[3].')':''));
				continue;
			}
			
			if(!isset($_D['1133']))continue;
			if(!$_D['1133'])continue;
			
			$services = $this->_getServicesByCountry($this->DestAddress['countryID']);
			$service_code2service = array();
			foreach ($services as $service){
				
				$service_code2service[$service['xmlCode']] = $service;
			}
			
			for ($i=1;$i<=$_D['1133'];$i++){
			
				$service_code = $_D['1274-'.$i];
				$service = $service_code2service[$service_code];
				$Rates[$service['id']][] = array(
					'name' => $service['name'],
					'id' => $service['id'],
					'rate' => $this->_convertCurrency($_D['1419-'.$i], $this->_getSettingValue('CONF_SHIPPING_FEDEX_CURRENCY'), 0),
					);
			}
		}
		return $Rates;
	}
	
	function _sendXMLQuery($_Data){
		
		$result = array();
		
		if(!$_Data){
			$this->_writeLogMessage(MODULE_LOG_FEDEX, 'No data for sending');
			return '';
		}
		
		if ( !($ch = curl_init()) ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Local error: '.translate("err_curlinit"));
			return translate("err_curlinit");
		}

		if ( curl_errno($ch) != 0 ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch).' '.curl_error($ch));
			return translate("err_curlinit");
		}

		if(!is_array($_Data))$_Data = array($_Data);
		
		$url = ($this->_getSettingValue('CONF_SHIPPING_FEDEX_TESTMODE')?'https://gatewaybeta.fedex.com/GatewayDC':'https://gateway.fedex.com/GatewayDC');

		$ParsedUrl = parse_url($url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Referer: " . CONF_SHOP_NAME,
												   "Host: " . $ParsedUrl['host'],
												   "Accept: image/gif,image/jpeg,image/pjpeg,text/plain,text/html,*/*",
												   "Pragma:",
												   "Content-Type:image/gif"));
		initCurlProxySettings($ch);
												   
		foreach($_Data as $_Key=>$__Q){
		
			curl_setopt($ch, CURLOPT_POSTFIELDS, $__Q);
			$_res = curl_exec($ch);
		
			if ( curl_errno($ch) != 0){
				
				$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch).' '.curl_error($ch)." {$url}");
				continue;
			}
			$result[$_Key] = $_res;
		}
		curl_close($ch);
		return $result;
	}
	
	function _getServicesByCountry($_DestCountryID){
		
		$Country = cnGetCountryById($this->_getSettingValue('CONF_SHIPPING_FEDEX_COUNTRY_CODE'));
		$DestCountry = cnGetCountryById($_DestCountryID);
		if ($Country['country_iso_3'] == 'USA' && $DestCountry['country_iso_3'] == 'USA' ){
			
			return $this->getShippingServices('Domestic');
		}else {
			
			return $this->getShippingServices('International');
		}
		return array();
	}
	
	function _InitVars(){
		
		parent::_initVars();
		$this->title = FEDEXSHIPPINGMODULE_TTL;
		$this->description = FEDEXSHIPPINGMODULE_DSCR;
		$this->sort_order = 0;
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_TESTMODE';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_ACCOUNT_NUMBER';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_METER_NUMBER';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_PACKAGING';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_CARRIER';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_CURRENCY';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_COUNTRY_CODE';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_POSTAL_CODE';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_STATE_OR_PROVINCE_CODE';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_CITY';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_ADDRESS';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_PHONE_NUMBER';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_NAME';
		$this->Settings[] = 'CONF_SHIPPING_FEDEX_ENABLE_ERROR_LOG';
		
		$this->ShippingServices = array(
			1 => array(
				'id' => 1,
				'name' => 'Priority',
				'xmlCode'=> '01',
			),
			2 => array(
				'id' => 2,
				'name' => '2day',
				'xmlCode'=> '03',
			),
			3 => array(
				'id' => 3,
				'name' => 'Standard Overnight',
				'xmlCode'=> '05',
			),
			4 => array(
				'id' => 4,
				'name' => 'First Overnight',
				'xmlCode'=> '06',
			),
			5 => array(
				'id' => 5,
				'name' => 'Express Saver',
				'xmlCode'=> '20',
			),
			6 => array(
				'id' => 6,
				'name' => 'Overnight Freight',
				'xmlCode'=> '70',
			),
			7 => array(
				'id' => 7,
				'name' => '2day Freight',
				'xmlCode'=> '80',
			),
			8 => array(
				'id' => 8,
				'name' => 'Express Saver Freight',
				'xmlCode'=> '83',
			),
			9 => array(
				'id' => 9,
				'name' => 'International Priority',
				'xmlCode'=> '01',
			),
			10 => array(
				'id' => 10,
				'name' => 'International Economy',
				'xmlCode'=> '03',
			),
			11 => array(
				'id' => 11,
				'name' => 'International First',
				'xmlCode'=> '06',
			),
			12 => array(
				'id' => 12,
				'name' => 'FedEx ground',
				'xmlCode'=> '92',
			),
			13 => array(
				'id' => 13,
				'name' => 'Ground home delivery',
				'xmlCode'=> '90',
			),
		);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_SHIPPING_FEDEX_TESTMODE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> FEDEX_CNF_TESTMODE_TTL, 
			'settings_description' 	=> FEDEX_CNF_TESTMODE_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 10,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_ACCOUNT_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_ACCOUNT_NUMBER_TTL, 
			'settings_description' 	=> FEDEX_CNF_ACCOUNT_NUMBER_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 10,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_METER_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_METER_NUMBER_TTL, 
			'settings_description' 	=> FEDEX_CNF_METER_NUMBER_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 20,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_PACKAGING'] = array(
			'settings_value' 		=> '01', 
			'settings_title' 			=> FEDEX_CNF_PACKAGING_TTL, 
			'settings_description' 	=> FEDEX_CNF_PACKAGING_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(fedexShippingModule::_getPackagingTypes(),', 
			'sort_order' 			=> 40,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_CARRIER'] = array(
			'settings_value' 		=> 'FDXE', 
			'settings_title' 			=> FEDEX_CNF_CARRIER_TTL, 
			'settings_description' 	=> FEDEX_CNF_CARRIER_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(array(array("title"=>"All","value"=>"ALL"),array("title"=>"FedEx Express","value"=>"FDXE"), array("title"=>"FedEx Ground", "value"=>"FDXG")),', 
			'sort_order' 			=> 50,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_CURRENCY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_CURRENCY_TTL, 
			'settings_description' 	=> FEDEX_CNF_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 55,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_COUNTRY_CODE'] = array(
			'settings_value' 		=> CONF_COUNTRY, 
			'settings_title' 			=> FEDEX_CNF_COUNTRY_CODE_TTL, 
			'settings_description' 	=> FEDEX_CNF_COUNTRY_CODE_DSCR, 
			'settings_html_function' 	=> 'setting_COUNTRY_SELECT(true,', 
			'sort_order' 			=> 60,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_POSTAL_CODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_POSTAL_CODE_TTL, 
			'settings_description' 	=> FEDEX_CNF_POSTAL_CODE_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 70,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_STATE_OR_PROVINCE_CODE'] = array(
			'settings_value' 		=> CONF_ZONE, 
			'settings_title' 			=> FEDEX_CNF_STATE_OR_PROVINCE_CODE_TTL, 
			'settings_description' 	=> FEDEX_CNF_STATE_OR_PROVINCE_CODE_DSCR, 
			'settings_html_function' 	=> 'setting_ZONE_SELECT('.$this->_getSettingRealName('CONF_SHIPPING_FEDEX_COUNTRY_CODE').',',
			'sort_order' 			=> 80,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_CITY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_CITY_TTL, 
			'settings_description' 	=> FEDEX_CNF_CITY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 90,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_ADDRESS'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_ADDRESS_TTL, 
			'settings_description' 	=> FEDEX_CNF_ADDRESS_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 100,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_PHONE_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_PHONE_NUMBER_TTL, 
			'settings_description' 	=> FEDEX_CNF_PHONE_NUMBER_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 110,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_NAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_NAME_TTL, 
			'settings_description' 	=> FEDEX_CNF_NAME_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 120,
		);
		$this->SettingsFields['CONF_SHIPPING_FEDEX_ENABLE_ERROR_LOG'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> FEDEX_CNF_ERROR_LOG_TTL, 
			'settings_description' 	=> FEDEX_CNF_ERROR_LOG_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(',
			'sort_order' 			=> 130,
		);
	}

	function getShippingServices($_Type = '', $_ID = null){
		
		$_ShippingServices = &$this->ShippingServices;
		
		if(isset($_ID))return $_ShippingServices[$_ID];
		
		$ShippingTypes = $this->_getShippingTypes();
		
		if (!in_array($_Type, array_keys($ShippingTypes)) && $_Type) return array();
		
		if(!$_Type)return  $_ShippingServices;
		else{
			
			$_tRet = array();
			foreach ($ShippingTypes[$_Type] as $_ind){
				
				$_tRet[$_ind] = $_ShippingServices[$_ind];
			}
			return $_tRet;
		}
	}
	
	function _getShippingTypes(){
		
		return array(
			'Domestic' 	=> array(1,2,3, 4, 5, 6, 7, 8, 12, 13),
			'International' 	=> array(9, 10, 11, 12),
		);
	}
	
	function _prepareXMLQuery(&$_Services,  $order, $address){
		
		if(!$this->_getSettingValue('CONF_SHIPPING_FEDEX_METER_NUMBER')){
			
			$MeterNumber = $this->_getMeterNumber();
		}else {
			
			$MeterNumber = $this->_getSettingValue('CONF_SHIPPING_FEDEX_METER_NUMBER');
		}
		if(!$MeterNumber){
			$this->_writeLogMessage(MODULE_LOG_FEDEX, 'Couldn`t get meter number');
			return '';
		}
		
		if(!isset($address['city']))$address['city'] = '';

		$this->DestAddress = $address;
		
		$OrderWeight = $this->_getOrderWeight($order);
		if(!$OrderWeight){
			
			$this->_writeLogMessage(MODULE_LOG_FEDEX, 'Products from order must have weight or they are free for shipping');
			return '';
		}
		
		$OrderWeight = $this->_convertMeasurement($OrderWeight, CONF_WEIGHT_UNIT, 'LBS');
		
		$OrderWeight = ceil($OrderWeight*10)/10;
		
		$ProvinceCode 	= '';
		$CountryCode 	= '';
		
		$CountryCode = cnGetCountryById($this->_getSettingValue('CONF_SHIPPING_FEDEX_COUNTRY_CODE'));
		$CountryCode = $CountryCode['country_iso_2'];
		
		if(($CountryCode=='US' || $CountryCode=='CA')){
			
			$ProvinceCode = znGetSingleZoneById($this->_getSettingValue('CONF_SHIPPING_FEDEX_STATE_OR_PROVINCE_CODE'));
			$ProvinceCode = $ProvinceCode['zone_code'];
		}
		
		$DestCountry = cnGetCountryById($address['countryID']);
		$DestCountry = $DestCountry['country_iso_2'];
		
		if(($DestCountry=='US' || $DestCountry=='CA')){
			
			$DestProvinceCode = znGetSingleZoneById($address['zoneID']);
			$DestProvinceCode = $DestProvinceCode['zone_code'];
		}
		
		$Data = array(
			0 => 25,
			10 => $this->_getSettingValue('CONF_SHIPPING_FEDEX_ACCOUNT_NUMBER'),
			498 => $MeterNumber,
			117 => $CountryCode,
			50 => $DestCountry,
			24 => ""/*test*/,
			23 => 1,
			75 => 'LBS',
			1273 =>  $this->_getSettingValue('CONF_SHIPPING_FEDEX_PACKAGING'),
			1401 => sprintf('%0.1f', $OrderWeight),
			116 => 1,
			68 => 'USD',
			);
		if ($this->_getSettingValue('CONF_SHIPPING_FEDEX_CARRIER')!='ALL') {
			$Data['3025'] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_CARRIER');
		}
		if($CountryCode=='US'||$CountryCode=='CA'){
			
			$Data[8] = $ProvinceCode;
		}
			$Data[9] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_POSTAL_CODE');
		if($DestCountry=='US'||$DestCountry=='CA'){
			
			$Data[16] = $DestProvinceCode;
		}
			$Data[17] = $address['zip'];
		$Data['99'] = '';
		$Data = $this->_prepareData($Data);

		return $Data;
	}
	
	/*
	 Current class methods
	 */
	
	function _prepareData($_rData){
	
		$Data = '';
		foreach($_rData as $_Key=>$_Val){
		
			$Data .= $_Key.',"'.$_Val.'"';
		}
		return $Data;
	}
	
	function _parseData($_Data){
	
		$rData = array();
		$Out = '';
		preg_match_all('|([^,^\"]+),\"([^\"]*)\"|', $_Data, $Out);
		if(count($Out)!=3)return $rData;
		
		foreach($Out[1] as $_Ind=>$_Key){
		
			if(isset($Out[2][$_Ind])){
			
				$rData[$_Key] = $Out[2][$_Ind];
			}
		}
		
		return $rData;
	}
	
	/**
	 * Send meter number request
	 */
	function _getMeterNumber(){
		
		$Result 		= '';
		$Data = array();
		
		$CountryCode = cnGetCountryById($this->_getSettingValue('CONF_SHIPPING_FEDEX_COUNTRY_CODE'));
		$CountryCode = $CountryCode['country_iso_2'];
		$Data[4014] = $CountryCode;
		
		if($CountryCode=='US' || $CountryCode=='CA'){
			
			$Province = znGetSingleZoneById($this->_getSettingValue('CONF_SHIPPING_FEDEX_STATE_OR_PROVINCE_CODE'));
			$Data[4012] = $Province['zone_code'];
		}
		
		$Data[0] = 211;
		$Data[10] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_ACCOUNT_NUMBER');
		$Data[4003] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_NAME');
		$Data[4008] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_ADDRESS');
		$Data[4011] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_CITY');
		$Data[4013] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_POSTAL_CODE');
		$Data[4015] = $this->_getSettingValue('CONF_SHIPPING_FEDEX_PHONE_NUMBER');
		
		$Result = $this->_sendXMLQuery($this->_prepareData($Data));
		if(is_array($Result))list($Result) = $Result;
		$Result = $this->_parseData($Result);
		
		if(isset($Result[2])){
		
			$this->_writeLogMessage(0, 'FedEx error: '.$Result[2].' ('.$Result[3].')');
			return '';
		}else{
		
			_setSettingOptionValue( $this->_getSettingRealName('CONF_SHIPPING_FEDEX_METER_NUMBER'), $Result[498] );
			return $Result[498];
		}
	}
	
	function _getPackagingTypes(){
		
		return array(
			array(
				'title' => 'FedEx envelope',
				'value' => '06',
				),
			array(
				'title' => 'FedEx pak',
				'value' => '02',
				),
			array(
				'title' => 'FedEx box',
				'value' => '03',
				),
			array(
				'title' => 'FedEx tube',
				'value' => '04',
				),
			array(
				'title' => 'FedEx 10 kg box',
				'value' => '15',
				),
			array(
				'title' => 'FedEx 25 kg box',
				'value' => '25',
				),
			array(
				'title' => 'Your packaging',
				'value' => '01',
				),
			);
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