<?php
/**
 * VKontakte payment implementation
 *
 * @connect_module_class_name VKontaktePayment
 * @package DynamicModules
 * @subpackage Payment
 * @author WebAsyst Team
 * @see http://vkontakte.ru/pages.php?o=-1&p=Схема+сервиса+Оплата+товаров
 *
 */
class VKontaktePayment extends PaymentModule
{
	var $type = PAYMTD_TYPE_REPLACE;
	var $language = 'rus';
	var $default_logo = './images_common/vkontakte/vkontakte.ico';
	var $SingleInstall = true;

	function _initVars()
	{

		parent::_initVars();
		$this->title 		= "Вконтакте";
		$this->description 	= "Экспорт продуктов в каталог «Вконтакта» и возможность покупки прямо из сети. Инструкции по подключению: <a href='http://www.webasyst.ru/support/help/shop-script-vkontakte-integration.html' target='_blank'>http://www.webasyst.ru/support/help/shop-script-vkontakte-integration.html</a>";
		$this->sort_order 	= 0;
		$this->Settings = array(
		//"CONF_PAYMENTMODULE_VKONTAKTE_ENABLED",
				/*"CONF_PAYMENTMODULE_VKONTAKTE_MERCHANT_ID",*/
		//		"CONF_PAYMENTMODULE_VKONTAKTE_APP_ID",
				/*"CONF_PAYMENTMODULE_VKONTAKTE_SHARED_SECRET",
				"CONF_PAYMENTMODULE_VKONTAKTE_MODE",
				"CONF_PAYMENTMODULE_VKONTAKTE_PAY",*/
		//		"CONF_PAYMENTMODULE_VKONTAKTE_SHIPPING",
				/*"CONF_PAYMENTMODULE_VKONTAKTE_ORDERSTATUS",
				"CONF_PAYMENTMODULE_VKONTAKTE_RUB",*/
				"CONF_PAYMENTMODULE_VKONTAKTE_HELLO",
		);
	}

