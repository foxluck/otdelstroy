<?php
/**
 * Delete shipping method
 *
 * @param int $SID - method ID
 */
function shDeleteShippingMethod( $SID ){

	db_phquery("DELETE FROM ?#SHIPPING_METHODS_TABLE WHERE SID=?", $SID);
}

/**
 * Get shipping methods by module
 *
 * @param ShippingRateCalculator $shippingModule
 * @return array
 */
function shGetShippingMethodsByModule( $shippingModule ){

	$moduleID = $shippingModule->get_id();

	if ( strlen($moduleID) == 0 )return array();

	$q = db_phquery("select * FROM ?#SHIPPING_METHODS_TABLE WHERE module_id=?", $moduleID );
	$data = array();
	while( $row = db_fetch_row($q) ){

		LanguagesManager::ml_fillFields(SHIPPING_METHODS_TABLE, $row);
		$data[] = $row;
	}
	return $data;
}

/**
 * Get shipping method information by ID
 *
 * @param int $shippingMethodID
 * @return array
 */
function shGetShippingMethodById( $shippingMethodID ){

	$row = db_phquery_fetch(DBRFETCH_ROW, "SELECT * FROM ?#SHIPPING_METHODS_TABLE WHERE SID=?", $shippingMethodID );
	LanguagesManager::ml_fillFields(SHIPPING_METHODS_TABLE, $row);
	return $row;
}

/**
 * Get information about all shipping methods
 *
 * @param bool $enabledOnly - if true return only enabled shipping methods, else all
 * @return array
 */
function shGetAllShippingMethods( $enabledOnly = false )
{
	static $cache = array();
	if(!isset($cache[$enabledOnly])){
		$whereClause = "";
		if ( $enabledOnly ){
			$whereClause = " WHERE Enabled=1 ";
		}
		$q = db_phquery("SELECT * FROM ?#SHIPPING_METHODS_TABLE {$whereClause} ORDER BY sort_order");
		$cache[$enabledOnly] = array();
		while( $row = db_fetch_row($q) ){
			LanguagesManager::ml_fillFields(SHIPPING_METHODS_TABLE, $row);
			$cache[$enabledOnly][] = $row;
		}
		
	}
	return $cache[$enabledOnly];
}


// *****************************************************************************
// Purpose  get all installed shipping modules
// Inputs
// Remarks
// Returns  nothing
function shGetInstalledShippingModules()
{
	$moduleFiles = GetFilesInDirectory( "./modules/shipping", "php" );
	$shipping_modules = array();
	foreach( $moduleFiles as $fileName )
	{
		$className = GetClassName( $fileName );
		if(!$className)continue;
		$shipping_module = new $className();
		if ( $shipping_module->is_installed() )
		$shipping_modules[] = $shipping_module;
	}
	return $shipping_modules;
}


// *****************************************************************************
// Purpose  add shipping method
// Inputs
// Remarks
// Returns  nothing
function shAddShippingMethod( $Name, $description, $Enabled, $sort_order, $module_id, $email_comments_text, $logo = '' )
{
	$fields_values = array(
		'Enabled'=>$Enabled,
		'module_id'=>$module_id,
		'sort_order'=>$sort_order,
		'logo'=>$logo,
	);
	$ml_fields = LanguagesManager::ml_getLangFieldNames('Name');
	foreach($ml_fields as $field){
		$fields_values[$field] = isset($Name[$field])?$Name[$field]:'';
	}
	$ml_fields = LanguagesManager::ml_getLangFieldNames('description');
	foreach($ml_fields as $field){
		$fields_values[$field] = isset($description[$field])?$description[$field]:'';
	}
	$ml_fields = LanguagesManager::ml_getLangFieldNames('email_comments_text');
	foreach($ml_fields as $field){
		$fields_values[$field] = isset($email_comments_text[$field])?$email_comments_text[$field]:'';
	}

	$fields = '`'.implode('`, `',array_keys($fields_values)).'`';
	$fields_data = '?'.implode(', ?',array_keys($fields_values));
	$sql = "INSERT `?#SHIPPING_METHODS_TABLE` ({$fields}) VALUES ({$fields_data})";

	db_phquery($sql,$fields_values);
	return db_insert_id();
}


// *****************************************************************************
// Purpose  update shipping method
// Inputs
// Remarks
// Returns  nothing
function shUpdateShippingMethod($SID, $Name, $description, $Enabled, $sort_order,$module_id, $email_comments_text, $logo='' ){

	$fields_values = array(
		'Enabled'=>$Enabled,
		'module_id'=>$module_id,
		'sort_order'=>$sort_order,
		'logo'=>$logo,
	);
	$ml_fields = LanguagesManager::ml_getLangFieldNames('Name');
	foreach($ml_fields as $field){
		$fields_values[$field] = isset($Name[$field])?$Name[$field]:'';
	}
	$ml_fields = LanguagesManager::ml_getLangFieldNames('description');
	foreach($ml_fields as $field){
		$fields_values[$field] = isset($description[$field])?$description[$field]:'';
	}
	$ml_fields = LanguagesManager::ml_getLangFieldNames('email_comments_text');
	foreach($ml_fields as $field){
		$fields_values[$field] = isset($email_comments_text[$field])?$email_comments_text[$field]:'';
	}

	$fields = '`'.implode('`, `',array_keys($fields_values)).'`';
	$fields_data = '?'.implode(', ?',array_keys($fields_values));
	$sql = 'UPDATE `?#SHIPPING_METHODS_TABLE` SET ?% WHERE SID=?';

	db_phquery($sql,$fields_values,$SID);
}

/**
 * Check shipping method existing
 *
 * @param int $shippingMethodID - method ID
 * @return bool
 */
function shShippingMethodIsExist( $shippingMethodID ){

	return (db_phquery_fetch( DBRFETCH_FIRST, "SELECT COUNT(*) FROM ?#SHIPPING_METHODS_TABLE WHERE Enabled=1 AND SID=?", $shippingMethodID )>0);
}

function shGetMaxSortOrder(){
	return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT MAX(sort_order) FROM ?#SHIPPING_METHODS_TABLE');
}
?>