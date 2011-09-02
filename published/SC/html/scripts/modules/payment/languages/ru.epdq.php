<?php
	define('EPDQ_TTL', 'ePDQ Barclaycard Business');
	define('EPDQ_DSCR', 'Обработка кредитных карт через платежную систему ePDQ (http://www.barclaycardbusiness.co.uk/accepting_cards/phone_mail_internet/index.html).');
	
	define('EPDQ_TXT_AUTH', 'Auth');
	define('EPDQ_TXT_PREAUTH', 'PreAuth');

	define('EPDQ_TXT_PURCHASE', 'Оплатить заказ сейчас на сервере ePDQ');
	
	define('EPDQ_CFG_HOST_TTL', 'Хост');
	define('EPDQ_CFG_HOST_DSCR', 'Введите имя хоста ePDQ, на который мы будем отправлять информацию о заказе (это информацию Вы должны получить от ePDQ).<br />Не вводите префикс https:// и путь к файлу.');
	
	define('EPDQ_CFG_CLIENT_ID_TTL', 'Client ID');
	define('EPDQ_CFG_CLIENT_ID_DSCR', 'Ваш ePDQ Client ID');
	
	define('EPDQ_CFG_PASSPHRASE_TTL', 'Pass-Phrase');
	define('EPDQ_CFG_PASSPHRASE_DSCR', 'Pass-Phrase устанавливается в ePDQ Configuration Page  (https://secure2.mde.epdq.co.uk/cgi-bin/CcxBarclaysEpdqAdminTool.e). Обратите внимание, что Pass-Phrase отличается от Вашего пароля ePDQ аккаунта!');
	
	define('EPDQ_CFG_CHARGETYPE_TTL', 'Авторизация оплаты по карте');
	define('EPDQ_CFG_CHARGETYPE_DSCR', 'Auth - деньги автоматически зачисляются на Ваш счет. PreAuth - деньги на счете клиента резервируются, после чего Вы вручную можете определить, продолжать списание со счета клиента или нет (например, после проверки платежа).');
	
	define('EPDQ_CFG_TRANSCURRENCYCODE_TTL', 'Цифровой ISO-код валюты, выбранной выше');
	define('EPDQ_CFG_TRANSCURRENCYCODE_DSCR', 'Посмотрите полный <a href=http://www.iso.org/iso/en/prods-services/popstds/currencycodeslist.html target=_blank>список ISO-кодов валют</a>. Обратитесь в ePDQ для того, чтобы узнать список поддерживаемых валют.');
	
	define('EPDQ_CFG_TRANSCURRENCY_TTL', 'Валюта транзакций');
	define('EPDQ_CFG_TRANSCURRENCY_DSCR', 'Укажите валюту, в которую должна переводиться сумма заказа перед отправкой на сервер ePDQ.');
?>