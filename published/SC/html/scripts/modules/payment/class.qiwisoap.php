<?php
/**
 * @connect_module_class_name QiwiSOAP
 * @todo check soap extensions
 * @package DynamicModules
 * @subpackage Payment
 * @author WebAsyst Team
 * @version 1.6
 * @link http://www.payonlinesystem.ru/
 */
class QiwiSOAP extends PaymentModule
{
	var $type = PAYMTD_TYPE_ONLINE;
	var $default_logo = 'http://webasyst.net/images/shop/modules/qiwi.gif';
	var $language = 'rus';

	private $url = 'https://ishop.qiwi.ru/services/ishop';
	private $http_url = 'https://w.qiwi.ru/setInetBill_utf.do';
	
	function _initVars()
	{
			
		parent::_initVars();
		$this->title = QIWI_TTL;
		$this->description = QIWI_DSCR;
		$this->sort_order = 1;
			
		$this->Settings = array(
			'CONF_QIWI_LOGIN',
			'CONF_QIWI_PASSWORD',
			'CONF_QIWI_LIFETIME',
			'CONF_QIWI_CUSTOMER_PHONE',
			'CONF_QIWI_ALARM',
			'CONF_QIWI_CURRENCY',
			'CONF_QIWI_SUCCESS_STATUS',
			'CONF_QIWI_CANCEL_STATUS',
			'CONF_QIWI_TESTMODE',
			'CONF_QIWI_PREFIX',
		);

		Autoload::add("IShopServerWSService", "published/SC/html/scripts/modules/payment/includes/qiwi/IShopServerWSService.php");
		Autoload::add("IShopClientWSService", "published/SC/html/scripts/modules/payment/includes/qiwi/IShopClientWSService.php");
		Autoload::add("nusoap_base", "published/SC/html/scripts/modules/payment/includes/nusoap/nusoap.php");
		$this->log_mode = LOGMODE_MSG;
	}

