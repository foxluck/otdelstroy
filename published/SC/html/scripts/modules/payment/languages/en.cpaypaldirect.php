<?php
define('CPAYPALDIRECT_TTL', 'PayPal Website Payments Pro - Direct Payment');
define('CPAYPALDIRECT_DSCR', 'PayPal Direct Payment module allows you to accept credit card payments. Credit card information is collected on your website and then transferred to PayPal server where payment is processed.<br>Visit PayPal website to learn more about <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_wp-pro-overview-outside" target=_blank>Website Payments Pro</a>');
define('CPAYPALDIRECT_TXT_FNAME', 'Credit Card Holder first name:');
define('CPAYPALDIRECT_TXT_LNAME', 'Credit Card Holder last name:');
define('CPAYPALDIRECT_TXT_CCNUMBER', 'Credit Card Number:');
define('CPAYPALDIRECT_TXT_CCTYPE', 'Credit Card Type:');
define('CPAYPALDIRECT_TXT_CVV2','Card Verification Value (CVV):');
define('CPAYPALDIRECT_TXT_EXPDATE','Expires:');

define('CPAYPALDIRECT_TXT_TEST','Sandbox');
define('CPAYPALDIRECT_TXT_LIVE','Live');
define('CPAYPALDIRECT_TXT_DEFAULT', 'Default');

define('CPAYPALDIRECT_CFG_USERNAME_TTL', 'API Username');
define('CPAYPALDIRECT_CFG_USERNAME_DSCR', 'Enter the API username provided to you when generating API Access Certificate for Website Payments Pro');

define('CPAYPALDIRECT_CFG_PASSWORD_TTL', 'Password');
define('CPAYPALDIRECT_CFG_PASSWORD_DSCR', 'Enter the password that you set up when you signed up for Website Payments Pro');

define('CPAYPALDIRECT_CFG_CERTPATH_TTL', 'PayPal certificate');
define('CPAYPALDIRECT_CFG_CERTPATH_DSCR', 'Download API certificate from your PayPal account and then specify certificate file name in this box');

define('CPAYPALDIRECT_CFG_MODE_TTL', 'Mode');
define('CPAYPALDIRECT_CFG_MODE_DSCR', 'Select "Sandbox" to test PayPal payments, and "Live" for real transactions');

define('CPAYPALDIRECT_CFG_PAYMENTACTION_TTL', 'Payment authorization type');
define('CPAYPALDIRECT_CFG_PAYMENTACTION_DSCR', 'How you want to obtain payment:<ul><li>Authorization indicates that this payment is a basic authorization subject to settlement with PayPal Authorization & Capture.</li><li>Sale indicates that this is a final sale for which you are requesting payment.</li></ul>');

define('CPAYPALDIRECT_CFG_ORDERSTATUS_TTL', 'Append approved orders following status');
define('CPAYPALDIRECT_CFG_ORDERSTATUS_DSCR', 'If you would like PayPal Direct orders to be automatically assigned a particular order status please select the status. Select "Default" to assign PayPal Direct orders default new orders status (which is configured in "Configuration" screen of back end)');

define('CPAYPALDIRECT_CFG_CURRENCY_TTL', 'USD currency type');
define('CPAYPALDIRECT_CFG_CURRENCY_DSCR', 'Order amount transferred to PayPal web site is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1).');

define('CPAYPALDIRECT_TXT_STATUS_COMMENT','PayPal transaction # %s');
?>