<?php
class RegisterCustomer extends ActionsController
{
	function ajax_get_states()
	{
		$country_id = $this->getData('country_id');
		$states = znGetZones($country_id);
		$GLOBALS['_RESULT'] = array(
		'states' => $states
		);
		die();
	}

	function save()
	{
		global $smarty_mail;
		$login	= $this->getData('login');
		$cust_password1 = $this->getData('cust_password1');
		$cust_password2 = $this->getData('cust_password2');
		$first_name = $this->getData('first_name');
		$last_name = $this->getData('last_name');
		$Email = $this->getData('email');
		$subscribed4news = ( $this->getData('subscribed4news')? 1 : 0 );
		$additional_field_values = scanArrayKeysForID($this->getData(), array( 'additional_field' ) );
		$affiliationLogin	= $this->getData('affiliationLogin');
		$countryID = $this->getData('address','countryID');
		if(!$countryID)$countryID = CONF_DEFAULT_COUNTRY;
		$state = $this->getData('address','state');
		$zip = $this->getData('address','zip');
		$city = $this->getData('address','city');
		$address = $this->getData('address','address');
		if (!($zoneID = $this->getData('address','zoneID')))
		$zoneID = 0;

		$error = regVerifyContactInfo( $login, $cust_password1, $cust_password2,
		$Email, $first_name, $last_name, $subscribed4news,
		$additional_field_values );

		if ( $error == "" ) unset( $error );

		if (!isset($error) && isset($affiliationLogin))
		if ( !regIsRegister($affiliationLogin) && $affiliationLogin)
		$error = translate("err_wrong_referrer");
		if ( !isset($error) )
		if ( regIsRegister($login) )
		$error = translate("err_user_already_exists");

		if ( !isset($error) ){

			$error = regVerifyAddress(	$first_name, $last_name, $countryID, $zoneID, $state, $zip, $city, $address );
			if ( $error == "" ) unset( $error );
		}

		if((!isset($error) ||!$error) && CONF_ENABLE_CONFIRMATION_CODE){

			require_once(DIR_CLASSES.'/class.ivalidator.php');
			$iVal = new IValidator();
			if(!$iVal->checkCode($this->getData('fConfirmationCode')))$error = translate("err_wrong_ccode");
		}

		if ( !isset($error) ){

			$cust_password = $cust_password1;

			$registerResult = regRegisterCustomer($login, $cust_password, $Email, $first_name,$last_name, $subscribed4news, $additional_field_values
			,$affiliationLogin
			);

			if ( $registerResult ){

				$addressID = regAddAddress($first_name, $last_name, $countryID,$zoneID, $state, $zip, $city,$address, $login, $errorCode );
				if(!$addressID){
					Message::raiseMessageRedirectSQ(MSG_ERROR, '', $errorCode, '', array('Data'=>$this->getData()));
				}
				regSetDefaultAddressIDByLogin( $login, $addressID );

				regEmailNotification( $smarty_mail,
				$login, $cust_password, $Email, $first_name,
				$last_name, $subscribed4news, $additional_field_values,
				$countryID, $zoneID, $state, $zip, $city, $address, 0 );

				if(!CONF_ENABLE_REGCONFIRMATION){
					regAuthenticate( $login, $cust_password );
				}

				RedirectSQ('ukey=successful_registration');
			}else{
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'err_input_state', '', array('Data'=>$this->getData()));
			}
		}else{

			Message::raiseMessageRedirectSQ(MSG_ERROR, '', $error, '', array('Data'=>$this->getData()));
		}
	}

	function main()
	{
		$smarty = &Core::getSmarty();
		// countries
		$callBackParam = array();
		$count_row = 0;
		$countries = cnGetCountries( $callBackParam, $count_row );
		$smarty->assign('countries', $countries );

		if(!Message::loadData2Smarty()){
			$smarty->assign('subscribed4news', 1);
		}
		Message::loadData2Smarty('post_data');

		$address = $smarty->get_template_vars('address');
		if(!isset($address['countryID'])){
			$address['countryID'] = CONF_DEFAULT_COUNTRY;
		}

		if(count($countries)){

			$zones = znGetZonesById($address['countryID']);
			$smarty->assign('zones', array('address'=>$zones));
		}

		// additional fields
		$additional_fields=GetRegFields();
		$smarty->assign('additional_fields', $additional_fields );


		if(isset($_SESSION['s_RefererLogin']))$smarty->assign('SessionRefererLogin', $_SESSION['s_RefererLogin']);
		$smarty->assign('main_content_template', 'register.html');
	}
}

ActionsController::exec('RegisterCustomer');
?>