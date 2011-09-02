<?php

define('CHRONOPAYDIRECT_TTL','Chronopay (gateway)');

define('CHRONOPAYDIRECT_DSCR','Credit card processing through Chronopay gateway (www.chronopay.com).<br>
	Information is collected on your web site and then transferred to Authorize.Net web site. Required CURL and valid SSL certificate.');

define('CHRONOPAYDIRECT_CFG_PRODUCT_ID_TTL','Product ID');

define('CHRONOPAYDIRECT_CFG_PRODUCT_ID_DSCR','This information can be obtained from your Chronopay account.');

define('CHRONOPAYDIRECT_CFG_CURCODE_TTL', 'USD currency type');
define('CHRONOPAYDIRECT_CFG_CURCODE_DSCR', 'Order amount transferred to LinkPoint web site is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1).');


define('CHRONOPAYDIRECT_TXT_ERROR_PROCESSING', 'Transaction processing error');
define('CHRONOPAYDIRECT_INVALID_SERVER_RESPONCE','Invalid server responce, Please try again later');

define('CHRONOPAYDIRECT_CFG_SHAREDSECRET_TTL', 'Shared Secret');
define('CHRONOPAYDIRECT_CFG_SHAREDSECRET_DSCR', 'Sent to you by email from ChronoPay');

define('CHRONOPAYDIRECT_CFG_ORDERSTATUS_TTL', 'Append approved orders following status');
define('CHRONOPAYDIRECT_CFG_ORDERSTATUS_DSCR', 'If you would like orders to be automatically assigned a particular status please select the status. Select "Default" to assign default new orders status (which is configured in "Configuration" screen of back end).');
define('CHRONOPAYDIRECT_TXT_DEFAULT','Default');
	

define('CHRONOPAYDIRECT_TXT_CARDHOLDER','Cardholder’s name');
define('CHRONOPAYDIRECT_TXT_PHONE','Phone number');
define('CHRONOPAYDIRECT_TXT_CARD_NUMBER','Card number');
define('CHRONOPAYDIRECT_TXT_CVV','CVV value');
define('CHRONOPAYDIRECT_TXT_EXPIRATION','Card’s expiration');
?>