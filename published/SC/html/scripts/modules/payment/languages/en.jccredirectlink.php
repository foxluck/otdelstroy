<?php
	define('JCCRL_TTL', 'JCC');
	define('JCCRL_DSCR', 'Cyprus JCC payment gateway (www.jcc.com.cy). Redirect Link integration method.');
	
	define('JCCRL_CFG_MERID_TTL', 'Merchant ID');
	define('JCCRL_CFG_MERID_DSCR', 'Your JCC merchant ID');
	
	define('JCCRL_CFG_MERPWD_TTL', 'Merchant Checkout Password');
	define('JCCRL_CFG_MERPWD_DSCR', 'Your JCC password');
	
	define('JCCRL_CFG_ACQID_TTL','Acquirer ID');
	define('JCCRL_CFG_ACQID_DSCR','Please input acquirer ID supplied to you by JCC');
	
	define('JCCRL_CFG_CUR_SHOP_TTL','Transaction currency');
	define('JCCRL_CFG_CUR_SHOP_DSCR','Please select currecy type in which order amount sent to payment gateway will be dominated. We recommend using Cyprus Pounds. Contact JCC for the list of supported currency types.');
	
	define('JCCRL_CFG_CUR_ISONUM_TTL','The ISO code of the currency you have mentioned above');
	define('JCCRL_CFG_CUR_ISONUM_DSCR','Enter ISO code of the currency you have selected above. Please see the list of <a href=http://www.iso.org/iso/en/prods-services/popstds/currencycodeslist.html target=_blank>currency numeric ISO codes</a>. Input 196 for Cyprus Pounds. Contact JCC for the list of supported currency types.');
	
	define('JCCRL_SUBMIT_BTN', 'Proceed to secure payment gateway to pay now!');

	define('JCCRL_CFG_CAPTURE_TTL', 'Authorization');
	define('JCCRL_CFG_CAPTURE_DSCR', 'Automatic - transaction is automatically captured if approved. Manual - merchant will indicate later from the Merchant Admin site whether to capture the transaction or not. If not present, system will use the selected option from the database.');
	
	define('JCCRL_TXT_CAPTURE_A', 'Automatic');
	define('JCCRL_TXT_CAPTURE_M', 'Manual');
	
	define('JCCRL_CFG_URL_TTL', 'Post URL');
	define('JCCRL_CFG_URL_DSCR', 'Please enter URL where transactions data will be posted');
?>