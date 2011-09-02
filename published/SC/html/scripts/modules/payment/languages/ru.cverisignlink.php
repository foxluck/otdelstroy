<?php
define('CVERISIGNLINK_TTL',
	'VeriSign');
define('CVERISIGNLINK_DSCR',
	'Обработка кредитных карт через платежную систему VeriSign (www.verisign.com). Метод Payflow Link.');
	
define('CVERISIGNLINK_CFG_LOGIN_TTL',
	'VeriSign Login');
define('CVERISIGNLINK_CFG_LOGIN_DSCR',
	'Введите ваш идентификатор в системе VeriSign');
define('CVERISIGNLINK_CFG_PARTNER_TTL',
	'VeriSign partner');
define('CVERISIGNLINK_CFG_PARTNER_DSCR',
	'Введите идентификатор вашего партнера VeriSign (эта информация предоставляется реселлером VeriSign, через которого вы подключены). Если вы подключлись непосредственно в VeriSign, введите <b>VeriSign</b>');
define('CVERISIGNLINK_CFG_TRANSTYPE_TTL',
	'Режим работы');
define('CVERISIGNLINK_CFG_TRANSTYPE_DSCR',
	'');
define('CVERISIGNLINK_CFG_USD_CURRENCY_TTL',
	'Доллары США');
define('CVERISIGNLINK_CFG_USD_CURRENCY_DSCR',
	'Сумма заказа, передаваемая в VeriSign, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться');
	
define('CVERISIGNLINK_TXT_GETTRANSTYPEOPTIONS_1',
	'Sale (мгновенное списание денег)');
define('CVERISIGNLINK_TXT_GETTRANSTYPEOPTIONS_2',
	'Авторизация');
	
define('CVERISIGNLINK_TXT_AFTER_PROCESSING_HTML_1',
	'Продолжить оплату на сервере VeriSign');
?>