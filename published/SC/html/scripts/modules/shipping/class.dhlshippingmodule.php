<?php
/**
 * @connect_module_class_name DHLShippingModule
 *
 */

class DHLShippingModule extends ShippingRateCalculator{

	//var $language = 'eng';
	
	var $ShippingServices;
	var $LogFile = '';
	var $default_logo = 'http://www.webasyst.net/collections/design/shipping-icons/dhl.gif';
	
	function _writeLogMessage($_Param1, $_Param2){
	
		if($this->_getSettingValue('CONF_SHIPPING_DHL_ERROR_LOG'))
			ShippingRateCalculator::_writeLogMessage($_Param1, $_Param2);
	}
	
	function DHLShippingModule($_ModID = 0){
		
		$this->LogFile = DIR_TEMP.'/dhl_errors.log';
		parent::__construct($_ModID);
	}
	
	function _initVars(){
		
		parent::_initVars();
		
		$this->title = DHLSHIPPINGMODULE_TTL;
		$this->description = DHLSHIPPINGMODULE_DSCR;
		$this->sort_order = 0;
		$this->Settings = array(
			'CONF_SHIPPING_DHL_TEST_MODE',
			'CONF_SHIPPING_DHL_ERROR_LOG',
			'CONF_SHIPPING_DHL_LOGIN_ID',
			'CONF_SHIPPING_DHL_PASSWORD',
			'CONF_SHIPPING_DHL_ACCOUNT_NUMBER',
			'CONF_SHIPPING_DHL_SHIPPING_KEY',
			'CONF_SHIPPING_DHL_ISHIPPING_KEY',
			'CONF_SHIPPING_DHL_DUTIABLE',
			'CONF_SHIPPING_DHL_BILLING_PARTY',
			'CONF_SHIPPING_DHL_SHIPDATE',
			'CONF_SHIPPING_DHL_SHIPMENT_TYPE',
			'CONF_SHIPPING_DHL_DIMENSIONS',
			'CONF_SHIPPING_DHL_AP',
			'CONF_SHIPPING_DHL_AP_VALUE',
			'CONF_SHIPPING_DHL_COD',
			'CONF_SHIPPING_DHL_USD_CURRENCY',
			'CONF_SHIPPING_DHL_SERVICES',
			);
			
		$this->ShippingServices = array(
			1 => array(
				'id' => 1,
				'name' => 'Express',
				'xmlCode'=> 'E',
				'max weight' => 150,
				'number' => 0,
			),
			2 => array(
				'id' => 2,
				'name' => 'Express 10:30 AM',
				'xmlCode'=> 'E',
				'spCode' => '1030',
				'max weight' => 150,
				'number' => 1,
			),
			3 => array(
				'id' =>3,
				'name' => 'Express Saturday',
				'xmlCode'=> 'E',
				'spCode' => 'SAT',
				'max weight' => 150,
				'number' => 2,
			),
			4 => array(
				'id' => 4,
				'name' => 'Next Afternoon ',
				'xmlCode'=> 'N',
				'max weight' => 150,
				'number' => 3,
			),
			5 => array(
				'id' => 5,
				'name' => 'Ground',
				'xmlCode'=> 'G',
				'max weight' => 150,
				'number' => 4,
			),
			6 => array(
				'id' => 6,
				'name' => 'Second Day Service',
				'xmlCode'=> 'S',
				'max weight' => 150,
				'number' => 5,
			),
			7 => array(
				'id' => 7,
				'name' => 'International delivery',
				'xmlCode'=> 'IE',
				'number' => 7,
			),
			8 => array(
				'id' => 8,
				'name' => 'Hazardeous',
				'xmlCode'=> 'E',
				'spCode' => 'HAZ',
				'number' => 8,
			),
		);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_SHIPPING_DHL_DUTIABLE'] = array(
			'settings_value' 		=> 1, 
			'settings_title' 			=> DHL_CNF_DUTIABLE_TTL, 
			'settings_description' 	=> DHL_CNF_DUTIABLE_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_BILLING_PARTY'] = array(
			'settings_value' 		=> 'S', 
			'settings_title' 			=> DHL_CNF_BILLINGPARTY_TTL, 
			'settings_description' 	=> DHL_CNF_BILLINGPARTY_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(DHLShippingModule::_getBillingParties(),', 
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_AP'] = array(
			'settings_value' 		=> 0, 
			'settings_title' 			=> DHL_CNF_AP_TTL, 
			'settings_description' 	=> DHL_CNF_AP_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(DHLShippingModule::_getAPOptions(),', 
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_AP_VALUE'] = array(
			'settings_value' 		=> "0;0", 
			'settings_title' 			=> DHL_CNF_AP_VALUE_TTL, 
			'settings_description' 	=> DHL_CNF_AP_VALUE_DSCR, 
			'settings_html_function' 	=> 'DHLShippingModule::_setting_AP_VALUE('.$this->ModuleConfigID.',', 
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_COD'] = array(
			'settings_value' 		=> '-',
			'settings_title' 			=> DHL_CNF_COD_TTL,
			'settings_description' 	=> DHL_CNF_COD_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(DHLShippingModule::_getCODMethods(),',
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_USD_CURRENCY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_USD_CURRENCY_TTL, 
			'settings_description' 	=> DHL_CNF_USD_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_TEST_MODE'] = array(
			'settings_value' 		=> 1, 
			'settings_title' 			=> DHL_CNF_TEST_MODE_TTL, 
			'settings_description' 	=> DHL_CNF_TEST_MODE_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_ERROR_LOG'] = array(
			'settings_value' 		=> 1, 
			'settings_title' 			=> DHL_CNF_ERROR_LOG_TTL, 
			'settings_description' 	=> DHL_CNF_ERROR_LOG_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 0,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_PASSWORD_TTL, 
			'settings_description' 	=> DHL_CNF_PASSWORD_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 10,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_LOGIN_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_LOGIN_ID_TTL, 
			'settings_description' 	=> DHL_CNF_LOGIN_ID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 10,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_ACCOUNT_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_ACCOUNT_NUMBER_TTL, 
			'settings_description' 	=> DHL_CNF_ACCOUNT_NUMBER_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 10,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_SHIPPING_KEY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_SHIPPING_KEY_TTL, 
			'settings_description' 	=> DHL_CNF_SHIPPING_KEY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 20,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_ISHIPPING_KEY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_ISHIPPING_KEY_TTL, 
			'settings_description' 	=> DHL_CNF_ISHIPPING_KEY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,true,', 
			'sort_order' 			=> 20,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_SHIPDATE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_SHIPDATE_TTL, 
			'settings_description' 	=> DHL_CNF_SHIPDATE_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 30,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_SHIPMENT_TYPE'] = array(
			'settings_value' 		=> 'P', 
			'settings_title' 			=> DHL_CNF_SHIPMENT_TYPE_TTL, 
			'settings_description' 	=> DHL_CNF_SHIPMENT_TYPE_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(DHLShippingModule::_getShipmentType(),', 
			'sort_order' 			=> 40,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_DIMENSIONS'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_DIMENSIONS_TTL, 
			'settings_description' 	=> DHL_CNF_DIMENSIONS_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 50,
		);
		$this->SettingsFields['CONF_SHIPPING_DHL_SERVICES'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> DHL_CNF_SERVICES_TTL, 
			'settings_description' 	=> DHL_CNF_SERVICES_DSCR, 
			'settings_html_function' 	=> 'setting_CHECKBOX_LIST(DHLShippingModule::_getServices(),', 
			'sort_order' 			=> 6,
		);
	}
	
	function _prepareXMLQuery(&$_Services,  $order, $address){

		$XMLQuery = 
		'<?xml version="1.0"?>
		<eCommerce action="Request" version="1.1">
			<Requestor>
				<ID>'.$this->_getSettingValue('CONF_SHIPPING_DHL_LOGIN_ID').'</ID>
				<Password>'.$this->_getSettingValue('CONF_SHIPPING_DHL_PASSWORD').'</Password>
			</Requestor>';

		$OrderWeight = '';
		$OrderAmount = ceil($this->_convertCurrency($order['order_amount'], 0, $this->_getSettingValue('CONF_SHIPPING_DHL_USD_CURRENCY')));

		//additional protection config
		$ap = $this->my_getSettingValue('CONF_SHIPPING_DHL_AP_VALUE');
		$ap = explode(";", $ap);
		if (count($ap)>0 && (int)$ap[0]==1)
		{
			$ap_value = ceil($this->_convertCurrency((float)$ap[1], 0, $this->_getSettingValue('CONF_SHIPPING_DHL_USD_CURRENCY')));
		}
		else // unrecognized Additional Protection value format
		{
			$ap_value = $OrderAmount;
		}

		//letter/package shipment type
		if($this->_getSettingValue('CONF_SHIPPING_DHL_SHIPMENT_TYPE')!='L'){
		
			$OrderWeight = $this->_getOrderWeight($order);
			if(!$OrderWeight) return '';
			
			$OrderWeight = ceil($this->_convertMeasurement($OrderWeight, CONF_WEIGHT_UNIT, 'LBS'));

			//dimensions

			$dimensions = $this->my_getSettingValue('CONF_SHIPPING_DHL_DIMENSIONS');
			if (strlen(trim($dimensions)) > 0)
			{
				$dimensions = explode("x",$dimensions);
				for ($i=0;$i<3;$i++)
					if (!isset($dimensions[$i])) $dimensions[$i] = 0;
				$dl = (int)$dimensions[0];
				$dw = (int)$dimensions[1];
				$dh = (int)$dimensions[2];

				if ($dl && $dw && $dh) //all values are defined, and no values equal zero
					$dimensions = true;
				else
					$dimensions = false;
			}
			else //dimensions are not defined
			{
				$dimensions = false;
			}

		}
		else
			$dimensions = false;

		//address
		$DestCountry = cnGetCountryById($address['countryID']);
		$DestCountry = $DestCountry['country_iso_2'];
		
		$DestProvinceCode = znGetSingleZoneById($address['zoneID']);
		$DestProvinceCode = $DestProvinceCode['zone_code'];

		$International = $address['countryID']==CONF_COUNTRY?false:true;
		
		$_TC = count($_Services)-1;
		for(;$_TC>=0;$_TC--){
			
			if(!($this->_getSettingValue('CONF_SHIPPING_DHL_SERVICES')&pow(2, $this->ShippingServices[$_Services[$_TC]['id']]['number'])))continue;
			if($International && $_Services[$_TC]['id']!=7)continue;
			if(!$International && $_Services[$_TC]['id']==7)continue;
			if(0&&isset($this->ShippingServices[$_Services[$_TC]['id']]['max weight']) && $OrderWeight>$this->ShippingServices[$_Services[$_TC]['id']]['max weight']){
			
				$this->_writeLogMessage(0, str_replace('{%WEIGHT%}', $OrderWeight, DHL_TXTER_OVERWEIGHT));
				continue;
			}

			$shiptimestamp = time()+3600*24*$this->_getSettingValue('CONF_SHIPPING_DHL_SHIPDATE');
			$shipweekday = date("w", $shiptimestamp);

			if ($shipweekday == 0) //sunday
			{
				$shipdate = date("Y-m-d", $shiptimestamp+3600*24);
			}
/* [UNCOMMENT IF YOU WOULD LIKE TO ENABLE SATURDAY DELIVERY]			else if ($shipweekday == 6) //saturday
				{
					$shipdate = date("Y-m-d", $shiptimestamp+2*3600*24);
				}*/
				else //week day
				{
					$shipdate = date("Y-m-d", $shiptimestamp);
				}

			$XMLQuery .= '
				<'.($International?'IntlShipment action="RateEstimate" version="1.0"':'Shipment action="RateEstimate" version="1.0"').'>
					<ShippingCredentials>
						<ShippingKey>'.$this->_getSettingValue($International?'CONF_SHIPPING_DHL_ISHIPPING_KEY':'CONF_SHIPPING_DHL_SHIPPING_KEY').'</ShippingKey>
						<AccountNbr>'.$this->_getSettingValue('CONF_SHIPPING_DHL_ACCOUNT_NUMBER').'</AccountNbr>
					</ShippingCredentials>
					<ShipmentDetail>
						<ShipDate>'.$shipdate.'</ShipDate>
						<Service>
							<Code>'.$this->ShippingServices[$_Services[$_TC]['id']]['xmlCode'].'</Code>
						</Service>
						<ShipmentType>
							<Code>'.$this->_getSettingValue('CONF_SHIPPING_DHL_SHIPMENT_TYPE').'</Code>
						</ShipmentType>'.(isset($this->ShippingServices[$_Services[$_TC]['id']]['spCode'])?'
						<SpecialServices>
							<SpecialService>
								<Code>'.$this->ShippingServices[$_Services[$_TC]['id']]['spCode'].'</Code>
							</SpecialService>
						</SpecialServices>
						':'').($OrderWeight?'<Weight>'.$OrderWeight.'</Weight>
						':'').
						($dimensions?'
						<Dimensions>
							<Length>'.$dl.'</Length>
							<Width>'.$dw.'</Width>
							<Height>'.$dh.'</Height>
						</Dimensions>
						':'').
						(!$this->my_getSettingValue('CONF_SHIPPING_DHL_AP') || !strcmp($this->my_getSettingValue('CONF_SHIPPING_DHL_AP'),'NR')?'':'
						<AdditionalProtection>
							<Code>'.$this->my_getSettingValue('CONF_SHIPPING_DHL_AP').'</Code>
							<Value>'.$ap_value.'</Value>
						</AdditionalProtection>').
						($International?'
						<ContentDesc>Ordered products</ContentDesc>
						':'').
					'</ShipmentDetail>
					'.($International?
					'<Dutiable>
					 <DutiableFlag>'.($this->_getSettingValue('CONF_SHIPPING_DHL_DUTIABLE')?'Y':'N').'</DutiableFlag>
					 <CustomsValue>'.$OrderAmount.'</CustomsValue>
					</Dutiable> '
					:'').
					'<Billing>
						<Party>
							<Code>'.($this->my_getSettingValue('CONF_SHIPPING_DHL_BILLING_PARTY')?$this->my_getSettingValue('CONF_SHIPPING_DHL_BILLING_PARTY'):'S').'</Code>
						</Party>'.
					(!$this->my_getSettingValue('CONF_SHIPPING_DHL_COD') || !strcmp($this->my_getSettingValue('CONF_SHIPPING_DHL_COD'),'-')?'':'
						<CODPayment>
							<Code>'.$this->my_getSettingValue('CONF_SHIPPING_DHL_COD').'</Code>
							<Value>'.$OrderAmount.'</Value>
						</CODPayment>')
					.($International?
						'<DutyPaymentType>S</DutyPaymentType>':'').'
					</Billing>
					<Receiver>
						<Address>
							'.($International?'
							<Street><![CDATA['.($address['address']).']]></Street>
							<City><![CDATA['.($address['city']).']]></City>
							':'').'
							<State>'.$DestProvinceCode.'</State>
							<Country>'.$DestCountry.'</Country>
							<PostalCode>'.$address['zip'].'</PostalCode>
						</Address>
						'.($International?'
						':'').'
					</Receiver>
					<TransactionTrace>'.$_Services[$_TC]['id'].'</TransactionTrace>
				</'.($International?'IntlShipment':'Shipment').'>
				';
		}
		$XMLQuery .= '
		</eCommerce>';
if(0){
		header('Content-type: application/xml');
		print $XMLQuery;
		die;
}

		return $XMLQuery;
	}
	
	function _sendXMLQuery($_XMLQuery){

		if(!$_XMLQuery)return '';
	
		if ( !($ch = curl_init()) ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.translate("err_curlinit"));
			return translate("err_curlinit");
		}

		if ( curl_errno($ch) != 0 ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch).' '.curl_error($ch));
			return translate("err_curlinit");
		}

		if($this->_getSettingValue('CONF_SHIPPING_DHL_TEST_MODE'))
			$url = 'https://eCommerce.airborne.com/ApiLandingTest.asp';
		else
			$url = 'https://eCommerce.airborne.com/ApiLanding.asp';
			
		@curl_setopt($ch, CURLOPT_URL, $url );
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		@curl_setopt($ch, CURLOPT_HEADER, 0);
		@curl_setopt($ch, CURLOPT_POST, 1);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $_XMLQuery);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
		initCurlProxySettings($ch);

		$result = @curl_exec($ch);
		if ( curl_errno($ch) != 0){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch).' '.curl_error($ch)." {$url}");
			return translate("err_curlexec");
		}

		curl_close($ch);

if(0){
		header('Content-type: application/xml');
		print $result;
		die;
}

		return $result;
	}
	