	function _initSettingFields()
	{

		/*$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_MERCHANT_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Идентификатор интернет-магазина', 
			'settings_description' 	=> 'Присваивается вашему магазину «Вконтактом» при регистрации', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);	*//*	$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_APP_ID'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> 'Идентификатор приложения',
			'settings_description' 	=> 'Присваивается вашему приложению «Вконтактом» при регистрации',
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
			);
			*//*
		$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_SHARED_SECRET'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Защищенный ключ', 
			'settings_description' 	=> 'Ключ из настроек магазина внутри «Вконтакта»', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_MODE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> 'Тестовый режим', 
			'settings_description' 	=> 'Должен соответствовать одноименной настройке в профиле вашего магазина во «Вконтакте»', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		
		$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_PAY'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> 'Принимать вконтактовские рубли на основной витрине магазина', 
			'settings_description' 	=> 'Если включить, кнопка «Оформить заказ через «Вконтакт» будет показываться в корзине на <em>главной</em> витрине вашего магазина', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);*/
		//Принимать вконтактовские рубли на основной витрине магазина
		//Если включить, кнопка «Оформить заказ через «Вконтакт» будет показываться в корзине на <em>главной</em> витрине вашего магазина
	
		/*
		 $this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_SHIPPING'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> 'Расчет стоимости доставки',
			'settings_description' 	=> 'Выполнять рассчеты стоимости доставки на стороне магазина',
			'settings_html_function' 	=> 'setting_CHECK_BOX(',
			'sort_order' 			=> 1,
			);
			*//*
		$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> 'Статус заказа',
			'settings_description' 	=> 'Этот статус будет автоматически установлен для каждого заказа после <em>успешной</em> оплаты на стороне «Вконтакта»',
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_RUB'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> 'Рубли',
			'settings_description' 	=> 'Валюта магазина, соответствующая вконтактовским рублям',
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
			'sort_order' 			=> 1,
		);
		*/
		$this->SettingsFields['CONF_PAYMENTMODULE_VKONTAKTE_HELLO'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> 'Описание', 
			'settings_description' 		=> 'Опциональный HTML-код, которой будет выводиться на витрине (главной странице) магазина как приложения для соцсети', 
			'settings_html_function' 	=> 'setting_TEXT_AREA(', 
			'sort_order' 				=> 1,
		);
	}

	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array(
			'settings_title'=>'Адрес вконтактовской витрины магазина',
			'settings_description'=>'Скопируйте этот адрес и сохраните его в настройках вашего магазина внутри «Вконтакта»',
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getStoreUrl())
			.'">',
			);/*
		$customProperties[] = array(
			'settings_title'=>'Адрес обратного вызова',
			'settings_description'=>'Скопируйте этот адрес и сохраните его в настройках вашего магазина внутри «Вконтакта»',
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getDirectTransactionResultURL('check',array($this->ModuleConfigID,__FILE__)))
			.'">',
			);*/
		return $customProperties;
	}

	public function getCheckoutButton()
	{
		$params = array();
		$params['merchant_id'] = $this->_getSettingValue('CONF_PAYMENTMODULE_VKONTAKTE_MERCHANT_ID');
		if(!$params['merchant_id']){
			return "Не задан идентификатор интернет-магазина";
		}
		$params['testmode'] = $this->_getSettingValue('CONF_PAYMENTMODULE_VKONTAKTE_MODE')?'true':'false';
		$Register = &Register::getInstance();
		$store_mode = $Register->get('store_mode');
		$frame_enabled = false;

		$params['success_url'] = $this->getTransactionResultURL('success');
		$params['fail_url'] = $this->getTransactionResultURL('failure');

		if($frame_enabled&&($store_mode == 'vkontakte')){
			/*	$res = <<<JS
			 <script src="http://vkontakte.ru/js/api/xd_connection.js?2" type="text/javascript"></script>
			 JS;*/
		}else{
			$res = <<<JS
		<script type="text/javascript" src="http://vkontakte.ru/js/api/merchant.js?12" charset="windows-1251"></script>
JS;
		}
		$res .= <<<JS
		<script type="text/javascript">
		var prepareOrder = function() {
		  var result = {
		    merchant_id: {$params['merchant_id']},
		    fail_url: '{$params['fail_url']}',
		    success_url: '{$params['success_url']}',
		    required_fields: 'recipient_phone',
		    testmode: {$params['testmode']},
		    items: [],
		    custom: {}
		  };
JS;

		$cartContent = cartGetCartContent();
		//$resDiscount = dscGetCartDiscounts( $cartContent["total_price"], (isset($_SESSION["log"]) ? $_SESSION["log"] : "") );
		//$cart_discount_show = max(0,$resDiscount['other_discounts']['cu']);
		//$coupon_discount_show = max(0,$resDiscount['coupon_discount']['cu']);
		$currency_id = defined('CONF_PAYMENTMODULE_VKONTAKTE_RUB')?constant('CONF_PAYMENTMODULE_VKONTAKTE_RUB'):0;
		if($currency_id>0){
			$currency = new Currency();
			$currency->loadByCID($currency_id);
			$rate = floatval($currency->currency_value);
		}else{
			$rate = 1.0;
		}
		
		$total_amount = 0.0;
		$total_amountUC = 0.0;

		foreach($cartContent['cart_content'] as $content_item){
			$total_amount += ($content_item['costUC']*$rate*$content_item['quantity']);
			$total_amountUC += ($content_item['costUC']*$content_item['quantity']);
			if($total_amount > 15000){
				return ($store_mode == 'vkontakte')?"Общая сумма вашего заказа не должна превышать 15000 рублей для оплаты во Вконтакте":'';
			}
			$id = $content_item['productID'].':'.$content_item['variants'];
			//TODO escape strings
			$content_item['name'] = htmlentities($content_item['name'],ENT_QUOTES,'utf-8');
			//$content_item['brief_description'] = str_replace(array("\r\n","\n","\r"),'\n',$content_item['brief_description']);
			$content_item['brief_description'] = htmlentities(strip_tags(str_replace(array("\r\n","\n","\r"),'\n',$content_item['brief_description'])),ENT_QUOTES,'utf-8');
			unset($property);
			if($content_item['thumbnail_url']){
				$content_item['thumbnail_url'] = preg_replace('@(?<!(http:))[/]{2,}@','/',BASE_URL.$content_item['thumbnail_url']);
			}
			$content_item['vk_price'] = number_format(($content_item['costUC']*$rate), 2, '.', '');
				
			$res .= <<<JS
			result.items.push({
				id: '{$id}',
				name: '{$content_item['name']}',
				description: '{$content_item['brief_description']}',
				currency: 'RUB',
				price: {$content_item['vk_price']},
				quantity: {$content_item['quantity']},
				photo_url: '{$content_item['thumbnail_url']}'
			});
JS;
		}
		
		if(defined('CONF_MINIMAL_ORDER_AMOUNT')&&constant('CONF_MINIMAL_ORDER_AMOUNT')&&($total_amountUC<constant('CONF_MINIMAL_ORDER_AMOUNT'))){
			return ($store_mode == 'vkontakte')?(translate("cart_min_order_amount_not_reached").'&nbsp;'.show_price(CONF_MINIMAL_ORDER_AMOUNT)):''; 
		}
		
		
		
		/*
		if($cart_discount_show>0){
		$res .= <<<JS
		result.items.push({
		id: 'cart_discount',
		name: 'Скидка',
		description: '',
		currency: 'RUB',
		price: -{$cart_discount_show},
		quantity: 1,
		});
		JS;
		}
		if($coupon_discount_show>0){
		$res .= <<<JS
		result.items.push({
		id: 'cart_discount',
		name: 'Купон',
		description: 'Ваша скидка по купону',
		currency: 'RUB',
		price: -{$coupon_discount_show},
		quantity: 1,
		});
		JS;
		}*/
		$session_id = self::encode(session_id());
		$cart ='';
		$ip = 0;
		$text = htmlentities(strip_tags(translate('str_checkout').(($params['testmode']=='true')?' в тестовом режиме':'')),ENT_QUOTES,'utf-8');
		$res .= <<<JS

		result.custom[1] = '{$session_id}';
		return result;
	};
JS;
		if($frame_enabled&&($store_mode == 'vkontakte')){
			$res .= <<<JS
			</script>
			<input type="button" id="vkontakte_show_payment_box" value="{$text}">
			<script type="text/javascript">
			//VK.init(function() {
				var button = document.getElementById('vkontakte_show_payment_box');
				if(button){
					button.onclick = function(){
						VK.callMethod('showMerchantPaymentBox', prepareOrder());
					};
				}
			/*}, function(){
				// API initialization failed
				alert('API initialization failed');
			});
			
			; */
			</script>
JS;
		}else{
			$res .= <<<JS
			var button_params = {
				type:'round',
				text:'{$text}'
			};
			document.write(VK.Merchant.button(prepareOrder,button_params));
			</script>
JS;
		}
		return $res;
	}

	/**
	 */
	function after_processing_html( $orderID )
	{
		$order = ordGetOrder( $orderID );
		$order_amount = $order["order_amount"];

		//$exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE');
		if ( (float)$exhange_rate == 0 )
		$exhange_rate = 1;

		$order_amount = round($order_amount/$exhange_rate,2);

		$is_MSIE = (isset($_SERVER['HTTP_USER_AGENT'])&&(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false))?true:false;

		$res = "";

		//$description = str_replace("[orderID]",$orderID,$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_PAYMENTS_DESC'));
		if($is_MSIE){
			//			$description = translit($description);
		}

		//merchant_id
		//fail_url
		//success_url
		//items[]
		//	id	=	productID ?
		//	name
		//	description
		//	currency
		//	price
		//	quantity
		//	photo_url
		//	digital
		//custom[]
		//required_fields
		//testmode

		$params = array();
		$params['success_url'] = $this->getTransactionResultURL('success');
		$params['fail_url'] = $this->getTransactionResultURL('failure');
		$params['merchant_id'] = $this->_getSettingValue('CONF_PAYMENTMODULE_VKONTAKTE_MERCHANT_ID');
		$params['testmode'] = $this->_getSettingValue('CONF_PAYMENTMODULE_VKONTAKTE_MODE')?'true':'false';
		$res = <<<JS
		<script type="text/javascript" src="http://vkontakte.ru/js/api/merchant.js?12" charset="windows-1251"></script>
		<script type="text/javascript">
		var prepareOrder = function() {
		  var result = {
		    merchant_id: {$params['merchant_id']},
		    fail_url: '{$params['fail_url']}',
		    success_url: '{$params['success_url']}',
		    required_fields: 'recipient_phone',
		    testmode: {$params['testmode']},
		    items: [],
		    custom: {}
		  };
JS;
		$content = ordGetOrderContent($orderID);
		foreach($content as $content_item){
			$res .= <<<JS
			result.items.push({
				id: 1,
				name: '{$content_item['name']}',
				description: '{$content_item['brief_description']}',
				currency: 'RUB',
				price: 300,
				quantity: 2,
			});
JS;
		}
		$text = htmlentities(strip_tags(translate('str_checkout')),ENT_QUOTES,'utf-8');
		$res .= <<<JS
		result.custom[1] = 'Первое дополнительное значение';
		result.custom[5] = 'Пятое дополнительное значение';
		return result;
	}
	var button_params = {
		type:'round',
		text:'{$text}',
		text_right:'Контактиге'
	}
	document.write(VK.Merchant.button(prepareOrder,button_params));
	</script>
JS;
		return $res;
	}

	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend')
	{
		$log = '';
		if($source == 'handler'){
			if($this->validateResultKey(array($this->ModuleConfigID,__FILE__))){
				$sharedSecret = $this->_getSettingValue('CONF_PAYMENTMODULE_VKONTAKTE_SHARED_SECRET');
				$request = Env::Post();
				if($sharedSecret){//check callback sign
					if($sign = Env::getData($request,'sig')){
						unset($request['sig']);
					}
					$sign = strtolower($sign);
					ksort($request);
					$string = '';
					foreach($request as $key=>$value){
						$string .= "{$key}={$value}";
					}
					$string .= $sharedSecret;
					$hash = strtolower(md5($string));
					if($hash != $sign){
						$this->sendResponceCode(10);
					}else{
						$merchant_id = (int)$this->_getSettingValue('CONF_PAYMENTMODULE_VKONTAKTE_MERCHANT_ID');
						if((int)$request['merchant_id']!= $merchant_id){
							$this->sendResponceCode(102,true,"Запрос не предназначен этому магазину","Проверьте настройки параметра callback URL в настройках магазина #{$request['merchant_id']}/#{$merchant_id}");
						}else{
							$test_mode = false;
							switch($request['notification_type']){

								//проверка наличия и стоимости товаров
								case 'check-items-availability-test':
									$test_mode = true;
								case 'check-items-availability':{
									$this->checkAvailability($request,$test_mode);
									break;
								}

								//резервирование товара
								case 'item-reservation-test':
									$test_mode = true;
								case 'item-reservation':{
									$this->sendResponceCode(202,true,"Действие '{$request['notification_type']}' не поддерживается платежным модулем","Поддержка действия будет реализована в будущих версиях");
									break;
								}

								//отмена резервирование товара
								case 'cancel-item-reservation-test':
									$test_mode = true;
								case 'cancel-item-reservation':{
									$this->sendResponceCode(202,true,"Действие '{$request['notification_type']}' не поддерживается платежным модулем","Поддержка действия будет реализована в будущих версиях");
									break;
								}

								//запрос на вычисление стоимости доставки
								case 'calculate-shipping-cost-test':
									$test_mode = true;
								case 'calculate-shipping-cost':{
									//TODO complete code or use properly message
									//get current cart
									//calculate shipping cost
									$this->sendResponceCode(203,true,"Действие '{$request['notification_type']}' не поддерживается платежным модулем","Поддержка действия будет реализована в будущих версиях");
									break;
								}

								//изменение статуса заказа
								case 'order-state-change-test':
									$test_mode = true;
								case 'order-state-change':{
									//place order
									//cleanup current cart content
									if(($cart_key = Env::getData($request,'custom_1'))&&($session_id = self::decode($cart_key))){
										$current_session_id = session_id();
										session_write_close();
										session_id($session_id);
										session_start();
										$cartEntry = new ShoppingCart();
										$cartEntry->cleanCurrentCart();
									}else{
										$cartEntry = new ShoppingCart();
										$cartEntry->cleanCurrentCart();
									}
									//
									$this->orderStateChange($request,$test_mode);
									$this->sendResponceCode(204,true,"Действие '{$request['notification_type']}' не поддерживается платежным модулем","Поддержка действия будет реализована в будущих версиях");
									//
									if($current_session_id){
										session_write_close();
										session_id($current_session_id);
										session_start();
									}
									//$this->sendResponceCode(103,true,"Ошибка получения ключа корзины покупателя","Обратитесь к разработчикам платежного модуля");
									break;
								}
								default:{
									$this->sendResponceCode(11,true,"неизвестное действие '{$request['notification_type']}'","Обратитесь к разработчикам платежного модуля для консультаций");
									break;
								}
							}
						}
					}
					/**
					 * order.get – возвращает список последних заказов
					 * order.getById – возвращает информацию об отдельном заказе
					 * order.changeState – изменяет состояние заказа
					 */
				}else{
					$this->sendResponceCode(101,true,"Не задан секретный ключ в настройках платежного модуля","Необходимо настроить платежный модуль магазина");
				}
			}else{
				$this->sendResponceCode(100,true,"Неверный callback URL","Проверьте настройки параметра callback URL в настройках магазина #{$request['merchant_id']}");
			}
		}elseif(($source == 'frontend')&&($transaction_result == 'success')){
			$cartEntry = new ShoppingCart();
			$cartEntry->cleanCurrentCart();
		}
		return parent::transactionResultHandler($transaction_result,$message.$log,$source);
	}

	private function sendResponceCode($code = 1002,$critical = true,$description = '',$techMessage = '')
	{
		$responce_codes = array(
		1=>'общая ошибка',
		2=>'временная ошибка базы данных',
		10=>'несовпадение вычисленной и переданной подписи',
		11=>'параметры запроса не соответствуют спецификации;',
		//в запросе нет необходимых полей;
		//другие ошибки целостности запроса.
		20 =>'товара не существует',
		21 =>'покупателя не существует',
		22 =>'﻿некорректная сумма заказа',
		23 =>'﻿некорректный метод доставки',
		24 =>'﻿товара нет в наличии',
		//100-999=>'ошибки с номерами 100-999 задаются продавцом, при возврате таких ошибок обязательно должно присутствовать текстовое описание ошибки',
		1000 =>'неверные данные формы; означает, что форма, отправленная в окно оплаты, была составлена не по спецификации.',
		1001 =>'не удалось разобрать ответ на уведомление; означает, что ответ на уведомление не соответствует спецификации.',
		1002 =>'неизвестная ошибка',
		);
		$code = max(1,min(1002,$code));
		if(isset($responce_codes[$code])){
			$description = trim($responce_codes[$code]." ".$description);
		}
		$critical = $critical?'true':'false';
		//TODO escape chars
		header("Content-Type: text/xml; encoding=utf-8");
		print <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>
<failure>
	<error-code>{$code}</error-code>
	<error-description>{$description}</error-description>
	<error-parameters>{$techMessage}</error-parameters>
	<critical>{$critical}</critical>
</failure>
EOF;
		$this->addDebugLog("code={$code}\t{$description}\t{$techMessage}\n".var_export(array('GET'=>$_GET,'POST'=>$_POST),true));
		exit;
	}

	private function checkAvailability($requestData = array(), $test = true)
	{
		$xml = <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>
<items>
EOF;
		//TODO convert price into RUB
		$currency_id = defined('CONF_PAYMENTMODULE_VKONTAKTE_RUB')?constant('CONF_PAYMENTMODULE_VKONTAKTE_RUB'):0;
		if($currency_id>0){
			$currency = new Currency();
			$currency->loadByCID($currency_id);
			$rate = floatval($currency->currency_value);
		}else{
			$rate = 1.0;
		}
		$items = scanArrayKeysForID($requestData,array('item_currency','item_currency_str','item_id','item_price','item_quantity'));
		foreach($items as  $item_id=>$item){
			if($item['item_id']){
				$product = new Product();
				$product_info = explode(':',$item['item_id']);
				$productID = (int)array_shift($product_info);
				if($product->loadByID($productID)){
					$in_stock = max(0,min($product->in_stock,$item['item_quantity']));
					$price = $rate*$product->Price;
					$xml .= <<<EOF
	
	<item id="{$item['item_id']}">
		<price currency="RUB">{$price}</price>
		<quantity>{$in_stock}</quantity>
	</item>
EOF;
				}else{
					$xml .= <<<EOF
	
	<item id="{$item['item_id']}" last="1">
		<price currency="{$item['item_currency_str']}">{$item['item_price']}</price>
		<quantity>0</quantity>
	</item>
EOF;
				}
			}
		}
		$xml .= <<<EOF
</items>
EOF;
		header("Content-Type: text/xml; encoding=utf-8");
		print $xml;
		$this->addDebugLog("XML:\n{$xml}\n".var_export(array('GET'=>$_GET,'POST'=>$_POST,'items'=>$items),true));
		exit;
		// last="либо 1 либо не указывается"
	}

	/**
	 *
	 * @return int orderID
	 */
	private function orderStateChange($requestData = array(),$test = true)
	{
		$order_info = array();
		/*customerID, order_time, customer_ip, statusID, order_amount, currency_code, currency_value, ".
		 "customer_firstname, customer_lastname, customer_email, ".
		 shipping_firstname,
		 shipping_lastname,
		 +shipping_country,
		 shipping_state,
		 +shipping_zip,
		 +shipping_city,
		 +shipping_address,
		 "billing_firstname, billing_lastname, billing_country, billing_state, billing_zip, billing_city, billing_address,".
		 "source
		 */
		//Костыль на костыле костылем погоняет

		//TODO convert price from RUB into current currency


		$order_info['order_time'] = Time::dateTime();
		$order_info['customer_ip'] = stGetCustomerIP_Address(); //BAD - it's VK server host
		$order_info['customer_email'] = $requestData['shipping_email'];//

		$customer_name = explode(' ',$requestData['user_name'],2);

		$order_info['customer_firstname'] = $customer_name[0];//
		$order_info['customer_lastname'] = $customer_name[1];//

		$currency_id = defined('CONF_PAYMENTMODULE_VKONTAKTE_RUB')?constant('CONF_PAYMENTMODULE_VKONTAKTE_RUB'):0;
		if($currency_id>0){
			$currency = new Currency();
			$currency->loadByCID($currency_id);
			$currency_value = floatval($currency->currency_value);
		}else{
			$currency_value = 1.0;
		}
		if($requestData['currency_str']!='RUB'){
			$this->sendResponceCode(301,true,"Invalid currency_str {$requestData['currency_str']} (must be RUB)");
		}
		$order_info['currency_code'] = $requestData['currency_str'];//
		$order_info['order_amount'] = floatval($requestData['amount'])/$currency_value;//
		$order_info['currency_value'] = $currency_value;

		$order_info['shipping_city'] = $requestData['shipping_city'];//
		$order_info['shipping_country'] = $requestData['shipping_country_str'];//
		$order_info['shipping_zip'] = $requestData['shipping_code'];//
		$order_info['shipping_address'] = $requestData['shipping_street'].' '.$requestData['shipping_house'].' '.$requestData['shipping_flat'];//

		$shipping_name = explode(' ',$requestData['recipient_name'],2);

		$order_info['shipping_firstname'] = $shipping_name[0];//
		$order_info['shipping_lastname'] = $shipping_name[1];//

		$order_info['shipping_type'] = $requestData['shipping_method'];//

		$order_info['customers_comment'] = '#'.$requestData['order_id'].' '.' id:'.$requestData['user_id'].' '.$requestData['order_comment'];//

		/*	$order_info[''] = $requestData[''];//
		 $order_info[''] = $requestData[''];//
		 $order_info[''] = $requestData[''];//
		 $order_info[''] = $requestData[''];//
		 */
		//CONF_ORDSTATUS_PENDING
		$order_info['payment_type'] = $this->title;
		$order_info['payment_module_id'] = $this->get_id();

		$order_info['billing_firstname'] = $order_info['customer_firstname'];
		$order_info['billing_lastname'] = $order_info['customer_lastname'];
		//'billing_country' => $billing_info['country_name'],
		$order_status = $this->_getSettingValue('CONF_PAYMENTMODULE_VKONTAKTE_ORDERSTATUS');
		$order_info['statusID'] = ($order_status>0)?$order_status:CONF_ORDSTATUS_PENDING;

		//register new customer or use existing
		if(isset($_SESSION['log'])&&$_SESSION['log']){
			$customer = Customer::getAuthedInstance();
			$customerID = $customer->customerID;
			if(!$customer->vkontakte_id){
				$customer->vkontakte_id = $requestData['user_id'];
				$customer->save();
			}
		}
		if(!$customer){
			$sql = 'SELECT `customerID` FROM `?#CUSTOMERS_TABLE` WHERE `vkontakte_id`=?';
			$customerID = (int) db_phquery_fetch(DBRFETCH_FIRST,$sql,$requestData['user_id']);
		}
		if(!$customerID){
			$customer = new Customer();
			$customer->Email = $requestData['shipping_email'];
			$customer->first_name = $order_info['customer_firstname'];
			$customer->last_name = $order_info['customer_lastname'];
			$customer->vkontakte_id = $requestData['user_id'];
			$customer->save();
			$customerID = $customer->customerID;
		}

		$order_info['customerID'] = $customerID;

		//shipping cost
		$items = scanArrayKeysForID($requestData,array('item_currency','item_currency_str','item_id','item_price','item_quantity'));
		$cost = 0;
		foreach($items as &$item){
			$item['item_price'] = floatval($item['item_price'])/$currency_value;
			$cost += ($item['item_price'] * $item['item_quantity']);
			if($item['item_id']){
				$product = new Product();
				$product_info = explode(':',$item['item_id']);
				$productID = (int)array_shift($product_info);
				if($product->loadByID($productID)){
					$item['item_name'] = $product->name;
				}
			}
		}
		unset($item);

		$order_info['shipping_cost'] = max(0,$order_info['order_amount']-$cost);
		$order_info['order_amount'] = round($order_info['order_amount'],2);

		$dbq = '
			INSERT ?#ORDERS_TABLE (?&) VALUES (?@)
		';
		db_phquery($dbq, array_keys($order_info), $order_info);
		$orderID = db_insert_id();

		//Order content workaround
		if($current_cart){
			//move current cart into ordered
			$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);

			$Register = &Register::getInstance();
			/*@var $Register Register*/
			//shipping

			$checkoutEntry->customers_comment(Env::getData($requestData,'order_comment'));
			$orderID = $checkoutEntry->emulate_ordOrderProcessing();
			$cartEntry = new ShoppingCart();
			$cartEntry->cleanCurrentCart();
		}else{
			//create new order content
			foreach($items as $item){
				if($item['item_id']){
					$product_info = explode(':',$item['item_id']);
					$productID = (int)array_shift($product_info);
					$sql = 'INSERT INTO `?#SHOPPING_CART_ITEMS_TABLE` (`productID`) VALUES (?)';
					db_phquery($sql,$productID);
					$item_id = db_insert_id();

					$sql = 'INSERT INTO `?#ORDERED_CARTS_TABLE` (`itemID`, `orderID`, `name`, `Price`, `Quantity`, `tax`) VALUES (?, ?, ?, ?, ?, ?)';
					db_phquery($sql,$item_id,$orderID,$item['item_name'],floatval($item['item_price']),$item['item_quantity'],0);
				}
			};
		}


		stChangeOrderStatus($orderID, $order_info['statusID'], translate('ordr_comment_orderplaced').($test?' в режиме тестирования':''));
		if(isset($requestData['new_state'])&&($requestData['new_state']=='chargeable')){
			stChangeOrderStatus($orderID, $order_info['statusID'], "Заказ (#{$requestData['order_id']}) оплачен ".($test?' в режиме тестирования':''));
		}

		$smarty_mail = new ViewSC();
		$smarty_mail->template_dir = DIR_TPLS."/email";
		_sendOrderNotifycationToAdmin( $orderID, $smarty_mail, 0);
		//it's unsuported by VK future
		//_sendOrderNotifycationToCustomer( $orderID, $smarty_mail, $order_info['customer_email'], regGetLoginById($order_info['customerID']),'', '', 0);


		header("Content-Type: text/xml; encoding=utf-8");
		$xml = <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>
