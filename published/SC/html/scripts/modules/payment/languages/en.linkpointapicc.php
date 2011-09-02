<?php
	define('LPAPICC_TTL', 'LinkPoint/YourPay API');
	define('LPAPICC_DSCR', 'LinkPoint/YourPay payment gateway integration (www.linkpoint.com / www.yourpay.com). LinkPoint API integration method. This is more "tight" integration than LinkPoint Connect, however it requires that you have SSL certificate installed for your domain name, because customers input credit card data on your online store side.');
	
	define('LPAPICC_TXT_AVAILABLECREDITCARDS', 'We accept');
	define('LPAPICC_TXT_CCNUMBER', 'Credit Card Number:');
	define('LPAPICC_TXT_CVV', 'CVV (3 digit number on the back of your card):');
	define('LPAPICC_TXT_EXPDATE','Expires:');
	
	define('LPAPICC_TXT_TESTGOOD','Test mode (ACCEPTED transaction status is always returned)');
	define('LPAPICC_TXT_TESTDECLINE','Test modes (DECLINED transaction status is always returned)');
	define('LPAPICC_TXT_LIVE','Live');
	define('LPAPICC_TXT_DEFAULT', 'Default');
	
	define('LPAPICC_MSG_UNKNOWNCCTYPE', 'Unknown credit card type');
	define('LPAPICC_MSG_UNAVAILABLECCTYPE', 'We do not accept "%cardname%" credit cards. Sorry for the inconvenience.');
	
	define('LPAPICC_CFG_MERCHNUMBER_TTL', 'Store Number');
	define('LPAPICC_CFG_MERCHNUMBER_DSCR', 'Your LinkPoint/YourPay store number. Generally a six- to ten-digit number assigned at merchant account setup');
	
	define('LPAPICC_CFG_CERTPATH_TTL', 'PEM certificate');
	define('LPAPICC_CFG_CERTPATH_DSCR', 'You can download your PEM certification file from your account at <a href="https://www.linkpointcentral.com" target="_blank" class="standard">LinkPoint Central</a>');
	
	define('LPAPICC_CFG_MODE_TTL', 'Mode');
	define('LPAPICC_CFG_MODE_DSCR', 'Choose between test and live modes');
	
	define('LPAPICC_CFG_HOST_TTL', 'Posting URL');
	define('LPAPICC_CFG_HOST_DSCR', 'Please indicate a host where your store will send customer credit card data. You should have received it when subscribing for LinkPoint account.');
	
	define('LPAPICC_CFG_PAYMENTACTION_TTL', 'Authorization type');
	define('LPAPICC_CFG_PAYMENTACTION_DSCR', 'Credit card authorization type. Please contact LinkPoint for details on which method is more suitable for you.');
	
	define('LPAPICC_CFG_CVV_TTL', 'Request CVV');
	define('LPAPICC_CFG_CVV_DSCR', 'Please select whether to request customer a CVV value or not');
	
	define('LPAPICC_CFG_AVAILABLECREDITCARDS_TTL', 'Available credit card types');
	define('LPAPICC_CFG_AVAILABLECREDITCARDS_DSCR', 'Please select credit card types which you accept in your store');
	
	define('LPAPICC_CFG_ORDERSTATUS_TTL', 'Append approved orders following status');
	define('LPAPICC_CFG_ORDERSTATUS_DSCR', 'If you would like approved orders to be automatically assigned a particular order status please select the status. Select "Default" to assign LinkPoint API orders default new orders status (which is configured in "Configuration" screen of back end)');
	
	define('LPAPICC_CFG_CURRENCY_TTL', 'USD currency type');
	define('LPAPICC_CFG_CURRENCY_DSCR', 'Order amount transferred to LinkPoint web site is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1).');
?>