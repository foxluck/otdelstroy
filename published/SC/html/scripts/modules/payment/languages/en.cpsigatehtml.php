<?php
define('CPSIGATEHTML_TTL',
	'PSiGate HTML Posting');
define('CPSIGATEHTML_DSCR',
	'www.psigate.com');
	
define('CPSIGATEHTML_CFG_MERCHANTID_TTL',
	'PSiGate Merchant ID');
define('CPSIGATEHTML_CFG_MERCHANTID_DSCR',
	'Please input your PSiGate merchant ID as provided to you in the welcome email');
define('CPSIGATEHTML_CFG_CHARGETYPE_TTL',
	'Charge type');
define('CPSIGATEHTML_CFG_CHARGETYPE_DSCR',
	'Please indicate credit card charge type<br>(Preauth - reserves the funds on a credit card; Sale - charges the card immediately)');
define('CPSIGATEHTML_CFG_TESTMODE_TTL',
	'Test mode');
define('CPSIGATEHTML_CFG_TESTMODE_DSCR',
	'');
define('CPSIGATEHTML_CFG_REQUEST_CC_INFO_TTL',
	'Request credit card information on your shopping cart side');
define('CPSIGATEHTML_CFG_REQUEST_CC_INFO_DSCR',
	'Enable this checkbox only if you have SSL certificate. Disable it if your want credit card information to be collected on PSiGate secure web site. Note that you should let PSiGate know about the method you choose (contact them for details)');
define('CPSIGATEHTML_CFG_USD_CURRENCY_TTL',
	'USD currency type');
define('CPSIGATEHTML_CFG_USD_CURRENCY_DSCR',
	'Order amount transferred to PSiGate is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');

	
define('CPSIGATEHTML_TXT_GETCHARGETYPEOPTIONS_1',
	'Preauth');
define('CPSIGATEHTML_TXT_GETCHARGETYPEOPTIONS_2',
	'Sale');
	
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_1',
	'Credit card number:');
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_2',
	'Expires:');
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_3',
	'month');
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_4',
	'year');
	
define('CPSIGATEHTML_TXT_PAYMENT_PROCESS_1',
	'Please input credit card number');
define('CPSIGATEHTML_TXT_PAYMENT_PROCESS_2',
	'Please specify credit card expiration month');
define('CPSIGATEHTML_TXT_PAYMENT_PROCESS_3',
	'Please specify credit card expiration year');
	
define('CPSIGATEHTML_TXT_AFTER_PROCESSING_HTML_1',
	'Proceed to PSiGate payment gateway');
?>