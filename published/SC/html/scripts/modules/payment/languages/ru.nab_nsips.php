<?php
	define('NAB_NSIPS_TTL', 'National Australia Bank');
	define('NAB_NSIPS_DSCR', 'Обработка кредитных карт. National Secure Internet Payment Service (www.national.com.au)');
	
	define('NAB_NSIPS_CFG_URL_TTL', 'URL отправки транзакций');
	define('NAB_NSIPS_CFG_URL_DSCR', 'Укажите адрес, на который будет отправляться информация о заказе покупателя.<br />Данную информацию Вы можете посмотреть в "Welcome Pack"-электронном письме от National Australia Bank');
	
	define('NAB_NSIPS_CFG_MERCHID_TTL', 'Merchant ID');
	define('NAB_NSIPS_CFG_MERCHID_DSCR', 'Уникальный 64-цифровой merchant ID, предоставленный в "Welcome Pack"-письме от National Australia Bank');
	
	define('NAB_NSIPS_CFG_CURRENCY_TTL', 'Австралийские доллары (AUD)');
	define('NAB_NSIPS_CFG_CURRENCY_DSCR', 'Сумма заказа, передаваемая в NAB, указывается в австралийских долларах (AUD). Выберите валюту из списка, которая представляет собой AUD - это необходимо для корректного пересчета суммы заказа. Если валюта не выбрана, сумма не будет пересчитываться');
?>