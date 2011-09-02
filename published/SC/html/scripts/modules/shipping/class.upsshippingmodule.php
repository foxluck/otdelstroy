<?php
/**
 * @connect_module_class_name upsShippingModule
 *
 */

class upsShippingModule extends ShippingRateCalculator{

	//var $language = 'eng';
		
	var $DestAddress = array();
	var $default_logo = 'http://www.webasyst.net/collections/design/shipping-icons/ups.gif';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title = UPSSHIPPINGMODULE_TTL;
		$this->description = UPSSHIPPINGMODULE_DSCR;
		$this->sort_order = 0;
		$this->Settings[] = 'CONF_SHIPPING_UPS_ACCESSLICENSENUMBER';
		$this->Settings[] = 'CONF_SHIPPING_UPS_USERID';
		$this->Settings[] = 'CONF_SHIPPING_UPS_PASSWORD';
		$this->Settings[] = 'CONF_SHIPPING_UPS_SHIPPER_COUNTRY_ID';
		$this->Settings[] = 'CONF_SHIPPING_UPS_SHIPPER_CITY';
		$this->Settings[] = 'CONF_SHIPPING_UPS_SHIPPER_POSTALCODE';
		$this->Settings[] = 'CONF_SHIPPING_UPS_PICKUP_TYPE';
		$this->Settings[] = 'CONF_SHIPPING_UPS_CUSTOMER_CLASSIFICATION';
		$this->Settings[] = 'CONF_SHIPPING_UPS_PACKAGE_TYPE';
		$this->Settings[] = 'CONF_SHIPPING_UPS_ENABLE_ERROR_LOG';
		$this->Settings[] = 'CONF_SHIPPING_UPS_USD_CURRENCY';
	}
	
	function install(){
		
		$this->SettingsFields['CONF_SHIPPING_UPS_CUSTOMER_CLASSIFICATION'] = array(
			'settings_value' 		=> 0, 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_CUSTOMER_CLASSIFICATION_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_CUSTOMER_CLASSIFICATION_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(translate("str_default").":0,Wholesale:01,Occasional:03,Retail:04",', 
			'sort_order' 			=> 50,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_USD_CURRENCY'] = array(
			'settings_value' 		=> 0, 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_USD_CURRENCY_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_USD_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 50,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_PACKAGE_TYPE'] = array(
			'settings_value' 		=> '02', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_PACKAGE_TYPE_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_PACKAGE_TYPE_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(upsShippingModule::_getPackageTypes(), ', 
			'sort_order' 			=> 50,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_PICKUP_TYPE'] = array(
			'settings_value' 		=> '01', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_PICKUP_TYPE_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_PICKUP_TYPE_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(upsShippingModule::_getPickupTypes(), ', 
			'sort_order' 			=> 60,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_ACCESSLICENSENUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_ACCESSLICENSENUMBER_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_ACCESSLICENSENUMBER_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_USERID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_USERID_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_USERID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 3,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_PASSWORD_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_PASSWORD_TTL, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 5,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_SHIPPER_COUNTRY_ID'] = array(
			'settings_value' 		=> CONF_COUNTRY, 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_SHIPPER_COUNTRY_ID_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_SHIPPER_COUNTRY_ID_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(upsShippingModule::_getCountriesOptions(), ', 
			'sort_order' 			=> 20,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_SHIPPER_CITY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_SHIPPER_CITY_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_SHIPPER_CITY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 30,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_SHIPPER_POSTALCODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_SHIPPER_POSTALCODE_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG_SHIPPER_POSTALCODE_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 40,
		);
		$this->SettingsFields['CONF_SHIPPING_UPS_ENABLE_ERROR_LOG'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> UPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_TTL, 
			'settings_description' 	=> UPSSHIPPINGMODULE_CFG__ENABLE_ERROR_LOG_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 50,
		);

		ShippingRateCalculator::install();
	}

	function settings_list(){
	
		if(!$this->_defined('CONF_SHIPPING_UPS_USD_CURRENCY')){
		
			$Ind = array_search($this->_getSettingRealName('CONF_SHIPPING_UPS_USD_CURRENCY'), $this->Settings);
			if( $Ind !== false){
			
				unset($this->Settings[$Ind]);
			}
		}
		if(!$this->_defined('CONF_SHIPPING_UPS_CUSTOMER_CLASSIFICATION')){
		
			$Ind = array_search($this->_getSettingRealName('CONF_SHIPPING_UPS_CUSTOMER_CLASSIFICATION'), $this->Settings);
			if( $Ind !== false){
			
				unset($this->Settings[$Ind]);
			}
		}
		return $this->Settings;
	}
	
	function _getCountriesOptions(){
		
		$Options = array();
		$CountriesNum = 0;
		$Countries = cnGetCountries( array('raw data'=>true), $CountriesNum );
		foreach ($Countries as $_Country){
			
			$Options[] = array("title"=>$_Country['country_name'], "value"=>$_Country['countryID']);
		}
		return $Options;
	}

	function _getCountryISO2ByID($_CountryID){
		
		$Country = cnGetCountryById( $_CountryID );
		return $Country['country_iso_2'];
	}
	
	/**
	 * Return list of shipping services
	 *
	 * @param string $_Type shipping type (Domestic, Inrenational)
	 * @return array
	 */
	function getShippingServices($_Type = ''){
		
		$_ShippingServices = array(
			1 => array(
				'id' => 1,
				'name' => 'UPS Next Day Air',
				'upsServiceCode'=> '01',
			),
			2 => array(
				'id' => 2,
				'name' => 'UPS 2nd Day Air',
				'upsServiceCode'=> '02',
			),
			3 => array(
				'id' => 3,
				'name' => 'UPS Ground',
				'upsServiceCode'=> '03',
			),
			4 => array(
				'id' => 4,
				'name' => 'UPS Worldwide Express',
				'upsServiceCode'=> '07',
			),
			5 => array(
				'id' => 5,
				'name' => 'UPS Worldwide Expedited',
				'upsServiceCode'=> '08',
			),
			6 => array(
				'id' => 6,
				'name' => 'UPS Standard',
				'upsServiceCode'=> '11',
			),
			7 => array(
				'id' => 7,
				'name' => 'UPS 3 Day Select',
				'upsServiceCode'=> '12',
			),
			8 => array(
				'id' => 8,
				'name' => 'UPS Next Day Air Saver',
				'upsServiceCode'=> '13',
			),
			9 => array(
				'id' => 9,
				'name' => 'UPS Next Day Air - Early A.M.',
				'upsServiceCode'=> '14',
			),
			10 => array(
				'id' => 10,
				'name' => 'UPS Worldwide Express Plus',
				'upsServiceCode'=> '54',
			),
			11 => array(
				'id' => 11,
				'name' => 'UPS 2nd Day Air A.M.',
				'upsServiceCode'=> '59',
			),
			
			12 => array(
				'id' => 12,
				'name' => 'UPS Express',
				'upsServiceCode'=> '07',
			),
			13 => array(
				'id' => 13,
				'name' => 'UPS Standard',
				'upsServiceCode'=> '11',
			),
			14 => array(
				'id' => 14,
				'name' => 'UPS Worldwide Express Plus',
				'upsServiceCode'=> '54',
			),
			15 => array(
				'id' => 15,
				'name' => 'UPS Express Saver',
				'upsServiceCode'=> '65',
			),
			
			16 => array(
				'id' => 16,
				'name' => 'UPS Express',
				'upsServiceCode'=> '01',
			),
			17 => array(
				'id' => 17,
				'name' => 'UPS Expedited',
				'upsServiceCode'=> '02',
			),
			18 => array(
				'id' => 18,
				'name' => 'UPS Worldwide Express',
				'upsServiceCode'=> '07',
			),
			19 => array(
				'id' => 19,
				'name' => 'UPS Worldwide Expedited',
				'upsServiceCode'=> '08',
			),
			20 => array(
				'id' => 20,
				'name' => 'UPS Standard',
				'upsServiceCode'=> '11',
			),
			21 => array(
				'id' => 21,
				'name' => 'UPS 3 Day Select',
				'upsServiceCode'=> '12',
			),
			22 => array(
				'id' => 22,
				'name' => 'UPS Express Saver',
				'upsServiceCode'=> '13',
			),
			23 => array(
				'id' => 23,
				'name' => 'UPS Express Early A.M.',
				'upsServiceCode'=> '14',
			),
			24 => array(
				'id' => 24,
				'name' => 'UPS Worldwide Express Plus',
				'upsServiceCode'=> '54',
			),
			
			25 => array(
				'id' => 25,
				'name' => 'UPS Next Day Air',
				'upsServiceCode'=> '01',
			),
			26 => array(
				'id' => 26,
				'name' => 'UPS 2nd Day Air',
				'upsServiceCode'=> '02',
			),
			27 => array(
				'id' => 27,
				'name' => 'UPS Ground',
				'upsServiceCode'=> '03',
			),
			28 => array(
				'id' => 28,
				'name' => 'UPS Worldwide Express',
				'upsServiceCode'=> '07',
			),
			29 => array(
				'id' => 29,
				'name' => 'UPS Worldwide Expedited',
				'upsServiceCode'=> '08',
			),
			30 => array(
				'id' => 30,
				'name' => 'UPS Next Day Air - Early A.M.',
				'upsServiceCode'=> '14',
			),
			31 => array(
				'id' => 31,
				'name' => 'UPS Worldwide Express Plus',
				'upsServiceCode'=> '54',
			),
			
			32 => array(
				'id' => 32,
				'name' => 'UPS Express',
				'upsServiceCode'=> '07',
			),
			33 => array(
				'id' => 33,
				'name' => 'UPS Expedited',
				'upsServiceCode'=> '08',
			),
			34 => array(
				'id' => 34,
				'name' => 'UPS Express Plus',
				'upsServiceCode'=> '54',
			),
			
			35 => array(
				'id' => 35,
				'name' => 'UPS Worldwide Express',
				'upsServiceCode'=> '07',
			),
			36 => array(
				'id' => 36,
				'name' => 'UPS Worldwide Expedited',
				'upsServiceCode'=> '08',
			),
			37 => array(
				'id' => 37,
				'name' => 'UPS Worldwide Express Plus',
				'upsServiceCode'=> '54',
			),
			38 => array(
				'id' => 38,
				'name' => 'UPS Worldwide Expedited',
				'upsServiceCode'=> '08',
			),
		);
		
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
			'US' 	=> array(1,2,3, 4, 5, 6, 7, 8, 9, 10, 11),
			'European Union' 	=> array(12, 13, 14, 15, 38),
			'Canada' 	=> array(16, 17, 18, 19, 20, 21, 22, 23, 24),
			'Puerto Rico' 	=> array(25,26,27,28,29,30,31),
			'Mexico' 	=> array(32, 33, 34),
			'All Other' 	=> array(35, 36, 37),
		);
	}
	
	function _prepareXMLQuery(&$_Services,  $order, $address){
		
		if(!isset($address['city']))$address['city'] = '';

		$this->DestAddress = $address;
		
		$OrderWeight = $this->_getOrderWeight($order);
		
		if(!$OrderWeight)return '';
		$MeasCode = '';
		
		switch(CONF_WEIGHT_UNIT){
			
			default:
			case 'lbs':
			case 'lb':
				$MeasCode = 'LBS';
				break;
			case 'g':
				$OrderWeight = floatval($OrderWeight/1000);
			case 'kg':
			case 'kgs':
				$MeasCode = 'KGS';
		}
		$OrderWeight = ceil($OrderWeight*10)/10;
		
		$CustomerClassification = '';
		
		if($this->_defined('CONF_SHIPPING_UPS_CUSTOMER_CLASSIFICATION')){
		
			if((int)$this->_getSettingValue('CONF_SHIPPING_UPS_CUSTOMER_CLASSIFICATION')){
				$CustomerClassification = $this->_getSettingValue('CONF_SHIPPING_UPS_CUSTOMER_CLASSIFICATION');
			}elseif($this->_getSettingValue('CONF_SHIPPING_UPS_PICKUP_TYPE') == 11){
				$CustomerClassification = '04';
			}
		}elseif($this->_getSettingValue('CONF_SHIPPING_UPS_PICKUP_TYPE') == 11){
				$CustomerClassification = '04';
		}

		$XMLQuery = '
		<?xml version="1.0"?>
			<AccessRequest xml:lang="en-US">
			<AccessLicenseNumber>'.$this->_getSettingValue('CONF_SHIPPING_UPS_ACCESSLICENSENUMBER').'</AccessLicenseNumber>
			<UserId>'.$this->_getSettingValue('CONF_SHIPPING_UPS_USERID').'</UserId>
			<Password>'.$this->_getSettingValue('CONF_SHIPPING_UPS_PASSWORD').'</Password>
		</AccessRequest>
		
		<?xml version="1.0"?>
		<RatingServiceSelectionRequest>
		<Request>
			<RequestAction>Rate</RequestAction>
			<RequestOption>Shop</RequestOption>
		</Request>
		<PickupType>
			<Code>'.$this->_getSettingValue('CONF_SHIPPING_UPS_PICKUP_TYPE').'</Code>
		</PickupType>'.(!$CustomerClassification?'':'
		<CustomerClassification>
			<Code>'.$CustomerClassification.'</Code>
		</CustomerClassification>').'
		<Shipment>
			<Shipper>
				<Address>
					'.($this->_getSettingValue('CONF_SHIPPING_UPS_SHIPPER_POSTALCODE')?'<PostalCode>'.$this->_getSettingValue('CONF_SHIPPING_UPS_SHIPPER_POSTALCODE').'</PostalCode>':'').'
					'.($this->_getSettingValue('CONF_SHIPPING_UPS_SHIPPER_CITY')?'<City>'.$this->_getSettingValue('CONF_SHIPPING_UPS_SHIPPER_CITY').'</City>':'').'
					<CountryCode>'.$this->_getCountryISO2ByID($this->_getSettingValue('CONF_SHIPPING_UPS_SHIPPER_COUNTRY_ID')).'</CountryCode>
				</Address>
			</Shipper>
			<ShipTo>
				<Address>
					'.($address['zip']?'<PostalCode>'.$address['zip'].'</PostalCode>':'').'
					'.($address['city']&&!$address['zip']?'<City>'.$address['city'].'</City>':'').'
					<CountryCode>'.$this->_getCountryISO2ByID($address['countryID']).'</CountryCode>
				</Address>
			</ShipTo>
			<Package>
				<PackagingType>
				<Code>'.$this->_getSettingValue('CONF_SHIPPING_UPS_PACKAGE_TYPE').'</Code>
				</PackagingType>
				<PackageWeight>
					<UnitOfMeasurement>
						<Code>'.$MeasCode.'</Code>
					</UnitOfMeasurement>
					<Weight>'.$OrderWeight.'</Weight>
				</PackageWeight>
			</Package>
		</Shipment>
		</RatingServiceSelectionRequest>
		';
		return $XMLQuery;
	}

	function _sendXMLQuery($_XMLQuery){

		if(!$_XMLQuery)return '';
		if ( !($ch = curl_init()) ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Local error: '.translate("err_curlinit"));
			return translate("err_curlinit");
		}

		if ( curl_errno($ch) != 0 ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch).' '.curl_error($ch));
			return translate("err_curlinit");
		}

//		$url = 'https://wwwcie.ups.com/ups.app/xml/Rate';
		$url = 'https://www.ups.com/ups.app/xml/Rate';
		@curl_setopt($ch, CURLOPT_URL, $url );
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		@curl_setopt($ch, CURLOPT_HEADER, 0);
		@curl_setopt($ch, CURLOPT_POST, 1);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $_XMLQuery);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		initCurlProxySettings($ch);

		$result = @curl_exec($ch);
		if ( curl_errno($ch) != 0){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch).' '.curl_error($ch)." {$url}");
			return translate("err_curlexec");
		}

		curl_close($ch);
		return $result;
	}
	
	function _parseXMLAnswer($_XMLAnswer){
		
		$Rates 	= array();
		$xml2ar 	= new xml2Array();
		@list($mainXml) = $xml2ar->parse($_XMLAnswer);
		
		$Coef = 1;
		if($this->_defined('CONF_SHIPPING_UPS_USD_CURRENCY')){
		
			$_Currency = currGetCurrencyByID( $this->_getSettingValue('CONF_SHIPPING_UPS_USD_CURRENCY') );
			if($this->_getSettingValue('CONF_SHIPPING_UPS_USD_CURRENCY'))$Coef = $_Currency['currency_value'];
		}else{
		
			$Currencies = currGetAllCurrencies();
			foreach ($Currencies as $_Currency){
			
				if($_Currency['currency_iso_3'] == 'USD'){
					
					$Coef = $_Currency['currency_value'];
					break;
				}
			}
		}
		unset($xml2ar);
		$NoInfo = true;
		$_TC = count($mainXml['children']);
		for ( $i=0; $i<$_TC; $i++ ){

			if($mainXml['children'][$i]['name']=='RATEDSHIPMENT'){
			
				$NoInfo = false;
				$_Rate = array(
					'name' 	=> '',
					'id' 		=> '',
					'rate' 	=> '',
					);
				$__TC = count($mainXml['children'][$i]['children']);
				for ( $_i=0; $_i<$__TC; $_i++){
					
					$_Node = &$mainXml['children'][$i]['children'][$_i];
					if($_Node['name']=='SERVICE'){

						foreach ($_Node['children'] as $__Node){
								
								if($__Node['name']=='CODE'){
									
									$_t = $this->_getServiceByServiceCode($__Node['tagData']);
									$_Rate['name'] = $_t['name'];
									$_Rate['id'] = $_t['id'];
								}
							}
					}elseif ($_Node['name']=='TOTALCHARGES'){
						
						foreach ($_Node['children'] as $__Node){
							
							if($__Node['name']=='MONETARYVALUE'){
								
								$_Rate['rate'] = $__Node['tagData']/$Coef;
							}
						}
					}
				}
				$Rates[$_Rate['id']][] = $_Rate;
			}
		}
		if($NoInfo && $this->_getSettingValue('CONF_SHIPPING_UPS_ENABLE_ERROR_LOG')){
			
			$fp = fopen(DIR_TEMP."/ups_errors.log", "a");
			fwrite($fp, "\n".date("Y-m-d H:i:s")."\n".$_XMLAnswer);
			fclose($fp);
		}
		
		return $Rates;
	}
	
	function _getServiceByServiceCode($_ServiceCode){
		
		$Zone = $this->_getShippingTypeByCountry($this->DestAddress['countryID']);

		$Services = $this->getShippingServices($Zone);
		foreach ($Services as $_Service){
			
			if($_Service['upsServiceCode'] == $_ServiceCode){
				
				return $_Service;
			}
		}
		return array('name'=>'');
	}

	function _getShippingTypeByCountry($_CountryID){
		
		$CountryISO2 = $this->_getCountryISO2ByID($_CountryID);
		switch ($CountryISO2){
			case 'US':
				$Zone = 'US';
				break;
			case 'CA':
				$Zone = 'Canada';
				break;
			case 'MX':
				$Zone = 'Mexico';
				break;
			case 'PR':
				$Zone = 'Puerto Rico';
				break;
			default:
				if(!in_array($CountryISO2, array('AT','PT','BE','FI','FR','DE','NL','IT','LU','PT','NL','IE','ES')))
					$Zone = 'All Other';
				else 
					$Zone = 'European Union';
				break;
		}
		return $Zone;
	}
	
	function _getServicesByCountry($_CountryID){
		
		return $this->getShippingServices($this->_getShippingTypeByCountry($_CountryID));
	}

	function _getPickupTypes(){

		return array(
			array(
				'title' 		=> 'Daily Pickup',
				'value' 	=> '01',
				),
			array(
				'title' 		=> 'Customer Counter',
				'value' 	=> '03',
				),
			array(
				'title' 		=> 'One Time Pickup',
				'value' 	=> '06',
				),
			array(
				'title' 		=> 'On Call Air Pickup',
				'value' 	=> '07',
				),
			array(
				'title' 		=> 'Suggested Retail Rates (UPS Store)',
				'value' 	=> '11',
				),
			array(
				'title' 		=> 'Letter Center',
				'value' 	=> '19',
				),
			array(
				'title' 		=> 'Air Service Center',
				'value' 	=> '20',
				),
			);
	}

	function _getPackageTypes(){
		
		return array(
			array(
				'title' 		=> 'UPS letter/ UPS Express Envelope',
				'value' 	=> '01',
				),
			array(
				'title' 		=> 'Customer package',
				'value' 	=> '02',
				),
			array(
				'title' 		=> 'UPS Tube',
				'value' 	=> '03',
				),
			array(
				'title' 		=> 'UPS Pak',
				'value' 	=> '04',
				),
			array(
				'title' 		=> 'UPS Express Box',
				'value' 	=> '21',
				),
			array(
				'title' 		=> 'UPS 25Kg Box',
				'value' 	=> '24',
				),
			array(
				'title' 		=> 'UPS 10Kg Box',
				'value' 	=> '25',
				),
			);
	}
}
?>