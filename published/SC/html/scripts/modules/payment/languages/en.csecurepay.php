<?php
define('CSECUREPAY_TTL','Secure Pay');
define('CSECUREPAY_DSCR','');
define('CSECUREPAY_CFG_MERCH_ID_TTL','Merchant ID');
define('CSECUREPAY_CFG_MERCH_ID_DSCR','');
define('CSECUREPAY_CFG_USD_TTL', 'USD currency');
define('CSECUREPAY_CFG_USD_DSCR', '');
define('CSECUREPAY_CFG_AVS_TTL', 'AVS');
define('CSECUREPAY_CFG_AVS_DSCR', 'Note: The AVS system used by SecurePay.Com supports the United States. All AVS requests for Card Accounts issued outside the US may or may not return an AVS match. It is dependent on the card issuing bank and whether that card issuing bank subscribes and participates with the AVS system.');

define('CSECURE_FORM_TTL','The information about your credit card');
define('CSECURE_FORM_SP_NAME_TTL','The name on the credit card');
define('CSECURE_FORM_SP_CC_NUMBER_TTL','The account number of the credit card (No dashes or spaces)');
define('CSECURE_FORM_SP_MONTH_TTL','The month of expiration on the card');
define('CSECURE_FORM_SP_YEAR_TTL','The year of expiration on the card');
define('CSECURE_FORM_SP_STREET_TTL','The street address in the billing address for the credit card');
define('CSECURE_FORM_SP_CITY_TTL','The City in the billing address for the credit card');
define('CSECURE_FORM_SP_STATE_TTL','The State in the billing address for the credit card');
define('CSECURE_FORM_SP_ZIP_TTL','The Zip code in the billing address for the credit card');
define('CSECURE_FORM_SP_COUNTRY_TTL','The Country in the billing address for the credit card');

define('CSECURE_RESPONSE_FLD_APPROVAL_NUMBER','Approval number');
define('CSECURE_RESPONSE_FLD_CARD_RESPONSE','Card Response');
define('CSECURE_RESPONSE_FLD_AVS_DATA','AVS Data');

define('CSECURE_AVS_A','Address (Street) matches, Zip does not.');
define('CSECURE_AVS_E','AVS Error');
define('CSECURE_AVS_G','Issuing bank does not subscribe to the AVS system.');
define('CSECURE_AVS_N','no match on Address or Zip Code.');
define('CSECURE_AVS_R','Retry, system unavailable or timed out.');
define('CSECURE_AVS_S','Service not supported by issuer.');
define('CSECURE_AVS_U','Address information unavailable.');
define('CSECURE_AVS_W','9 digit Zip matches, Address does not.');
define('CSECURE_AVS_X','Exact AVS match.');
define('CSECURE_AVS_Y','Address and 5 digit zip code match.');
define('CSECURE_AVS_Z','5 digit Zip Code matches, Address does not.');

define('CSECUREPAY_AVS0_DSCR','when you do not want to do an AVS Check');
define('CSECUREPAY_AVS1_DSCR','when you want a transaction authorization and a  Full AVS (both street address and zip code)');
define('CSECUREPAY_AVS2_DSCR','when you want an AVS only, Full AVS but do not authorize the Credit Card');
define('CSECUREPAY_AVS3_DSCR','when you want Credit Card Authorization and Zip Code AVS Only');
define('CSECUREPAY_AVS4_DSCR','when you want AVS with Zip Code only, do not authorize the Credit Card');
?>
