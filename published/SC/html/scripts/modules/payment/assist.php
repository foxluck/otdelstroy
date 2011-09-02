<?php
/**
 * Модуль интеграции с платежной системой Ассист
 * @link http://www.assist.ru/files/TechNEW.doc
 * @connect_module_class_name CAssist
 * @package DynamicModules
 * @subpackage Payment
 */

class CAssist extends PaymentModule {

	var $type = PAYMTD_TYPE_CC;
	var $language = 'rus';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/assist.gif';
	//https://secure.assist.ru/shops/cardpayment.cfm
	//https://payments.paysecure.ru/pay/order.cfm
	var $url = 'https://secure.assist.ru/shops/cardpayment.cfm';

	function _initVars(){

		parent::_initVars();
		$this->title 		= "Assist";
		$this->description 	= "Обработка кредитных карт через систему Assist (www.assist.ru)<br>Возможность приема платежей по кредитным картам VISA, MasterCard, JCB, DCI и через платежные системы WebMoney, Яндекс.Деньги, QIWI";
		$this->sort_order 	= 2;

		$this->Settings = array(
				"CONF_PAYMENTMODULE_ASSIST_MERCHANT_ID",
				"CONF_PAYMENTMODULE_ASSIST_AUTHORIZATION_MODE",
				"CONF_PAYMENTMODULE_ASSIST_LANG",
				"CONF_PAYMENTMODULE_ASSIST_TEST_MODE",
		);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_ASSIST_MERCHANT_ID'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> 'Merchant_ID', 
			'settings_description' 		=> 'Ваш ID в системе Assist', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 				=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_ASSIST_AUTHORIZATION_MODE'] = array(
			'settings_value' 			=> 0, 
			'settings_title' 			=> 'Режим предварительной авторизации', 
			'settings_description' 		=> 'Включите эту настройку, если Вы хотите, чтобы Ваш оплата по картам производилась в режиме предварительной авторизации; чтобы работать в нормальном режиме, выключите настройку', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 				=> 2,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_ASSIST_LANG'] = array(
				'settings_value' 			=> 'RU',
				'settings_title' 			=> 'Язык выдачи результатов',
				'settings_description' 		=> 'Выберите язык интерфейса на сервере Assist, который увидит покупатель при оплате',
				'settings_html_function' 	=> 'setting_SELECT_BOX(CAssist::_getLanguages(),',
				'sort_order' 				=> 3,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_ASSIST_TEST_MODE'] = array(
			'settings_value' 			=> 0, 
			'settings_title' 			=> 'Использовать тестовый режим', 
			'settings_description'	 	=> 'Включите эту настройку, если Вы хотите, чтобы оплата проводилась в тестовом режиме', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 				=> 3,
		);
	}

	function after_processing_html( $orderID )
	{
		$order = ordGetOrder( $orderID );
		//calculate order amount
		$order_amount = round(100*$order["order_amount"] * $order["currency_value"])/100;
		$comment = "Оплата заказа #{$orderID}";

		$is_MSIE = (isset($_SERVER['HTTP_USER_AGENT'])&&(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false))?true:false;
		if($is_MSIE){
			$order = array_map('translit',$order);
			$comment = translit($comment);
		}
		foreach($order as &$value){
			$value = htmlentities($value,ENT_QUOTES,'utf-8');
			unset($value);
		}

		$res = "";
		$extra_form =($is_MSIE?'':' accept-charset="windows-1251"');
		$res .= <<<HTML
<table width='100%'>
	<tr>
		<td align='center'>

		<FORM NAME="form1" ACTION="{$this->url}" METHOD="POST"{$extra_form}>
		<INPUT TYPE="HIDDEN" NAME="Shop_IDP" VALUE="{$this->_getSettingValue('CONF_PAYMENTMODULE_ASSIST_MERCHANT_ID')}">
		<INPUT TYPE="HIDDEN" NAME="Order_IDP" VALUE="{$orderID}">
		<INPUT TYPE="HIDDEN" NAME="Subtotal_P" VALUE="{$order_amount}">
		<INPUT TYPE="HIDDEN" NAME="Delay" VALUE="{$this->_getSettingValue('CONF_PAYMENTMODULE_ASSIST_AUTHORIZATION_MODE')}">
		<INPUT TYPE="HIDDEN" NAME="Language" VALUE="0">
		<INPUT TYPE="HIDDEN" NAME="URL_RETURN_OK" VALUE="{$this->getTransactionResultURL('success')}">
		<INPUT TYPE="HIDDEN" NAME="URL_RETURN_NO" VALUE="{$this->getTransactionResultURL('failure')}">
		<INPUT TYPE="HIDDEN" NAME="Currency" VALUE="{$order["currency_code"]}">
		<INPUT TYPE="HIDDEN" NAME="Comment" VALUE="{$comment}">
		<INPUT TYPE="HIDDEN" NAME="LastName" VALUE="{$order["billing_lastname"]}">
		<INPUT TYPE="HIDDEN" NAME="FirstName" VALUE="{$order["billing_firstname"]}">
		<INPUT TYPE="HIDDEN" NAME="Email" VALUE="{$order["customer_email"]}">
		<INPUT TYPE="HIDDEN" NAME="Address" VALUE="{$order["billing_address"]}">
		<INPUT TYPE="HIDDEN" NAME="Country" VALUE="{$order["billing_country"]}">
		<INPUT TYPE="HIDDEN" NAME="State" VALUE="{$order["billing_state"]}">
		<INPUT TYPE="HIDDEN" NAME="City" VALUE="{$order["billing_city"]}">
		<INPUT TYPE="HIDDEN" NAME="Zip" VALUE="{$order["billing_zip"]}">
		<INPUT TYPE="HIDDEN" NAME="IsFrame" VALUE="1">
		<INPUT TYPE="HIDDEN" NAME="f_Email" VALUE="0">
		<INPUT TYPE="HIDDEN" NAME="CardPayment" VALUE="1">
		<INPUT TYPE="HIDDEN" NAME="WebMoneyPayment" VALUE="1">
		<INPUT TYPE="HIDDEN" NAME="PayCashPayment" VALUE="1">
		<INPUT TYPE="HIDDEN" NAME="QiwiBeelinePayment" VALUE="1">
		<INPUT TYPE="HIDDEN" NAME="AssistIDCCPayment" VALUE="1">;
HTML;
$res .= ($this->_getSettingValue('CONF_PAYMENTMODULE_ASSIST_TEST_MODE')?'Тестовый режим. Выберите ответ системы: '.$this->getResponceCodes(true):'');
$res .= <<<HTML
		<INPUT TYPE="SUBMIT" NAME="Submit" VALUE="Оплатить заказ по кредитной карте сейчас!" onclick="document.all.Submit.disabled=true; document.form1.submit();">
		</FORM>
		</td>
	</tr>
</table>

HTML;
		return $res;

		$res = "";
		$res .= <<<HTML
			<table width='100%'>
				<tr>
					<td align='center'>

					<FORM NAME="form1" ACTION="{$this->url}" METHOD="POST"{$extra_form}>
					<INPUT TYPE="HIDDEN" NAME="Merchant_ID"		 VALUE="{$this->_getSettingValue('CONF_PAYMENTMODULE_ASSIST_MERCHANT_ID')}">
					<INPUT TYPE="HIDDEN" NAME="OrderNumber"		 VALUE="{$orderID}">
					<INPUT TYPE="HIDDEN" NAME="Delay"			 VALUE="{$this->_getSettingValue('CONF_PAYMENTMODULE_ASSIST_AUTHORIZATION_MODE')}">
					
					<INPUT TYPE="HIDDEN" NAME="TestMode"		 VALUE="{$this->_getSettingValue('CONF_PAYMENTMODULE_ASSIST_TEST_MODE')}">

					<INPUT TYPE="HIDDEN" NAME="Language"		 VALUE="{$this->_getSettingValue('CONF_PAYMENTMODULE_ASSIST_LANG')}">
					<INPUT TYPE="HIDDEN" NAME="OrderCurrency"	 VALUE="{$order["currency_code"]}">
					<INPUT TYPE="HIDDEN" NAME="OrderComment"	 VALUE="{$comment}">
					<INPUT TYPE="HIDDEN" NAME="OrderAmount"		 VALUE="{$order_amount}">
					
					<INPUT TYPE="HIDDEN" NAME="Lastname"		 VALUE="{$order["billing_lastname"]}">
					<INPUT TYPE="HIDDEN" NAME="Firstname"		 VALUE="{$order["billing_firstname"]}">
					<INPUT TYPE="HIDDEN" NAME="Email"			 VALUE="{$order["customer_email"]}">
	<!--				
					<INPUT TYPE="HIDDEN" NAME="URL_RETURN"		 VALUE="{$this->getTransactionResultURL('success')}">
					<INPUT TYPE="HIDDEN" NAME="URL_RETURN_OK"	 VALUE="{$this->getTransactionResultURL('success')}">
					<INPUT TYPE="HIDDEN" NAME="URL_RETURN_NO"	 VALUE="{$this->getTransactionResultURL('failure')}">
	-->				
					<INPUT TYPE="HIDDEN" NAME="CardPayment"		 VALUE="1">
					<INPUT TYPE="HIDDEN" NAME="YMPayment"		 VALUE="1">
					<INPUT TYPE="HIDDEN" NAME="WMPayment"		 VALUE="1">
					<INPUT TYPE="HIDDEN" NAME="QIWIPayment"		 VALUE="1">
<!--					<INPUT TYPE="HIDDEN" NAME="AssistIDPayment"	 VALUE="1"> -->
			
					<INPUT TYPE="SUBMIT" NAME="Submit" VALUE="Оплатить заказ по кредитной карте сейчас!" onclick="document.all.Submit.disabled=true; document.form1.submit();">
					</FORM>
					</td>
				</tr>
			</table>
HTML;
		return $res;
	}

	static function _getLanguages(){
		return 'Русский:RU,Английский:EN,(не определен):';
	}

	function getResponceCodes($as_html = true)
	{
		$codes = array(
			'AS000' => 'АВТОРИЗАЦИЯ УСПЕШНО ЗАВЕРШЕНА',
			'AS100' => 'ОТКАЗ В АВТОРИЗАЦИИ',
			'AS101' => 'ОТКАЗ В АВТОРИЗАЦИИ. Ошибочный номер карты',
			'AS102' => 'ОТКАЗ В АВТОРИЗАЦИИ. Недостаточно средств',
			'AS104' => 'ОТКАЗ В АВТОРИЗАЦИИ. Неверный срок действия карты',
			'AS105' => 'ОТКАЗ В АВТОРИЗАЦИИ. Превышен лимит операций по карте',
			'AS106' => 'ОТКАЗ В АВТОРИЗАЦИИ. Неверный PIN',
			'AS107' => 'ОТКАЗ В АВТОРИЗАЦИИ. Ошибка приема данных',
			'AS108' => 'ОТКАЗ В АВТОРИЗАЦИИ. Подозрение на мошенничество',
			'AS109' => 'ОТКАЗ В АВТОРИЗАЦИИ. Превышен лимит операций ASSIST',
			'AS110' => 'ОТКАЗ В АВТОРИЗАЦИИ. Требуется авторизация по 3D-Secure',
			'AS200' => 'ПОВТОРИТЕ АВТОРИЗАЦИЮ',
			'AS300' => 'ОПЕРАЦИЯ В ПРОЦЕССЕ. ЖДИТЕ',
			'AS400' => 'ПЛАТЕЖА С ТАКИМИ ПАРАМЕТРАМИ НЕ СУЩЕСТВУЕТ',
			'AS998' => 'ОШИБКА СИСТЕМЫ. Свяжитесь с ASSIST',
		);
		if($as_html){
			$html = '';
			foreach($codes as $code=>$description)
			{
				$html .= "\t<option value=\"{$code}\">{$description}</option>\n";
			}
			$html = "\n<select name=\"DemoResult\">\n{$html}</select>\n<br>\n";
			return $html;
		}else{
			return $codes;
		}
	}
}
?>