<?php
define('INTERSHIPPERMODULE_TTL', 'InterShipper');
define('INTERSHIPPERMODULE_DSCR', 'Real-time shipping quotes with UPS, USPS, DHL, FedEx.<br>Required to have an account at www.intershipper.com.');

define('INTERSHIPPER_CFG_USERNAME_TTL', 'InterShipper account username');
define('INTERSHIPPER_CFG_USERNAME_DSCR', '');

define('INTERSHIPPER_CFG_PASSWORD_TTL', 'InterShipper account password');
define('INTERSHIPPER_CFG_PASSWORD_DSCR', '');

define('INTERSHIPPER_CFG_CLASSES_TTL', 'Shipping options');
define('INTERSHIPPER_CFG_CLASSES_DSCR', 'Select applicable service classes');

define('INTERSHIPPER_CFG_SHIPMETHOD_TTL', 'Pickup/drop-off type');
define('INTERSHIPPER_CFG_SHIPMETHOD_DSCR', 'Specifies the means by which a carrier is to obtain the packages to be shipped');

define('INTERSHIPPER_CFG_SHMOPTION_TTL', 'Additional shipments pickup/drop-off options');
define('INTERSHIPPER_CFG_SHMOPTION_DSCR', 'Specify additional shipping details depending on selected pickup/drop-off type');

define('INTERSHIPPER_CFG_PACKAGING_TTL', 'Package type');
define('INTERSHIPPER_CFG_PACKAGING_DSCR', '');

define('INTERSHIPPER_CFG_CONTENTS_TTL', 'Package content');
define('INTERSHIPPER_CFG_CONTENTS_DSCR', '');

define('INTERSHIPPER_CFG_INSURANCE_TTL', 'Insurance');
define('INTERSHIPPER_CFG_INSURANCE_DSCR', 'Enter insurance amount as a percent of order amount (e.g. 10%), exact insurance amount in USD (e.g. 15.96) or leave this field empty to disable insurance');

define('INTERSHIPPER_CFG_USD_TTL', 'USD currency type');
define('INTERSHIPPER_CFG_USD_DSCR', 'Shipping charges calculated by InterShipper server are denominated in USD. Specify currency type in your shopping cart which is assumed as USD to make shipping charges recalculated properly (according to USD exchange rate)');

define('INTERSHIPPER_CFG_CARRIERS_TTL', 'Carriers');
define('INTERSHIPPER_CFG_CARRIERS_DSCR', 'Select shipping companies for real-time shipping quotes');

define('INTERSHIPPER_CFG_STATE_TTL', 
	'Origin state');
define('INTERSHIPPER_CFG_STATE_DSCR', 
	'Select/enter origin state name');
	
define('INTERSHIPPER_CFG_POSTAL_TTL', 
	'Origin Zip code');
define('INTERSHIPPER_CFG_POSTAL_DSCR', 
	'Enter your origin location Zip code');
	
define('INTERSHIPPER_CFG_COUNTRY_TTL', 
	'Origin country');
define('INTERSHIPPER_CFG_COUNTRY_DSCR', 
	'InterShipper can calculate shipping rates only for shipments sent from USA. Select USA from the countries list');
	
define('INTERSHIPPER_CFG_CITY_TTL', 
	'Origin city');
define('INTERSHIPPER_CFG_CITY_DSCR', 
	'Enter origin city name');
	
	
define('INTERSHIPPER_TXT_CARRIER_ACCOUNT', 
	'If you have an account with this carrier input this account username:<br>(leave empty if not applicable)');
define('INTERSHIPPER_TXT_CARRIER_INVOICED', 
	'I am invoiced directly from the carrier');

?>