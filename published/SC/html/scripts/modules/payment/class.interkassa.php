<?php
/**
 * @connect_module_class_name InterKassa
 * @package DynamicModules
 * @subpackage Payment
 */
class InterKassa extends PaymentModule {

	var $type = PAYMTD_TYPE_ONLINE;
	var $language = 'rus';
	var $default_logo = 'http://webasyst.net/images/shop/modules/interkassa.gif';
	
	private $url = 'http://www.interkassa.com/lib/payment.php';
	private	$test_url = 'http://test.robokassa.ru/Index.aspx';

	function _initVars(){
			
		parent::_initVars();
		$this->title = INTERKASSA_TTL;
		$this->description = INTERKASSA_DSCR;
		$this->sort_order = 1;
			
		$this->Settings = array(
		'CONF_INTERKASSA_SHOP_ID',
		'CONF_INTERKASSA_SECRET_KEY',
		//'CONF_INTERKASSA_DEBUGMODE',
		'CONF_INTERKASSA_PAYSYSTEM_ALIAS',
		'CONF_INTERKASSA_SHOPCURRENCY',
		//'CONF_INTERKASSA_ORDERSTATUS',
		);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_INTERKASSA_SHOP_ID'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> INTERKASSA_CFG_SHOP_ID_TTL,
				'settings_description' 	=> INTERKASSA_CFG_SHOP_ID_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
				'sort_order' 			=> 1,
		);
		
