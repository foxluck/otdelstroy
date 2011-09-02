<?php
define('IDEALBASIC_TTL', 'ING BANK - iDEAL Basic');
define('IDEALBASIC_DSCR', 'Обработка кредитных карт через ING Bank iDEAL payment gateway, Netherlands (www.ideal.nl)');

define('IDEALBASIC_TEST_TTL', 'Тестовый режим');
define('IDEALBASIC_TEST_DSCR', '');

define('IDEALBASIC_SECRET_KEY_TTL', 'Секретный ключ');
define('IDEALBASIC_SECRET_KEY_DSCR', 'Введите секретный ключ как в вашем аккаунте iDEAL. Учтите, что ключи различаются для тестового и рабочего режимов оплаты');

define('IDEALBASIC_MERCHANT_ID_TTL', 'Merchant ID');
define('IDEALBASIC_MERCHANT_ID_DSCR', 'You iDEAL merchant ID');

define('IDEALBASIC_EUR_CURRENCY_TTL', 'Выберите евро');
define('IDEALBASIC_EUR_CURRENCY_DSCR', 'Order amount transferred to GSPay is denominated in EUR. Specify currency type in your shopping cart which is assumed as EUR (order amount will be calculated according to EUR exchange rate; if not specified exchange rate will be assumed as 1)');

define('IDEALBASIC_TXT_PURCHASE_DESCRIPTION', 'Order from "%s"');

define('IDEALBASIC_TXT_SUBMIT', 'Proceed to secure ING Bank iDEAL payment gateway');
define('IDEALBASIC_TXT_SHIPPINGTAX', 'Доставка и налоги');

define('IDEALBASIC_BANK_TTL', 'Банк');
define('IDEALBASIC_BANK_TTL_DSCR', 'Модулем поддерживается интеграция с ING и Rabobank. Выбор банка влияет на URL, на который будут отправлены данные о заказе для совершения платежа.');

?>