<?php
define('CVERISIGNLINK_TTL',
	'PayPal Payflow Link');
define('CVERISIGNLINK_DSCR',
	'Credit card processing through PayPal Payflow payment gateway (formerly known as VeriSign). Payflow Link integration method (https://www.paypal.com/cgi-bin/webscr?cmd=_payflow-link-overview-outside).');
	
define('CVERISIGNLINK_CFG_LOGIN_TTL',
	'Merchant Login ID');
define('CVERISIGNLINK_CFG_LOGIN_DSCR',
	'Please input your PayPal Payflow Login');
define('CVERISIGNLINK_CFG_PARTNER_TTL',
	'Partner');
define('CVERISIGNLINK_CFG_PARTNER_DSCR',
	'The ID provided to you by the authorized PayPal Reseller who registered you for the Payflow Pro service. If you purchased your account directly from PayPal, use "PayPal". If purchased from VeriSign, use "VeriSign" (without quotes).');
define('CVERISIGNLINK_CFG_TRANSTYPE_TTL',
	'Transaction type');
define('CVERISIGNLINK_CFG_TRANSTYPE_DSCR',
	'');
define('CVERISIGNLINK_CFG_USD_CURRENCY_TTL',
	'USD currency type');
define('CVERISIGNLINK_CFG_USD_CURRENCY_DSCR',
	'Order amount posted to PayPal is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');
	
define('CVERISIGNLINK_TXT_GETTRANSTYPEOPTIONS_1',
	'Sale / Payment (authorization & automatic settlement)');
define('CVERISIGNLINK_TXT_GETTRANSTYPEOPTIONS_2',
	'Credit card authorization only');
	
define('CVERISIGNLINK_TXT_AFTER_PROCESSING_HTML_1',
	'Proceed to secure PayPal Payflow payment gateway');
?>