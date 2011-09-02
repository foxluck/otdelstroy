<?php
/**
 * @connect_module_class_name ROBOXchange
 * @package DynamicModules
 * @subpackage Payment
 */
class ROBOXchange extends PaymentModule {

	var $type = PAYMTD_TYPE_ONLINE;
	var $language = 'rus';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/robokassa.gif';

	private $url = 'https://merchant.roboxchange.com/Index.aspx';
	private	$test_url = 'http://test.robokassa.ru/Index.aspx';

	function _initVars(){
			
		parent::_initVars();
		$this->title = ROBOXCHANGE_TTL;
		$this->description = ROBOXCHANGE_DSCR;
		$this->sort_order = 1;
			
		$this->Settings = array(
		'CONF_ROBOXCHANGE_MERCHANTLOGIN',
		//'CONF_ROBOXCHANGE_MERCHANT_ID',
		'CONF_ROBOXCHANGE_MERCHANTPASS1',
		'CONF_ROBOXCHANGE_MERCHANTPASS2',
		'CONF_ROBOXCHANGE_TESTMODE',
		'CONF_ROBOXCHANGE_LANG',
		'CONF_ROBOXCHANGE_ROBOXCURRENCY',
		'CONF_ROBOXCHANGE_SHOPCURRENCY',
		'CONF_ROBOXCHANGE_ORDERSTATUS',
		);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_ROBOXCHANGE_MERCHANTLOGIN'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> ROBOXCHANGE_CFG_MERCHANTLOGIN_TTL,
				'settings_description' 	=> ROBOXCHANGE_CFG_MERCHANTLOGIN_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
				'sort_order' 			=> 1,
		);
		/*$this->SettingsFields['CONF_ROBOXCHANGE_MERCHANT_ID'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> ROBOXCHANGE_CFG_MERCHANT_ID_TTL,
		 'settings_description' 	=> ROBOXCHANGE_CFG_MERCHANT_ID_DSCR,
		 'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
		 'sort_order' 			=> 1,
		 );*/