	function _initSettingFields()
	{

		$this->SettingsFields['CONF_QIWI_LOGIN'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_LOGIN_TTL,
			'settings_description' 		=> QIWI_CFG_LOGIN_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_QIWI_PASSWORD'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_PASSWORD_TTL,
			'settings_description' 		=> QIWI_CFG_PASSWORD_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_QIWI_PREFIX'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_PREFIX_TTL,
			'settings_description' 		=> QIWI_CFG_PREFIX_DSCR,
			'settings_html_function' 	=> 'QiwiSOAP::settings_PREFIX(',
			'sort_order' 				=> 1,
		);


		$this->SettingsFields['CONF_QIWI_LIFETIME'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_LIFETIME_TTL,
			'settings_description' 		=> QIWI_CFG_LIFETIME_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(2,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_QIWI_CUSTOMER_PHONE'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_CUSTOMER_PHONE_TTL,
			'settings_description' 		=> QIWI_CFG_CUSTOMER_PHONE_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(QiwiSOAP::_getCustomerFields(),',
			'sort_order' 				=> 1,
		);


		$this->SettingsFields['CONF_QIWI_ALARM'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_ALARM_TTL,
			'settings_description' 		=> QIWI_CFG_ALARM_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(QiwiSOAP::_getAlarmVariants(),',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_QIWI_CURRENCY'] = array(
			'settings_value' 			=> defined('CONF_DEFAULT_CURRENCY')?constant('CONF_DEFAULT_CURRENCY'):'',
			'settings_title' 			=> QIWI_CFG_CURRENCY_TTL,
			'settings_description' 		=> QIWI_CFG_CURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
			'sort_order' 				=> 1,
		);


		$this->SettingsFields['CONF_QIWI_SUCCESS_STATUS'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_SUCCESS_STATUS_TTL,
			'settings_description' 		=> QIWI_CFG_SUCCESS_STATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 				=> 1,
		);


		$this->SettingsFields['CONF_QIWI_CANCEL_STATUS'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_CANCEL_STATUS_TTL,
			'settings_description' 		=> QIWI_CFG_CANCEL_STATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 				=> 1,
		);


		$this->SettingsFields['CONF_QIWI_TESTMODE'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> QIWI_CFG_TESTMODE_TTL,
			'settings_description' 		=> QIWI_CFG_TESTMODE_DSCR,
			'settings_html_function' 	=> 'setting_CHECK_BOX(',
			'sort_order' 				=> 1,
		);

	}

	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array (
			'settings_title'=>QIWI_CUST_SOAP_URL_TTL,
			'settings_description'=>QIWI_CUST_SOAP_URL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getDirectTransactionResultURL('result',array(__FILE__)))
			.'">',
			);
			return $customProperties;
	}

	public function transactionResultHandler($transaction_result, $message, $source)
	{
		if($source != 'handler'){
			return parent::transactionResultHandler($transaction_result,$message,$source);
		}
		if($this->validateResultKey(array(__FILE__))){
			try{
				$soap_client = $this->getQiwiSoapServer();

				$soap_client->setHandler($this);
				$post = null;
				if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
					$post = !empty($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : null;
				} else {
					$post = implode("\r\n", file('php://input'));
				}
				$register = Register::getInstance();
				$orderID = null;
				$register->set('QIWI_orderID',$orderID);
				$status = null;
				$register->set('QIWI_status',$status);
				$soap_client->service($post);
				$this->_log(LOGTYPE_DEBUG,$soap_client->getDebug());
				$orderID = $register->get('QIWI_orderID');
				if($orderID && ($order = _getOrderById($orderID))){

					if($result = $this->checkBill($orderID)){
						$comments = "\n".QIWI_TXT_QIWI_ROBOT;
						if($phone = $result->user){
							$comments .= "\n".QIWI_TXT_CUSTOMER_PHONE.": ".$phone;
						}
						$statusID = null;
						$status = $register->get('QIWI_status');
						switch($result->status){
							case 60:{
								$amount = PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_POS_SHOP_CURRENCY'));
								if($amount != $result->amount){
									$comments .= "\n".QIWI_TXT_INVALID_AMOUNT.": {$result->amount}";
								}else{
									$statusID = $this->_getSettingValue('CONF_QIWI_SUCCESS_STATUS');
								}
								break;
							}
							case 150:
							case 151:
							case 160:
							case 161:{
								$statusID = $this->_getSettingValue('CONF_QIWI_CANCEL_STATUS');
								break;
							}
						}

						if($status != $result->status){
							$comments .= "\nupdateBill->status: ".$this->getBillCodeDescription($status);
						}
						if(!$statusID){
							$statusID = $order['statusID'];
						}
						if($register->get('QIWI_testmode')){
							$comments .= "\n".QIWI_TXT_MANUAL;
						}
						ostSetOrderStatusToOrder( $orderID, $statusID,$this->getBillCodeDescription($result->status).$comments,0,true);
					}
				}else{
					$this->_log(LOGTYPE_MSG,'Unknown orderID');
				}
			}catch(SoapFault $sf){
				$this->_log(LOGTYPE_ERROR,$sf->getMessage());
			}

		}

		#updateBill
		#checkBill
	}

	public function payment_form_html()
	{
		;#fill and check customer phone number
		$title = QIWI_TXT_CUSTOMER_PHONE;
		$customer_phone = '';
		if($field_id = $this->_getSettingValue('CONF_QIWI_CUSTOMER_PHONE')){
			$checkoutEntry = Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
			$customerEntry = $checkoutEntry->customer();
			$fields = GetRegFieldsValuesByCustomerID($customerEntry->customerID);
			foreach($fields as $field){
				if($field['reg_field_ID'] == $field_id){
					$customer_phone = $field['reg_field_value'];
					break;
				}
			}
		}
		return <<<HTML
		{$title}: <input type="text" name="customer_phone" class="customer_phone" value="{$customer_phone}"/>
HTML;
	}

	function payment_process($order)
	{
		#validate customer phone number
		$customer_phone = isset($_POST['customer_phone'])?$_POST['customer_phone']:'';
		$customer_phone = preg_replace('/[\s\(\)\-]+/','',$customer_phone);
		$pattern = '@^(\+7|8)(\d{3})(\d{7})$@';
		if(!preg_match($pattern,$customer_phone)){
			return QIWI_TXT_INVALID_CUSTOMER_PHONE;
		}
		return 1;
	}

	public function after_processing_html($orderID, $active = true)
	{
		$order = ordGetOrder( $orderID );
		$amount = PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_POS_SHOP_CURRENCY'));
		$customer_phone = '';
		if($field_id = $this->_getSettingValue('CONF_QIWI_CUSTOMER_PHONE')){
			$checkoutEntry = Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
			$customerEntry = $checkoutEntry->customer();
			$fields = GetRegFieldsValuesByCustomerID($order['customerID']);
			foreach($fields as $field){
				if($field['reg_field_ID'] == $field_id){
					$customer_phone = $field['reg_field_value'];
					break;
				}
			}
		}
		$customer_phone = preg_replace('/[\s\(\)\-]+/','',$customer_phone);
		$customer_phone = preg_replace('@^(\+7|8)@','',$customer_phone);

		$login		 = $this->_getSettingValue('CONF_QIWI_LOGIN');
		$password	 = $this->getPassword($orderID);
		$lifetime	 = date('d.m.Y H:i:s',time()+3600*max(1,(int)$this->_getSettingValue('CONF_QIWI_LIFETIME')));
		$alarm		 = $this->_getSettingValue('CONF_QIWI_ALARM');

		$content = ordGetOrderContent($orderID);
		ordPrepareOrderInfo($order);
		$description ="Оплата заказа {$order['orderID_view']} (";
		foreach($content as $content_item){
			$description .= "\n".preg_replace('/^\[[^\]]*]/','',str_replace(array('&nbsp;'),array(' '),strip_tags($content_item['name']).'x'.$content_item['Quantity']."; "));
		}

		$description = htmlspecialchars(preg_replace('/[^\.\?,\[]\(\):;"@\\%\s\w\d]+/',' ',$description.")"));
		$prefix = $this->_getSettingValue('CONF_QIWI_PREFIX');
		$phone_description = QIWI_TXT_CUSTOMER_PHONE;
		$success_url 		= $this->getTransactionResultURL('success');
		$fail_url	 		= $this->getTransactionResultURL('failure');
		/*
		 * <input type="hidden" name="successUrl" value="{$success_url}"/>
			<input type="hidden" name="failUrl" value="{$fail_url}"/>
		 */
		$form = <<<HTML
		<form method="POST" action="{$this->http_url}" accept-charset="UTF-8">
			<input type="hidden" name="from" value="{$login}"/>
			{$phone_description}:&nbsp;<input type="string" name="to" value="{$customer_phone}"/>
			<input type="hidden" name="summ" value="{$amount}"/>
			<input type="hidden" name="com" value="{$description}"/>
			<input type="hidden" name="lifetime" value="{$lifetime}"/>
			<input type="hidden" name="check_agt" value="false"/>
			<input type="hidden" name="txn_id" value="{$prefix}{$order['orderID']}"/>
			<input type="submit" value="Оплатить счет в QIWI"/>
		</form>
HTML;
			return $form;
	}

	public function after_processing_php($orderID)
	{
		return true;
		$order = ordGetOrder( $orderID );

		$amount = PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_POS_SHOP_CURRENCY'));
		$customer_phone = isset($_POST['customer_phone'])?$_POST['customer_phone']:'';
		$customer_phone = preg_replace('/[\s\(\)\-]+/','',$customer_phone);
		$customer_phone = preg_replace('@^(\+7|8)@','',$customer_phone);
		$description ="Оплата заказа {$order['orderID_view']} (";

		$content = ordGetOrderContent($orderID);
		foreach($content as $content_item){
			$description .= "\n".preg_replace('/^\[[^\]]*]/','',str_replace(array('&nbsp;'),array(' '),strip_tags($content_item['name']).'x'.$content_item['Quantity']."; "));
		}
		if(strlen($description)>255){
			$description = substr($description,1,252).'...';
		}

		$content_description = htmlspecialchars(preg_replace('/[^\.\?,\[]\(\):;"@\\%\s\w\d]+/',' ',$description.")"));
		return $customer_phone?$this->createBill($orderID,$amount,$customer_phone,$content_description):true;
	}

	private function getPassword($orderID)
	{
		$prefix = $this->_getSettingValue('CONF_QIWI_PREFIX');
		$password = $this->_getSettingValue('CONF_QIWI_PASSWORD');
		setlocale(LC_CTYPE, 'ru_RU.CP-1251', 'ru_RU.CP1251', 'ru_RU.win');
		$string = $prefix.$orderID.strtoupper(md5(iconv('utf-8','cp1251',$password)));
		$hash = strtoupper(md5(iconv('utf-8','cp1251',$string)));
		$this->_log(LOGTYPE_DEBUG,__METHOD__."\t{$orderID}\t{$password}\t{$string}\t{$hash}");
		return $hash;
	}

	/**
	 *
	 * @return IShopServerWSService
	 */
	private function getQiwiSoapClient()
	{
		if(!class_exists('nusoap_base',false)) {
			class_exists('nusoap_base');
		}
		//TODO init proxy settings
		$options = array();
		$options['location']	= $this->url;
		$options['trace']		= 1;
		$instance = new IShopServerWSService(dirname(__FILE__).'/includes/qiwi/'.'IShopServerWS.wsdl', $options);
		if($this->log_mode==LOGMODE_DEBUG){
			$instance->setDebugLevel(9);
		}
		$instance->soap_defencoding = 'UTF-8';
		return $instance;
	}

	/**
	 *
	 * @return IShopClientWSService
	 */
	private function getQiwiSoapServer()
	{
		if(!class_exists('nusoap_base',false)) {
			class_exists('nusoap_base');
		}
		$options = array();
		$options['location']	= $this->url;
		$options['trace']		= 1;
		$instance = new IShopClientWSService(dirname(__FILE__).'/includes/qiwi/'.'IShopClientWS.wsdl', $options);
		if($this->log_mode==LOGMODE_DEBUG){
			$instance->setDebugLevel(9);
		}
		$instance->soap_defencoding = 'UTF-8';
		return $instance;
	}

	private function createBill($orderID,$amount,$phone_number,$description)
	{
		$result = '';
		try{
			$soap_client = $this->getQiwiSoapClient();

			$login		 = $this->_getSettingValue('CONF_QIWI_LOGIN');
			$password	 = $this->_getSettingValue('CONF_QIWI_PASSWORD');
			$lifetime	 = date('d.m.Y H:i:s',time()+3600*max(1,(int)$this->_getSettingValue('CONF_QIWI_LIFETIME')));
			$alarm		 = $this->_getSettingValue('CONF_QIWI_ALARM');
			$prefix		 = $this->_getSettingValue('CONF_QIWI_PREFIX');

			$parameters = new createBill();

			$parameters->login		=	$login;				# логин (id) магазина;
			$parameters->password	=	$password;			# пароль для магазина;
			$parameters->user		=	$phone_number;		# идентификатор пользователя (номер телефона);
			$parameters->amount		=	$amount;			# сумма, на которую выставляется счет (разделитель «.»);
			$parameters->comment	=	$description;		# комментарий к счету, который увидит пользователь (максимальная длина 255 байт);
			$parameters->txn		=	$prefix.$orderID;	# уникальный идентификатор счета (максимальная длина 30 байт);
			$parameters->lifetime	=	$lifetime;			# время действия счета (в формате dd.MM.yyyy HH:mm:ss);
			$parameters->alarm		=	$alarm;				# отправить оповещение пользователю (1 - уведомление SMS-сообщением, 2 - уведомление звонком, 0 - не оповещать);
			# ПРИМЕЧАНИЕ
			# Уведомления доступны только магазинам, зарегистрированным по схеме "Именной кошелек". Для магазинов, зарегистрированных по схеме "Прием платежей", уведомления заблокированы.
			$parameters->create	=	1;	# флаг для создания нового пользователя (если он не зарегистрирован в системе).
			# В ответ возвращается результат выполнения функции (см. Справочник кодов завершения).




			$response = $soap_client->createBill($parameters);
			$this->_log(LOGTYPE_DEBUG,$soap_client->getDebug());
			if($response->createBillResult){
				$result = $this->getResponseCodeDescription($response->createBillResult);
				$this->_log(LOGTYPE_ERROR,__METHOD__." #{$orderID}\tphone:{$phone_number}\t{$result}");
			}
		}catch(SoapFault $sf){
			$result = $sf->getMessage();
			$this->_log(LOGTYPE_ERROR,$sf->getMessage());
			$this->_log(LOGTYPE_DEBUG,$soap_client->getDebug());
		}
		return $result;
	}

	/**
	 *
	 *
	 * @param checkBill $parameters
	 * @return updateBillResponse
	 */
	public function updateBill($login,$password,$txn,$status)
	{
		$result = new updateBillResponse();
		$result->updateBillResult = 300;
		$args = func_get_args();
		$this->_log(LOGTYPE_DEBUG,var_export($args,true));

		if($login == $this->_getSettingValue('CONF_QIWI_LOGIN')){

			$prefix = $this->_getSettingValue('CONF_QIWI_PREFIX');
			$pattern = "/^{$prefix}/";
			if(preg_match($pattern,$txn)){
				$orderID = intval(preg_replace($pattern,'',$txn));	# уникальный идентификатор счета (максимальная длина 30 байт);

				$order = _getOrderById($orderID);
				if($order){
					$system_password = $this->getPassword($orderID);
					if($password == $system_password){
						$register = Register::getInstance();
						$register->set('QIWI_orderID',$orderID);
						$register->set('QIWI_status',$status);
						$result->updateBillResult = 0;

					}elseif($this->_getSettingValue('CONF_QIWI_TESTMODE')&&!$password){
						$register = Register::getInstance();
						$register->set('QIWI_orderID',$orderID);
						$register->set('QIWI_status',$status);
						$mode = true;
						$register->set('QIWI_testmode',$mode);
						$this->_log(LOGTYPE_MSG,'Test mode - password is empty but expected '.$system_password.' for order '.$orderID.' - '.$txn);
						$result->updateBillResult = 0;
					}else{
						$this->_log(LOGTYPE_DEBUG,'get invalid password '.$password.' but expect '.$system_password.' for order '.$orderID.' - '.$txn);
						$result->updateBillResult = 150;
					}
				}else{
					$result->updateBillResult = 210;
				}
			}else{
				$result->updateBillResult = 210;
			}

		}else{

			$result->updateBillResult = 298;
		}
		$register->set('QIWI_updateBillResult',$result);

		# login – логин (id) магазина;
		# password – пароль. Данный параметр может быть сформирован 2 способами:
		# − С использованием подписи WSS X.509, когда каждое уведомление подписывается сервером ОСМП. Данный варинт более сложен в реализации, однако намного безопаснее;
		# − С пользованием упрощенного алгоритма. В поле записывается специально вычисленное по следующему алгоритму значение:
		# uppercase(md5(txn + uppercase(md5(пароля))))
		# Все строки, от которых вычисляется функция md5, преобразуются в байты в кодировке windows-1251. Данный вариант в реализации проще, однако, менее надежен.
		# Пример 1. Пример вычисления значения поля password по упрощенному алгоритму
		# Пусть заказ="Заказ1", а пароль="Пароль магазина", тогда функция
		# MD5("Пароль магазина")=936638421CA12C3E15E72FA7B75E03CE.
		# В поле password будет записано следующее значение:
		# MD5("Заказ1"+MD5("Пароль магазина"))=MD5("Заказ1"+"936638421CA12C3E15E72FA7B75E03CE")= EC19350E3051D8A9834E5A2CF25FD0D9
		# txn – уникальный идентификатор счета (максимальная длина 30 байт);
		# status – новый статус счета (см. Справочник статусов счетов).
		# В ответ возвращается результат выполнения запроса (см. Коды завершения).
		return $result;
	}

	/**
	 *
	 * @param $orderID
	 * @return checkBillResponse
	 */
	private function checkBill($orderID)
	{
		try{
			$soap_client = $this->getQiwiSoapClient();

			$login		 = $this->_getSettingValue('CONF_QIWI_LOGIN');
			$password	 = $password = $this->_getSettingValue('CONF_QIWI_PASSWORD');
			$prefix = $this->_getSettingValue('CONF_QIWI_PREFIX');

			$parameters = new checkBill();
			$parameters->login		= $login;		# логин (идентификатор) магазина;
			$parameters->password	= $password;	# пароль для магазина;
			$parameters->txn		= $prefix.$orderID;		# уникальный идентификатор счета (максимальная длина 30 байт).

			$result =  $soap_client->checkBill($parameters);
			$this->_log(LOGTYPE_DEBUG,$soap_client->getDebug());
			$this->_log(LOGTYPE_DEBUG,var_export($result,true));
			return $result;
		}catch(SoapFault $sf){
			$this->_log(LOGTYPE_ERROR,$sf->getMessage()."\n".$soap_client->getDebug());
			return false;
		}

		## Результаты выполнения функции:
		# user – идентификатор пользователя (номер телефона);
		# amount – сумма, на которую выставлен счет (разделитель «.»);
		# date – дата выставления счета (в формате dd.MM.yyyy HH:mm:ss);
		# lifetime – время действия счета (в формате dd.MM.yyyy HH:mm:ss);
		# status – статус счета (см. Справочник статусов счетов)

		//TODO update order status and write changelog
	}

	/**
	 * optional future
	 * @return void
	 */
	private function cancelBill()
	{
		# login – логин (идентификатор) магазина;
		# password – пароль для магазина;
		# txn – уникальный идентификатор счета (максимальная длина 30 байт).
	}

	private function getResponseCodeDescription($response_code)
	{
		$codes = array();
		$codes[-1]	=	"Неизвестный код ответа [{$response_code}]";
		$codes[0]	=	'Успех';
		$codes[13]	=	'Сервер занят, повторите запрос позже';
		$codes[150]	=	'Ошибка авторизации (неверный логин/пароль)';
		$codes[210]	=	'Счет не найден';
		$codes[215]	=	'Счет с таким txn-id уже существует';
		$codes[241]	=	'Сумма слишком мала';
		$codes[242]	=	'Превышена максимальная сумма платежа – 15 000р.';
		$codes[278]	=	'Превышение максимального интервала получения списка счетов';
		$codes[298]	=	'Агента не существует в системе';
		$codes[300]	=	'Неизвестная ошибка';
		$codes[330]	=	'Ошибка шифрования';
		$codes[370]	=	'Превышено максимальное кол-во одновременно выполняемых запросов';
		return isset($codes[$response_code])?$codes[$response_code]:$codes[-1];
	}

	private function getBillCodeDescription($response_code)
	{
		if($response_code<0){
			return $this->getResponseCodeDescription(-$response_code);
		}
		$codes	=	array();
		$codes[-1]	=	"Неизвестный код статуса счета [{$response_code}]";
		$codes[50]	=	'Выставлен';
		$codes[52]	=	'Проводится';
		$codes[60]	=	'Оплачен';
		$codes[150]	=	'Отменен (ошибка на терминале)';
		$codes[151]	=	'Отменен (ошибка авторизации: недостаточно средств на балансе, отклонен абонентом при оплате с лицевого счета оператора сотовой связи и т.п.).';
		$codes[160]	=	'Отменен';
		$codes[161]	=	'Отменен (Истекло время)';
		return isset($codes[$response_code])?$codes[$response_code]:$codes[-1];
	}



	public static function _getCustomerFields()
	{
		$fields = GetRegFields();
		$res = array('не указано:0');
		foreach($fields as $field){
			$res[] = xHtmlSpecialChars($field['reg_field_name'].':'.$field['reg_field_ID']);
		}
		return implode(',',$res);
	}

	public static function _getAlarmVariants()
	{
		$alarms = array();
		$alarms[] = array('title'=>QIWI_TXT_ALARM0,	'value'=>0);
		$alarms[] = array('title'=>QIWI_TXT_ALARM1,	'value'=>1);
		$alarms[] = array('title'=>QIWI_TXT_ALARM2,	'value'=>2);
		return $alarms;
	}

	public static function settings_PREFIX($settingsID)
	{
		if ( isset($_POST["save"]) && isset($_POST["setting".$settings_constant_name]) ){
			$_POST["setting".$settings_constant_name] = preg_replace('/[^#_A-Za-z0-9]/','',$_POST["setting".$settings_constant_name]);
			$_POST["setting".$settings_constant_name] = substr($_POST["setting".$settings_constant_name],0,10);
		}
		return setting_TEXT_BOX(0,$settingsID);
	}
}
?>