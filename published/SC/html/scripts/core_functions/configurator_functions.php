<?php
// Purpose	gets all options
function cfgGetOptions()
{
	$options = db_query("SELECT optionID, ".LanguagesManager::sql_prepareField('name')." AS name FROM ".PRODUCT_OPTIONS_TABLE);
	$data = array();
	while( $option_row = db_fetch_row($options) )
	$data[] = $option_row;
	return $data;
}

function cfgGetProductOptionValue( $productID ){

	$data = array();
	
	$options = db_query("SELECT *, ".LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'name')." FROM ".PRODUCT_OPTIONS_TABLE." ORDER BY sort_order, ".LanguagesManager::sql_getSortField(PRODUCT_OPTIONS_TABLE, 'name'));
	while( $option_row = db_fetch_row($options) ) {
		$optionID = $option_row["optionID"];
		$item = array();
		LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $option_row);
		$item["option_row"]		= $option_row;
		$item["option_value"] = array(
			'option_value'=>null,
			'option_type'=>0,
			'option_show_times'=>1,
		);

		$item['value_count'] = 0;

		if(isset($value_counts[$optionID])) {
			$item['value_count'] = $value_counts[$optionID];
		}
		$data[$optionID] = $item;
	}

	$sql = "SELECT COUNT(*) AS 'cnt', `optionID` FROM `?#PRODUCTS_OPTIONS_SET_TABLE` WHERE `productID`=? GROUP BY `optionID`";
	$res = db_phquery($sql,$productID);
	while($row = db_fetch_assoc($res)) {
		$optionID = $row["optionID"];
		if(isset($data[$optionID])) {
			$data[$optionID]['value_count'] = $row['cnt'];
		}
	}

	$sql = 'SELECT * FROM `?#PRODUCT_OPTIONS_VALUES_TABLE` WHERE `productID`=?';
	$res = db_phquery($sql, $productID);
	while( $row=db_fetch_assoc($res)) {
		$optionID = $row["optionID"];
		if(isset($data[$optionID])) {
			LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_VALUES_TABLE, $row);
			$data[$optionID]['option_value'] = $row;
		}
	}

	return $data;
}

function cfgSet_N_VALUES_OptionType( $productID, $optionID ){

	$q = db_phquery( "SELECT COUNT(*) FROM `?#PRODUCT_OPTIONS_VALUES_TABLE` WHERE optionID=? AND productID=?", $optionID, $productID );
	$count = db_fetch_row($q);
	$count = $count[0];

	if ( $count == 0 ){
		$dbq = "
			INSERT ?#PRODUCT_OPTIONS_VALUES_TABLE
			( optionID, productID, option_type, option_show_times )
			VALUES( ?optionID, ?productID, '', 2, 1 )
		";
	}
	else{
		$dbq = "
			UPDATE ?#PRODUCT_OPTIONS_VALUES_TABLE SET OPTION_TYPE=1
			WHERE productID=?productID AND optionID=?optionID
		";
	}
	db_phquery($dbq, array('optionID' => $optionID, 'productID' => $productID));
}

function cfgUpdateOptionValue( $productID, $updatedValues ){
	$productID = intval($productID);
	foreach( $updatedValues as $key => $value ){
		$key = intval($key);
		if ( $updatedValues[$key]["option_radio_type"] == "UN_DEFINED" || $updatedValues[$key]["option_radio_type"] == "ANY_VALUE" ) {
			$option_type=0;
		}
		else{
			$option_type=1;
		}
		if ( $updatedValues[$key]["option_radio_type"] == "UN_DEFINED" ){

			$option_value = null;
		}else{

			$option_value = $updatedValues[$key];
		}

		$where_clause = " WHERE optionID={$key} AND productID={$productID}";

		$q=db_query("SELECT COUNT(*) FROM ".PRODUCT_OPTIONS_VALUES_TABLE." ".$where_clause );
		$r = db_fetch_row($q);
		$fields = LanguagesManager::ml_getLangFieldNames('option_value');

		if ( $r[0]==1 ){ // if row exists
			foreach($fields as &$field){
				$field = "`{$field}` = ?{$field}";
			}
			$fields = implode(', ',$fields);
			$data = $option_value;
			$data['option_type'] = $option_type;
			$dbq = "UPDATE ?#PRODUCT_OPTIONS_VALUES_TABLE
				SET {$fields}, option_type=?option_type ".$where_clause;
			db_phquery($dbq, $data);
		}else{ // insert query

			$data_place = '';
			foreach($fields as $field){
				$data_place .= "?{$field}, ";
			}
			$fields = implode(', ',$fields);
			$dbq = "INSERT ?#PRODUCT_OPTIONS_VALUES_TABLE (optionID, productID, {$fields}, option_type)
				VALUES(?optionID, ?productID, {$data_place} ?option_type)";
			$data = $option_value;
			$data['optionID'] = $key;
			$data['productID'] = $productID;
			$data['option_type'] = $option_type;

			db_phquery($dbq, $data);
		}
	}
}

