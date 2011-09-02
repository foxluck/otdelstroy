<?php
define('CLINKPOINT_TTL',
	'LinkPoint/YourPay Connect');
define('CLINKPOINT_DSCR',
	'LinkPoint/YourPay payment gateway integration (www.linkpoint.com / www.yourpay.com). LinkPoint Connect integration method.');

define('CLINKPOINT_CFG_STORENAME_TTL',
	'LinkPoint/YourPay Store Number');
define('CLINKPOINT_CFG_STORENAME_DSCR',
	'Please enter your LinkPoint/YourPay store number');

define('CLINKPOINT_CFG_INTEGRATION_TYPE_TTL',
	'Integration method');
define('CLINKPOINT_CFG_INTEGRATION_TYPE_DSCR',
	'Please specify integration method:<br>1 - credit card (CC) information is collected on LinkPoint server;<br>2 - CC info is collected in your shopping cart;<br>3 - same as 2 plus CC info is saved into your database.');
	
define('CLINKPOINT_CFG_POSTING_URL_TTL',
	'Posting URL');
define('CLINKPOINT_CFG_POSTING_URL_DSCR',
	'Please input order data posting URL provided to you by LinkPoint/YourPay (you should have received it by email)');

define('CLINKPOINT_CFG_USD_CURRENCY_TTL',
	'USD currency');
define('CLINKPOINT_CFG_USD_CURRENCY_DSCR',
	'Order amount transferred to LinkPoint is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');

define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_1',
	'Credit card number');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_2',
	'Cardholder name');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_3',
	'Expires');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_4',
	'month');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_5',
	'year');
	
define('CLINKPOINT_TXT_PAYMENT_PROCESS_1',
	'Please input credit card number');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_2',
	'Please input credit card holder name');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_3',
	'Please input credit card verification value (CVV)');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_4',
	'Please specify credit card expiration month');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_5',
	'Please specify credit card expiration year');
	
define('CLINKPOINT_TXT_AFTER_PROCESSING_HTML_1',
	'Proceed to LinkPoint secure server to complete payment');
	
define('CLINKPOINT_TXT_1',
	'1 - CC info is collected at LinkPoint secure server (recommended)');
define('CLINKPOINT_TXT_2',
	'2 - CC info is collected in your shopping cart (on your web site)');
define('CLINKPOINT_TXT_3',
	'3 - CC info is collected on your web site and saved in the database (in encryped way)');
?>