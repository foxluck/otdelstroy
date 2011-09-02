<?php
	define('SKIPJACKDC_TTL', 'SkipJack');
	define('SKIPJACKDC_DSCR', 'Credit card processing using SkipJack payment gateway (www.skipjack.com). Direct Call integration method.');
	
	define('SKIPJACKDC_CFG_URL_TTL', 'Posting URL');
	define('SKIPJACKDC_CFG_URL_DSCR', 'Please indicate URL where your store will send customer credit card data. Use "https://developer.skipjackic.com/scripts/evolvcc.dll?AuthorizeAPI" for test purposes and "https://www.skipjackic.com/scripts/evolvcc.dll?AuthorizeAPI" when going live');
	
	define('SKIPJACKDC_CFG_SERIAL_TTL', 'Serial Number');
	define('SKIPJACKDC_CFG_SERIAL_DSCR', 'Your Unique Skipjack HTML Serial Number');
	
	define('SKIPJACKDC_CFG_USD_TTL', 'USD currency type');
	define('SKIPJACKDC_CFG_USD_DSCR', 'Order amount sent to SkipJack web site is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1).');
	
	define('SKIPJACKDC_CFG_ASKCVV_TTL', 'Request CVV');
	define('SKIPJACKDC_CFG_ASKCVV_DSCR', 'Please select whether to request customer a CVV value or not');
	
	define('SKIPJACKDC_CFG_ORDERSTATUS_TTL', 'Append approved orders following status');
	define('SKIPJACKDC_CFG_ORDERSTATUS_DSCR', 'If you would like approved orders to be automatically assigned a particular order status please select the status. Select "Default" to assign approved orders default new orders status (which is configured in "Configuration" screen of back end)');
	
	define('SKIPJACKDC_TXT_CCNUMBER', 'Card Number');
	define('SKIPJACKDC_TXT_CVV', 'CVV (3-digit number on the back of the card)');
	define('SKIPJACKDC_TXT_EXPDATE', 'Expires');
	define('SKIPJACKDC_TXT_DEFAULT', 'Default');
	
	global $SKIPJACKDC_responsetexts;
	
	$SKIPJACKDC_responsetexts = array(
		'-1' => 'Error in request Invalid',
		'0' => 'Communication Failure – Check Transaction Status before retrying transaction.',
		'1' => 'Success (Valid Data)',
		'-35' => 'Error invalid credit card number',
		'-37' => 'Error failed communication',
		'-39' => 'Error length serial number',
		'-51' => 'Error length zip code',
		'-52' => 'Error length shipto zip code',
		'-53' => 'Error length expiration date',
		'-54' => 'Error length account number date',
		'-55' => 'Error length street address',
		'-56' => 'Error length shipto street address',
		'-57' => 'Error length transaction amount',
		'-58' => 'Error length name',
		'-59' => 'Error length location',
		'-60' => 'Error length state',
		'-61' => 'Error length shipto state',
		'-62' => 'Error length order string',
		'-64' => 'Error invalid phone number',
		'-65' => 'Error empty name',
		'-66' => 'Error empty email',
		'-67' => 'Error empty street address',
		'-68' => 'Error empty city',
		'-69' => 'Error empty state',
		'-79' => 'Error length customer name',
		'-80' => 'Error length shipto customer name',
		'-81' => 'Error length customer location',
		'-82' => 'Error length customer state',
		'-83' => 'Error length shipto phone',
		'-84' => 'Pos error duplicate ordernumber',
		'-85' => 'Pos error airline leg info invalid',
		'-86' => 'Pos error airline ticket info invalid',
		'-87' => 'Pos check error routing number must be 9 numeric digits',
		'-88' => 'Pos error check account number missing or invalid',
		'-89' => 'Pos error check MICR missing or invalid',
		'-90' => 'Pos error check number missing or invalid',
		'-91' => 'Pos error CVV2',
		'-92' => 'Pos error Error Approval Code',
		'-93' => 'Pos error Blind Credits Not Allowed',
		'-94' => 'Pos error Blind Credits Failed',
		'-95' => 'Pos error Voice Authorizations Not Allowed',
		'-96' => 'Pos Error Voice Authorizations Failed',
		'-97' => 'Pos Error Fraud Rejection',
		'-98' => 'Pos Error Invalid Discount Amount',
		'-99' => 'Pos Error Invalid Pin Block',
		'-100' => 'Pos Error Invalid Key Serial Number',
		'-101' => 'Pos Error Invalid Authentication Data',
		'-102' => 'Pos Error Authentication Data Not Allowed',
		'-103' => 'Pos Error Invalid Birth Date',
		'-104' => 'Pos Error Invalid Identification Type',
		'-105' => 'Pos Error Invalid Track Data',
		'-106' => 'Pos Error Invalid Account Type',
		'-107' => 'Pos Error Invalid Sequence Number',
		'-108' => 'Pos Error Invalid Transaction ID',
		'-109' => 'Pos Error Invalid From Account Type',
		'-110' => 'Pos Error Invalid To Account Type',
		'-112' => 'Pos Error Invalid Auth Option',
		'-113' => 'Pos Error Transaction Failed',
		'-114' => 'Pos Error Invalid Incoming Eci',
	);
?>