<?php
/**
 * @connect_module_class_name uspsShippingModule
 * @link http://www.usps.com/webtools/technical.htm
 */
class uspsShippingModule extends ShippingRateCalculator{

	//var $language = 'eng';
	var $default_logo = 'http://www.webasyst.net/collections/design/shipping-icons/usps.gif';

	function _initVars(){

		parent::_initVars();
		$this->title = SHIPPING_MODULE_USPS_TTL;
		$this->description = SHIPPING_MODULE_USPS_DSCR;
		$this->sort_order = 0;
		$this->Settings[] = 'CONF_SHIPPING_USPS_USERID';
		//$this->Settings[] = 'CONF_SHIPPING_USPS_PASSWORD';
		$this->Settings[] = 'CONF_SHIPPING_USPS_ZIPORIGINATION';
		$this->Settings[] = 'CONF_SHIPPING_USPS_PACKAGESIZE';
		$this->Settings[] = 'CONF_SHIPPING_USPS_MACHINABLE';
		$this->Settings[] = 'CONF_SHIPPING_USPS_DOMESTIC_SERVS';
		$this->Settings[] = 'CONF_SHIPPING_USPS_INTERNATIONAL_SERVS';
		$this->Settings[] = 'CONF_SHIPPING_USPS_ENABLE_ERROR_LOG';
		$this->Settings[] = 'CONF_SHIPPING_USPS_USD_CURRENCY';
	}