<success>
	<order-id>{$requestData['order_id']}</order-id>
	<merchant-order-id>{$orderID}</merchant-order-id>
</success>
EOF;
		print $xml;
		$this->addDebugLog("XML:\n{$xml}\n".var_export(array('GET'=>$_GET,'POST'=>$_POST,'items'=>$items),true));
		exit;
	}

	/**
	 * calcualte shipping methods
	 * @todo allow calculate shipping cost via postcode
	 * @return void
	 */
	private function calculateShippingCost()
	{
		/*
		 //get shipping methods
		 <?xml version="1.0" encoding="UTF-8" ?>
		 <shipping-methods>
		 <тип-доставки id="идентификатор варианта" name="имя варианта">
		 <price currency="код валюты">стоимость доставки</price>
		 </тип-доставки>
		 ...
		 </shipping-methods>
		 **/
	}

	/**
	 * Simple XOR string encrypt
	 * @param $string string
	 * @param $key string
	 * @return string
	 */
	private static function xorEncrypt( $string, $key = null)
	{
		if(is_null($key)){
			$key = md5(__FILE__);
		}
		$key_length = mb_strlen( $key );
		$string_length = mb_strlen( $string );
		for ( $i = 0; $i < $string_length; $i++ ){
			$string[$i] = chr(ord( $string[$i] ) ^ ord( $key[$i % $key_length] ));
		}
		return $string;
	}

	private static function encode($string,$key = null)
	{
		return base64_encode(self::xorEncrypt($string,$key));
	}

	private static function decode($string,$key = null)
	{
		return self::xorEncrypt(base64_decode($string),$key);
	}


	function getDirectTransactionResultURL($transaction_result,$params = array(),$https = false)
	{
		$url = parent::getDirectTransactionResultURL($transaction_result,$params,$https);
		if(!SystemSettings::is_hosted()){
			$url = str_replace('/published/SC/html/scripts/callbackhandlers/','/shop/callbackhandlers/',$url);
		}
		return $url;
	}


	private function addDebugLog($text)
	{
		if(true||$this->_getSettingValue('CONF_YANDEXCPP_DEVELOPER')){
			$text .= "\nMemory: ".sprintf('%0.2fMb',memory_get_usage(true)/1048576);
			$this->LogFile = DIR_LOG.'/VKontakte.log';
			$this->log_mode |= LOGMODE_MSG|LOGMODE_ERROR;
			$this->_log(LOGMODE_MSG,$text);
		}
	}
	
	private function getStoreUrl()
	{
		$scURL = str_replace(array("http://","https://"),array('',''), trim( BASE_WA_URL ));
		if(SystemSettings::is_hosted()){
			$scURL .= 'shop/';
		}
		return "http://".$scURL.(MOD_REWRITE_SUPPORT?'vkontakte/':'?store_mode=vkontakte');
	}
}
?>