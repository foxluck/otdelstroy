<?php
	define('PAYMENTECH_TTL', 'CHASE Paymentech (Orbital)');
	define('PAYMENTECH_DSCR', 'Credit card processing through CHASE Paymentech payment gateway (www.paymentech.com)');
	
	define('PAYMENTECH_CFG_ORDERSTATUS_TTL', 'Order status after successful transaction');
	define('PAYMENTECH_CFG_ORDERSTATUS_DSCR', 'If you would like approved orders to be automatically assigned a particular order status please select the status. Select "Default" to assign successful orders default new orders status (which is configured in "Configuration" screen of back end).');
	
	define('PAYMENTECH_CFG_MESSAGETYPE_TTL', 'Transaction type');
	define('PAYMENTECH_CFG_MESSAGETYPE_DSCR', '');
	
	define('PAYMENTECH_TXT_AUTH', 'Authorization only');
	define('PAYMENTECH_TXT_AUTHCAPTURE', ' Authorization & Capture');
	
	define('PAYMENTECH_CFG_TZCODE_TTL', 'Time zone of your store (numeric; 3 digits)');
	define('PAYMENTECH_CFG_TZCODE_DSCR', 'Time zone code is calculated as described in the <a href="https://www.paymentech.net/download/pdf/Orbital_Gateway_Interface_Specification_v3.4.pdf">Paymentech XML API</a> (page 101; TzCode). Examples for US: EDT - 705, PDT - 708.');
	
	define('PAYMENTECH_CFG_MERCHANTID_TTL', 'Merchant ID');
	define('PAYMENTECH_CFG_MERCHANTID_DSCR', 'Gateway merchant account number assigned by Paymentech');
	
	define('PAYMENTECH_CFG_CURRISO_TTL', 'Transaction currency ISO numeric code');
	define('PAYMENTECH_CFG_CURRISO_DSCR', 'Enter ISO code of the currency you have selected above. See the list of <a href=http://www.iso.org/iso/en/prods-services/popstds/currencycodeslist.html target=_blank>currency numeric ISO codes</a>. Input 826 for GBP, 978 for EURO, 840 for USD.');
	
	define('PAYMENTECH_CFG_CURREXP_TTL', 'Transaction currency exponent');
	define('PAYMENTECH_CFG_CURREXP_DSCR', 'If your transaction currency is Japanese Yen, enter 0. Else enter 2.');
	
	define('PAYMENTECH_TXT_DEFAULT', 'Default');
	
	define('PAYMENTECH_TXT_CCNUMBER', 'Credit card number');
	define('PAYMENTECH_TXT_CSV', 'CVV');
	define('PAYMENTECH_TXT_EXPDATE', 'Expires');
	
	define('PAYMENTECH_CFG_TESTMODE_TTL', 'Test mode');
	define('PAYMENTECH_CFG_TESTMODE_DSCR', '');
	
	define('PAYMENTECH_CFG_CURRSHOP_TTL', 'Transaction currency');
	define('PAYMENTECH_CFG_CURRSHOP_DSCR', 'Please select currecy type in which order amount sent to payment gateway will be dominated. We recommend using USD. Contact Paymentech for the list of supported currency types.');
	
	define('PAYMENTECH_TXT_ERROR_CODE', 'Error code: ');
	define('PAYMENTECH_TXT_ERROR_MSG', 'Error Message: ');
	define('PAYMENTECH_TXT_ERROR_UKNOWN', 'Unknown error');
	define('PAYMENTECH_TXT_ERROR_PROCESSING', 'Transaction processing error');
	define('PAYMENTECH_TXT_DECLINED', 'Transaction has been declined');
	
	define('PAYMENTECH_CFG_PLATFORM_TTL', 'Platform');
	define('PAYMENTECH_CFG_PLATFORM_DSCR', 'Paymentech processing platform for your account. Contact CHASE Paymentech for more information.');
?>