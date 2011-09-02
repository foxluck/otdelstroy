<?php
	define('HSBC_TTL', 'HSBC');
	define('HSBC_DSCR', 'Credit card processing with payment gateway of the famous HSBC bank (www.hsbc.com). CPI Integration.<br />Requires that you have a SSL certificate installed for your online store domain name.');
	
	define('HSBC_CFG_MODE_TTL', 'Mode');
	define('HSBC_CFG_MODE_DSCR', '');
	define('HSBC_TXT_PMODE', 'Production mode. The customer will be billed for the order.');
	define('HSBC_TXT_TMODE', 'Test mode. No money will be taken.');
	
	define('HSBC_CFG_STOREFRONTID_TTL', 'Storefront ID of your CPI service (Client Alias)');
	define('HSBC_CFG_STOREFRONTID_DSCR', 'Sent to you by email from HSBC. Example: UK12345678CUR');
	
	define('HSBC_CFG_TRANTYPE_TTL', 'Transaction type');
	define('HSBC_CFG_TRANTYPE_DSCR', 'Please select transaction method: Auth - funds are authorized during purchase but not settled to your account automatically; Capture - funds are automatically cleared to your account.');
	define('HSBC_TXT_TRANTYPE_AUTH', 'Auth');
	define('HSBC_TXT_TRANTYPE_CAPTURE', 'Capture');
	
	define('HSBC_CFG_USERID_TTL', 'User ID');
	define('HSBC_CFG_USERID_DSCR', 'Sent to you by email from HSBC');

	define('HSBC_CFG_SHAREDSECRET_TTL', 'Shared Secret');
	define('HSBC_CFG_SHAREDSECRET_DSCR', 'Sent to you by email from HSBC');

	define('HSBC_CFG_TRANSCURR_TTL', 'Transaction currency');
	define('HSBC_CFG_TRANSCURR_DSCR', 'Select currency type in which transaction will be sent to HSBC. Order amount will be automatically converted into selected currency according to exchange rates defined in your store settings.<br /><b>Currently HSBC only support GBP payments!</b>');

	define('HSBC_CFG_CURCODE_TTL', '3-digit ISO code of the transaction currency');
	define('HSBC_CFG_CURCODE_DSCR', 'Enter numeric ISO code of the currency you have selected above. Example: USD=840, EUR=978, GBP=826.<br /><b>Currently HSBC only support GBP payments! Please enter "826".</b>');
	
	define('HSBC_SUBMIT_BTN','Pay now on secure HSBC website');
?>