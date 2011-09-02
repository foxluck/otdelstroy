<?php
	define('INNOVATIVEGTW_TTL', 'Innovative Gateway');
	define('INNOVATIVEGTW_DSCR', 'Обработка кредитных карт через через платежную систему Innovative Gateway (www.innovativegateway.com)');
	
	define('INNOVATIVEGTW_CFG_USERNAME_TTL', 'Имя пользователя');
	define('INNOVATIVEGTW_CFG_USERNAME_DSCR', 'Данные Вашего аккаунта в Innovative Gateway');
	
	define('INNOVATIVEGTW_CFG_PWD_TTL', 'Пароль');
	define('INNOVATIVEGTW_CFG_PWD_DSCR', 'Данные Вашего аккаунта в Innovative Gateway');
	
	define('INNOVATIVEGTW_CFG_TRANTYPE_TTL', 'Тип транзакции');
	define('INNOVATIVEGTW_CFG_TRANTYPE_DSCR', 'Preauth - сумма резервируется на счете клиента, но не переводится на Ваш счет автоматически; Sale - сумма автоматически переводится на Ваш счет. Для получения более подробной информации обратитесь в Innovative Gateway.');
	
	define('INNOVATIVEGTW_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
	define('INNOVATIVEGTW_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');
	
	define('INNOVATIVEGTW_CFG_SHOPCUR_TTL', 'Доллары США');
	define('INNOVATIVEGTW_CFG_SHOPCUR_DSCR', 'Сумма заказа, передаваемая на сервер платежной системы, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться');
	
	define('INNOVATIVEGTW_TXT_PREAUTH', 'Preauth');
	define('INNOVATIVEGTW_TXT_SALE', 'Sale');
	define('INNOVATIVEGTW_TXT_CCTYPE', 'Тип кредитной карты');
	define('INNOVATIVEGTW_TXT_CCNUMBER', 'Номер карты');
	define('INNOVATIVEGTW_TXT_CVV', 'CVV'); 
	define('INNOVATIVEGTW_TXT_EXPDATE', 'Истекает');
	define('INNOVATIVEGTW_TXT_DEFAULT', 'По умолчанию');
?>