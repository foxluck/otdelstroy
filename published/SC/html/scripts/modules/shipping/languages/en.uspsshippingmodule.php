<?php
define('SHIPPING_MODULE_USPS_TTL','USPS');
define('SHIPPING_MODULE_USPS_DSCR','USPS Web Tools. Real-time shipping rates calculations.<br>Your need to have an account with www.usps.com to make this module work.<br />Please read our <a href="http://www.webasyst.net/help/setup-shipping-quotes-usps.htm" target="_blank">detailed description on how to setup USPS real-time shipping quotes</a>.');

define('USPS_CONF_ZIPORIGINATION_TTL','Origin Zip code');
define('USPS_CONF_ZIPORIGINATION_DSCR','Enter your origin location Zip code (max 5 characters)');

define('USPS_CONF_USERID_TTL','USPS User ID');
define('USPS_CONF_USERID_DSCR','Your USPS account User ID');

define('USPS_CONF_PASSWORD_TTL','USPS Password');
define('USPS_CONF_PASSWORD_DSCR','Password to your USPS account');

define('USPS_CONF_PACKAGESIZE_TTL','Default package size');
define('USPS_CONF_PACKAGESIZE_DSCR','Applicable for domestic shipments only. More information at USPS.com');

define('USPS_CONF_MACHINABLE_TTL','Machinable');
define('USPS_CONF_MACHINABLE_DSCR', 'Indicate whether packages are machinable or not. Applicable for domestic shipments only. More information at USPS.com');

define('USPS_CONF_DOMESTIC_SERVS_TTL','Allowed domestic services');
define('USPS_CONF_DOMESTIC_SERVS_DSCR','Select available domestic shipment services');

define('USPS_CONF_INTERNATIONAL_SERVS_TTL','Available international packages');
define('USPS_CONF_INTERNATIONAL_SERVS_DSCR','Select available international shipment services');

define('USPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_TTL', 'Enable USPS error log');
define('USPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_DSCR', 'If enabled, USPS error response codes will be saved into temp/usps_errors.log file');

define('USPS_CONF_USD_CURRENCY_TTL', 'USD currency type');
define('USPS_CONF_USD_CURRENCY_DSCR', 'Shipping charges calculated by USPS server are denominated in USD. Specify currency type in your shopping cart which is assumed as USD to make shipping charges recalculated properly (according to USD exchange rate)');
?>