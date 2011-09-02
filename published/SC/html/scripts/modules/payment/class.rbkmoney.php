<?php

/**
 * @connect_module_class_name RBKMoney
 * @package DynamicModules
 * @subpackage Payment
 */
class RBKMoney extends PaymentModule
{
	var $language = 'rus';
	var $type = PAYMTD_TYPE_ONLINE;
	var $processing_url = 'https://rbkmoney.ru/acceptpurchase.aspx';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/rbkmoney.gif';
	var $callback_url = '';

	function _initVars()
	{
		parent::_initVars();

		//$callback_url = $_SERVER['HTTP_HOST'].'/published/SC/html/scripts/rupay.php';

		$this->title 		= 'RBK Money';
		$this->description 	= str_replace('{0}', $callback_url, 'Модуль интеграции с платежной системой RBK Money (<a href="http://www.rbkmoney.ru/" target="_top">www.rbkmoney.ru</a>; ранее &mdash; RUpay), учитывающий последние изменения, связанные с покупкой ими RUpay.');
		$this->sort_order 	= 1;
		
		$this->method_title = 'RBK Money';
		$this->method_description = 'Оплата через платежную систему <a href="http://www.rbkmoney.ru">RBK Money</a>. У вас должен быть счет в этой системе для того, чтобы произвести оплату.';
		$this->Settings = array(
			'CONF_PAYMENT_RBKMONEY_ESHOPID',
			'CONF_PAYMENT_RBKMONEY_SECRET'
        );
	}

	function _initSettingFields()
	{
		$this->SettingsFields['CONF_PAYMENT_RBKMONEY_ESHOPID'] = array(
		'settings_value' 		=> '',
		'settings_title' 		=> 'Номер сайта продавца',
		'settings_description' 	=> 'Номер вашего аккаунта в платежной системе RBK Money, на который будет поступать оплата по заказам.',
		'settings_html_function'=> 'setting_TEXT_BOX(0,',
		'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENT_RBKMONEY_SECRET'] = array(
		'settings_value' 		=> '',
		'settings_title' 		=> 'Секретный ключ',
		'settings_description' 	=> 'Ваш секретный ключ в системе RBK Money, известный только вам. Необходим для проверки ответа от платежной системы RUpay.',
		'settings_html_function'=> 'setting_TEXT_BOX(0,',
		'sort_order' 			=> 1,
		);
	}

	function after_processing_html($order_id,$active = true)
	{
		$order_info = ordGetOrder($order_id);
		$order_amount = number_format($order_info['order_amount']*$order_info['currency_value'], 2, '.', '');
		$postData = array(
		'orderId'=>$order_id,
		'eshopId'=>$this->_getSettingValue('CONF_PAYMENT_RBKMONEY_ESHOPID'),
		"serviceName"=>'Оплата по заказу '.CONF_ORDERID_PREFIX.$order_id,
		"recipientAmount"=>$order_amount,
		"recipientCurrency"=>$order_info['currency_code'],
		"successUrl"=>getTransactionResultURL('success',$this->ModuleConfigID),
		"failUrl"=>getTransactionResultURL('failure',$this->ModuleConfigID),
		//"userField_1"=>$order_info['currency_value'],
		//"userField_2"=>$order_info['order_amount'],
        );
		$is_MSIE = (isset($_SERVER['HTTP_USER_AGENT'])&&(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false))?true:false;
		if($is_MSIE){
			$postData['serviceName'] = translit($postData['serviceName']);
		}
		$hidden_fields_html = '';
		foreach($postData as $field => $value){
			$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($field).'" value="'.xHtmlSpecialChars($value).'" />'."\n";
		}
		$form = '<form action="'.$this->processing_url.'" name="RBKMoneyForm"'.($is_MSIE||true?'':' accept-charset="windows-1251"').' method="post">';
		$form .= $hidden_fields_html;
		if($active){
			$form .= '<h1>Переадресация на сервер RBK Money...</h1>';
		}else{
			$form .= 'Для оплаты заказа используйте кнопку ниже';
		}
		$form .= '<input type="submit" value="Перейти на сервер RBK Money"></form>';
		if($active){
			$form .= '<script language="JavaScript">
var old_onload = window.onload;
window.onload = function()
{if(old_onload) old_onload();
setTimeout("document.RBKMoneyForm.submit()",2000);
};
</script>';
		}
		return $form;
	}
	function transactionResultHandler($transaction_result){
		//here code to process order statuses
		$message = 'Результат обработки платежа RBK Money';
		return parent::transactionResultHandler($transaction_result,$message);
	}
};

?>