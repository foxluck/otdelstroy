<?php
class AddressEditor extends ActionsController
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
		$addressEntry = new Address();
		$addressEntry->loadFromArray($this->getData('address'));
		$res = $addressEntry->checkInfo();
		if(PEAR::isError($res)){
			Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '' , array('Data'=>$this->getData()));
		}
		$addressID = intval($this->getData('addressID'));
		if($addressID){
			$addressEntry->addressID = $addressID;
		}

		$customerEntry = Customer::getAuthedInstance();
		$addressEntry->customerID = $customerEntry->customerID;
		$addressEntry->save();

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=address_book', 'msg_information_saved');

	}

	function main()
	{
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty*/
		$addressID = intval($this->getData('addressID'));
		if($addressID){
			$address = regGetAddressByLogin( $addressID, $_SESSION['log'] );
			if($address === false )RedirectSQ('?ukey=home');
		}
		if($addressID)$smarty->assign('address', $address);

		Message::loadData2Smarty();
		$smarty_address = $smarty->get_template_vars('address');

		$smarty->assign('zones', array('address' => znGetZonesById($smarty_address['countryID']?$smarty_address['countryID']:CONF_DEFAULT_COUNTRY)) );

		$callBackParam = null;
		$count_row = 0;
		$smarty->assign('countries', cnGetCountries( $callBackParam, $count_row ) );

		$smarty->assign('CurrentSubTpl', 'address_editor.tpl.html');

	}
}

ActionsController::exec('AddressEditor');

//old code
/*
if($addressID){
	$address = regGetAddressByLogin( $addressID, $_SESSION['log'] );
	if($address === false )RedirectSQ('?ukey=home');
}

if(isset($_POST['action']) && $_POST['action'] == 'save' ){

	$addressEntry = new Address();

	$addressEntry->loadFromArray($_POST['address']);
	$res = $addressEntry->checkInfo();
	if(PEAR::isError($res)){
		Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '' , array('Data'=>$_POST));
	}

	if($addressID && $address_editor)$addressEntry->addressID = $addressID;

	$customerEntry = Customer::getAuthedInstance();
	$addressEntry->customerID = $customerEntry->customerID;

	$addressEntry->save();

	Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=address_book', 'msg_information_saved');
}

if($addressID)$smarty->assign('address', $address);

if(isset($_POST['action']) && $_POST['action'] = 'change_country'){

	$smarty->assign('address', $_POST['address']);
}

Message::loadData2Smarty();
$smarty_address = $smarty->get_template_vars('address');

$smarty->assign('zones', array('address' => znGetZonesById($smarty_address['countryID']?$smarty_address['countryID']:CONF_DEFAULT_COUNTRY)) );


$callBackParam = null;
$count_row = 0;
$smarty->assign('countries', cnGetCountries( $callBackParam, $count_row ) );

$smarty->assign('CurrentSubTpl', 'address_editor.tpl.html');
*/
?>