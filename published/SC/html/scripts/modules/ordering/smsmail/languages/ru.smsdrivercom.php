<?php
define('SMSDRIVERCOM_CFG_LOGIN_TTL',
	'Логин');
define('SMSDRIVERCOM_CFG_LOGIN_DSCR',
	'Логин от Вашей учетной записи SMSDriver');
	
define('SMSDRIVERCOM_CFG_PASSWORD_TTL',
	'Пароль');
define('SMSDRIVERCOM_CFG_PASSWORD_DSCR',
	'Пароль к Вашей учетной записи SMSDriver');
	
define('SMSDRIVERCOM_CFG_UNICODE_TTL',
	'Конвертировать сообщение в юникод');
define('SMSDRIVERCOM_CFG_UNICODE_DSCR',
	'Если опция включена, максимальная длина сообщения - 70 символов');
	
define('SMSDRIVERCOM_CFG_ORIGINATOR_TTL',
	'Отправитель сообщения, как он будет выглядеть на телефоне получателя');
define('SMSDRIVERCOM_CFG_ORIGINATOR_DSCR',
	'Отправитель может состоять из цифр - в этом случае его длина ограничена 15-ю символами, или буквенно-цифровым (например, название вашей компании) - в этом случае длина ограничена 11-ю символами. Русские буквы в имени отправителя не разрешены.');
?>