	function install(){

		$this->SettingsFields['CONF_SHIPPING_USPS_USD_CURRENCY'] = array(
			'settings_value' 		=> 0, 
			'settings_title' 			=> USPS_CONF_USD_CURRENCY_TTL, 
			'settings_description' 	=> USPS_CONF_USD_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 50,
		);
		$this->SettingsFields['CONF_SHIPPING_USPS_ZIPORIGINATION'] = array(
			'settings_value' 		=> 0, 
			'settings_title' 			=> USPS_CONF_ZIPORIGINATION_TTL, 
			'settings_description' 	=> USPS_CONF_ZIPORIGINATION_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 3,
		);
		$this->SettingsFields['CONF_SHIPPING_USPS_USERID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> USPS_CONF_USERID_TTL, 
			'settings_description' 	=> USPS_CONF_USERID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 1,
		);
		/*$this->SettingsFields['CONF_SHIPPING_USPS_PASSWORD'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> USPS_CONF_PASSWORD_TTL,
			'settings_description' 	=> USPS_CONF_PASSWORD_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,',
			'sort_order' 			=> 1,
			);*/
		$this->SettingsFields['CONF_SHIPPING_USPS_PACKAGESIZE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> USPS_CONF_PACKAGESIZE_TTL, 
			'settings_description' 	=> USPS_CONF_PACKAGESIZE_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(array(array("title"=>"Regular", "value"=>"Regular"), array("title"=>"Large", "value"=>"Large"),array("title"=>"Oversize", "value"=>"Oversize")),', 
			'sort_order' 			=> 4,
		);
		$this->SettingsFields['CONF_SHIPPING_USPS_MACHINABLE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> USPS_CONF_MACHINABLE_TTL, 
			'settings_description' 	=> USPS_CONF_MACHINABLE_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(array(array("title"=>"yes", "value"=>"True"), array("title"=>"no", "value"=>"False")),', 
			'sort_order' 			=> 5,
		);

		$_Servs = $this->getShippingServices('Domestic');
		$_boxDescr = array();
		foreach ($_Servs as $_Serv){

			$_boxDescr[] = $_Serv['id'].' => "'.$_Serv['name'].'"';
		}
		$this->SettingsFields['CONF_SHIPPING_USPS_DOMESTIC_SERVS'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> USPS_CONF_DOMESTIC_SERVS_TTL, 
			'settings_description' 	=> USPS_CONF_DOMESTIC_SERVS_DSCR, 
			'settings_html_function' 	=> 'setting_CHECKBOX_LIST(array('.implode(', ', $_boxDescr).'),', 
			'sort_order' 			=> 6,
		);
		$_Servs = $this->getShippingServices('International');
		$_boxDescr = array();
		foreach ($_Servs as $_Serv){

			$_boxDescr[] = $_Serv['id'].' => "'.$_Serv['name'].'"';
		}
		$this->SettingsFields['CONF_SHIPPING_USPS_INTERNATIONAL_SERVS'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> USPS_CONF_INTERNATIONAL_SERVS_TTL, 
			'settings_description' 	=> USPS_CONF_INTERNATIONAL_SERVS_DSCR, 
			'settings_html_function' 	=> 'setting_CHECKBOX_LIST(array('.implode(', ', $_boxDescr).'),', 
			'sort_order' 			=> 7,
		);
		$this->SettingsFields['CONF_SHIPPING_USPS_ENABLE_ERROR_LOG'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> USPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_TTL, 
			'settings_description' 	=> USPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 50,
		);

		ShippingRateCalculator::install();
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
				'name' => 'Express',
				'code' => 'Express',
				'maxWeight' => array(
					'lbs' 	=> '70',
					'oz' 	=> '0',
		),
				'packageSizes' => array(
					'Regular',
					'Large'
					),
					),
					2 => array(
				'id' => 2,
				'name' => 'First Class',
				'code' => 'First Class',
				'maxWeight' => array(
						'lbs' 	=> '0',
						'oz' 	=> '13',
					),
				'packageSizes' => array(
					'Regular',
					'Large'
					),
				'FirstClassMailType' => array(
					'Letter',
					'Flat',
					'Parcel',
					'Postcard'
					),
					),
					3 => array(
				'id' => 3,
				'name' => 'Priority',
				'code' => 'Priority',
				'maxWeight' => array(
						'lbs' 	=> '70',
						'oz' 	=> '0',
					),
				'packageSizes' => array(
					'Regular',
					'Large'
					),
					),
					4 => array(
				'id' => 4,
				'name' => 'Parcel Post',
				'code' => 'Parcel',
				'maxWeight' => array(
						'lbs' 	=> '70',
						'oz' 	=> '0',
					),
				'packageSizes' => array(
					'Regular',
					'Large',
					'Oversize',
					),
					),
					5 => array(
				'id' => 5,
				'name' => 'Bound Printed Matter',
				'code' => 'BPM',
				'maxWeight' => array(
						'lbs' 	=> '15',
						'oz' 	=> '0',
					),
				'packageSizes' => array(
					'Regular',
					'Large'
					),
					),
					6 => array(
				'id' => 6,
				'name' => 'Media Mail',
				'code' => 'Media',
				'maxWeight' => array(
						'lbs' 	=> '70',
						'oz' 	=> '0',
					),
				'packageSizes' => array(
					'Regular',
					'Large'
					),
					),
					7 => array(
				'id' => 7,
				'name' => 'Library Mail',
				'code' => 'Library',
				'maxWeight' => array(
						'lbs' 	=> '70',
						'oz' 	=> '0',
					),
				'packageSizes' => array(
					'Regular',
					'Large'
					),
					),
					8 => array(
				'id' => 8,
				'name' => 'All',
				'maxWeight' => array(
					'lbs' 	=> '70',
					'oz' 	=> '0',
					),
				'packageSizes' => array(
					'Regular',
					'Large',
					'Oversize',
					),
					),
					9 => array(
				'id' => 9,
				'name' => 'Package',
				'maxWeight' => array(
					'lbs' 	=> '70',
					'oz' 	=> '0',
					),
				'packageSizes' => array(
					),
					),
					10 => array(
				'id' => 10,
				'name' => 'Postcards or aerogrammes',
				'maxWeight' => array(
					'lbs' 	=> '70',
					'oz' 	=> '0',
					),
				'packageSizes' => array(
					),
					),
					11 => array(
				'id' => 11,
				'name' => 'Matter for the blind',
				'maxWeight' => array(
					'lbs' 	=> '70',
					'oz' 	=> '0',
					),
				'packageSizes' => array(
					),
					),
					12 => array(
				'id' => 12,
				'name' => 'Envelope',
				'maxWeight' => array(
					'lbs' 	=> '70',
					'oz' 	=> '0',
					),
				'packageSizes' => array(
					),
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

	function getEnabledServices($_Type = ''){

		$_Services = $this->getShippingServices($_Type);
		$Enabled = array();

		switch ($_Type){
			case 'Domestic':
				foreach ($_Services as $_Serv){

					if ($this->_getSettingValue('CONF_SHIPPING_USPS_DOMESTIC_SERVS')&pow(2, $_Serv['id'])){

						$Enabled[] = $_Serv['id'];
					}
				}
				break;
			case 'International':
				foreach ($_Services as $_Serv){

					if ($this->_getSettingValue('CONF_SHIPPING_USPS_INTERNATIONAL_SERVS')&pow(2, $_Serv['id'])){

						$Enabled[] = $_Serv['id'];
					}
				}
				break;
		}
		return $Enabled;
	}

	function _getShippingTypes(){

		return array(
			'Domestic' 	=> array(1,2,3, 4, 5, 6, 7),
			'International' 	=> array(9, 10, 11, 12),
		);
	}

	function _prepareXMLQuery(&$_Services,  $order, $address){
		if(!count($_Services))return '';
		$XMLQuery = '';

		$Type = $this->_getServiceType($_Services[0]['id']);

		switch ($Type){
			case 'Domestic':
				//$XMLQuery .= '<RateV2Request USERID="'.$this->_getSettingValue('CONF_SHIPPING_USPS_USERID').'" PASSWORD="'.xHtmlSpecialChars($this->_getSettingValue('CONF_SHIPPING_USPS_PASSWORD')).'">'."\n";
				$XMLQuery .= '<RateV4Request USERID="'.$this->_getSettingValue('CONF_SHIPPING_USPS_USERID').'" PASSWORD=""><Revision/>'."\n";
				break;
			case 'International':
				//$XMLQuery .= '<IntlRateRequest USERID="'.$this->_getSettingValue('CONF_SHIPPING_USPS_USERID').'" PASSWORD="'.xHtmlSpecialChars($this->_getSettingValue('CONF_SHIPPING_USPS_PASSWORD')).'">'."\n";
				$XMLQuery .= '<IntlRateRequest USERID="'.$this->_getSettingValue('CONF_SHIPPING_USPS_USERID').'" PASSWORD="">'."\n";
				break;
		}

		$_weight = $this->_getOrderWeight($order);
		if(!$_weight)return '';
		$_weight = $this->_convertMeasurement($_weight, CONF_WEIGHT_UNIT, 'lbs');
		$enabledServices = $this->getEnabledServices($Type);

		foreach ($_Services as $_Service){

			if(!in_array($_Service['id'], $enabledServices))continue;
			$XMLQuery .= '<Package ID="'.$_Service['id'].'">'."\n";

			switch ($Type){
				case 'Domestic':
					$matches = null;
					$address['zip'] = trim($address['zip']);
					if(preg_match('/([\d]{1,5})/',$address['zip'],$matches)){
						$address['zip'] = $matches[1];
					}
					$XMLQuery .=
						'<Service>'.strtoupper($_Service['code']).'</Service>'."\n";
					if(in_array(strtoupper($_Service['code']),array('FIRST CLASS','FIRST CLASS HFP COMMERCIAL'))){
						//						$XMLQuery .=
						//							'<FirstClassMailType>'.$this->_getSettingValue('CONF_SHIPPING_USPS_FIRST_CLASS_TYPE').'</FirstClassMailType>'."\n";
						$XMLQuery .=
							'<FirstClassMailType>FLAT</FirstClassMailType>'."\n";					
					}
					$XMLQuery .=
						'<ZipOrigination>'.$this->_getSettingValue('CONF_SHIPPING_USPS_ZIPORIGINATION').'</ZipOrigination>'."\n".
						'<ZipDestination>'.$address['zip'].'</ZipDestination>'."\n".
						'<Pounds>'.floor($_weight).'</Pounds>'."\n".
						'<Ounces>'.round((16*($_weight-floor($_weight))),2).'</Ounces>'."\n".
					/**/					'<Container/>'."\n".
						'<Size>'.$this->_getSettingValue('CONF_SHIPPING_USPS_PACKAGESIZE').'</Size>'."\n".
						'<Machinable>'.$this->_getSettingValue('CONF_SHIPPING_USPS_MACHINABLE').'</Machinable>'."\n";
					break;
				case 'International':
					$_Country = cnGetCountryById($address['countryID']);
					$CountryList = $this->_getCountryList();
					$_Country = $CountryList[$_Country['country_iso_2']];
					$XMLQuery .=
						'<Pounds>'.floor($_weight).'</Pounds>'."\n".
						'<Ounces>'.round((16*($_weight-floor($_weight))),2).'</Ounces>'."\n".
						'<MailType>'.$_Service['name'].'</MailType>'."\n".
						'<Country>'.$_Country.'</Country>'."\n";
					break;
			}
			$XMLQuery .='</Package>'."\n";
		}

		switch ($Type){
			case 'Domestic':
				$XMLQuery .= '</RateV4Request>';
				break;
			case 'International':
				$XMLQuery .= '</IntlRateRequest>';
				break;
		}
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

		if(strpos($_XMLQuery, 'RateV2Request')!==false){
			$_Type = 'Domestic';
		}elseif(strpos($_XMLQuery, 'RateV4Request')!==false){
			$_Type = 'DomesticV4';
		}else{
			$_Type = 'International';
		}
			
		switch ($_Type){
			case 'Domestic':
				$url = 'http://production.shippingapis.com/shippingapi.dll?API=RateV2&XML='.urlencode($_XMLQuery);
				break;
			case 'DomesticV4':
				$url = 'http://production.shippingapis.com/ShippingAPI.dll?API=RateV4&XML='.urlencode($_XMLQuery);
				break;
			case 'International':
				$url = 'http://production.shippingapis.com/shippingapi.dll?API=IntlRate&XML='.urlencode($_XMLQuery);
				break;
		}

		if(!extension_loaded('curl')){
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: extension not loaded');
			return translate("err_curlinit");
		}

		@curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 20);
		@curl_setopt( $ch, CURLOPT_URL, $url );
		@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
		initCurlProxySettings($ch);

		$result = @curl_exec($ch);
		if ( curl_errno($ch) != 0){

			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_error($ch).' - '.curl_errno($ch)." {$url}");
			return translate("err_curlexec");
		}

		curl_close($ch);
		return $result;
	}

	function _parseXMLAnswer($_XMLAnswer){

		$Rates 	= array();
		$NoInfo 	= true;

		$objXML = new xml2Array();
		$parsedAnswer = $objXML->parse($_XMLAnswer);
		if(PEAR::isError($parsedAnswer)){
			return $parsedAnswer->getMessage();
		}
		@list($arrOutput) = $parsedAnswer;

		$Coef = 1;
		if($this->_defined('CONF_SHIPPING_USPS_USD_CURRENCY')){

			$_Currency = currGetCurrencyByID( $this->_getSettingValue('CONF_SHIPPING_USPS_USD_CURRENCY') );
			if($this->_getSettingValue('CONF_SHIPPING_USPS_USD_CURRENCY'))$Coef = $_Currency['currency_value'];
		}else{

			$Currencies = currGetAllCurrencies();
			foreach ($Currencies as $_Currency){
					
				if($_Currency['currency_iso_3'] == 'USD'){

					$Coef = $_Currency['currency_value'];
					break;
				}
			}
		}
		switch (strtoupper($arrOutput['name']))	{
			case 'RATEV2RESPONSE':
				foreach ($arrOutput['children'] as $_Package){
					foreach ($_Package['children'] as $_pChild){
						if($_pChild['name'] == 'POSTAGE'){
							$_t = array();
							foreach ($_pChild['children'] as $_tr){
								switch(strtoupper($_tr['name'])){
									case 'MAILSERVICE':
										$_t['name'] = $_tr['tagData'];
										break;
									case 'RATE':
										$_t['rate'] = $_tr['tagData']/$Coef;
										break;
								}
							}
							$NoInfo = false;
							$Rates[$_Package['attrs']['ID']][] = $_t;
						}elseif($_pChild['name'] == 'ERROR'){
							$_t = array();
							$_t['rate'] = 0;
							$_t['name'] = 'ERROR';
							foreach ($_pChild['children'] as $_tr){
								switch(strtoupper($_tr['name'])){
									case 'DESCRIPTION':
										$_t['name'] = ($_tr['tagData']?"{$_tr['tagData']} - ":'');
										$_t['name'] .=translate('sr_please_contact_seller');
										break;
								}
							}
							$NoInfo = true;
							$Rates[$_Package['attrs']['ID']][] = $_t;
						}
					}
				}
				break;
			case 'RATEV4RESPONSE':
				foreach ($arrOutput['children'] as $_Package)
				{
					$ID = $_Package['attrs']['ID'];
					foreach ($_Package['children'] as $_pChild)
					{
						switch(strtoupper($_pChild['name']))
						{
							case 'POSTAGE':
								$_t = array();
								foreach ($_pChild['children'] as $_tr) {
									switch(strtoupper($_tr['name'])){
										case 'MAILSERVICE':
											$_t['name'] = strip_tags(html_entity_decode($_tr['tagData']));
											break;
										case 'RATE':
											$_t['rate'] = $_tr['tagData']/$Coef;
											break;
									}
								}
								$NoInfo = false;
								$Rates[$ID][] = $_t;
								break;
										case 'ERROR':
											$_t = array();
											$_t['rate'] = 0;
											$_t['name'] = 'ERROR';
											foreach ($_pChild['children'] as $_tr){
												switch(strtoupper($_tr['name'])){
													case 'DESCRIPTION':
														$_t['name'] = ($_tr['tagData']?"{$_tr['tagData']} - ":'');
														$_t['name'] .=translate('sr_please_contact_seller');
														break;
													default:{
														break;
													}
												}
											}
											$NoInfo = true;
											$Rates[$ID][] = $_t;
											break;
													default:
														break;
						}
					}
				}
				break;
			case 'INTLRATERESPONSE':
				foreach ($arrOutput['children'] as $_Package){
					foreach ($_Package['children'] as $_pChild){
						if($_pChild['name'] == 'SERVICE'){

							$_t = array('name'=>array(), 'rate'=>0);
							foreach ($_pChild['children'] as $_tr){

								if(!isset($_tr['tagData']))
								$_tr['tagData'] = 0;
								switch (strtoupper($_tr['name'])){
									case 'MAILTYPE':
										$_t['name'][0] = $_tr['tagData'].' - ';
										break;
									case 'SVCCOMMITMENTS':
										$_t['name'][2] = '( '.$_tr['tagData'].' )';
										break;
									case 'SVCDESCRIPTION':
										$_t['name'][1] = $_tr['tagData'];
										break;
									case 'POSTAGE':
										$_t['rate'] = $_tr['tagData']/$Coef;
										break;
								}
							}
							ksort($_t['name']);
							$_t['name'] = strip_tags(html_entity_decode(implode(' ', $_t['name'])));
							$Rates[$_Package['attrs']['ID']][] = $_t;
							$NoInfo = false;
						}elseif($_pChild['name'] == 'ERROR'){
							$_t = array('name'=>array(), 'rate'=>0);
							foreach ($_pChild['children'] as $_tr){
								if(!isset($_tr['tagData']))$_tr['tagData'] = 'none';
								switch (strtoupper($_tr['name'])){
									case 'DESCRIPTION':$_t['name'] = $_tr['tagData'].' - '.translate('sr_please_contact_seller');break;

								}
							}
							$Rates[$_Package['attrs']['ID']][] = $_t;
							$NoInfo = true;
						}
					}
				}
				break;
			case 'ERROR':{
				$_t = array('name'=>array(), 'rate'=>0);
				foreach ($arrOutput['children'] as $_tr){
					if(!isset($_tr['tagData']))
					$_tr['tagData'] = 0;
					switch ($_tr['name']){
						case 'DESCRIPTION':
							$_t['name'] = strip_tags(html_entity_decode($_tr['tagData'])).' - '.translate('sr_please_contact_seller');
							break;
					}
				}
				$Rates[$_Package['attrs']['ID']][] = $_t;
				$NoInfo = true;
				break;
			}
		}

		if($NoInfo && $this->_getSettingValue('CONF_SHIPPING_USPS_ENABLE_ERROR_LOG')){

			$fp = fopen(DIR_TEMP."/usps_errors.log", "a");
			fwrite($fp, "\n".date("Y-m-d H:i:s")."\n".$_XMLAnswer);
			fclose($fp);
		}
		return $Rates;
	}

	function _getServicesByCountry($_CountryID){

		$Country = cnGetCountryById($_CountryID);
		if ($Country['country_iso_3'] == 'USA' ){

			return $this->getShippingServices('Domestic');
		}else {

			return $this->getShippingServices('International');
		}
		return array();
	}

	function _getCountryList() {
		$list = array('AF' => 'Afghanistan',
	                 'AL' => 'Albania',
	                 'DZ' => 'Algeria',
	                 'AD' => 'Andorra',
	                 'AO' => 'Angola',
	                 'AI' => 'Anguilla',
	                 'AG' => 'Antigua and Barbuda',
	                 'AR' => 'Argentina',
	                 'AM' => 'Armenia',
	                 'AW' => 'Aruba',
	                 'AU' => 'Australia',
	                 'AT' => 'Austria',
	                 'AZ' => 'Azerbaijan',
	                 'BS' => 'Bahamas',
	                 'BH' => 'Bahrain',
	                 'BD' => 'Bangladesh',
	                 'BB' => 'Barbados',
	                 'BY' => 'Belarus',
	                 'BE' => 'Belgium',
	                 'BZ' => 'Belize',
	                 'BJ' => 'Benin',
	                 'BM' => 'Bermuda',
	                 'BT' => 'Bhutan',
	                 'BO' => 'Bolivia',
	                 'BA' => 'Bosnia-Herzegovina',
	                 'BW' => 'Botswana',
	                 'BR' => 'Brazil',
	                 'VG' => 'British Virgin Islands',
	                 'BN' => 'Brunei Darussalam',
	                 'BG' => 'Bulgaria',
	                 'BF' => 'Burkina Faso',
	                 'MM' => 'Burma',
	                 'BI' => 'Burundi',
	                 'KH' => 'Cambodia',
	                 'CM' => 'Cameroon',
	                 'CA' => 'Canada',
	                 'CV' => 'Cape Verde',
	                 'KY' => 'Cayman Islands',
	                 'CF' => 'Central African Republic',
	                 'TD' => 'Chad',
	                 'CL' => 'Chile',
	                 'CN' => 'China',
	                 'CX' => 'Christmas Island (Australia)',
	                 'CC' => 'Cocos Island (Australia)',
	                 'CO' => 'Colombia',
	                 'KM' => 'Comoros',
	                 'CG' => 'Congo (Brazzaville),Republic of the',
	                 'ZR' => 'Congo, Democratic Republic of the',
	                 'CK' => 'Cook Islands (New Zealand)',
	                 'CR' => 'Costa Rica',
	                 'CI' => 'Cote d\'Ivoire (Ivory Coast)',
	                 'HR' => 'Croatia',
	                 'CU' => 'Cuba',
	                 'CY' => 'Cyprus',
	                 'CZ' => 'Czech Republic',
	                 'DK' => 'Denmark',
	                 'DJ' => 'Djibouti',
	                 'DM' => 'Dominica',
	                 'DO' => 'Dominican Republic',
	                 'TP' => 'East Timor (Indonesia)',
	                 'EC' => 'Ecuador',
	                 'EG' => 'Egypt',
	                 'SV' => 'El Salvador',
	                 'GQ' => 'Equatorial Guinea',
	                 'ER' => 'Eritrea',
	                 'EE' => 'Estonia',
	                 'ET' => 'Ethiopia',
	                 'FK' => 'Falkland Islands',
	                 'FO' => 'Faroe Islands',
	                 'FJ' => 'Fiji',
	                 'FI' => 'Finland',
	                 'FR' => 'France',
	                 'GF' => 'French Guiana',
	                 'PF' => 'French Polynesia',
	                 'GA' => 'Gabon',
	                 'GM' => 'Gambia',
	                 'GE' => 'Georgia, Republic of',
	                 'DE' => 'Germany',
	                 'GH' => 'Ghana',
	                 'GI' => 'Gibraltar',
	                 'GB' => 'Great Britain and Northern Ireland',
	                 'GR' => 'Greece',
	                 'GL' => 'Greenland',
	                 'GD' => 'Grenada',
	                 'GP' => 'Guadeloupe',
	                 'GT' => 'Guatemala',
	                 'GN' => 'Guinea',
	                 'GW' => 'Guinea-Bissau',
	                 'GY' => 'Guyana',
	                 'HT' => 'Haiti',
	                 'HN' => 'Honduras',
	                 'HK' => 'Hong Kong',
	                 'HU' => 'Hungary',
	                 'IS' => 'Iceland',
	                 'IN' => 'India',
	                 'ID' => 'Indonesia',
	                 'IR' => 'Iran',
	                 'IQ' => 'Iraq',
	                 'IE' => 'Ireland',
	                 'IL' => 'Israel',
	                 'IT' => 'Italy',
	                 'JM' => 'Jamaica',
	                 'JP' => 'Japan',
	                 'JO' => 'Jordan',
	                 'KZ' => 'Kazakhstan',
	                 'KE' => 'Kenya',
	                 'KI' => 'Kiribati',
	                 'KW' => 'Kuwait',
	                 'KG' => 'Kyrgyzstan',
	                 'LA' => 'Laos',
	                 'LV' => 'Latvia',
	                 'LB' => 'Lebanon',
	                 'LS' => 'Lesotho',
	                 'LR' => 'Liberia',
	                 'LY' => 'Libya',
	                 'LI' => 'Liechtenstein',
	                 'LT' => 'Lithuania',
	                 'LU' => 'Luxembourg',
	                 'MO' => 'Macao',
	                 'MK' => 'Macedonia, Republic of',
	                 'MG' => 'Madagascar',
	                 'MW' => 'Malawi',
	                 'MY' => 'Malaysia',
	                 'MV' => 'Maldives',
	                 'ML' => 'Mali',
	                 'MT' => 'Malta',
	                 'MQ' => 'Martinique',
	                 'MR' => 'Mauritania',
	                 'MU' => 'Mauritius',
	                 'YT' => 'Mayotte (France)',
	                 'MX' => 'Mexico',
	                 'MD' => 'Moldova',
	                 'MC' => 'Monaco (France)',
	                 'MN' => 'Mongolia',
	                 'MS' => 'Montserrat',
	                 'MA' => 'Morocco',
	                 'MZ' => 'Mozambique',
	                 'NA' => 'Namibia',
	                 'NR' => 'Nauru',
	                 'NP' => 'Nepal',
	                 'NL' => 'Netherlands',
	                 'AN' => 'Netherlands Antilles',
	                 'NC' => 'New Caledonia',
	                 'NZ' => 'New Zealand',
	                 'NI' => 'Nicaragua',
	                 'NE' => 'Niger',
	                 'NG' => 'Nigeria',
	                 'KP' => 'North Korea (Korea, Democratic People\'s Republic of)',
	                 'NO' => 'Norway',
	                 'OM' => 'Oman',
	                 'PK' => 'Pakistan',
	                 'PA' => 'Panama',
	                 'PG' => 'Papua New Guinea',
	                 'PY' => 'Paraguay',
	                 'PE' => 'Peru',
	                 'PH' => 'Philippines',
	                 'PN' => 'Pitcairn Island',
	                 'PL' => 'Poland',
	                 'PT' => 'Portugal',
	                 'QA' => 'Qatar',
	                 'RE' => 'Reunion',
	                 'RO' => 'Romania',
	                 'RU' => 'Russia',
	                 'RW' => 'Rwanda',
	                 'SH' => 'Saint Helena',
	                 'KN' => 'Saint Kitts (St. Christopher and Nevis)',
	                 'LC' => 'Saint Lucia',
	                 'PM' => 'Saint Pierre and Miquelon',
	                 'VC' => 'Saint Vincent and the Grenadines',
	                 'SM' => 'San Marino',
	                 'ST' => 'Sao Tome and Principe',
	                 'SA' => 'Saudi Arabia',
	                 'SN' => 'Senegal',
	                 'YU' => 'Serbia-Montenegro',
	                 'SC' => 'Seychelles',
	                 'SL' => 'Sierra Leone',
	                 'SG' => 'Singapore',
	                 'SK' => 'Slovak Republic',
	                 'SI' => 'Slovenia',
	                 'SB' => 'Solomon Islands',
	                 'SO' => 'Somalia',
	                 'ZA' => 'South Africa',
	                 'GS' => 'South Georgia (Falkland Islands)',
	                 'KR' => 'South Korea (Korea, Republic of)',
	                 'ES' => 'Spain',
	                 'LK' => 'Sri Lanka',
	                 'SD' => 'Sudan',
	                 'SR' => 'Suriname',
	                 'SZ' => 'Swaziland',
	                 'SE' => 'Sweden',
	                 'CH' => 'Switzerland',
	                 'SY' => 'Syrian Arab Republic',
	                 'TW' => 'Taiwan',
	                 'TJ' => 'Tajikistan',
	                 'TZ' => 'Tanzania',
	                 'TH' => 'Thailand',
	                 'TG' => 'Togo',
	                 'TK' => 'Tokelau (Union) Group (Western Samoa)',
	                 'TO' => 'Tonga',
	                 'TT' => 'Trinidad and Tobago',
	                 'TN' => 'Tunisia',
	                 'TR' => 'Turkey',
	                 'TM' => 'Turkmenistan',
	                 'TC' => 'Turks and Caicos Islands',
	                 'TV' => 'Tuvalu',
	                 'UG' => 'Uganda',
	                 'UA' => 'Ukraine',
	                 'AE' => 'United Arab Emirates',
	                 'UY' => 'Uruguay',
	                 'UZ' => 'Uzbekistan',
	                 'VU' => 'Vanuatu',
	                 'VA' => 'Vatican City',
	                 'VE' => 'Venezuela',
	                 'VN' => 'Vietnam',
	                 'WF' => 'Wallis and Futuna Islands',
	                 'WS' => 'Western Samoa',
	                 'YE' => 'Yemen',
	                 'ZM' => 'Zambia',
	                 'ZW' => 'Zimbabwe');

		return $list;
	}

	function settings_list(){

		if(!$this->_defined('CONF_SHIPPING_USPS_USD_CURRENCY')){

			$Ind = array_search($this->_getSettingRealName('CONF_SHIPPING_USPS_USD_CURRENCY'), $this->Settings);
			if( $Ind !== false){
					
				unset($this->Settings[$Ind]);
			}
		}
		return $this->Settings;
	}

	function _getRates(&$_Services,  $order, $address){
		$cachedRates = Storage::getInstance(__CLASS__);
		$address_ = md5(serialize($address));
		$order_ = md5(serialize($order));
		//TODO add adjustable cache settings
		if(true&&($address_ == $cachedRates->getData('address'))
		&&($order_ == $cachedRates->getData('order'))
		&&((time()+60)>$cachedRates->getData('timestamp'))
		&&($_Services ===$cachedRates->getData('services_'))){
			$result = $cachedRates->getData('result');
			$_Services = $cachedRates->getData('services');
		}else{
			$cachedRates->clean();
			$cachedRates->setData('services_',$_Services);
			$result = parent::_getRates($_Services,  $order, $address);
			//TODO: cache only success result
			if(count($_Services)){
				$sum = 0;
				foreach($_Services as $_Service){
					$sum += $_Service['rate'];
				}
				if($sum){
					$cachedRates->setData('address',$address_);
					$cachedRates->setData('order',$order_);
					$cachedRates->setData('result',$result);
					$cachedRates->setData('services',$_Services);
					$cachedRates->setData('timestamp',time());
				}
			}
		}

		if(!count($_Services)){
			$_Services[] = array(
						'id' => sprintf("%02d%02d", 0, 0),
						'name' => translate('sr_please_contact_seller'),
						'rate' => 0,
			);
		}
		return $result;
	}
}
?>