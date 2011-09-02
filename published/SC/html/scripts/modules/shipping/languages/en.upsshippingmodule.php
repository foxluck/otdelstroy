<?php
define('UPSSHIPPINGMODULE_TTL',
	'UPS');
define('UPSSHIPPINGMODULE_DSCR',
	'UPS OnLine Tools. Real-time shipping rate calculations.<br>Your need to have an account with www.ups.com to make this module work.<br />Please read our <a href="http://www.webasyst.net/help/setup-shipping-quotes-ups.htm" target="_blank">detailed description on how to setup UPS real-time shipping quotes</a>.');
	
define('UPSSHIPPINGMODULE_CFG_PACKAGE_TYPE_TTL',
	'Package type');
define('UPSSHIPPINGMODULE_CFG_PACKAGE_TYPE_DSCR',
	'');
define('UPSSHIPPINGMODULE_CFG_PICKUP_TYPE_TTL',
	'Pickup type');
define('UPSSHIPPINGMODULE_CFG_PICKUP_TYPE_DSCR',
	'');
define('UPSSHIPPINGMODULE_CFG_ACCESSLICENSENUMBER_TTL',
	'XML Access Key');
define('UPSSHIPPINGMODULE_CFG_ACCESSLICENSENUMBER_DSCR',
	'Please indicate XML Access Key which can be obtained from UPS');
define('UPSSHIPPINGMODULE_CFG_USERID_TTL',
	'UPS User ID');
define('UPSSHIPPINGMODULE_CFG_USERID_DSCR',
	'Your UPS login');
define('UPSSHIPPINGMODULE_CFG_PASSWORD_TTL',
	'UPS Password');
define('UPSSHIPPINGMODULE_CFG_PASSWORD_DSCR',
	'Your UPS accont password');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_COUNTRY_ID_TTL',
	'Origin country');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_COUNTRY_ID_DSCR',
	'Please select an origin country, from where you will ship');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_CITY_TTL',
	'Origin city');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_CITY_DSCR',
	'Please enter your city name');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_POSTALCODE_TTL',
	'Origin postal code (Zip)');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_POSTALCODE_DSCR',
	'Enter your origin location Zip code');
define('UPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_TTL', 'Enable UPS error log');
define('UPSSHIPPINGMODULE_CFG__ENABLE_ERROR_LOG_DSCR', 'If enabled, UPS error response codes will be saved into temp/ups_errors.log file');

define('UPSSHIPPINGMODULE_CFG_USD_CURRENCY_TTL', 'USD currency type');
define('UPSSHIPPINGMODULE_CFG_USD_CURRENCY_DSCR', 'Shipping charges calculated by UPS server are denominated in USD. Specify currency type in your shopping cart which is assumed as USD to make shipping charges recalculated properly (according to USD exchange rate)');

define('UPSSHIPPINGMODULE_CFG_CUSTOMER_CLASSIFICATION_TTL', 'Customer classification');
define('UPSSHIPPINGMODULE_CFG_CUSTOMER_CLASSIFICATION_DSCR', 'Select "default" to use default UPS values');
?>