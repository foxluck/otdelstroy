<?php
/**
 * @connect_module_class_name HSBC
 * @package DynamicModules
 * @subpackage Payment
 */
	class HSBC extends PaymentModule {
	
		var $type = PAYMTD_TYPE_CC;
		var $language = 'eng';
		var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/hsbc.gif';
		
		function _initVars(){
			
			$this->title = HSBC_TTL;
			$this->description = HSBC_DSCR;
			$this->sort_order = 1;
			
			$this->Settings = array( 
					'CONF_PMNT_HSBC_MODE',
					'CONF_PMNT_HSBC_STOREFRONTID',
					'CONF_PMNT_HSBC_USERID',
					'CONF_PMNT_HSBC_SHAREDSECRET',
					'CONF_PMNT_HSBC_TRANTYPE',
					'CONF_PMNT_HSBC_TRANSCURR',
					'CONF_PMNT_HSBC_CURCODE',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_PMNT_HSBC_MODE'] = array(
				'settings_value' 		=> 'T', 
				'settings_title' 			=> HSBC_CFG_MODE_TTL, 
				'settings_description' 	=> HSBC_CFG_MODE_DSCR, 
				'settings_html_function' 	=> 'setting_RADIOGROUP(HSBC_TXT_PMODE.":P,".HSBC_TXT_TMODE.":T",', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PMNT_HSBC_STOREFRONTID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> HSBC_CFG_STOREFRONTID_TTL,
				'settings_description' 	=> HSBC_CFG_STOREFRONTID_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PMNT_HSBC_USERID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> HSBC_CFG_USERID_TTL, 
				'settings_description' 	=> HSBC_CFG_USERID_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PMNT_HSBC_SHAREDSECRET'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> HSBC_CFG_SHAREDSECRET_TTL,
				'settings_description' 	=> HSBC_CFG_SHAREDSECRET_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PMNT_HSBC_TRANTYPE'] = array(
				'settings_value' 		=> 'Auth', 
				'settings_title' 			=> HSBC_CFG_TRANTYPE_TTL, 
				'settings_description' 	=> HSBC_CFG_TRANTYPE_DSCR, 
				'settings_html_function' 	=> 'setting_RADIOGROUP(HSBC_TXT_TRANTYPE_AUTH.":Auth,".HSBC_TXT_TRANTYPE_CAPTURE.":Capture",', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PMNT_HSBC_TRANSCURR'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> HSBC_CFG_TRANSCURR_TTL, 
				'settings_description' 	=> HSBC_CFG_TRANSCURR_DSCR, 
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PMNT_HSBC_CURCODE'] = array(
				'settings_value' 		=> '826', 
				'settings_title' 			=> HSBC_CFG_CURCODE_TTL, 
				'settings_description' 	=> HSBC_CFG_CURCODE_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
		}
	
		function after_processing_html( $orderID ){
		
			if (isset($_POST['CpiResultsCode'])) {
				
				return $this->_responseHandler();
			}else{
				
				return $this->_prepareForm($orderID);
			}
		}

		function _getCountryCodes(){
			
			$country_codes=array
			(
			'AF'=>'004',
			'AL'=>'008',
			'DZ'=>'012',
			'AS'=>'016',
			'AD'=>'020',
			'AO'=>'024',
			'AI'=>'660',
			'AQ'=>'010',
			'AG'=>'028',
			'AR'=>'032',
			'AM'=>'051',
			'AW'=>'533',
			'AU'=>'036',
			'AT'=>'040',
			'AZ'=>'031',
			'BS'=>'044',
			'BH'=>'048',
			'BD'=>'050',
			'BB'=>'052',
			'BY'=>'112',
			'BE'=>'056',
			'BZ'=>'084',
			'BJ'=>'204',
			'BM'=>'060',
			'BT'=>'064',
			'BO'=>'068',
			'BA'=>'070',
			'BW'=>'072',
			'BV'=>'074',
			'BR'=>'076',
			'IO'=>'086',
			'BN'=>'096',
			'BG'=>'100',
			'BF'=>'854',
			'BI'=>'108',
			'KH'=>'116',
			'CM'=>'120',
			'CA'=>'124',
			'CV'=>'132',
			'KY'=>'136',
			'CF'=>'140',
			'TD'=>'148',
			'CL'=>'152',
			'CN'=>'156',
			'CX'=>'162',
			'CC'=>'166',
			'CO'=>'170',
			'KM'=>'174',
			'CG'=>'178',
			'CK'=>'184',
			'CR'=>'188',
			'CI'=>'384',
			'HR'=>'191',
			'CU'=>'192',
			'CY'=>'196',
			'CZ'=>'203',
			'DK'=>'208',
			'DJ'=>'262',
			'DM'=>'212',
			'DO'=>'214',
			'TP'=>'626',
			'EC'=>'218',
			'EG'=>'818',
			'SV'=>'222',
			'GQ'=>'226',
			'ER'=>'232',
			'EE'=>'233',
			'ET'=>'231',
			'FK'=>'238',
			'FO'=>'234',
			'FJ'=>'242',
			'FI'=>'246',
			'FR'=>'250',
			'GF'=>'254',
			'PF'=>'258',
			'TF'=>'260',
			'GA'=>'266',
			'GM'=>'270',
			'GE'=>'268',
			'DE'=>'276',
			'GH'=>'288',
			'GI'=>'292',
			'GR'=>'300',
			'GL'=>'304',
			'GD'=>'308',
			'GP'=>'312',
			'GU'=>'316',
			'GT'=>'320',
			'GN'=>'324',
			'GW'=>'624',
			'GY'=>'328',
			'HT'=>'332',
			'HM'=>'334',
			'HN'=>'340',
			'HK'=>'344',
			'HU'=>'348',
			'IS'=>'352',
			'IN'=>'356',
			'ID'=>'360',
			'IR'=>'364',
			'IQ'=>'368',
			'IE'=>'372',
			'IL'=>'376',
			'IT'=>'380',
			'JM'=>'388',
			'JP'=>'392',
			'JO'=>'400',
			'KZ'=>'398',
			'KE'=>'404',
			'KI'=>'296',
			'KP'=>'408',
			'KW'=>'414',
			'KG'=>'417',
			'LA'=>'418',
			'LV'=>'428',
			'LB'=>'422',
			'LS'=>'426',
			'LR'=>'430',
			'LY'=>'434',
			'LI'=>'438',
			'LT'=>'440',
			'LU'=>'442',
			'MO'=>'446',
			'MK'=>'807',
			'MG'=>'450',
			'MW'=>'454',
			'MY'=>'458',
			'MV'=>'462',
			'ML'=>'466',
			'MT'=>'470',
			'MH'=>'584',
			'MQ'=>'474',
			'MR'=>'478',
			'MU'=>'480',
			'YT'=>'175',
			'MX'=>'484',
			'MD'=>'498',
			'MC'=>'492',
			'MN'=>'496',
			'MS'=>'500',
			'MA'=>'504',
			'MZ'=>'508',
			'MM'=>'104',
			'NA'=>'516',
			'NR'=>'520',
			'NP'=>'524',
			'AN'=>'530',
			'NL'=>'528',
			'NC'=>'540',
			'NZ'=>'554',
			'NI'=>'558',
			'NE'=>'562',
			'NG'=>'566',
			'NU'=>'570',
			'NF'=>'574',
			'MP'=>'580',
			'NO'=>'578',
			'OM'=>'512',
			'PK'=>'586',
			'PW'=>'585',
			'PA'=>'591',
			'PG'=>'598',
			'PY'=>'600',
			'PE'=>'604',
			'PH'=>'608',
			'PN'=>'612',
			'PL'=>'616',
			'PT'=>'620',
			'PR'=>'630',
			'QA'=>'634',
			'RE'=>'638',
			'RO'=>'642',
			'RU'=>'643',
			'RW'=>'646',
			'WS'=>'882',
			'SM'=>'674',
			'ST'=>'678',
			'SA'=>'682',
			'SN'=>'686',
			'SC'=>'690',
			'SL'=>'694',
			'SG'=>'702',
			'SK'=>'703',
			'SI'=>'705',
			'SB'=>'090',
			'SO'=>'706',
			'ZA'=>'710',
			'GS'=>'239',
			'ES'=>'724',
			'LK'=>'144',
			'SH'=>'654',
			'KN'=>'659',
			'LC'=>'662',
			'PM'=>'666',
			'VC'=>'670',
			'SD'=>'736',
			'SR'=>'740',
			'SJ'=>'744',
			'SZ'=>'748',
			'SE'=>'752',
			'CH'=>'756',
			'SY'=>'760',
			'TW'=>'158',
			'TJ'=>'762',
			'TZ'=>'834',
			'TH'=>'764',
			'TG'=>'768',
			'TK'=>'772',
			'TO'=>'776',
			'TT'=>'780',
			'TN'=>'788',
			'TR'=>'792',
			'TM'=>'795',
			'TC'=>'796',
			'TV'=>'798',
			'VI'=>'850',
			'UG'=>'800',
			'UA'=>'804',
			'AE'=>'784',
			'GB'=>'826',
			'UM'=>'581',
			'US'=>'840',
			'UY'=>'858',
			'UZ'=>'860',
			'VU'=>'548',
			'VA'=>'336',
			'VE'=>'862',
			'VN'=>'704',
			'WF'=>'876',
			'EH'=>'732',
			'YE'=>'887',
			'YU'=>'891',
			'ZM'=>'894',
			'ZW'=>'716'
			);
			
			return $country_codes;
		}
		
		/**
		 * Generate a hash to perform the POST or to check received parameters
		 *
		 * @param array $fields
		 * @param mixed $output: will field by output from exec
		 * @return string
		 */
    function _getHash($fields, &$output){
    	
			$cmd = '';
			reset($fields);
			while(list($k,$v)=each($fields)){
			        $cmd.=" \"$v\" ";
			}
			
			/**
			 * Path where the TestHash.e executable is located
			 */
			$path = realpath(dirname(__FILE__).'/hsbc');
			putenv("LD_LIBRARY_PATH=$path");
			
			if(isWindows()){
				$cmd="$path\TestHash.exe \"".$this->_getSettingValue('CONF_PMNT_HSBC_SHAREDSECRET')."\" $cmd 2>&1";
			}else{
				// Linux
				//Executes the TestHash to get the hash
				$cmd="$path/TestHash.e \"".$this->_getSettingValue('CONF_PMNT_HSBC_SHAREDSECRET')."\" $cmd 2>&1";
			}
			$ret=exec($cmd, $output);
			$ret=split(':',$ret);
			//Returns the hash
			$hash=trim($ret[1]);      
			return($hash);
    }

		function _prepareForm($orderID){
			
			$res = '';

			$order = ordGetOrder( $orderID );
			$order_amount = PaymentModule::_convertCurrency($order['order_amount'],0, $this->_getSettingValue('CONF_PMNT_HSBC_TRANSCURR'));
			$order_amount = RoundFloatValue($order_amount);

			$country_codes = $this->_getCountryCodes();

			$countries = cnGetCountries(array('offset'=>0,'CountRowOnPage'=>1000000), $count_row);

			$billing_countrycode = null;
			$shipping_countrycode = null;
			foreach ($countries as $country){
				
				if(is_null($billing_countrycode) && $country['country_name']==$order['billing_country']){
					
					$billing_countrycode = $country_codes[$country['country_iso_2']];
				}
				if(is_null($shipping_countrycode) && $country['country_name']==$order['shipping_country']){
					
					$shipping_countrycode = $country_codes[$country['country_iso_2']];
				}
			}
			$billing_address = $this->_makeValidAddres($order['billing_address']);
			$shipping_address = $this->_makeValidAddres($order['shipping_address']);
			$post_1=array(
				'CpiDirectResultUrl'=>'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				'CpiReturnUrl'=>'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				
				'OrderDesc' => substr(CONF_SHOP_NAME.' order',0,54),
				'OrderId' => $orderID,
				'PurchaseAmount' => $order_amount*100,
				'PurchaseCurrency' => $this->_getSettingValue('CONF_PMNT_HSBC_CURCODE'),
				'StorefrontId' => $this->_getSettingValue('CONF_PMNT_HSBC_STOREFRONTID'),
				'TimeStamp' => time().'000',
				'TransactionType' => $this->_getSettingValue('CONF_PMNT_HSBC_TRANTYPE'),
				'Mode' => $this->_getSettingValue('CONF_PMNT_HSBC_MODE'),
				
				'BillingAddress1' => $billing_address[0],
				'BillingAddress2' => $billing_address[1],
				'BillingCity' => substr($order['billing_city'],0,25),				   
				'BillingCountry'=>$billing_countrycode,
				'BillingCounty'=>substr($order['billing_state'],0,25),							   
				'BillingFirstName'=>substr($order['billing_firstname'],0,32),
				'BillingLastName'=>substr($order['billing_lastname'],0,32),
				'BillingPostal'=>substr($order['billing_zip'],0,20),
				'ShopperEmail'=>substr($order['customer_email'],0,64),
				'ShippingAddress1'=>$billing_address[0],							   
				'ShippingAddress2'=>$billing_address[1],
				'ShippingCity'=>substr($order['shipping_city'],0,25),							   
				'ShippingCountry'=>$shipping_countrycode,							   
				'ShippingCounty'=>substr($order['shipping_state'],0,25),							   
				'ShippingFirstName'=>substr($order['shipping_firstname'],0,32),
				'ShippingLastName'=>substr($order['shipping_lastname'],0,32),
				'ShippingPostal'=>substr($order['shipping_zip'],0,20)

			);
			
      $post_1['OrderHash'] = $this->_getHash($post_1,$output);
      $output = implode('', $output);
      $output = $output?(' ('.$output.')'):'';

      $hidden_fields_html = '';
      reset($post_1);
      
      while(list($k,$v)=each($post_1)){
      	
				$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
      }
       
      if (strlen($post_1['OrderHash'])!=28) {
      	
      	$res = '<div style="color:red;text-align:center;font-weight:bold;">Failed executing HSBC software'.$output.'</div>';
      }else{
      	
				$res = '
					<form method="post" action="https://www.cpi.hsbc.com/servlet" style="text-align:center;" accept-charset="iso-8859-1">
						'.$hidden_fields_html.'
						<input type="submit" value="'.xHtmlSpecialChars(HSBC_SUBMIT_BTN).'" />
					</form>
					';
      }
			
			return $res;
		}
	
		function _responseHandler(){
			
			$ResponseCodes = array(
				0 => 'The transaction was approved.',
				1 => 'The user cancelled the transaction.',
				2 => 'The processor declined the transaction for an unknown reason.',
				3 => 'The transaction was declined because of a problem with the card. For example, an invalid card number or expiration date was specified.',
				4 => 'The processor did not return a response.',
				5 => 'The amount specified in the transaction was either too high or too low for the processor.',
				6 => 'The specified currency is not supported by either the processor or the card.',
				7 => 'The order is invalid because the order ID is a duplicate.',
				8 => 'The transaction was rejected by FraudShield.',
				9 => 'The transaction was placed in Review state by FraudShield (see note 1 below).',
				10 => 'The transaction failed because of invalid input data.',
				11 => 'The transaction failed because the CPI was configured incorrectly.',
				12 => 'The transaction failed because the Storefront was configured incorrectly.',
				13 => 'The connection timed out.',
				14 => 'The transaction failed because the cardholder’s browser refused a cookie.',
				15 => 'The customer’s browser does not support 128-bit encryption.',
				16 => 'The CPI cannot communicate with the Payment Engine.'
				);
				
				return '<div style="text-align:center;">'.$ResponseCodes[$_POST['CpiResultsCode']].'</div>';
		}
		
		function _makeValidAddres($value,$length = 60)
		{
			$value = preg_replace('/([\r\n]+|(&nbsp;))/',' ',trim($value));
			$value = preg_replace('/([\s]{2,})/',' ',$value);
			do{
				$separator = md5(time());
			}while(strpos($separator,$value)!==false);
			$value = wordwrap($value,$length,$separator);
			return array_map('trim',array_slice(array_merge(explode($separator,$value),array('','')),0,2));
		}
	}
?>