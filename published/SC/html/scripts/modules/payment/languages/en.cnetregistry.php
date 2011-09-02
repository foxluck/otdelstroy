<?php
define('CNETREGISTRY_TTL', 
	'NetRegistry');
define('CNETREGISTRY_DSCR', 
	'NetRegistry.com.au<br>Credit card information is collected on your web site and then transferred to NetRegistry server. Requires valid SSL certificate.');
	
define('CNETREGISTRY_CFG_LOGIN_TTL', 
	'NetRegistry MID');
define('CNETREGISTRY_CFG_LOGIN_DSCR', 
	'Your NetRegistry merchant login ID');
define('CNETREGISTRY_CFG_PASSWORD_TTL', 
	'NetRegistry Password');
define('CNETREGISTRY_CFG_PASSWORD_DSCR', 
	'Please input your NetRegistry password');
define('CNETREGISTRY_CFG_DOLLAR_CURRENCY_TTL', 
	'USD currency');
define('CNETREGISTRY_CFG_DOLLAR_CURRENCY_DSCR', 
	'Order amount transferred to NetRegistry is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');
define('CNETREGISTRY_CFG_SAVE_CC_INFORMATION_TTL', 
	'Save customer`s credit card data');
define('CNETREGISTRY_CFG_SAVE_CC_INFORMATION_DSCR', 
	'Enable this option if you would like to save customer`s credit card data in the database (it is stored in encrypted way)');

define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_1', 
	'Credit card number');
define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_2', 
	'Expires');
define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_3', 
	'month');
define('CNETREGISTRY_TXT_PAYMENT_FORM_HTML_4', 
	'year');
	
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_1', 
	'Please input credit card number');
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_2', 
	'Please specify credit card expiration month');
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_3', 
	'Please specify credit card expiration year');
define('CNETREGISTRY_TXT_PAYMENT_PROCESS_4', 
	'Couldn\'t connect to NetRegistry payment gateway');
	
define('CNETREGISTRY_TXT_NR_TRANSACTION_1', 
	'Error processing transaction');
define('CNETREGISTRY_TXT_NR_TRANSACTION_2', 
	'Failed to process this transaction');
define('CNETREGISTRY_TXT_NR_TRANSACTION_3', 
	'Failed to process this transaction');
?>