	function _parseXMLAnswer( $_XMLResponse ){

		$Rates 		= array();
		if(!$_XMLResponse)return $Rates;
		$_t = new xml2Array();
		@list($XMLArray) = $_t->parse($_XMLResponse);
		
		if(!isset($XMLArray['children']))return $Rates;
		
		$_TC = count($XMLArray['children'])-1;
		for(;$_TC>=0; $_TC--){
		
			if($XMLArray['children'][$_TC]['name']=='FAULTS'){
			
				$_t = &$XMLArray['children'][$_TC]['children'];
				$_jj = count($_t)-1;
				for(;$_jj>=0;$_jj--){
				
					if($_t[$_jj]['name']!='FAULT')continue;
					$__ll = count($_t[$_jj]['children'])-1;
					$Fault = array(
						'CODE' => '',
						'DESCRIPTION' => '',
						'SOURCE' => '',
					);
					for(;$__ll>=0;$__ll--){
					
						$Fault[$_t[$_jj]['children'][$__ll]['name']] = isset($_t[$_jj]['children'][$__ll]['tagData'])?$_t[$_jj]['children'][$__ll]['tagData']:'';
					}
					$this->_writeLogMessage(0, "\r\n".'Code: '.$Fault['CODE']."\r\n".'Desc: '.$Fault['DESCRIPTION']);
				}
			}elseif($XMLArray['children'][$_TC]['name']=='SHIPMENT' || $XMLArray['children'][$_TC]['name']=='INTLSHIPMENT'){
			
				$__TC = count($XMLArray['children'][$_TC]['children'])-1;
				$_tRate = array(
					'id'=>'',
					'name'=>'',
					'rate'=>0,
				);
				$SpecialCode = '';
				for(;$__TC>=0; $__TC--){
				
					switch($XMLArray['children'][$_TC]['children'][$__TC]['name']){
					
						case 'SHIPMENTDETAIL':
						case 'ESTIMATEDETAIL':
					
							$_t = &$XMLArray['children'][$_TC]['children'][$__TC]['children'];
							$_jj = count($_t)-1;
							for(;$_jj>=0;$_jj--){
							
								switch($_t[$_jj]['name']){
									case 'RATEESTIMATE':
										$___TC = count($_t[$_jj]['children'])-1;
										for(;$___TC>=0; $___TC--){
										
											if($_t[$_jj]['children'][$___TC]['name']=='TOTALCHARGEESTIMATE'){
											
												$_tRate['rate'] = $this->_convertCurrency($_t[$_jj]['children'][$___TC]['tagData'], $this->_getSettingValue('CONF_SHIPPING_DHL_USD_CURRENCY'), 0);
											}
										}
										break;
								}
							}
							break;
						case 'TRANSACTIONTRACE':
						
							if(isset($XMLArray['children'][$_TC]['children'][$__TC]['tagData']))$_tRate['id'] = $XMLArray['children'][$_TC]['children'][$__TC]['tagData'];
							break;
						case 'FAULTS':
						
							$_t = &$XMLArray['children'][$_TC]['children'][$__TC]['children'];
							$_jj = count($_t)-1;
							for(;$_jj>=0;$_jj--){
							
								if($_t[$_jj]['name']!='FAULT')continue;
								$__ll = count($_t[$_jj]['children'])-1;
								$Fault = array(
									'CODE' => '',
									'DESC' => '',
									'SOURCE' => '',
								);
								for(;$__ll>=0;$__ll--){
								
									$Fault[$_t[$_jj]['children'][$__ll]['name']] = isset($_t[$_jj]['children'][$__ll]['tagData'])?$_t[$_jj]['children'][$__ll]['tagData']:'';
								}
								$this->_writeLogMessage(0, "\r\n".'Code: '.$Fault['CODE']."\r\n".'Desc: '.$Fault['DESC']."\r\n".'Source: '.$Fault['SOURCE']);
							}
							break;
					}
				}
				
				if(isset($this->ShippingServices[$_tRate['id']]['name']))
					$_tRate['name'] = $this->ShippingServices[$_tRate['id']]['name'];
				else
					$_tRate['name'] = $_tRate['id'];
					
				if($_tRate['rate']>0)$Rates[$_tRate['id']][] = $_tRate;
			}
		}
		return $Rates;
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

	function _getShipmentType(){
		return 'Package:P,Letter:L';
	}

	function _getBillingParties(){
		return 'Sender:S,Receiver:R';
	}

	function _getAPOptions(){
		return 'Not Required:NR,Asset Protection:AP';
	}

	function _getCODMethods(){
		return "n/a:-,Cashier's Check or Money Order:M,Personal or Company Check:P";
	}

	function _getServices(){
	
		$_t = new DHLShippingModule();
		$_Servs = $_t->getShippingServices();
		$_boxDescr = array();
		foreach ($_Servs as $_Serv){
			
			$_boxDescr[$_Serv['number']] = $_Serv['name'];
		}
		return $_boxDescr;
	}

	function my_getSettingValue($_SettingName){ //acts like _getSettingValue() except it checks whether constant name is defined or not (required for some settings value in this module)
		if (defined($_SettingName.(($this->ModuleConfigID&&!$this->SingleInstall)?'_'.$this->ModuleConfigID:'')))
			return $this->_getSettingValue($_SettingName);
		else
			return NULL;
	}

	function _setting_AP_VALUE($_ModuleID, $_SettingID){ //Additional Protection value definition
				
		$dhl_module = new DHLShippingModule($_ModuleID);

		if(isset($_POST['save'])){ //save AP setting value

			$ap_type = (int)$_POST["dhl_ap_value_type"];
			$ap_value = (float)$_POST["dhl_ap_value"];

			//save 2 db
			_setSettingOptionValueByID($_SettingID,$ap_type.';'.$ap_value);
		}

		$ap = $dhl_module->my_getSettingValue('CONF_SHIPPING_DHL_AP_VALUE');
		$ap = explode(";", $ap);
		if (count($ap)>0)
		{
			$ap_type = (int)$ap[0];
			$ap_value = (float)$ap[1];
		}
		else // unrecognized Additional Protection value format
		{
			$ap_type = 0;
			$ap_value = 0;
		}

		switch($ap_type){ // define which radio button is selected
			case 1:
				$radio0text = "";
				$radio1text = " checked";
			break;
			default:
				$radio0text = " checked";
				$radio1text = "";
			break;
		}
		
		$out = '<table>'.
				'<tr>'.
				'<td><input type="radio" name="dhl_ap_value_type" value="0"'.$radio0text.'></td>'.
				'<td colspan="2">'.DHL_CNF_AP_VALUE_TYPE0.'</td>'.
				'</tr>'.
				'<tr>'.
				'<td><input type="radio" name="dhl_ap_value_type" value="1"'.$radio1text.'></td>'.
				'<td>'.DHL_CNF_AP_VALUE_TYPE1.'</td>'.
				'<td><input type="text" name="dhl_ap_value" value="'.$ap_value.'"></td>'.
				'</tr>'.
			   '</table>';

		return $out;

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