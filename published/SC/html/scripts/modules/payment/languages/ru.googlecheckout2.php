<?php
	define('GOOGLECHECKOUT2_TTL', 'Google Checkout');
	define('GOOGLECHECKOUT2_DSCR', 'Интеграция с <a href="http://checkout.google.com/sell?promo=sewebasyst" target="_blank">Google Checkout</a> (Level 2 integration). Для установки посмотрите <a href="http://www.webasyst.net/help/setup-google-checkout.htm" target="_blank">инструкции по установке Google Checkout</a> (на английском языке).<br>Google Checkout работает только для продавцов из США.');
	
	define('GOOGLECHECKOUT2_TXT_DONTCHANGE','Не изменять');
	
	define('GOOGLECHECKOUT2_CFG_MERCHANTID_TTL','Merchant ID');
	define('GOOGLECHECKOUT2_CFG_MERCHANTID_DSCR','Вы можете получить эту информацию в вашем аккаунте Гугл в разделе "Settings" -> "Integration"');
	
	define('GOOGLECHECKOUT2_CFG_MERCHANTKEY_TTL','Merchant key');
	define('GOOGLECHECKOUT2_CFG_MERCHANTKEY_DSCR','Вы можете получить эту информацию в вашем аккаунте Гугл в разделе "Settings" -> "Integration"');
	
	define('GOOGLECHECKOUT2_CFG_SANDBOX_TTL','Режим Sandbox');
	define('GOOGLECHECKOUT2_CFG_SANDBOX_DSCR','');
	
	define('GOOGLECHECKOUT2_CFG_ENABLED_TTL','Включить модуль');
	define('GOOGLECHECKOUT2_CFG_ENABLED_DSCR','');
	
	define('GOOGLECHECKOUT2_CFG_TRANSCURR_TTL','Валюта транзакций');
	define('GOOGLECHECKOUT2_CFG_TRANSCURR_DSCR','Стоимость заказа будет переконвертирована в указанную валюту, и данные будут отправлены в Гугл. Сейчас поддерживается только валюта USD.');
	
	define('GOOGLECHECKOUT2_TXT_DISCOUNT','Скидка');
	define('GOOGLECHECKOUT2_TXT_FREIGHT','Доставка');
	
	define('GOOGLECHECKOUT2_CFG_CHARGEORDER_TTL', 'Автоматически изменять статус оплаченных заказов');
	define('GOOGLECHECKOUT2_CFG_CHARGEORDER_DSCR', 'Когда вы выполняете действия с заказов в вашем аккаунте в Гугл, статус заказа в вашем интернет-магазине также будет изменяться.');
	
	define('GOOGLECHECKOUT2_CFG_CHARGEDORDERSTATUS_TTL', 'Статус заказа после оплаты');
	define('GOOGLECHECKOUT2_CFG_CHARGEDORDERSTATUS_DSCR', '');
	
	define('GOOGLECHECKOUT2_CFG_SHIPPEDORDERSTATUS_TTL', 'Статус доставленных заказов');
	define('GOOGLECHECKOUT2_CFG_SHIPPEDORDERSTATUS_DSCR', '');
	
	define('GOOGLECHECKOUT2_CFG_ARCHIVEDORDERSTATUS_TTL', 'Статус обработанных заказов');
	define('GOOGLECHECKOUT2_CFG_ARCHIVEDORDERSTATUS_DSCR', '');
	
	define('GOOGLECHECKOUT2_CFG_CALCULATESHIPTAX_TTL', 'Включить расчет стоимости доставки и налога');
	define('GOOGLECHECKOUT2_CFG_CALCULATESHIPTAX_DSCR', 'Гугл отправляет запрос на расчет стоимости в ваш интернет-магазин, и в течение 3 секунд этот запрос должен быть обработан. Если в вашем магазине используется модули доставки, которые требует более 3-х секунд на расчет стоимости, не включайте эту опцию.');
	
	define('GOOGLECHECKOUT2_CFG_SENDORDERNOTIFYCATION_TTL', 'Отправлять уведомление о заказе');
	define('GOOGLECHECKOUT2_CFG_SENDORDERNOTIFYCATION_DSCR', 'Гугл отправляет покупателю уведомление о заказе. Включите эту опцию, что уведомление также отправлялось и из вашего интернет-магазина (таким образом покупатель получит два уведомления).');
	
	define('GOOGLECHECKOUT2_CFG_SHIPPIGNRESCTRICTIONS_TTL','Deliver zones'); 
	define('GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_DSCR','deliver description'); 
	define('GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_ENTIRE_WORLD','Entire world');
	define('GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_US_ONLY','US only'); 
?>