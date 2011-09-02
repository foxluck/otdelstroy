<?php
define('CAUTHORIZENETAIM_TTL', 
	'Authorize.Net AIM');
define('CAUTHORIZENETAIM_DSCR', 
	'Authorize.Net Advanced Integration Module<br>Credit card information is collected on your web site and then transferred to Authorize.Net web site.');
	
define('CAUTHORIZENETAIM_CFG_LOGIN_TTL', 
	'Authorize.Net Login ID');
define('CAUTHORIZENETAIM_CFG_LOGIN_DSCR',
	'Please input your merchant login ID<br>This information is stored in encrypted way');
define('CAUTHORIZENETAIM_CFG_TRANKEY_TTL', 
	'Authorize.Net transaction key');
define('CAUTHORIZENETAIM_CFG_TRANKEY_DSCR', 
	'Please indicate transaction key which can be obtained from your Auhtorize.Net account<br>This information is stored in encrypted way');
define('CAUTHORIZENETAIM_CFG_TESTMODE_TTL', 
	'Test mode');
define('CAUTHORIZENETAIM_CFG_TESTMODE_DSCR', 
	'');
define('CAUTHORIZENETAIM_CFG_SAVE_CC_INFORMATION_TTL', 
	'Save customer`s credit card data');
define('CAUTHORIZENETAIM_CFG_SAVE_CC_INFORMATION_DSCR', 
	'Enable this option if you would like to save customer`s credit card data in the database (it is stored in encrypted way)');
define('CAUTHORIZENETAIM_CFG_AUTHORIZATION_TYPE_TTL', 
	'Authorization type');
define('CAUTHORIZENETAIM_CFG_AUTHORIZATION_TYPE_DSCR', 
	'Credit card authorization type');
define('CAUTHORIZENETAIM_CFG_WFSS_MERCHANT_TTL', 
	'Wells Fargo Secure Source merchant');
define('CAUTHORIZENETAIM_CFG_WFSS_MERCHANT_DSCR', 
	'Please enable this checkbox if you are working with Wells Fargo Secure Source');

define('CAUTHORIZENETAIM_TXT_1', 
	'Authorization only');
define('CAUTHORIZENETAIM_TXT_2', 
	'Authorize and capture');
define('CAUTHORIZENETAIM_TXT_3', 
	'Prior auth capture');
define('CAUTHORIZENETAIM_TXT_4', 
	'Customer type');
define('CAUTHORIZENETAIM_TXT_5', 
	'Business');
define('CAUTHORIZENETAIM_TXT_6', 
	'Individual');
define('CAUTHORIZENETAIM_TXT_7', 
	'Company');
define('CAUTHORIZENETAIM_TXT_8', 
	'Phone number');
define('CAUTHORIZENETAIM_TXT_9', 
	'Fax number');
define('CAUTHORIZENETAIM_TXT_10', 
	'(please leave blank if not applicable)');
define('CAUTHORIZENETAIM_TXT_11', 
	'Credit card payment');
define('CAUTHORIZENETAIM_TXT_12', 
	'Credit card number');
define('CAUTHORIZENETAIM_TXT_13', 
	'Cardholder name');
define('CAUTHORIZENETAIM_TXT_14', 
	'month');
define('CAUTHORIZENETAIM_TXT_15', 
	'year');
define('CAUTHORIZENETAIM_TXT_16', 
	'eCheck.Net payment');
define('CAUTHORIZENETAIM_TXT_17', 
	'Bank account number:');
define('CAUTHORIZENETAIM_TXT_18', 
	'Bank name:');
define('CAUTHORIZENETAIM_TXT_19', 
	'Account type:');
define('CAUTHORIZENETAIM_TXT_20', 
	'Checking');
define('CAUTHORIZENETAIM_TXT_21', 
	'Savings');
define('CAUTHORIZENETAIM_TXT_22', 
	'Bank account number:');
define('CAUTHORIZENETAIM_TXT_23', 
	'Name under account:');
define('CAUTHORIZENETAIM_TXT_24', 
	'<b>eCheck payment verification</b><br>(please fill in your tax ID <b>OR</b> driver\'s license information)');
define('CAUTHORIZENETAIM_TXT_25', 
	' Tax ID:');
define('CAUTHORIZENETAIM_TXT_26', 
	' Driver\'s license number:');
define('CAUTHORIZENETAIM_TXT_27', 
	'Driver\'s license state:');
define('CAUTHORIZENETAIM_TXT_28', 
	'Driver\'s license owner\'s date of birth:');
define('CAUTHORIZENETAIM_TXT_29', 
	'Enter your company name');
define('CAUTHORIZENETAIM_TXT_30', 
	'Enter your phone number');
define('CAUTHORIZENETAIM_TXT_31', 
	'Enter your credit card number');
define('CAUTHORIZENETAIM_TXT_32', 
	'Enter CVV (3-digit number on the back side of your card)');
define('CAUTHORIZENETAIM_TXT_33', 
	'Enter card expiration month and year');
define('CAUTHORIZENETAIM_TXT_34', 
	'Enter card expiration month and year');
define('CAUTHORIZENETAIM_TXT_35', 
	'Enter bank routing number');
define('CAUTHORIZENETAIM_TXT_36', 
	'Enter bank account number');
define('CAUTHORIZENETAIM_TXT_37', 
	'Enter bank name');
define('CAUTHORIZENETAIM_TXT_38', 
	'Enter name under account');
define('CAUTHORIZENETAIM_TXT_39', 
	'Enter your tax ID');
define('CAUTHORIZENETAIM_TXT_40', 
	'Enter your driver\'s license number');
define('CAUTHORIZENETAIM_TXT_41', 
	'Enter state of driver\'s license state issue');
define('CAUTHORIZENETAIM_TXT_42', 
	'Enter date of birth');

	
define('CAUTHORIZENETAIM_TXT_DEFAULT' ,'Default');

define('CAUTHORIZENETAIM_CFG_ORDERSTATUS_TTL', 'Append approved orders following status');
define('CAUTHORIZENETAIM_CFG_ORDERSTATUS_DSCR', 'If you would like successfully processed orders to be automatically assigned a particular order status please select the status. Select "Default" to assign successful orders default new orders status (which is configured in "Configuration" screen of back end)');

define('CAUTHORIZENETAIM_CFG_DECLINE_ECHECK_TTL','Store does NOT accept eCheck payments');
define('CAUTHORIZENETAIM_CFG_DECLINE_ECHECK_DSCR','');
?>