<?php
define('CPAYPALECHECKOUT_TTL', 'PayPal Website Payments Pro - Express Checkout');
define('CPAYPALECHECKOUT_DSCR', 'Visit PayPal website to learn more about <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_wp-pro-overview-outside" target=_blank>Website Payments Pro</a>');
define('CPAYPALECHECKOUT_TXT_TEST','Sandbox');
define('CPAYPALECHECKOUT_TXT_LIVE','Live');
define('CPAYPALECHECKOUT_TXT_DEFAULT', 'Default');
define('CPAYPALECHECKOUT_TXT_ERROR_CHECKOUT', 'Please contact store administrator for details');
define('CPAYPALECHECKOUT_TXT_CHECKOUT_CANCELED', 'Your PayPal payment was not accepted.');
define('CPAYPALECHECKOUT_TXT_CHECKOUT_SUCCESS', 'Your PayPal payment has been successfully accepted!<br>Please click "Place order" button to approve payment and finalize your order.');

define('CPAYPALECHECKOUT_CFG_USERNAME_TTL', 'API Username');
define('CPAYPALECHECKOUT_CFG_USERNAME_DSCR', 'Enter the API username provided to you when generating API Access Certificate for Website Payments Pro');

define('CPAYPALECHECKOUT_CFG_PASSWORD_TTL', 'Password');
define('CPAYPALECHECKOUT_CFG_PASSWORD_DSCR', 'Enter the password that you set up when you signed up for Website Payments Pro');

define('CPAYPALECHECKOUT_CFG_CERTPATH_TTL', 'PayPal certificate');
define('CPAYPALECHECKOUT_CFG_CERTPATH_DSCR', 'Download API certificate from your PayPal account and then specify certificate file name in this box');

define('CPAYPALECHECKOUT_CFG_MODE_TTL', 'Mode');
define('CPAYPALECHECKOUT_CFG_MODE_DSCR', 'Select "Sandbox" to test PayPal payments, and "Live" for real transactions');

define('CPAYPALECHECKOUT_CFG_PAYMENTACTION_TTL', 'Payment authorization method');
define('CPAYPALECHECKOUT_CFG_PAYMENTACTION_DSCR', 'How you want to obtain payment:<br><br><b>Sale</b> indicates that this is a final sale for which you are requesting payment.<br><b>Authorization</b> or <b>Order</b> indicate that this payment is subject to settlement with PayPal Authorization & Capture.');

define('CPAYPALECHECKOUT_CFG_ORDERSTATUS_TTL', 'Append approved orders following status');
define('CPAYPALECHECKOUT_CFG_ORDERSTATUS_DSCR', 'If you would like PayPal Expess Checkout orders to be automatically assigned a particular order status please select the status. Select "Default" to assign Express Checkout orders default new orders status (which is configured in "Configuration" screen of back end)');

define('CPAYPALECHECKOUT_CFG_CURRENCY_TTL', 'USD currency type');
define('CPAYPALECHECKOUT_CFG_CURRENCY_DSCR', 'Order amount transferred to PayPal web site is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');

define('CPAYPALECHECKOUT_CFG_NOSHIPPING_TTL', 'Do not prompt customer to select shipping address at PayPal website');
define('CPAYPALECHECKOUT_CFG_NOSHIPPING_DSCR', '');

define('CPAYPALECHECKOUT_USERINFO_PREFIX', 'PayPal approved following shipping address:<br>');
?>