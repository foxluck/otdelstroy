<?php
// *****************************************************************************
// Purpose	gets all additional fields (see registry form)
// Inputs   nothing
// Returns	array of item
//				each item
//					"reg_field_ID"			- field id
//					"reg_field_name"		- field name
//					"reg_field_required"	- 1, if field is required to set
//					"sort_order"			- sort order
function GetRegFields(){
	
	$q=db_phquery('
		SELECT *, '.LanguagesManager::sql_prepareField('reg_field_name').' AS reg_field_name FROM ?#CUSTOMER_REG_FIELDS_TABLE
		ORDER BY sort_order, reg_field_name' );
	$data=array();
	while( $row=db_fetch_assoc($q) ){
		
		LanguagesManager::ml_fillFields(CUSTOMER_REG_FIELDS_TABLE, $row);
		$data[]=$row;
	}
	return $data;
}


// *****************************************************************************
// Purpose	add additional field
// Inputs		$reg_field_name			- field name
//				$reg_field_required		- 1, if field is required to set
//				$sort_order				- sort order
// Returns	nothing
function AddRegField($reg_field_name, $reg_field_required, $sort_order){
	
	$name_inj = LanguagesManager::sql_prepareFieldInsert('reg_field_name', $reg_field_name);
	$sql = "
		INSERT ?#CUSTOMER_REG_FIELDS_TABLE ({$name_inj['fields']}, reg_field_required, sort_order) VALUES({$name_inj['values']},?,?) 
	";
	db_phquery($sql, $reg_field_required,$sort_order);
}


// *****************************************************************************
// Purpose	delete additional field
// Inputs		$reg_field_ID			- field id
// Returns	nothing
function DeleteRegField($reg_field_ID){
	
	$sql = '
		DELETE FROM ?#CUSTOMER_REG_FIELDS_VALUES_TABLE WHERE reg_field_ID=?
	';
	db_phquery($sql, $reg_field_ID);
	$sql = '
		DELETE FROM ?#CUSTOMER_REG_FIELDS_TABLE WHERE reg_field_ID=?
	';
	db_phquery($sql, $reg_field_ID);
}


// *****************************************************************************
// Purpose	update additional field
// Inputs		
//				$reg_field_ID			- field id
//				$reg_field_name			- field name
//				$reg_field_required		- 1, if field is required to set
//				$sort_order				- sort order
// Returns	nothing
function UpdateRegField($reg_field_ID, $reg_field_name, $reg_field_required, $sort_order){
	
	$sql = '
		UPDATE ?#CUSTOMER_REG_FIELDS_TABLE SET '.LanguagesManager::sql_prepareFieldUpdate('reg_field_name', $reg_field_name).', reg_field_required=?,sort_order=? WHERE reg_field_ID=?
	';
	db_phquery($sql, $reg_field_required,$sort_order,$reg_field_ID);
}


// *****************************************************************************
// Purpose	set additional field value to customer
// Inputs		
//				$reg_field_ID		- field id
//				$customer_login		- login
//				$reg_field_value	- value (string)
// Returns	nothing
function SetRegField($reg_field_ID, $customer_login, $reg_field_value){
	
	$customerID = regGetIdByLogin( $customer_login );
	
	$sql = '
		SELECT COUNT(*) FROM ?#CUSTOMER_REG_FIELDS_VALUES_TABLE WHERE reg_field_ID=? AND customerID=?
	';

	$q=db_phquery( $sql, $reg_field_ID, $customerID );
	$r=db_fetch_row($q);
	if ( $r[0] == 0 ){
		
		if ( trim($reg_field_value) == "" )return;
		
		$sql = '
			INSERT ?#CUSTOMER_REG_FIELDS_VALUES_TABLE (reg_field_ID, customerID, reg_field_value) VALUES(?,?,?)
		';
		db_phquery($sql,$reg_field_ID,$customerID,$reg_field_value);
	}
	else
	{
		if ( trim($reg_field_value) == "" ){
			
			$sql = '
				DELETE FROM ?#CUSTOMER_REG_FIELDS_VALUES_TABLE WHERE reg_field_ID=? AND customerID=?
			';
			db_phquery($sql,$reg_field_ID,$customerID);
		}else{
			
			$sql = '
				UPDATE ?#CUSTOMER_REG_FIELDS_VALUES_TABLE SET reg_field_value=? WHERE reg_field_ID=? AND customerID=?
			';
			db_phquery($sql,$reg_field_value,$reg_field_ID,$customerID);
		}
	}
}


// *****************************************************************************
// Purpose	
// Inputs		
//				$reg_field_ID		- field id
//				$customer_login		- login
//				$reg_field_value	- value (string)
// Returns	1 if field requred to set, 0 otherwise
function GetIsRequiredRegField($reg_field_ID){
	
	$sql = '
		SELECT reg_field_required FROM ?#CUSTOMER_REG_FIELDS_TABLE WHERE reg_field_ID=?
	';
	$q=db_phquery($sql, $reg_field_ID);
	$r=db_fetch_row($q);
	return $r["reg_field_required"];
}


// *****************************************************************************
// Purpose	gets additional reg fields values of a registered customer
// Inputs	customerID
// Returns	array of item
//				each item
//					"reg_field_ID"			- field id
//					"reg_field_name"		- field name
//					"reg_field_value"		- value
function GetRegFieldsValuesByCustomerID( $customerID )
{
	//get customer
	$customerID = (int) $customerID;
	if (!$customerID) return array();

	$q = db_query('SELECT *,'.LanguagesManager::sql_prepareField('reg_field_name').' AS reg_field_name FROM '.CUSTOMER_REG_FIELDS_TABLE.' ORDER BY sort_order, reg_field_name');
	$data=array();
	while( $r=db_fetch_row($q) )
	{
		LanguagesManager::ml_fillFields(CUSTOMER_REG_FIELDS_TABLE, $r);
		$q1=db_query("select reg_field_value from ".
			CUSTOMER_REG_FIELDS_VALUES_TABLE." where reg_field_ID='".$r["reg_field_ID"].
				"' AND customerID='".$customerID."'" );
		$reg_field_value="";
		if ( $r1=db_fetch_row($q1) )
			$reg_field_value = $r1["reg_field_value"];
		if ( strlen( trim($reg_field_value) ) > 0 )
		{
			$row=array();
			$row["reg_field_ID"]	= $r["reg_field_ID"];
			$row["reg_field_name"]	= $r["reg_field_name"];
			$row["reg_field_value"] = $reg_field_value;
			$data[]=$row;
		}	
	}
	return $data;
}


// *****************************************************************************
// Purpose	gets additional reg fields values of a registered customer
// Inputs	customer login
// Returns	array of item
//				each item
//					"reg_field_ID"			- field id
//					"reg_field_name"		- field name
//					"reg_field_value"		- value
function GetRegFieldsValues( $customer_login )
{
	//get customer
 	$customerID = regGetIdByLogin( $customer_login );
	if (!$customerID) return array();

	return GetRegFieldsValuesByCustomerID( $customerID );
}

// *****************************************************************************
// Purpose	gets additional field values of a customer by orderID
// Inputs	
// Returns	array of item
//				each item
//					"reg_field_ID"			- field id
//					"reg_field_name"		- field name
//					"reg_field_value"		- value
function GetRegFieldsValuesByOrderID( $orderID )
{
 	$orderID = (int) $orderID;
	if (!$orderID) return array();

	//check if this order has been made by a registered customer or not (quick checkout)
	$q=db_query("select customerID from ".
		ORDERS_TABLE." where orderID = $orderID;");
	$row = db_fetch_row($q);
	if ($row[0] > 0)
		return GetRegFieldsValuesByCustomerID( $row[0] ); //made by a registered customer

	//quick checkout
	$q=db_query("select *, ".LanguagesManager::sql_prepareField('reg_field_name')." AS reg_field_name from ".
		CUSTOMER_REG_FIELDS_TABLE." order by sort_order, reg_field_name ");
	$data = array();
	while( $r=db_fetch_row($q) )
	{
		LanguagesManager::ml_fillFields(CUSTOMER_REG_FIELDS_TABLE, $r);
		$q1=db_query("select reg_field_value from ".
			CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG." where reg_field_ID='".$r["reg_field_ID"].
				"' AND orderID='".$orderID."'" );
		$reg_field_value="";
		if ( $r1=db_fetch_row($q1) )
			$reg_field_value = $r1["reg_field_value"];
		if ( strlen( trim($reg_field_value) ) > 0 )
		{
			$row=array();
			$row["reg_field_ID"]	= $r["reg_field_ID"];
			$row["reg_field_name"]	= $r["reg_field_name"];
			$row["reg_field_value"] = $reg_field_value;
			$data[]=$row;
		}	
	}
	return $data;
}
?>