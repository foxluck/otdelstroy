<?php
/**
 *
 *
 */
define('POS_TTL','PayOnline System');
define('POS_DSCR','Online store integration with processing center PayOnline System (using the Standart scheme)');

define('POS_CFG_MERCHANT_ID_TTL','MerchantId');
define('POS_CFG_MERCHANT_ID_DSCR','Your ID obtained from the processing center');
define('POS_CFG_SECRET_KEY_TTL','PrivateSecurityKey');
define('POS_CFG_SECRET_KEY_DSCR','Your key obtained from the processing center');
define('POS_CFG_TRANSACTION_CURRENCY_TTL','Order currency');
define('POS_CFG_TRANSACTION_CURRENCY_DSCR','Currency in which transactions will be processed by the processing center.');
define('POS_CFG_SHOP_CURRENCY_TTL','Store currency');
define('POS_CFG_SHOP_CURRENCY_DSCR','Currency corresponding to the order currency.');
define('POS_CFG_GATEWAY_TTL','Redirection options');
define('POS_CFG_GATEWAY_DSCR','Customers can be redirected from the online storefront to the PayOnline System payment page via one of the following URLs.');
define('POS_CFG_VALID_UNTIL_TTL','"Pay before" period');
define('POS_CFG_VALID_UNTIL_DSCR','Payment period expressed in hours. Enter <strong>0</strong> to cancel the limitation.');
define('POS_CFG_CUSTOMER_LANG_TTL','Payment page language');
define('POS_CFG_CUSTOMER_LANG_DSCR','Choose the payment page language');
define('POS_CFG_DEBUGMODE_TTL','Debugging mode');
define('POS_CFG_DEBUGMODE_DSCR','Enable this option to save information about all transactions in a system file.');
define('POS_CFG_ORDERSTATUS_TTL','Automatic order status change');
define('POS_CFG_ORDERSTATUS_DSCR','All orders paid on the PayOnline System website will be automatically assigned the selected status (after receiving positive response from the PayOnline System server).');

define('POS_CUST_RESULT_URL_TTL','Callback URL for successful transactions');
define('POS_CUST_RESULT_URL_DSCR','Use this value to configure your PayOnline System account');
define('POS_CUST_FAIL_URL_TTL','Callback URL for declined transactions');
define('POS_CUST_FAIL_URL_DSCR','Use this value to configure your PayOnline System account');

define('POS_TXT_PROCESS','Pay via PayOnline System');

define('POS_TXT_CURRUB','(RUB) Russian ruble');
define('POS_TXT_CURUSD','(USD) US dollar');
define('POS_TXT_CUREUR','(EUR) Euro');

define('POS_TXT_GATEWAYCARD','Bank card payment form');
define('POS_TXT_GATEWAYSELECT','Payment method selection form');
define('POS_TXT_GATEWAYQIWI','QIWI payment form');
define('POS_TXT_GATEWAYWM','WebMoney payment form');

define('POS_TXT_LANUSER','Automatic selection');
define('POS_TXT_LANEN','English');
define('POS_TXT_LANRU','Russian');


define('POS_TXT_IAMOUNT','Amount');
define('POS_TXT_ITRANSCATIONID','Unique transaction or account identifier at QIWI/WebMoney');
define('POS_TXT_ICARDHOLDER','Cardholder name');
define('POS_TXT_ICARDNUMBER','Card number');
define('POS_TXT_ICOUNTRY','Country');
define('POS_TXT_IBINCOUNTRY','Country code determined from the card issuer BIN');
define('POS_TXT_ICITY','City');
define('POS_TXT_IADDRESS','Street address');
define('POS_TXT_IPHONE','Telephone number');
define('POS_TXT_IWMTRANID','Service WebMoney account number');
define('POS_TXT_IWMINVID','Unique WebMoney account number');
define('POS_TXT_IWMID','Payer WMID,');
define('POS_TXT_IWMPURSE','Payer WM purse number');
define('POS_TXT_IIPADDRESS','IP address');
define('POS_TXT_IIPCOUNTRY','Country code determined from the IP address');
define('POS_TXT_ISTATUS_CHANGED','Order status changed automatically');
define('POS_TXT_ICOMMENT_ADDED','Comment added automatically');
define('POS_TXT_DECLINED','Transaction declined');

define('POS_TXT_ERCODE1','An error has occurred, please try to pay again later.');
define('POS_TXT_ERCODE2','Payment by bank card is temporarily unavailable. Please choose a different payment method.');
define('POS_TXT_ERCODE3','Payment has been declined by the card issuing bank. Please contact your bank to find out the reason and try again later.');

?>