// Purpose	this function updates product option that can be configurated by customer
// Inputs     		$option_show_times - how many times do show in user part
//			$variantID_default - option id (FK) refers to
//				PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE (PK)
//			$setting - structure
//				$setting[ <optionID> ]["switchOn"] - if true show this
//						value in user part
//				$setting[ <optionID> ]["price_surplus"] - price surplus when
//						this option is selected by user
// Returns		nothing
function UpdateConfiguriableProductOption($optionID, $productID, $option_show_times, $variantID_default, $setting ){
	$optionID = intval($optionID);
	$productID = intval($productID);
	$where_clause = " WHERE optionID={$optionID} AND productID={$productID}";

	$q = db_query( "SELECT COUNT(*) FROM ".PRODUCT_OPTIONS_VALUES_TABLE.$where_clause );
	$r=db_fetch_row($q);
	if ( $r[0]!=0 ){
		$fields = LanguagesManager::ml_getLangFieldNames('option_value');
		foreach($fields as &$field){
			$field = "`{$field}` = ?{$field}";
		}
		$fields = implode(', ',$fields);
		$data = $option_value;
		$data['option_show_times'] = $option_show_times;
		$data['variantID'] = $variantID_default;
		$sql = "UPDATE ?#PRODUCT_OPTIONS_VALUES_TABLE SET {$fields}, `option_show_times`=?option_show_times, `variantID`=?variantID ".$where_clause;
		db_phquery($sql,$data);
	}else{

		db_phquery("
		 	INSERT ?#PRODUCT_OPTIONS_VALUES_TABLE
		 	(optionID, productID, option_type, option_show_times, variantID)
		 	VALUES(?optionID, ?productID, 0, ?option_show_times,  ?variantID_default)",
		array('optionID' => $optionID, 'productID'=>$productID, 'option_show_times' => $option_show_times, 'variantID_default' => $variantID_default));
	}

	$q1=db_phquery("SELECT variantID FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID=?", $optionID);
	$if_only = false;
	while( $r1=db_fetch_row($q1) ){

		$key = (int)$r1["variantID"];
		$where_clause=" WHERE productID={$productID} AND optionID={$optionID} AND variantID={$key}";
		if ( !isset($setting[$key]["switchOn"]) ){

			db_query( "DELETE FROM ".PRODUCTS_OPTIONS_SET_TABLE.$where_clause );
		}
		else
		{
			$q=db_query("SELECT COUNT(*) FROM ".PRODUCTS_OPTIONS_SET_TABLE.$where_clause);
			$r=db_fetch_row($q);
			if ( $r[0]!=0 ){
				$sql = 'UPDATE ?#PRODUCTS_OPTIONS_SET_TABLE SET price_surplus=?'.$where_clause;
				db_phquery($sql,(float)$setting[$key]["price_surplus"]);
				$if_only = true;
			}else{
				$sql = 'INSERT ?#PRODUCTS_OPTIONS_SET_TABLE (productID, optionID, variantID, price_surplus)
					VALUES(?, ?, ?, ?)';
				db_phquery($sql,$productID,$optionID,$key, (float)$setting[$key]["price_surplus"] );
				$if_only = true;
			}
		}
	}

	if ( !$if_only ){
		$sql = 'UPDATE ?#PRODUCT_OPTIONS_VALUES_TABLE SET option_show_times=0 WHERE optionID=? AND productID=?';
		db_phquery($sql, $optionID, $productID);
	}
}
?>