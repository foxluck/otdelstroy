<?php
define('CPAYPAL_TTL',
	'PayPal Website Payments Standard');
define('CPAYPAL_DSCR',
	'Basic PayPal module which is very simple to install - enter your email address and start accepting PayPal payments.  Visit PayPal website to learn more about <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_wp-standard-overview-outside" target=_blank>Website Payments Standard</a>');
	
define('CPAYPAL_CFG_MERCHANT_EMAIL_TTL',
	'Your PayPal account email');
define('CPAYPAL_CFG_MERCHANT_EMAIL_DSCR',
	'Please input your PayPal account email address');
	
define('CPAYPAL_TXT_AFTER_PROCESSING_HTML_1',
	'Pay now!');
	
	
define('CPAYPAL_CFG_MODE_TTL','Mode');
define('CPAYPAL_CFG_MODE_DSCR','Select "Sandbox" to test PayPal payments, and "Live" for real transactions');
define('CPAYPAL_TXT_TEST','Sandbox');
define('CPAYPAL_TXT_LIVE','Live');

define('CPAYPAL_CFG_CURRENCY_TTL', 'USD currency type');
define('CPAYPAL_CFG_CURRENCY_DSCR', 'Order amount transferred to PayPal web site is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');


?>