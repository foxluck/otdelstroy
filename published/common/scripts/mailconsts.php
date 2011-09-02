<?php

	define('MM_STATUS_DRAFT', 0);
	define('MM_STATUS_PENDING', 1);
	define('MM_STATUS_SENDING', 2);
	define('MM_STATUS_SENT', 3);
	define('MM_STATUS_RECEIVED', 4);
	define('MM_STATUS_ERROR', 99);
	define('MM_STATUS_TEMPLATE', 100);

	define('MM_TYPE_TEXT', 0);
	define('MM_TYPE_HTML', 1);
	define('MM_PRIORITY_LOW', 5);
	define('MM_PRIORITY_NORMAL', 3);
	define('MM_PRIORITY_HIGH', 1);

	$companyVariables = array(
		'COMPANY_NAME' => array(_('Company Name'), 'COM_NAME'),
		'COMPANY_STREETADDRESS' => array(_('Company Street Address'), 'COM_ADDRESSSTREET'),
		'COMPANY_CITY' => array(_('Company City'), 'COM_ADDRESSCITY'),
		'COMPANY_STATE' => array(_('Company State'), 'COM_ADDRESSSTATE'),
		'COMPANY_ZIP' => array(_('Company Zip'), 'COM_ADDRESSZIP'),
		'COMPANY_COUNTRY' => array(_('Company Country'), 'COM_ADDRESSCOUNTRY'),
		'COMPANY_CONTACTNAME' => array(_('Company Contact Name'), 'COM_CONTACTPERSON'),
		'COMPANY_CONTACTEMAIL' => array(_('Company Contact Email'), 'COM_EMAIL'),
		'COMPANY_CONTACTPHONE' => array(_('Company Contact Phone'), 'COM_PHONE'),
		'COMPANY_CONTACTFAX' => array(_('Company Contact Fax'), 'COM_FAX')
	);

	$limit = 0;
	if(Wbs::isHosted()) {
		$limit = Limits::get('MM');
	}

	define('MM_DAILY_SEND_LIMIT', $limit);

	define('PAGE_PREVIEW', '/common/html/scripts/preview.php');
	$_size_limit = '2000000'; // in bytes

	define('MMMESSAGE_MIN_ID', 100);

?>