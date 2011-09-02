<?php
	define('DHLSHIPPINGMODULE_TTL', 'DHL');
	define('DHLSHIPPINGMODULE_DSCR', 'DHL Airborne. Real-time shipping rate calculations.<br>Your need to have an account with DHL to make this module work.<br />Please read our <a href="http://www.webasyst.net/help/setup-shipping-quotes-dhl.htm" target="_blank">detailed description on how to setup DHL real-time shipping quotes</a>.');
	define('DHL_CNF_ACCOUNT_NUMBER_TTL', 'Account number');
	define('DHL_CNF_ACCOUNT_NUMBER_DSCR', 'Please enter your DHL account number');
	define('DHL_CNF_ISHIPPING_KEY_TTL', 'International Shipping Key');
	define('DHL_CNF_ISHIPPING_KEY_DSCR', 'Please enter Shipping Key provided to you by DHL');
	define('DHL_CNF_SHIPPING_KEY_TTL', 'Domestic Shipping Key');
	define('DHL_CNF_SHIPPING_KEY_DSCR', 'Please enter Shipping Key provided to you by DHL');
	define('DHL_CNF_DUTIABLE_TTL', 'Dutiable');
	define('DHL_CNF_DUTIABLE_DSCR', 'For international shipments only');
	define('DHL_CNF_BILLINGPARTY_TTL', 'Billing Party');
	define('DHL_CNF_BILLINGPARTY_DSCR', 'Select whether billing party is Sender or Receiver');
	define('DHL_CNF_SHIPDATE_TTL', 'The number of days to pick up the package');
	define('DHL_CNF_SHIPDATE_DSCR', 'Enter the number of days from the day of order in which the package is to be picked up by DHL.<br>Example: if you enter 3, and the order is placed on Tuesday, pickup date will be Friday');
	define('DHL_CNF_SHIPMENT_TYPE_TTL', 'Shipment type');
	define('DHL_CNF_SHIPMENT_TYPE_DSCR', 'Select shipment package type');
	define('DHL_CNF_TEST_MODE_TTL', 'Test environment');
	define('DHL_CNF_TEST_MODE_DSCR', 'Enable to run DHL module in test environment; and disable when moving to production');
	define('DHL_CNF_LOGIN_ID_TTL', 'API System ID');
	define('DHL_CNF_LOGIN_ID_DSCR', 'Enter your DHL API System ID');
	define('DHL_CNF_PASSWORD_TTL', 'API Password');
	define('DHL_CNF_PASSWORD_DSCR', 'Enter your DHL API password');
	define('DHL_CNF_SERVICES_TTL', 'Available services');
	define('DHL_CNF_SERVICES_DSCR', 'Select DHL services to be offered to customer');
	define('DHL_CNF_AP_TTL','Additional protection against loss or damage');
	define('DHL_CNF_AP_DSCR','Asset Protection - All Risk Asset Protection is available for shipments beyond the published Limit of Liability.<br>See DHL Service Guide or contact Customer Service at 1-800-CALL-DHL for complete details');
	define('DHL_CNF_AP_VALUE_TTL','Protection value');
	define('DHL_CNF_AP_VALUE_DSCR','Enter the declared value of the shipment when selecting Asset Protection (input a value in your default currency)');
	define('DHL_CNF_AP_VALUE_TYPE0','Equals order amount');
	define('DHL_CNF_AP_VALUE_TYPE1','Fixed declared value');
	define('DHL_CNF_DIMENSIONS_TTL','Dimensions');
	define('DHL_CNF_DIMENSIONS_DSCR','If your packages ship have fixed dimensions, please provide them in this field.<br>Input LxHxW, where L, H and W are Lendth, Height and Width accordingly (in inches). Example: 40x20x30<br>Leave blank to ignore (if your packages dimensions vary)');
	define('DHL_CNF_COD_TTL','Collect On Delivery');
	define('DHL_CNF_COD_DSCR','Select preferable Collect On Delivery (COD) method. If you do not use COD, choose "n/a".<br>Billing party should be set to Sender if you use COD.<br>COD amount is set equal to order amount.<br>Contact 1-800-CALL-DHL for complete details');
	define('DHL_CNF_USD_CURRENCY_TTL','USD currency type');
	define('DHL_CNF_USD_CURRENCY_DSCR','Shipping charges calculated by DHL server are denominated in USD. Specify currency type in your shopping cart which is assumed as USD to make shipping charges recalculated properly (according to USD exchange rate)');
	define('DHL_CNF_ERROR_LOG_TTL','Enable DHL error log');
	define('DHL_CNF_ERROR_LOG_DSCR','If enabled, DHL error response codes will be saved into temp/dhl_errors.log file');
	
	define('DHL_TXTER_OVERWEIGHT','Maximum allowed weight exceeded ({%WEIGHT%})');
?>