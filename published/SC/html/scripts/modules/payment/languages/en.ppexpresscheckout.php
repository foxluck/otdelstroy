<?php
	define('PPEXPRESSCHECKOUT_TTL', 'PayPal Website Payments Pro - Express Checkout');
	define('PPEXPRESSCHECKOUT_DSCR', 'Integration with <a href="https://www.paypal.com/us/mrb/pal=XREZHZ8R3F4YY" target="_blank">PayPal Express Checkout</a>.<br />Please read our <a href="http://www.webasyst.net/help/setup-paypal-express-checkout.htm" target="_blank">detailed description on how to enable PayPal Express Checkout</a>.');
	define('PPEXPRESSCHECKOUT_TXT_ERRORCALLER','Could not create CallerServices instance: ');
	define('PPEXPRESSCHECKOUT_TXT_TEST','Sandbox');
	define('PPEXPRESSCHECKOUT_TXT_LIVE','Live');
	define('PPEXPRESSCHECKOUT_TXT_DEFAULT', 'Default');
	define('PPEXPRESSCHECKOUT_TXT_ERROR_CHECKOUT', 'Please contact store administrator for details.');
	
	define('PPEXPRESSCHECKOUT_CFG_ENABLED_TTL', 'Enable module');
	define('PPEXPRESSCHECKOUT_CFG_ENABLED_DSCR', 'If disabled, PayPal Express Checkout option will not be offered to customers on shopping cart details page');
	
	define('PPEXPRESSCHECKOUT_CFG_USERNAME_TTL', 'API Username');
	define('PPEXPRESSCHECKOUT_CFG_USERNAME_DSCR', 'Enter the API username provided to you when generating API Access Certificate for Website Payments Pro');
	
	define('PPEXPRESSCHECKOUT_CFG_PASSWORD_TTL', 'Password');
	define('PPEXPRESSCHECKOUT_CFG_PASSWORD_DSCR', 'Enter the password that you set up when you signed up for Website Payments Pro');
	
	define('PPEXPRESSCHECKOUT_CFG_CERTPATH_TTL', 'PayPal certificate');
	define('PPEXPRESSCHECKOUT_CFG_CERTPATH_DSCR', 'Download API certificate from your PayPal account and then specify certificate file name in this box');
	
	define('PPEXPRESSCHECKOUT_CFG_MODE_TTL', 'Mode');
	define('PPEXPRESSCHECKOUT_CFG_MODE_DSCR', 'Select "Sandbox" to test PayPal payments, and "Live" for real transactions');
	
	define('PPEXPRESSCHECKOUT_CFG_PAYMENTACTION_TTL', 'Payment authorization method');
	define('PPEXPRESSCHECKOUT_CFG_PAYMENTACTION_DSCR', 'How you want to obtain payment:<br><br><b>Sale</b> indicates that this is a final sale for which you are requesting payment.<br><b>Authorization</b> or <b>Order</b> indicate that this payment is subject to settlement with PayPal Authorization & Capture.');
	
	define('PPEXPRESSCHECKOUT_CFG_ORDERSTATUS_TTL', 'Append approved orders following status');
	define('PPEXPRESSCHECKOUT_CFG_ORDERSTATUS_DSCR', 'If you would like PayPal Expess Checkout orders to be automatically assigned a particular status please select the status. Select "Default" to assign default new orders status (which is configured in "Configuration" screen of back end).');
	
	define('PPEXPRESSCHECKOUT_CFG_NOSHIPPING_TTL', 'Do not prompt customer to select shipping address at PayPal website');
	define('PPEXPRESSCHECKOUT_CFG_NOSHIPPING_DSCR', '');
	
	define('PPEXPRESSCHECKOUT_USERINFO_PREFIX', 'PayPal approved following shipping address:<br>');
	
	define('PPEXPRESSCHECKOUT_CFG_TRANSCURRENCY_TTL', 'Transaction currency');
	define('PPEXPRESSCHECKOUT_CFG_TRANSCURRENCY_DSCR', 'You may select a currecy type in which order amount sent to payment gateway should be recalculated (according to the rates defined in your store settings).');
	
	define('PPECHECKOUT_TXT_SHIPPINGINFO', 'Shipping Information');
	define('PPECHECKOUT_TXT_SHIPPINGMETHOD', 'Shipping Method');
	define('PPECHECKOUT_TXT_SHIPPINGADDRESS', 'Shipping Address');
	define('PPECHECKOUT_TXT_BILLINGINFO','Billing Information');
	define('PPECHECKOUT_TXT_PAYERINFO', 'Payer Information');
	define('PPECHECKOUT_TXT_ORDERDETAILS', 'Order Details');
	define('PPECHECKOUT_TXT_PAYMENTMETHOD', 'Payment Method');
	define('PPECHECKOUT_TXT_CUSTCOMMENT', 'PayPal payer email - %s; PayPal transaction # %s');
	define('PPECHECKOUT_TXT_CDCURRENCY', 'Customer defined currency');
	define('PPEC_TXT_ORDER_DETAILS', 'Order details');
	define('PPEC_TXT_TRANSACTION_ID', 'PayPal transaction id');
?>