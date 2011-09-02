<?php
define('IDEALBASIC_TTL', 'ING BANK - iDEAL Basic');
define('IDEALBASIC_DSCR', 'Credit card processing through ING Bank iDEAL payment gateway, Netherlands (www.ideal.nl)');

define('IDEALBASIC_TEST_TTL', 'Test mode');
define('IDEALBASIC_TEST_DSCR', '');

define('IDEALBASIC_SECRET_KEY_TTL', 'Secret key');
define('IDEALBASIC_SECRET_KEY_DSCR', 'Enter secret key as it had been entered in your iDEAL account. Please note that secret keys for test and production environments are different.');

define('IDEALBASIC_MERCHANT_ID_TTL', 'Merchant ID');
define('IDEALBASIC_MERCHANT_ID_DSCR', 'You iDEAL merchant ID');

define('IDEALBASIC_EUR_CURRENCY_TTL', 'Euro currency type');
define('IDEALBASIC_EUR_CURRENCY_DSCR', 'Order amount transferred to GSPay is denominated in EUR. Specify currency type in your shopping cart which is assumed as EUR (order amount will be calculated according to EUR exchange rate; if not specified exchange rate will be assumed as 1)');

define('IDEALBASIC_TXT_PURCHASE_DESCRIPTION', 'Order from "%s"');

define('IDEALBASIC_TXT_SUBMIT', 'Proceed to secure ING Bank iDEAL payment gateway');
define('IDEALBASIC_TXT_SHIPPINGTAX', 'Shipping and tax');

define('IDEALBASIC_BANK_TTL', 'Bank');
define('IDEALBASIC_BANK_TTL_DSCR', 'Select your iDEAL bank. Currently only ING and Rabobank are supported. This choice defines URL where order data will be sent for payment.');
?>