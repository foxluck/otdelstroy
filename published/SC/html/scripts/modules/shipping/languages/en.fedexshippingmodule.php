<?php
define('FEDEXSHIPPINGMODULE_TTL', 
	'FedEx');
define('FEDEXSHIPPINGMODULE_DSCR', 
	'FedEx Ship Manager Direct. Real-time shipping rate calculations.<br>Your need to have an account with FedEx to make this module work.<br />Please read our <a href="http://www.webasyst.net/help/setup-shipping-quotes-fedex.htm" target="_blank">detailed description on how to setup FedEx real-time shipping quotes</a>.');
	
define('FEDEX_CNF_ACCOUNT_NUMBER_TTL', 
	'Account number');
define('FEDEX_CNF_ACCOUNT_NUMBER_DSCR', 
	'Please enter your FedEx account number');
	
define('FEDEX_CNF_METER_NUMBER_TTL', 
	'Meter number');
define('FEDEX_CNF_METER_NUMBER_DSCR', 
	'If you do not have a meter number simply leave this field blank. It will be generated automatically.');
	
define('FEDEX_CNF_PACKAGING_TTL', 
	'Packaging');
define('FEDEX_CNF_PACKAGING_DSCR', 
	'For \'FedEx Ground\' must be \'Your packaging\' only');
	
define('FEDEX_CNF_CARRIER_TTL', 
	'Service');
define('FEDEX_CNF_CARRIER_DSCR', 
	'Please select FedEx service');
	
define('FEDEX_CNF_STATE_OR_PROVINCE_CODE_TTL', 
	'Origin state/province code');
define('FEDEX_CNF_STATE_OR_PROVINCE_CODE_DSCR', 
	'Required only if origination country is USA or Canada<br />
	Represents the state/province from which the shipment will be originating.');
	
define('FEDEX_CNF_POSTAL_CODE_TTL', 
	'Zip (postal) code');
define('FEDEX_CNF_POSTAL_CODE_DSCR', 
	'Required only if origination country is USA or Canada<br />
	May be required for other postal-aware countries.
	Represents the postal code from which the shipment will be originating.<br />
	Valid characters: A-Z; 0-9; a-z');
	
define('FEDEX_CNF_COUNTRY_CODE_TTL', 
	'Origin country');
define('FEDEX_CNF_COUNTRY_CODE_DSCR', 
	'Represents the country from which the shipment will be originating.');	
	
define('FEDEX_CNF_ADDRESS_TTL', 
	'Address');
define('FEDEX_CNF_ADDRESS_DSCR', 
	'Enter you street address<br />
	Required for meter number generation');
define('FEDEX_CNF_CITY_TTL', 
	'City');
define('FEDEX_CNF_CITY_DSCR', 
	'Enter city name<br />
	Required for meter number generation');
define('FEDEX_CNF_PHONE_NUMBER_TTL', 
	'Phone number');
define('FEDEX_CNF_PHONE_NUMBER_DSCR', 
	'111-222-3333<br />
	Required for meter number generation');
define('FEDEX_CNF_NAME_TTL', 
	'Your name');
define('FEDEX_CNF_NAME_DSCR', 
	'Enter your name<br />
	Required for meter number generation');
define('FEDEX_CNF_ERROR_LOG_TTL', 
	'Enable FedEx error log');
define('FEDEX_CNF_ERROR_LOG_DSCR', 
	'If enabled, FedEx error response codes will be saved into temp/fedex_errors.log file');

define('FEDEX_CNF_TESTMODE_TTL','Test environment');
define('FEDEX_CNF_TESTMODE_DSCR','Enable to run FedEx module in test environment; and disable when moving to production');
define('FEDEX_CNF_CURRENCY_TTL','USD currency type');
define('FEDEX_CNF_CURRENCY_DSCR','Shipping charges calculated by FedEx server are denominated in USD. Specify currency type in your shopping cart which is assumed as USD to make shipping charges recalculated properly (according to USD exchange rate)');
?>