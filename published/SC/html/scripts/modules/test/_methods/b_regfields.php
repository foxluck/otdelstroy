<?php
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();

if ( isset($_GET["delete"]) ){
	
	safeMode(1, 'delete=');

	DeleteRegField( $_GET["delete"] );
	Redirect(set_query('delete='));
}

if ( isset($_POST["save_fields"]) ) //save registration form custom fields
{
	safeMode(1);

	// add new field
	if ( !LanguagesManager::ml_isEmpty('reg_field_name', $_POST) ){
		
		AddRegField($_POST, isset($_POST["reg_field_required"]), $_POST["sort_order"] );
	}

	// update fields
	$data = scanArrayKeysForID($_POST, array( "reg_field_name_\w{2}", "reg_field_required", "sort_order" ) );
	
	foreach($data as $key => $val){
		
		UpdateRegField( $key, $val, isset($val["reg_field_required"]), $val["sort_order"] );
	}

	Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
}



if ( isset($_POST["save_address_form"]) ) //save address form configration
{
	
	safeMode(1);
	_setSettingOptionValue('CONF_ADDRESSFORM_STATE',(int)$_POST["addr_state"]);
	_setSettingOptionValue('CONF_ADDRESSFORM_ZIP',(int)$_POST["addr_zip"]);
	_setSettingOptionValue('CONF_ADDRESSFORM_CITY',(int)$_POST["addr_city"]);
	_setSettingOptionValue('CONF_ADDRESSFORM_ADDRESS',(int)$_POST["addr_address"]);
	Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
}


$fields=GetRegFields();
$smarty->assign("fields", $fields );

//set sub template
$smarty->assign("admin_sub_dpt", "custord_reg_fields.tpl.html");
?>