<?php
/**
 *
 * QiwiSOAP
 */
define('QIWI_TTL','QIWI');
define('QIWI_DSCR','Integration with the QIWI payment system via SOAP protocol');
define('QIWI_CFG_LOGIN_TTL','Identifier (login name)');
define('QIWI_CFG_LOGIN_DSCR','');
define('QIWI_CFG_PASSWORD_TTL','Password');
define('QIWI_CFG_PASSWORD_DSCR','');
define('QIWI_CFG_PREFIX_TTL',	'Invoice number prefix');
define('QIWI_CFG_PREFIX_DSCR',	'Use digits and Latin letters to enter a QIWI invoice number prefix');
define('QIWI_CFG_LIFETIME_TTL','Invoice lifetime');
define('QIWI_CFG_LIFETIME_DSCR','Specify invoice payment period in hours');
define('QIWI_CFG_CUSTOMER_PHONE_TTL','Customer telephone number');
define('QIWI_CFG_CUSTOMER_PHONE_DSCR','Select the field of your registration form, which corresponds to customer\'s telephone number');
define('QIWI_CFG_ALARM_TTL','Notifications');
define('QIWI_CFG_ALARM_DSCR','Notification sending options');
define('QIWI_CFG_CURRENCY_TTL','Rubles');
define('QIWI_CFG_CURRENCY_DSCR','Select the corresponding currency for correct re-calculation of the order amount');
define('QIWI_CFG_SUCCESS_STATUS_TTL','Paid order status');
define('QIWI_CFG_SUCCESS_STATUS_DSCR','Select a status for paid orders');
define('QIWI_CFG_CANCEL_STATUS_TTL','Canceled order status');
define('QIWI_CFG_CANCEL_STATUS_DSCR','Select a status for canceled orders');
define('QIWI_CFG_TESTMODE_TTL',		'Process requests without password');
define('QIWI_CFG_TESTMODE_DSCR',		'Use this mode to process requests manually initiated from your QIWI account.');

define('QIWI_CUST_SOAP_URL_TTL','URL');
define('QIWI_CUST_SOAP_URL_DSCR','Use this value to configure integration via SOAP protocol in your QIWI account');
define('QIWI_TXT_QIWI_ROBOT','QIWI notification');
define('QIWI_TXT_CUSTOMER_PHONE','Mobile telephone');
define('QIWI_TXT_INVALID_CUSTOMER_PHONE','Invalid mobile telephone number');
define('QIWI_TXT_INVALID_AMOUNT','Incorrect payment amount');
define('QIWI_TXT_MANUAL','Request initiated without a password');

define('QIWI_TXT_ALARM0','do not notify');
define('QIWI_TXT_ALARM1','SMS message');
define('QIWI_TXT_ALARM2','telephone call');
?>