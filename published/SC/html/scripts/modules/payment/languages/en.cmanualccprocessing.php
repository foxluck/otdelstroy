<?php
define('CMANUALCCPROCESSING_TTL',
	'Manual credit card processing');
define('CMANUALCCPROCESSING_DSCR',
	'Customer\'s credit card is stored in the database (encryped)');
	
define('CMANUALCCPROCESSING_CFG_REQUESTCVV_TTL',
	'Request CVV');
define('CMANUALCCPROCESSING_CFG_REQUESTCVV_DSCR',
	'Request card verification value (CVV)');
	
define('CMANUALCCPROCESSING_CFG_EMAIL4CCNUM_TTL',
	'Email address where we should send customers credit card data');
define('CMANUALCCPROCESSING_CFG_EMAIL4CCNUM_DSCR',
	'Due to security reasons, we do not save full credit card number in your store database. Instead of this we save only half of the card number to the database, and send the rest of the number to you by email to the address you enter here.');

define('CMANUALCCPROCESSING_TXT_EMAIL_SUBJECT',
	'Order #%ORDER_NUM% card information');
	
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_1',
	'Credit card number');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_2',
	'Cardholder name');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_3',
	'Expires');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_4',
	'month');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_5',
	'year');
	
define('CMANUALCCPROCESSING_TXT_payment_process_1',
	'Please input credit card number');
define('CMANUALCCPROCESSING_TXT_payment_process_2',
	'Please input credit card holder name');
define('CMANUALCCPROCESSING_TXT_payment_process_3',
	'Please input credit card verification value (CVV)');
define('CMANUALCCPROCESSING_TXT_payment_process_4',
	'Please specify credit card expiration month');
define('CMANUALCCPROCESSING_TXT_payment_process_5',
	'Please specify credit card expiration year');
?>