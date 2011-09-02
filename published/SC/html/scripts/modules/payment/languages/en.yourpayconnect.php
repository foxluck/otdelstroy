<?php
define('YOURPAYCONNECT_TTL',
	'YourPay Connect');
define('YOURPAYCONNECT_DSCR',
	'YourPay payment gateway integration (www.linkpoint.com)');

define('YOURPAYCONNECT_CFG_STORENAME_TTL',
	'YourPay shop name');
define('YOURPAYCONNECT_CFG_STORENAME_DSCR',
	'Please input your YourPay account ID');

define('YOURPAYCONNECT_CFG_INTEGRATION_TYPE_TTL',
	'Integration method');
define('YOURPAYCONNECT_CFG_INTEGRATION_TYPE_DSCR',
	'Please specify integration method:<br>1 - credit card (CC) information is collected on YourPay server;<br>2 - CC info is collected in your shopping cart;<br>3 - same as 2 plus CC info is saved into your database.');
	
define('YOURPAYCONNECT_CFG_USD_CURRENCY_TTL',
	'USD currency');
define('YOURPAYCONNECT_CFG_USD_CURRENCY_DSCR',
	'Order amount transferred to YourPay is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');

define('YOURPAYCONNECT_TXT_PAYMENT_FORM_HTML_1',
	'Credit card number');
define('YOURPAYCONNECT_TXT_PAYMENT_FORM_HTML_2',
	'Cardholder name');
define('YOURPAYCONNECT_TXT_PAYMENT_FORM_HTML_3',
	'Expires');
define('YOURPAYCONNECT_TXT_PAYMENT_FORM_HTML_4',
	'month');
define('YOURPAYCONNECT_TXT_PAYMENT_FORM_HTML_5',
	'year');
	
define('YOURPAYCONNECT_TXT_PAYMENT_PROCESS_1',
	'Please input credit card number');
define('YOURPAYCONNECT_TXT_PAYMENT_PROCESS_2',
	'Please input credit card holder name');
define('YOURPAYCONNECT_TXT_PAYMENT_PROCESS_3',
	'Please input credit card verification value (CVV)');
define('YOURPAYCONNECT_TXT_PAYMENT_PROCESS_4',
	'Please specify credit card expiration month');
define('YOURPAYCONNECT_TXT_PAYMENT_PROCESS_5',
	'Please specify credit card expiration year');
	
define('YOURPAYCONNECT_TXT_AFTER_PROCESSING_HTML_1',
	'Proceed to YourPay secure server to complete payment');
	
define('YOURPAYCONNECT_TXT_1',
	'1 - CC info is collected at YourPay secure server (recommended)');
define('YOURPAYCONNECT_TXT_2',
	'2 - CC info is collected in your shopping cart (on your web site)');
define('YOURPAYCONNECT_TXT_3',
	'3 - CC info is collected on your web site and saved in the database (in encryped way)');
?>