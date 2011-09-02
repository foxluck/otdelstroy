<?php
	define('PAYFLOWPRO_TTL', 'PayPal Payflow Pro');
	define('PAYFLOWPRO_DSCR', 'Credit card processing through PayPal Payflow payment gateway (formerly known as VeriSign). Payflow Pro integration method (https://www.paypal.com/cgi-bin/webscr?cmd=_payflow-pro-overview-outside).');
	
	define('PAYFLOWPRO_TXT_CDCURRENCY', 'Customer defined currency');
	define('PAYFLOWPRO_TXT_SALE', 'Sale / Payment (authorization & automatic settlement)');
	define('PAYFLOWPRO_TXT_AUTH', 'Credit card authorization only');
	define('PAYFLOWPRO_TXT_CCNUMBER', 'Credit Card Number');
	define('PAYFLOWPRO_TXT_CVV2', 'CVV (3-digit code on the back of the card)');
	define('PAYFLOWPRO_TXT_EXPDATE', 'Expires');
	define('PAYFLOWPRO_TXT_NORES', 'Failed executing Payflow software provided by PayPal. Check executing permissions and server configuration.');
	define('PAYFLOWPRO_TXT_ERRORPROCESSING', 'Error processing transaction.');
	define('PAYFLOWPRO_TXT_RESCODE', 'Result code');
	define('PAYFLOWPRO_TXT_RESMSG', 'Response message');
	define('PAYFLOWPRO_TXT_DONTCHANGE', 'Do not change');
	
	define('PAYFLOWPRO_CFG_TESTMODE_TTL', 'Test mode');
	define('PAYFLOWPRO_CFG_TESTMODE_DSCR', '');
	
	define('PAYFLOWPRO_CFG_PARTNER_TTL', 'Partner');
	define('PAYFLOWPRO_CFG_PARTNER_DSCR', 'The ID provided to you by the authorized PayPal Reseller who registered you for the Payflow Pro service. If you purchased your account directly from PayPal, use "PayPal". If purchased from VeriSign, use "VeriSign" (without quotes).');
	
	define('PAYFLOWPRO_CFG_PWD_TTL', 'Password');
	define('PAYFLOWPRO_CFG_PWD_DSCR', 'The 6- to 32-character password that you defined while registering for the account.');
	
	define('PAYFLOWPRO_CFG_TRANSTYPE_TTL', 'Transation type');
	define('PAYFLOWPRO_CFG_TRANSTYPE_DSCR', '');
	
	define('PAYFLOWPRO_CFG_USER_TTL', 'User');
	define('PAYFLOWPRO_CFG_USER_DSCR', 'Optional. If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions.');
	
	define('PAYFLOWPRO_CFG_VENDOR_TTL', 'Merchant Login ID');
	define('PAYFLOWPRO_CFG_VENDOR_DSCR', 'Your merchant login ID that you created when you registered for the Payflow Pro account.');
	
	define('PAYFLOWPRO_CFG_TRANSCURRENCY_TTL', 'Transaction currency');
	define('PAYFLOWPRO_CFG_TRANSCURRENCY_DSCR', 'You may select a currecy type in which order amount sent to payment gateway should be recalculated (according to the rates defined in your store settings).');
	
	define('PAYFLOWPRO_CFG_SUCCESS_ORDERSTATUS_TTL', 'Order status after successful transaction');
	define('PAYFLOWPRO_CFG_SUCCESS_ORDERSTATUS_DSCR', 'If you would like approved orders to be automatically assigned a particular order status please select the status. Select "Default" to assign successful orders default new orders status (which is configured in "Configuration" screen of back end).');
?>