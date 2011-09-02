<?php
define('INTERKASSA_TTL','<span style="font-weight: bolder; color: rgb(108, 109, 112);">INTER<span style="color:#1eabad;">KASSA</span></span>');
define('INTERKASSA_DSCR','Payment collection system INTERKASSA (<a href="http://www.interkassa.com" target="_blank">http://www.interkassa.com</a>)');

define('INTERKASSA_CFG_SHOP_ID_TTL','Store ID');
define('INTERKASSA_CFG_SHOP_ID_DSCR','Your online store ID registered in INTERKASSA, to which payment has been made.
<p>Example: <em>64C18529-4B94-0B5D-7405-F2752F2B716C</em></p>');

define('INTERKASSA_CFG_SECRET_KEY_TTL','Secret key');
define('INTERKASSA_CFG_SECRET_KEY_DSCR','Secret key is a text string appended to the payment credentials, which are sent to merchant together with the payment notification.
<br />It is used to enhance the security of the notification identification and should not be disclosed to third parties.');

define('INTERKASSA_CFG_DEBUGMODE_TTL','Debugging mode');
define('INTERKASSA_CFG_DEBUGMODE_DSCR','Enable this option to log automatic order processing actions.');

define('INTERKASSA_CFG_PAYSYSTEM_ALIAS_TTL','Payment method');
define('INTERKASSA_CFG_PAYSYSTEM_ALIAS_DSCR','This field allows you to limit the number of available payment methods only to one. To allow customers to select the desired payment method, leave this field <strong>empty</strong>.');

define('INTERKASSA_CFG_SHOPCURRENCY_TTL','Currency');
define('INTERKASSA_CFG_SHOPCURRENCY_DSCR','The currency in which order amount will be transferred to the Interkassa payment gateway.');

define('INTERKASSA_CFG_ORDERSTATUS_TTL','Automatic order status change');
define('INTERKASSA_CFG_ORDERSTATUS_DSCR','All orders paid on the IKI website will be automatically assigned the selected status (after receiving positive response from the IKI server).');

define('INTERKASSA_CUST_RESULTURL_TTL','Status URL');
define('INTERKASSA_CUST_RESULTURL_DSCR','Destination URL for payment notifications. <strong>Copy and paste this address into the corresponding setting field in your IKI account.</strong>');

define('INTERKASSA_CUST_SUCCESURL_TTL','Success URL');
define('INTERKASSA_CUST_SUCCESURL_DSCR','URL of the successful payment confirmation page. <strong>Copy and paste this address into the corresponding setting field in your IKI account.</strong>');

define('INTERKASSA_CUST_FAILURE_TTL','Fail URL');
define('INTERKASSA_CUST_FAILURE_DSCR','URL of the failed payment notification page. <strong>Copy and paste this address into the corresponding setting field in your IKI account.</strong>');

define('INTERKASSA_TXT_PROCESS', 'Pay now!');
define('INTERKASSA_TXT_CUSTOMER_CHOICE', 'Customer choice');

