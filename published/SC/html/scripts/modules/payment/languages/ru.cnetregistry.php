<?php
define('CNETREGISTRY_TTL', 
	'NetRegistry');
define('CNETREGISTRY_DSCR', 
	'Обработка кредитных карт через платежную систему NetRegistry.com.au<br>Информация о кредитной карте вводится покупателем на вашем сайте и затем передается на сервер NetRegistry.com.au.');
	
define('CNETREGISTRY_CFG_LOGIN_TTL', 
	'NetRegistry MID');
define('CNETREGISTRY_CFG_LOGIN_DSCR', 
	'Ваш идентификатор в системе NetRegistry');
define('CNETREGISTRY_CFG_PASSWORD_TTL', 
	'NetRegistry Password');
define('CNETREGISTRY_CFG_PASSWORD_DSCR', 
	'Ваш пароль в системе NetRegistry');
define('CNETREGISTRY_CFG_DOLLAR_CURRENCY_TTL', 
	'Доллары США');
define('CNETREGISTRY_CFG_DOLLAR_CURRENCY_DSCR', 
	'Сумма заказа, передаваемая в NetRegistry, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться');
define('CNETREGISTRY_CFG_SAVE_CC_INFORMATION_TTL', 
	'Сохранять информацию о кредитной карте');
define('CNETREGISTRY_CFG_SAVE_CC_INFORMATION_DSCR', 
	'Включите эту опцию, если вы хотели бы сохранять информацию о кредитной карте в базе данных магазина (информация сохраняется в зашифрованном виде)');

define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_1', 
	'Credit card number');
define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_2', 
	'Expires');
define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_3', 
	'month');
define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_4', 
	'year');
	
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_1', 
	'Введите номер кредитной карты');
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_2', 
	'Введите месяц окончания срока действия карты');
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_3', 
	'Введите год окончания срока действия карты');
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_4', 
	'Ошибка при подключении к серверу NetRegistry');
	
define('CNETREGISTRY_TXT_NR_TRANSACTION_1', 
	'Error processing transaction');
define('CNETREGISTRY_TXT_NR_TRANSACTION_2', 
	'Failed to process this transaction');
define('CNETREGISTRY_TXT_NR_TRANSACTION_3', 
	'Failed to process this transaction');
?>