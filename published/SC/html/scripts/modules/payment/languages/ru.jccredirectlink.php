<?php
	define('JCCRL_TTL', 'JCC');
	define('JCCRL_DSCR', 'Обработка кредитных карт - JCC payment gateway (Кипр). Веб-сайт: www.jcc.com.cy. Метод интеграции Redirect Link.');
	
	define('JCCRL_CFG_MERID_TTL', 'Merchant ID');
	define('JCCRL_CFG_MERID_DSCR', '');
	
	define('JCCRL_CFG_MERPWD_TTL', 'Merchant Checkout Password');
	define('JCCRL_CFG_MERPWD_DSCR', 'Ваш пароль');
	
	define('JCCRL_CFG_ACQID_TTL','Acquirer ID');
	define('JCCRL_CFG_ACQID_DSCR','Введите значение acquirer ID, предоставленное JCC');
	
	define('JCCRL_CFG_CUR_SHOP_TTL','Валюта транзакций');
	define('JCCRL_CFG_CUR_SHOP_DSCR','Укажите валюту, в которую должна переводиться сумма заказа перед отправкой на сервер JCC. Рекомендуется использовать Кипрские Фунты.');
	
	define('JCCRL_CFG_CUR_ISONUM_TTL','Цифровой ISO-код валюты, выбранной выше');
	define('JCCRL_CFG_CUR_ISONUM_DSCR','Посмотрите полный <a href=http://www.iso.org/iso/en/prods-services/popstds/currencycodeslist.html target=_blank>список ISO-кодов валют</a>. Введите 196, если Вы используете кипрские фунты. Обратитесь в JCC для того, чтобы узнать список поддерживаемых валют.');
	
	define('JCCRL_SUBMIT_BTN', 'Перейти к оплате на безопасном сервере');

	define('JCCRL_CFG_CAPTURE_TTL', 'Авторизация оплаты по карте');
	define('JCCRL_CFG_CAPTURE_DSCR', 'Автомат - деньги автоматически зачисляются на Ваш счет. Ручная - деньги на счете клиента резервируются, после чего Вы вручную можете определить, продолжать списание со счета клиента или нет (например, после проверки платежа).');
	
	define('JCCRL_TXT_CAPTURE_A', 'Автомат');
	define('JCCRL_TXT_CAPTURE_M', 'Ручная');
	
	define('JCCRL_CFG_URL_TTL', 'URL отправки транзакции');
	define('JCCRL_CFG_URL_DSCR', 'Укажите адрес, по которому будет отправлена информация о заказе в JCC');
?>