<?php
	define('GOOGLECHECKOUT2_TTL', 'Google Checkout');
	define('GOOGLECHECKOUT2_DSCR', 'Integration with <a href="http://checkout.google.com/sell?promo=sewebasyst" target="_blank">Google Checkout</a> (<a href="http://code.google.com/apis/checkout/developer/index.html#level_2_integration" target="_blank">Level 2</a> integration).<br />Please read our <a href="http://www.webasyst.net/help/setup-google-checkout.htm" target="_blank">detailed description on how to enable Google Checkout</a>.');

	define('GOOGLECHECKOUT2_TXT_DONTCHANGE','Do not change'); 

	define('GOOGLECHECKOUT2_CFG_MERCHANTID_TTL','Merchant ID');
	define('GOOGLECHECKOUT2_CFG_MERCHANTID_DSCR','Login to your Google Checkout seller account and go to "Settings" -> "Integration" to obtain Merchant ID');
	
	define('GOOGLECHECKOUT2_CFG_MERCHANTKEY_TTL','Merchant key');
	define('GOOGLECHECKOUT2_CFG_MERCHANTKEY_DSCR','Login to your Google Checkout seller account and go to "Settings" -> "Integration" to obtain Merchant Key');
	
	define('GOOGLECHECKOUT2_CFG_SANDBOX_TTL','Sandbox mode');
	define('GOOGLECHECKOUT2_CFG_SANDBOX_DSCR','');
	
	define('GOOGLECHECKOUT2_CFG_ENABLED_TTL','Enable module');
	define('GOOGLECHECKOUT2_CFG_ENABLED_DSCR','If disabled, Google Checkout option will not be offered to customers on shopping cart details page');
	
	define('GOOGLECHECKOUT2_CFG_TRANSCURR_TTL','Transaction currency');
	define('GOOGLECHECKOUT2_CFG_TRANSCURR_DSCR','Order amount will be automatically converted into selected currency according to exchange rates defined in your store settings and then sent to Google Checkout. <b>Only USD is currently supported.</b>');
	
	define('GOOGLECHECKOUT2_TXT_DISCOUNT','Discount');
	define('GOOGLECHECKOUT2_TXT_FREIGHT','Shipping');
	
	define('GOOGLECHECKOUT2_CFG_CHARGEORDER_TTL', 'Charge customer automatically');
	define('GOOGLECHECKOUT2_CFG_CHARGEORDER_DSCR', 'If enabled, customer payment for the order will be immediately settled after your store receives information from Google that payment has been authorized. If disabled, payment will remain authorized until you manually settle it in your Google account.');
	
	define('GOOGLECHECKOUT2_CFG_CHARGEDORDERSTATUS_TTL', 'Apply charged order following status');
	define('GOOGLECHECKOUT2_CFG_CHARGEDORDERSTATUS_DSCR', 'When Google Checkout payment has been charged, Google sends a notification to your store. If you would like order status to be changed on this event, select the status to apply.');

	define('GOOGLECHECKOUT2_CFG_SHIPPEDORDERSTATUS_TTL', 'Apply shipped order following status');
	define('GOOGLECHECKOUT2_CFG_SHIPPEDORDERSTATUS_DSCR', 'When you press "Ship" button for the order in your Google account, Google sends a notification to your store. If you would like order status to be changed on this event, select the status to apply.');
	
	define('GOOGLECHECKOUT2_CFG_CALCULATESHIPTAX_TTL', 'Enable shipping costs and tax calculation for Google Checkout orders');
	define('GOOGLECHECKOUT2_CFG_CALCULATESHIPTAX_DSCR', 'If enabled, we will pass information about all available shipping types and tax rates defined in your store, and Google will request calculating shipping and tax rates during checkout.');

	define('GOOGLECHECKOUT2_CFG_SENDORDERNOTIFYCATION_TTL', 'Send order notification to customer from your store');
	define('GOOGLECHECKOUT2_CFG_SENDORDERNOTIFYCATION_DSCR', 'When customer places an order on the Google side, Google sends her an email notification on this order. If you would like to send notification to customer either, enable this checkbox.');
	
	define('GOOGLECHECKOUT2_CFG_SHIPPIGNRESCTRICTIONS_TTL','Deliver zones'); 
	define('GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_DSCR','deliver description'); 
	define('GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_ENTIRE_WORLD','Entire world');
	define('GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_US_ONLY','US only'); 	
?>