		$this->SettingsFields['CONF_ROBOXCHANGE_MERCHANTPASS1'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> ROBOXCHANGE_CFG_MERCHANTPASS1_TTL,
				'settings_description' 	=> ROBOXCHANGE_CFG_MERCHANTPASS1_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
				'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_ROBOXCHANGE_MERCHANTPASS2'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> ROBOXCHANGE_CFG_MERCHANTPASS2_TTL,
				'settings_description' 	=> ROBOXCHANGE_CFG_MERCHANTPASS2_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
				'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_ROBOXCHANGE_LANG'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> ROBOXCHANGE_CFG_LANG_TTL,
				'settings_description' 	=> ROBOXCHANGE_CFG_LANG_DSCR,
				'settings_html_function' 	=> 'setting_SELECT_BOX(ROBOXchange::_getLanguages(),',
				'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_ROBOXCHANGE_ROBOXCURRENCY'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> ROBOXCHANGE_CFG_ROBOXCURRENCY_TTL,
				'settings_description' 	=> ROBOXCHANGE_CFG_ROBOXCURRENCY_DSCR,
				'settings_html_function' 	=> 'setting_SELECT_BOX(ROBOXchange::_getRoboxCurrencies(),',
				'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_ROBOXCHANGE_SHOPCURRENCY'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> ROBOXCHANGE_CFG_SHOPCURRENCY_TTL,
				'settings_description' 	=> ROBOXCHANGE_CFG_SHOPCURRENCY_DSCR,
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
				'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_ROBOXCHANGE_TESTMODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> ROBOXCHANGE_CFG_TESTMODE_TTL, 
			'settings_description' 	=> ROBOXCHANGE_CFG_TESTMODE_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_ROBOXCHANGE_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> ROBOXCHANGE_CFG_ORDERSTATUS_TTL,
			'settings_description' 	=> ROBOXCHANGE_CFG_ORDERSTATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 			=> 1,
		);
	}

	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array(
			'settings_title'=>ROBOXCHANGE_CUST_RESULTURL_TTL,
			'settings_description'=>ROBOXCHANGE_CUST_RESULTURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getDirectTransactionResultURL('success',array(__FILE__)))
			.'">'
			);
			$customProperties[] = array(
			'settings_title'=>ROBOXCHANGE_CUST_SUCCESURL_TTL,
			'settings_description'=>ROBOXCHANGE_CUST_SUCCESURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getTransactionResultURL('success',array(__FILE__)))
			.'">'
			);
			$customProperties[] = array(
			'settings_title'=>ROBOXCHANGE_CUST_FAILURE_TTL,
			'settings_description'=>ROBOXCHANGE_CUST_FAILURE_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getTransactionResultURL('failure',array(__FILE__)))
			.'">'
			);

			return $customProperties;
	}

	function after_processing_html( $orderID, $active = true  ){

		$res = '';
			
		$order = ordGetOrder( $orderID );
		$content = ordGetOrderContent($orderID);
		$description =CONF_SHOP_NAME.": ";
		foreach($content as $content_item){
			$description .= preg_replace('/^\[[^\]]*]/','',str_replace(array('&nbsp;'),array(' '),strip_tags($content_item['name']).'x'.$content_item['Quantity'].";"));
		}

		$description = preg_replace('/[^\.\?,\[]\(\):;"@\\%\s\w\d]+/',' ',$description);
			
		$order_amount = RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_ROBOXCHANGE_SHOPCURRENCY')));

		//**
		$form_fields = array();
		$form_fields['MrchLogin'] 		= $this->_getSettingValue('CONF_ROBOXCHANGE_MERCHANTLOGIN');
		$form_fields['OutSum'] 			= $order_amount;
		$form_fields['InvId'] 			= $orderID;
		$form_fields['SignatureValue'] 	= md5(implode(':',$form_fields).':'.$this->_getSettingValue('CONF_ROBOXCHANGE_MERCHANTPASS1'));
		$form_fields['Desc'] 			= mb_substr($description,0,100,"UTF-8");
		$form_fields['IncCurrLabel'] 	= $this->_getSettingValue('CONF_ROBOXCHANGE_ROBOXCURRENCY');
		$form_fields['Culture'] 		= $this->_getSettingValue('CONF_ROBOXCHANGE_LANG');
		$form_fields['ResultURL'] 		= $this->getDirectTransactionResultURL('success',array(__FILE__));
		$form_fields['SuccessURL'] 		= $this->getTransactionResultURL('success',array(__FILE__));
		$form_fields['FailURL'] 		= $this->getTransactionResultURL('failure',array(__FILE__));


		$form_url = $this->_getSettingValue('CONF_ROBOXCHANGE_TESTMODE')?$this->test_url:$this->url;

		$form = "\n<form id=\"roboxchange_form\" method=\"POST\" action=\"{$form_url}\" style=\"text-align:center;\">\n";
		foreach($form_fields as $field=>$value){
			if(!$value)continue;
			$value = xHtmlSpecialChars($value);
			$form .= "\n\t<input type=\"hidden\" value=\"{$value}\" name=\"{$field}\">";
		}
		$form .= "\n".'<input type="submit" value="'.xHtmlSpecialChars(ROBOXCHANGE_TXT_PROCESS).'" />'."\n";
		$form .= "\n</form>\n";

		if($active){
			$form .= '<script type="text/javascript">
<!--
setTimeout(\'document.getElementById("roboxchange_form").submit();\',2000);
//-->
</script>';
		}

		return $form;

		//**

		$post_1 = array(
		'mrh' => $this->_getSettingValue('CONF_ROBOXCHANGE_MERCHANTLOGIN'),
		'out_summ' => $order_amount,
		'inv_id' => $orderID,
		);
			
		$post_1['crc'] = md5(implode(':',$post_1).':'.$this->_getSettingValue('CONF_ROBOXCHANGE_MERCHANTPASS1'));
			
		$post_1['lang'] = $this->_getSettingValue('CONF_ROBOXCHANGE_LANG');
		$post_1['in_curr'] = $this->_getSettingValue('CONF_ROBOXCHANGE_ROBOXCURRENCY');
		$post_1['inv_desc'] = CONF_SHOP_NAME;
			
		$hidden_fields_html = '';
		reset($post_1);

		while(list($k,$v)=each($post_1)){

			$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
		}

		$res = '
				<form method="post" action="https://www.roboxchange.com/ssl/calc.asp" style="text-align:center;">
					'.$hidden_fields_html.'
					<input type="submit" value="'.xHtmlSpecialChars(ROBOXCHANGE_TXT_PROCESS).'" />
				</form>
				';
		return $res;
	}

	static function _getLanguages(){
		return ROBOXCHANGE_TXT_LANGRU.':ru,'.ROBOXCHANGE_TXT_LANGEN.':en,'.ROBOXCHANGE_TXT_LANUSER.':';
	}

	static function _getRoboxCurrencies(){
		/*
			http://www.roboxchange.com/xml/currlist.asp
			*/
		$options = array();
			
		$error_options = $options;
			
		$error_options[] = array(
		'title' => ROBOXCHANGE_TXT_NOCURR,
		'value' => ''
		);
			
		$ch=curl_init();
		if(!$ch)return $error_options;
			
		curl_setopt ($ch, CURLOPT_URL,'http://www.roboxchange.com/xml/currlist.asp');
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		initCurlProxySettings($ch);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);

		$http_response = curl_exec($ch);

		if(!$http_response){
			$this->_writeLogMessage(MODULE_LOG_CURL,curl_errno($ch).':'.curl_error($ch));
			curl_close($ch);
			return $error_options;
		}else{
			if($content_type = curl_getinfo($ch,CURLINFO_CONTENT_TYPE)){
				if(preg_match('/charset=[\'"]?([a-z\-0-9]+)[\'"]?/i',$content_type,$matches)){
					$charset = strtolower($matches[1]);
					if(!in_array($charset,array('utf-8','utf8'))){
						$http_response = iconv($charset,'utf-8',$http_response);
					}
				}
			}
		}
		curl_close($ch);
		$xml = simplexml_load_string($http_response);
		foreach ($xml->item as $xmlItem){
			$options[] = array(
			'title' => (string)$xmlItem->curr_name,
			'value' => (string)$xmlItem->curr,
			);
		}
		return $options;
	}
	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend'){
		if($source != 'handler'){
			return parent::transactionResultHandler($transaction_result,$message.$log,$source);
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
		/*
		 * OutSum=nOutSum&InvId=nInvId&SignatureValue=sSignatureValue
		 */

		$orderID = intval($this->_getRequestData('InvId',0));
		$log = '';
		if($orderID &&($order = _getOrderById($orderID))){
			$log = 'log';
			//if($this->validateResultKey(array($orderID,$order['customer_email']))){
			if($this->validateResultKey(array(__FILE__))){
				//check callback sign
				$sharedSecret = $this->_getSettingValue('CONF_ROBOXCHANGE_MERCHANTPASS2');
				if($sharedSecret){
					$nOutSum = $this->_getRequestData('OutSum',0);
					$sign = strtolower(md5("{$nOutSum}:{$orderID}:{$sharedSecret}"));
					$server_sign = strtolower($this->_getRequestData('SignatureValue',''));
					if($server_sign!=$sign){
						$transaction_result = 'failure';
						$log .= ' invalid post data sign';
						if($flog){
							fwrite($flog,"\nsign:\n".var_export(array('string'=>"{$nOutSum}:{$orderID}:{$sharedSecret}",'$server_sign'=>$server_sign,'$sign'=>$sign),true));
						}
						//var_dump(array('invalid post data sign',$_POST['sign'],$sign,$value));
					}
				}
				if($transaction_result == 'success'){
					//change order status on setted at module settings
					$statusID = $this->_getSettingValue('CONF_ROBOXCHANGE_ORDERSTATUS');
					if($statusID!=-1){
						$transaction_id = intval($this->_getRequestData('transaction_id',0));
						$comment = $transaction_id?sprintf('ROBOXchange transaction ID: %d',$transaction_id):'status changed by ROBOXchange';
						if($this->_getSettingValue('CONF_ROBOXCHANGE_TESTMODE')){
							$comment .= "\nTest mode";
						}
						ostSetOrderStatusToOrder( $orderID, $statusID,$comment,0);

					}
					print "OK".$orderID;
					exit;
				}elseif($transaction_result == 'failure'){
					//log at order processing history
					$statusID = 3;
					//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
					//ostSetOrderStatusToOrder($orderID, $statusID, translate('ordr_added_comment').': '.$comment, ($this->getData('notify_customer')?1:0), true);
				}
			}else{
				$transaction_result = 'failure';
				$statusID = 3;
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
		print $log;exit;
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