<?php
	define('CCTYPE_VISA', 'Visa');
	define('CCTYPE_MASTERCARD', 'MasterCard');
	define('CCTYPE_AMEXPRESS', 'AmericanExpress');
	define('CCTYPE_DINERS_CLUB', 'DinersClub');
	define('CCTYPE_DISCOVER', 'Discover');
	define('CCTYPE_JCB', 'JCB');
	define('CCTYPE_AUSTRALIANBANKCARD', 'AustralianBankCard');
	
	global $CardNames;
	
	$CardNames = array(
		CCTYPE_VISA => 'Visa',
		CCTYPE_MASTERCARD => 'Master Card',
		CCTYPE_AMEXPRESS => 'American Express',
		CCTYPE_DINERS_CLUB => 'Diners Club',
		CCTYPE_DISCOVER => 'Discover',
		CCTYPE_JCB => 'JCB',
		CCTYPE_AUSTRALIANBANKCARD => 'Australian BankCard'
	);
	
	global $CardBinTypes;
	
	$CardBinTypes = array(
		CCTYPE_VISA => 0,
		CCTYPE_MASTERCARD => 1,
		CCTYPE_AMEXPRESS => 2,
		CCTYPE_DINERS_CLUB => 3,
		CCTYPE_DISCOVER => 4,
		CCTYPE_JCB => 5,
		CCTYPE_AUSTRALIANBANKCARD => 6
	);
?>