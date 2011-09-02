<?php
/**
 * @connect_module_class_name PayOnlineSystem
 * @package DynamicModules
 * @subpackage Payment
 * @author WebAsyst Team
 * @version 1.6
 * @link http://www.payonlinesystem.ru/
 *
 */
class PayOnlineSystem extends PaymentModule
{
	var $type = PAYMTD_TYPE_ONLINE;
	var $default_logo = 'http://webasyst.net/images/shop/modules/payonline.gif';
	var $language = 'rus';

	private $url = 'https://secure.payonlinesystem.com/%s/payment/%s';
	function _initVars(){
			
		parent::_initVars();
		$this->title = POS_TTL;
		$this->description = POS_DSCR;
		$this->sort_order = 1;
			
		$this->Settings = array(
			'CONF_POS_MERCHANT_ID',
			'CONF_POS_SECRET_KEY',
			'CONF_POS_TRANSACTION_CURRENCY',
			'CONF_POS_SHOP_CURRENCY',
			'CONF_POS_GATEWAY',
			'CONF_POS_VALID_UNTIL',
			'CONF_POS_CUSTOMER_LANG',
//			'CONF_POS_DEBUGMODE',
			'CONF_POS_ORDERSTATUS',
		);

		$this->DebugMode = LOGMODE_DEBUG;
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_POS_MERCHANT_ID'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> POS_CFG_MERCHANT_ID_TTL,
			'settings_description' 		=> POS_CFG_MERCHANT_ID_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_POS_SECRET_KEY'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> POS_CFG_SECRET_KEY_TTL,
			'settings_description' 		=> POS_CFG_SECRET_KEY_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_POS_TRANSACTION_CURRENCY'] = array(
			'settings_value'	 		=> '',
			'settings_title' 			=> POS_CFG_TRANSACTION_CURRENCY_TTL,
			'settings_description'	 	=> POS_CFG_TRANSACTION_CURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PayOnlineSystem::_getTransactionCurrencies(),',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_POS_SHOP_CURRENCY'] = array(
			'settings_value'	 		=> '',
			'settings_title' 			=> POS_CFG_SHOP_CURRENCY_TTL,
			'settings_description'	 	=> POS_CFG_SHOP_CURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_POS_GATEWAY'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> POS_CFG_GATEWAY_TTL,
			'settings_description' 	=> POS_CFG_GATEWAY_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PayOnlineSystem::_getTransactionGateway(),',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_POS_VALID_UNTIL'] = array(
			'settings_value' 			=> 0,
			'settings_title' 			=> POS_CFG_VALID_UNTIL_TTL,
			'settings_description' 		=> POS_CFG_VALID_UNTIL_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(2,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_POS_CUSTOMER_LANG'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> POS_CFG_CUSTOMER_LANG_TTL,
			'settings_description' 		=> POS_CFG_CUSTOMER_LANG_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PayOnlineSystem::_getLanguages(),',
			'sort_order' 				=> 1,
		);
/*
		$this->SettingsFields['CONF_POS_DEBUGMODE'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> POS_CFG_DEBUGMODE_TTL,
			'settings_description'	 	=> POS_CFG_DEBUGMODE_DSCR,
			'settings_html_function' 	=> 'setting_CHECK_BOX(',
			'sort_order' 				=> 1,
		);
		*/
		$this->SettingsFields['CONF_POS_ORDERSTATUS'] = array(
			'settings_value' 			=> '-1',
			'settings_title' 			=> POS_CFG_ORDERSTATUS_TTL,
			'settings_description' 		=> POS_CFG_ORDERSTATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 				=> 1,
		);
	}

	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array (
			'settings_title'=>POS_CUST_RESULT_URL_TTL,
			'settings_description'=>POS_CUST_RESULT_URL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getDirectTransactionResultURL('success',array(__FILE__)))
			.'">',
			);
			$customProperties[] = array (
			'settings_title'=>POS_CUST_FAIL_URL_TTL,
			'settings_description'=>POS_CUST_FAIL_URL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getDirectTransactionResultURL('failure',array(__FILE__)))
			.'">',
			);
			return $customProperties;
	}

	function after_processing_html( $orderID, $active = true )
	{

		$res = '';
			
		$order = ordGetOrder( $orderID );

		$order_amount = PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_POS_SHOP_CURRENCY'));

		$form_fields = array();

		$form_fields['MerchantId'] 		= $this->_getSettingValue('CONF_POS_MERCHANT_ID');
		$form_fields['OrderId'] 		= $orderID;
		$form_fields['Amount'] 			= sprintf('%0.2f',$order_amount);
		$form_fields['Currency']	 	= $this->_getSettingValue('CONF_POS_TRANSACTION_CURRENCY');
		if($valid_until = $this->_getSettingValue('CONF_POS_VALID_UNTIL')){
			$form_fields['ValidUntil'] 	= date('Y-m-d H:i:s',strtotime($order['order_time'])+$valid_until*3600);
		}
		#
		$hash = '';
		foreach($form_fields as $field=>$value){
			$hash .= "{$field}={$value}&";
		}
		$hash .= 'PrivateSecurityKey='.$this->_getSettingValue('CONF_POS_SECRET_KEY');
		$form_fields['SecurityKey'] 	= md5($hash);
		$form_fields['ReturnURL'] 		= (preg_replace('@^https?://@','',$this->getTransactionResultURL('success')));
		$form_fields['FailURL'] 		= (preg_replace('@^https?://@','',$this->getTransactionResultURL('failure')));


		$form_fields['is_callback'] 	= '1';

		$lang = $this->_getSettingValue('CONF_POS_CUSTOMER_LANG');
		$language_iso2 = $this->_getSettingValue('CONF_POS_CUSTOMER_LANG');
		if(!$language_iso2){
			$language = LanguagesManager::getCurrentLanguage();
			/*@var $language Language*/
			$supported_languages = self::_getLanguages();
			foreach($supported_languages as $variant){
				if(strtolower($language->iso2)==$variant['value']){
					$language_iso2 = strtolower($language->iso2);
					break;
				}
			}
		}

		$form_url = sprintf($this->url,$language_iso2,$this->_getSettingValue('CONF_POS_GATEWAY'));

		$this->_log(LOGTYPE_DEBUG,'Form: '.var_export($form_fields,true)."\n hash: ".$hash);

		$form = <<<HTML
<form id="pos_form" method="POST" action="{$form_url}" style="text-align:center;">

HTML;
		foreach($form_fields as $field=>$value){
			if(!$value)continue;
			$value = xHtmlSpecialChars($value);
			$form .= <<<HTML
	<input type="hidden" value="{$value}" name="{$field}">
	
HTML;
		}
		$form .= "\n\t".'<input type="submit" value="'.xHtmlSpecialChars(POS_TXT_PROCESS).'" />'."\n";
		$form .= "\n</form>\n";

		if($active){
			$form .= <<<JS
<script type="text/javascript">
<!--
setTimeout('document.getElementById("pos_form").submit();',2000);
//-->
</script>
JS;
		}

		return $form;
	}

	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend')
	{
		if($source != 'handler'){
			if($code = $this->_getRequestData('ErrorCode')){
				switch($code){
					case 1:{
						$message .= '<p>'.POS_TXT_ERCODE1.'</p>';
						break;
					}
					case 2:{
						$message .= '<p>'.POS_TXT_ERCODE2.'</p>';
						break;
					}
					case 3:{
						$message .= '<p>'.POS_TXT_ERCODE3.'</p>';
						break;
					}
				}
			}
			return parent::transactionResultHandler($transaction_result,$message,$source);
		}
		//DEBUG:
		$log_file = DIR_TEMP.'/'.__CLASS__.'debug.log';
		if(false&&($flog = fopen($log_file,'a'))){
			fwrite($flog,"\n==========\n");
			$args = func_get_args();
			fwrite($flog,"\nArgs:\n".var_export($args,true));
			fwrite($flog,"\nGET:\n".var_export($_GET,true));
			fwrite($flog,"\nPOST:\n".var_export($_POST,true));
		}

		$orderID = intval($this->_getRequestData('OrderId',0));
		$log = '';
		if($orderID &&($order = _getOrderById($orderID))){
			if($this->validateResultKey(array(__FILE__))){
				//check callback sign
				$sharedSecret = $this->_getSettingValue('CONF_POS_SECRET_KEY');
				if($sharedSecret){
					//TODO use real order amount
					$nOutSum = $this->_getRequestData('OutSum',0);
					/**
					 * DateTime=2008-12-31 23:59:59&TransactionID=1234567&OrderId=56789&Amount=9.99&Currency=USD&PrivateSecurityKey=3844908d-4c2a-42e1-9be0-91bb5d068d22
					 */
					$fields = array('DateTime','TransactionID','OrderId','Amount','Currency');
					$string = '';
					foreach($fields as $field){
						$string .= $field.'='.$this->_getRequestData($field).'&';
					}
					$string .= 'PrivateSecurityKey='.$sharedSecret;
					$signature = strtolower(md5($string));
					$server_signature = strtolower($this->_getRequestData('SecurityKey',''));
					if($server_signature!=$signature){
						$transaction_result = 'failure';
						$log .= ' invalid post data sign';
						if($flog){
							fwrite($flog,"\nsign:\n".var_export(array('string'=>$string,'$server_sign'=>$server_sign,'$sign'=>$sign),true));
						}
					}
				}
				if($transaction_result == 'success'){
					//change order status on setted at module settings
					$statusID = $this->_getSettingValue('CONF_POS_ORDERSTATUS');
					$transaction_id = intval($this->_getRequestData('TransactionID',0));
					$details = '';
					$fields = array();
					$fields['TransactionID'] = POS_TXT_ITRANSCATIONID;
					switch($provider = $this->_getRequestData('Provider')){
						case 'Card':{
							$fields['CardHolder']	 = POS_TXT_ICARDHOLDER;
							$fields['CardNumber']	 = POS_TXT_ICARDNUMBER;
							$fields['Country']		 = POS_TXT_ICOUNTRY;
							$fields['BinCountry']	 = POS_TXT_IBINCOUNTRY;
							$fields['City']			 = POS_TXT_ICITY;
							$fields['Address']		 = POS_TXT_IADDRESS;
							break;
						}
						case 'Qiwi':{
							$fields['Phone']		 = POS_TXT_IPHONE;
							break;
						}
						case 'WebMoney':{

							$fields['WmTranId']		 = POS_TXT_IWMTRANID;
							$fields['WmInvId']		 = POS_TXT_IWMINVID;
							$fields['WmId']			 = POS_TXT_IWMID;
							$fields['WmPurse']		 = POS_TXT_IWMPURSE;
							break;
						}
						default:{
							$details .= "Unknown payment provider {$provider}";
							break;
						}

					}
					$details .= POS_TXT_IAMOUNT.': '.$this->_getRequestData('Amount').' '.$this->_getRequestData('Currency');
					$fields['IpAddress'] = POS_TXT_IIPADDRESS;
					$fields['IpCountry'] = POS_TXT_IIPCOUNTRY;
					foreach($fields as $field=>$description){
						if($value = $this->_getRequestData($field)){
							$details .= "\n{$description}: {$value}";
						}
					}


					if($statusID>0){
						$comment = POS_TXT_ISTATUS_CHANGED;
						$log .= 'Update order status';
						ostSetOrderStatusToOrder( $orderID, $statusID,$comment."\n".$details,0);
					}else{

						$comment = POS_TXT_ICOMMENT_ADDED;
						$log .= 'Update order comments';
						ostSetOrderStatusToOrder( $orderID, $order['statusID'],$comment."\n".$details,0,true);
					}

				}elseif($transaction_result == 'failure'){
					//log at order processing history
					$statusID = 3;
					$log .= 'cancel order';
					ostSetOrderStatusToOrder( $orderID, $order['statusID'],POS_TXT_DECLINED,0,true);
				}else{
					$log .= 'unknown transaction_result: '.$transaction_result;
				}
			}else{
				$transaction_result = 'failure';
				$statusID = 3;
				$log .= 'invalid secure key';
				//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
			}
		}else{
			$log = "Order with id {$orderID} not exists";
			$transaction_result = 'failure';
		}

		if($flog){
			fwrite($flog,"\nLog:\n".$log);
			fclose($flog);
		}
		return parent::transactionResultHandler($transaction_result,$message.$log,$source);
	}

	public static function _getTransactionCurrencies()
	{
		$currencies = array();
		$currencies[] = array('title'=>POS_TXT_CURRUB,'value'=>'RUB');
		$currencies[] = array('title'=>POS_TXT_CURUSD,'value'=>'USD');
		$currencies[] = array('title'=>POS_TXT_CUREUR,'value'=>'EUR');
		return $currencies;
	}


	public static function _getTransactionGateway()
	{
		$gateways = array();
		$gateways[] = array('title'=>POS_TXT_GATEWAYCARD,	'value'=>'');
		$gateways[] = array('title'=>POS_TXT_GATEWAYSELECT,	'value'=>'select/');
		$gateways[] = array('title'=>POS_TXT_GATEWAYQIWI,	'value'=>'select/qiwi/');
		$gateways[] = array('title'=>POS_TXT_GATEWAYWM,		'value'=>'select/webmoney/');
		return $gateways;
	}


	static function _getLanguages()
	{
		$languages = array();
		$languages[] = array('title'=>POS_TXT_LANUSER,	'value'=>'');
		$languages[] = array('title'=>POS_TXT_LANEN,	'value'=>'en');
		$languages[] = array('title'=>POS_TXT_LANRU,	'value'=>'ru');
		return $languages;
	}

	private function internal_debug()
	{

	}

	private function _getRequestData($var_name,$default = null)
	{
		$result = $default;
		if(isset($_GET[$var_name])){
			$result = $_GET[$var_name];
		}
		if(isset($_POST[$var_name])){
			$result = $_POST[$var_name];
		}
		return $result;
	}
}
?>