		$this->SettingsFields['CONF_INTERKASSA_SECRET_KEY'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> INTERKASSA_CFG_SECRET_KEY_TTL,
				'settings_description' 	=> INTERKASSA_CFG_SECRET_KEY_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
				'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_INTERKASSA_PAYSYSTEM_ALIAS'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> INTERKASSA_CFG_PAYSYSTEM_ALIAS_TTL,
				'settings_description' 	=> INTERKASSA_CFG_PAYSYSTEM_ALIAS_DSCR,
				'settings_html_function' 	=> 'setting_SELECT_BOX(InterKassa::_getPaysystemAlias(),',
				'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_INTERKASSA_SHOPCURRENCY'] = array(
				'settings_value' 		=> '',
				'settings_title' 			=> INTERKASSA_CFG_SHOPCURRENCY_TTL,
				'settings_description' 	=> INTERKASSA_CFG_SHOPCURRENCY_DSCR,
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
				'sort_order' 			=> 1,
		);
		/*
		$this->SettingsFields['CONF_INTERKASSA_DEBUGMODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> INTERKASSA_CFG_DEBUGMODE_TTL, 
			'settings_description' 	=> INTERKASSA_CFG_DEBUGMODE_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);*/
		$this->SettingsFields['CONF_INTERKASSA_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> INTERKASSA_CFG_ORDERSTATUS_TTL,
			'settings_description' 	=> INTERKASSA_CFG_ORDERSTATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 			=> 1,
		);
		
	}
	
	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array(
			'settings_title'=>INTERKASSA_CUST_RESULTURL_TTL,
			'settings_description'=>INTERKASSA_CUST_RESULTURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
				.xHtmlSpecialChars($this->getDirectTransactionResultURL('success',array(__FILE__)))
				.'">'
		);
		$customProperties[] = array(
			'settings_title'=>INTERKASSA_CUST_SUCCESURL_TTL,
			'settings_description'=>INTERKASSA_CUST_SUCCESURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
				.xHtmlSpecialChars($this->getTransactionResultURL('success',array(__FILE__)))
				.'">'
		);
		$customProperties[] = array(
			'settings_title'=>INTERKASSA_CUST_FAILURE_TTL,
			'settings_description'=>INTERKASSA_CUST_FAILURE_DSCR,
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
		$order_amount = RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_INTERKASSA_SHOPCURRENCY')));
		
		//**
		$form_fields = array();
		/*
		 * ik_shop_id
		 * ik_payment_amount
		 * ik_payment_id
		 * ik_payment_desc max 255
		 * ik_paysystem_alias
		 * ik_baggage_fields
		 * ik_sign_hash
		*/
		$form_fields['ik_shop_id'] 			= $this->_getSettingValue('CONF_INTERKASSA_SHOP_ID');
		$form_fields['ik_payment_amount'] 	= sprintf('%0.2f',$order_amount);
		$form_fields['ik_payment_id'] 		= $orderID;
		$form_fields['ik_paysystem_alias'] 	= $this->_getSettingValue('CONF_INTERKASSA_PAYSYSTEM_ALIAS');
		$form_fields['ik_baggage_fields'] 	= '';//Optional field
		$form_fields['ik_sign_hash'] 		= md5(implode(':',$form_fields).':'.$this->_getSettingValue('CONF_INTERKASSA_SECRET_KEY'));
		$form_fields['ik_payment_desc'] 	= mb_substr($description,0,255,"UTF-8");
		
		$form_url = $this->url;
		
		$form = "\n<form id=\"interkassa_form\" method=\"POST\" action=\"{$form_url}\" style=\"text-align:center;\">\n";
		foreach($form_fields as $field=>$value){
			if(!$value)continue;
			$value = xHtmlSpecialChars($value);
			$form .= "\n\t<input type=\"hidden\" value=\"{$value}\" name=\"{$field}\">";
		}
		$form .= "\n".'<input type="submit" value="'.xHtmlSpecialChars(INTERKASSA_TXT_PROCESS).'" />'."\n";
		$form .= "\n</form>\n";
		
		if($active){
		$form .= '<script type="text/javascript">
<!--
setTimeout(\'document.getElementById("interkassa_form").submit();\',2000);
//-->
</script>';
		}
		
		return $form;
	}

	static function _getPaysystemAlias(){
		
		$options = array();
		
		$options[] = array('value' => ''			,'title' => INTERKASSA_TXT_CUSTOMER_CHOICE);
		$options[] = array('value'=>'rbkmoney'		,'title'=>'RBK RUR');
		$options[] = array('value'=>'egold'			,'title'=>'E-Gold USD');
		$options[] = array('value'=>'webmoneyz'		,'title'=>'WMZ');
		$options[] = array('value'=>'webmoneyu'		,'title'=>'WMU');
		$options[] = array('value'=>'webmoneyr'		,'title'=>'WMR');
		$options[] = array('value'=>'webmoneye'		,'title'=>'WME');
		$options[] = array('value'=>'ukrmoneyu'		,'title'=>'UM UAH');
		$options[] = array('value'=>'ukrmoneyz'		,'title'=>'UM USD');
		$options[] = array('value'=>'ukrmoneyr'		,'title'=>'UM RUR');
		$options[] = array('value'=>'ukrmoneye'		,'title'=>'UM EUR');
		$options[] = array('value'=>'liberty'		,'title'=>'LR USD');
		$options[] = array('value'=>'pecunix'		,'title'=>'Pecunix USD');
		$options[] = array('value'=>'limonexumu'	,'title'=>'Limonex UM-UAH');
		$options[] = array('value'=>'limonexumz'	,'title'=>'Limonex UM-USD');
		$options[] = array('value'=>'limonexeg'		,'title'=>'Limonex E-GOLD');
		$options[] = array('value'=>'limonexwmz'	,'title'=>'Limonex WMZ');
		$options[] = array('value'=>'moneybookers'	,'title'=>'MB USD');
		$options[] = array('value'=>'webmoneyg'		,'title'=>'WMG');
		$options[] = array('value'=>'moneymailz'	,'title'=>'MM USD');
		$options[] = array('value'=>'moneymailr'	,'title'=>'MM RUR');
		$options[] = array('value'=>'moneymaile'	,'title'=>'MM EUR');
		$options[] = array('value'=>'perfectmoney'	,'title'=>'PM USD');
		$options[] = array('value'=>'imoney'		,'title'=>'iMoney UAH');
		$options[] = array('value'=>'liqpayz'		,'title'=>'LP USD');
		$options[] = array('value'=>'liqpayu'		,'title'=>'LP UAH');

		return $options;
	}
	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend'){
		if($source != 'handler'){
			return parent::transactionResultHandler($transaction_result,$message.$log,$source);
		}
		//DEBUG:
		$log_file = DIR_TEMP.'/'.__CLASS__.'debug.log';
		if(($flog = fopen($log_file,'a'))){
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
				$sharedSecret = $this->_getSettingValue('CONF_INTERKASSA_MERCHANTPASS2');
				if($sharedSecret){
					//TODO use real order amount
					$nOutSum = $this->_getRequestData('OutSum',0);
					$sign = strtolower(md5("{$nOutSum}:{$orderID}:{$sharedSecret}"));
					$server_sign = strtolower($this->_getRequestData('SignatureValue',''));
					if($server_sign!=$sign){
						$transaction_result = 'failure';
						$log .= ' invalid post data sign';
						if($flog){
							fwrite($flog,"\nsign:\n".var_export(array('string'=>"{$nOutSum}:{$orderID}:{$sharedSecret}",'$server_sign'=>$server_sign,'$sign'=>$sign),true));
						}
					}
				}
				if($transaction_result == 'success'){
					//change order status on setted at module settings
					$statusID = $this->_getSettingValue('CONF_INTERKASSA_ORDERSTATUS');
					if($statusID!=-1){
						$transaction_id = intval($this->_getRequestData('transaction_id',0));
						$comment = $transaction_id?sprintf('ROBOXchange transaction ID: %d',$transaction_id):'status changed by ROBOXchange';
						if($this->_getSettingValue('CONF_INTERKASSA_TESTMODE')){
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