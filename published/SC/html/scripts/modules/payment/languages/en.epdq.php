<?php
	define('EPDQ_TTL', 'ePDQ Barclaycard Business');
	define('EPDQ_DSCR', 'Credit card processing through ePDQ payment gateway (http://www.barclaycardbusiness.co.uk/accepting_cards/phone_mail_internet/index.html).');
	
	define('EPDQ_TXT_AUTH', 'Auth');
	define('EPDQ_TXT_PREAUTH', 'PreAuth');

	define('EPDQ_TXT_PURCHASE', 'Pay now on secure ePDQ payment server');
	
	define('EPDQ_CFG_HOST_TTL', 'Host');
	define('EPDQ_CFG_HOST_DSCR', 'Please enter host name where we should post transaction data. You should have received this information from ePDQ.<br />Do not enter https:// prefix and directory path / filename suffix.');
	
	define('EPDQ_CFG_CLIENT_ID_TTL', 'Client ID');
	define('EPDQ_CFG_CLIENT_ID_DSCR', 'Enter your ePDQ Client ID');
	
	define('EPDQ_CFG_PASSPHRASE_TTL', 'Pass-Phrase');
	define('EPDQ_CFG_PASSPHRASE_DSCR', 'Pass-Phrase is set in your ePDQ Configuration Page  (https://secure2.mde.epdq.co.uk/cgi-bin/CcxBarclaysEpdqAdminTool.e). Note that Pass-Phrase differs from your ePDQ password!');
	
	define('EPDQ_CFG_CHARGETYPE_TTL', 'Authorization mode');
	define('EPDQ_CFG_CHARGETYPE_DSCR', 'Auth - transaction is automatically captured if approved. PreAuth - you will have to indicate later from your ePDQ account whether to capture the transaction or not.');
	
	define('EPDQ_CFG_TRANSCURRENCYCODE_TTL', 'Transaction currency ISO 3 code');
	define('EPDQ_CFG_TRANSCURRENCYCODE_DSCR', 'Enter ISO code of the currency you have selected above. See the list of <a href=http://www.iso.org/iso/en/prods-services/popstds/currencycodeslist.html target=_blank>currency numeric ISO codes</a>. Input 826 for GBP, 978 for EURO, 840 for USD.');
	
	define('EPDQ_CFG_TRANSCURRENCY_TTL', 'Transaction currency');
	define('EPDQ_CFG_TRANSCURRENCY_DSCR', 'Please select currecy type in which order amount sent to payment gateway will be dominated. We recommend using GBP. Contact ePDQ for the list of supported currency types.');
?>