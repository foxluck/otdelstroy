<?php
define('CAUTHORIZENETAIM_TTL', 
	'Authorize.Net AIM');
define('CAUTHORIZENETAIM_DSCR', 
	'Обработка кредитных карт через платежную систему Authorize.Net по методу Advanced Integration Method (AIM).<br>Информация о кредитной карте вводится покупателем на вашем сайте и затем передается на сервер Authorize.Net.');
	
define('CAUTHORIZENETAIM_CFG_LOGIN_TTL', 
	'Authorize.Net ID');
define('CAUTHORIZENETAIM_CFG_LOGIN_DSCR',
	'Введите ваш идентификатор в системе Authorize.Net<br>Эта информация сохраняется в зашифрованном виде');
define('CAUTHORIZENETAIM_CFG_TRANKEY_TTL', 
	'Authorize.Net transaction key');
define('CAUTHORIZENETAIM_CFG_TRANKEY_DSCR', 
	'Введите transaction key, который вы можете получить в интерфейсе Authorize.Net<br>Эта информация сохраняется в зашифрованном виде');
define('CAUTHORIZENETAIM_CFG_TESTMODE_TTL', 
	'Тестовый режим');
define('CAUTHORIZENETAIM_CFG_TESTMODE_DSCR', 
	'');
define('CAUTHORIZENETAIM_CFG_SAVE_CC_INFORMATION_TTL', 
	'Сохранять информацию о кредитной карте');
define('CAUTHORIZENETAIM_CFG_SAVE_CC_INFORMATION_DSCR', 
	'Включите эту опцию, если вы хотели бы сохранять информацию о кредитной карте в базе данных магазина (информация сохраняется в зашифрованном виде)');
define('CAUTHORIZENETAIM_CFG_AUTHORIZATION_TYPE_TTL', 
	'Тип авторизации');
define('CAUTHORIZENETAIM_CFG_AUTHORIZATION_TYPE_DSCR', 
	'Выберите способ авторизации кредитной карты покупателя');
define('CAUTHORIZENETAIM_CFG_WFSS_MERCHANT_TTL', 
	'Пользователь Wells Fargo Secure Source');
define('CAUTHORIZENETAIM_CFG_WFSS_MERCHANT_DSCR', 
	'Включите, если вы используете Wells Fargo Secure Source для приема платежей');

define('CAUTHORIZENETAIM_TXT_1', 
	'Только авторизация (Authorization only)');
define('CAUTHORIZENETAIM_TXT_2', 
	'Авторизация и списание (Authorize and capture)');
define('CAUTHORIZENETAIM_TXT_3', 
	'Prior auth capture');
define('CAUTHORIZENETAIM_TXT_4', 
	'Тип плательщика');
define('CAUTHORIZENETAIM_TXT_5', 
	'Юридическое лицо');
define('CAUTHORIZENETAIM_TXT_6', 
	'Частное лицо');
define('CAUTHORIZENETAIM_TXT_7', 
	'Компания');
define('CAUTHORIZENETAIM_TXT_8', 
	'Телефон');
define('CAUTHORIZENETAIM_TXT_9', 
	'Факс');
define('CAUTHORIZENETAIM_TXT_10', 
	'(введите номер телефона, по которому мы сможем позвонить вам для проверки платежа)');
define('CAUTHORIZENETAIM_TXT_11', 
	'Оплата кредитной картой');
define('CAUTHORIZENETAIM_TXT_12', 
	'Номер кредитной карты');
define('CAUTHORIZENETAIM_TXT_13', 
	'Срок действия карты');
define('CAUTHORIZENETAIM_TXT_14', 
	'месяц');
define('CAUTHORIZENETAIM_TXT_15', 
	'год');
define('CAUTHORIZENETAIM_TXT_16', 
	'Оплата электронным чеком eCheck.Net');
define('CAUTHORIZENETAIM_TXT_17', 
	'Bank account number:');
define('CAUTHORIZENETAIM_TXT_18', 
	'Bank name:');
define('CAUTHORIZENETAIM_TXT_19', 
	'Account type:');
define('CAUTHORIZENETAIM_TXT_20', 
	'Checking');
define('CAUTHORIZENETAIM_TXT_21', 
	'Savings');
define('CAUTHORIZENETAIM_TXT_22', 
	'Bank account number:');
define('CAUTHORIZENETAIM_TXT_23', 
	'Name under account:');
define('CAUTHORIZENETAIM_TXT_24', 
	'<b>eCheck payment verification</b><br>(please fill in your tax ID <b>OR</b> driver\'s license information)');
define('CAUTHORIZENETAIM_TXT_25', 
	' Tax ID:');
define('CAUTHORIZENETAIM_TXT_26', 
	' Driver\'s license number:');
define('CAUTHORIZENETAIM_TXT_27', 
	'Driver\'s license state:');
define('CAUTHORIZENETAIM_TXT_28', 
	'Driver\'s license owner\'s date of birth:');
define('CAUTHORIZENETAIM_TXT_29', 
	'Please input company name');
define('CAUTHORIZENETAIM_TXT_30', 
	'Please input your phone number');
define('CAUTHORIZENETAIM_TXT_31', 
	'Please input credit card number');
define('CAUTHORIZENETAIM_TXT_32', 
	'Please input credit card verification value (CVV)');
define('CAUTHORIZENETAIM_TXT_33', 
	'Please specify credit card expiration month');
define('CAUTHORIZENETAIM_TXT_34', 
	'Please specify credit card expiration year');
define('CAUTHORIZENETAIM_TXT_35', 
	'Please input bank routing number');
define('CAUTHORIZENETAIM_TXT_36', 
	'Please input bank account number');
define('CAUTHORIZENETAIM_TXT_37', 
	'Please input bank name');
define('CAUTHORIZENETAIM_TXT_38', 
	'Please input name under account');
define('CAUTHORIZENETAIM_TXT_39', 
	'Please input your tax ID');
define('CAUTHORIZENETAIM_TXT_40', 
	'Please input your driver\'s license number');
define('CAUTHORIZENETAIM_TXT_41', 
	'Please input state of driver\'s license state issue');
define('CAUTHORIZENETAIM_TXT_42', 
	'Please input date of birth');
	
define('CAUTHORIZENETAIM_TXT_DEFAULT' ,'По умолчанию');

define('CAUTHORIZENETAIM_CFG_ORDERSTATUS_TTL', 'Присваивать следующий статус заказу в случае удачной обработке платежа');
define('CAUTHORIZENETAIM_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');

define('CAUTHORIZENETAIM_CFG_DECLINE_ECHECK_TTL','Магазин не
принимает оплату по eCheck');
define('CAUTHORIZENETAIM_CFG_DECLINE_ECHECK_DSCR','');
?>