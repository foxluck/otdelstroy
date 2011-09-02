<?php
	define('TCLINK_TTL', 'TrustCommerce');
	define('TCLINK_DSCR', 'Credit card processing using TrustCommerce payment gateway (www.trustcommerce.com)');
	
	define('TCLINK_CFG_USERID_TTL', 'Customer ID');
	define('TCLINK_CFG_USERID_DSCR', 'Customer ID assigned to you by TrustCommerce');
	
	define('TCLINK_CFG_PWD_TTL', 'Password');
	define('TCLINK_CFG_PWD_DSCR', 'Password assigned to you by TrustCommerce');
	
	define('TCLINK_ACTION_PREAUTH', 'Preauth');
	define('TCLINK_ACTION_SALE', 'Sale');
	
	define('TCLINK_CFG_DEMO_TTL', 'Demo mode');
	define('TCLINK_CFG_DEMO_DSCR', 'Please check the box if you want to run TrustCommerce module in test mode');
	
	define('TCLINK_CFG_ACTION_TTL', 'Transaction type');
	define('TCLINK_CFG_ACTION_DSCR', 'Preauth - funds are authorized during purchase but not settled to your account automatically; Sale - funds are automatically cleared to your account. Contact TrustCommerce for details.');
	
	define('TCLINK_CFG_ORDERSTATUS_TTL', 'Order status after successful transaction');
	define('TCLINK_CFG_ORDERSTATUS_DSCR', 'If you would like approved orders to be automatically assigned a particular order status please select the status. Select "Default" to assign successful orders default new orders status (which is configured in "Configuration" screen of back end)');
	define('TCLINK_TXT_DEFAULT', 'Default');
	
	define('TCLINK_TXT_CCNUMBER', 'Card Number');
	define('TCLINK_TXT_CVV', 'CVV');
	define('TCLINK_TXT_EXPDATE', 'Expires');
	
	define('TCLINK_ERROR_EXTENSION_LOADING','Failed loading TCLink extension. Can not proceed!');
	define('TCLINK_TXT_TRANSACTION_DECLINED', 'Transaction has been declined: ');
	define('TCLINK_TXT_BADDATA', 'Improperly formatted data. Offending fields: ');
	define('TCLINK_TXT_ERROR', 'Error occurred: ');
?>