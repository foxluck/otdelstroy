<?php
/**
 * Set current currency
 *
 * @param int $currencyID
 */
function currSetCurrentCurrency( $currencyID ){
	
	//register current currency type in session vars
	$_SESSION["current_currency"] = (int)$currencyID;

	if (isset($_SESSION["log"])){
		
		db_phquery("
			UPDATE ?#CUSTOMERS_TABLE SET CID=? WHERE Login=?
		", $currencyID, $_SESSION["log"]);
	}
}

/**
 * Get current currency
 *
 * @return int - currency id
 */
function currGetCurrentCurrencyUnitID(){
	
	if ( isset($_SESSION["log"]) ){

		$_SESSION["current_currency"] = db_phquery_fetch(DBRFETCH_FIRST, 
			"SELECT CID FROM ?#CUSTOMERS_TABLE WHERE Login=?", $_SESSION["log"]);
		if ( $_SESSION["current_currency"] != null )
			return isset($_SESSION["current_currency"]) ? $_SESSION["current_currency"] : CONF_DEFAULT_CURRENCY;
		else
			return null;
	}else{
		
		$q = db_query( "select count(CID) from ".CURRENCY_TYPES_TABLE." where CID=".CONF_DEFAULT_CURRENCY );
		$count = db_fetch_row($q);
		$count = $count[0];
		if ( $count == 0 )
			return null;
		else
			return isset($_SESSION["current_currency"]) ? $_SESSION["current_currency"] : CONF_DEFAULT_CURRENCY;
	}
}

/**
 * Return currency information in array (CID, Name, code, currency_value, where2show, sort_order, currency_iso_3 )
 *
 * @param int $currencyID - currency ID
 * @return array
 */
function currGetCurrencyByID( $currencyID ){
	static $rowCache;
	if(is_array($rowCache)&&isset($rowCache[$currencyID])){
		return $rowCache[$currencyID];
	}
	if(!is_array($rowCache)){
		$rowCache = array();
	}
	
	$rowCache[$currencyID] = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#CURRENCY_TYPES_TABLE WHERE CID=?', $currencyID);
	
	if (!$rowCache[$currencyID])$rowCache[$currencyID] = NULL;
	else LanguagesManager::ml_fillFields(CURRENCY_TYPES_TABLE, $rowCache[$currencyID]);
		
	return $rowCache[$currencyID];
}

/**
 * Get currency information by currency iso 3
 *
 * @param string $iso3
 * @return array
 */
function currGetCurrencyByISO3( $iso3 ){
	
	$row = db_phquery_fetch( DBRFETCH_ROW, "SELECT * FROM ?#CURRENCY_TYPES_TABLE WHERE currency_iso_3=?", $iso3);
	if (!$row)$row = NULL;
	else LanguagesManager::ml_fillFields(CURRENCY_TYPES_TABLE, $row);
	
	return $row;
}

/**
 * Get all currencies
 *
 * @return array
 */
function currGetAllCurrencies() {
	
	$q = db_phquery("SELECT *,".LanguagesManager::sql_prepareField('Name')." AS `Name` FROM ?#CURRENCY_TYPES_TABLE ORDER BY sort_order, `Name`");
	$data = array();
	while( $row = db_fetch_assoc($q) ){
		
		LanguagesManager::ml_fillFields(CURRENCY_TYPES_TABLE, $row);
		$data[] = $row;
	}
	return $data;
}

/**
 * Delete currency
 *
 * @param int $CID - currency id
 */
function currDeleteCurrency( $CID ){
	
	db_phquery("UPDATE ?#CUSTOMERS_TABLE SET CID=NULL WHERE CID=?", $CID );
	db_phquery("DELETE FROM ?#CURRENCY_TYPES_TABLE WHERE CID=?", $CID );
}

/**
 * Update currency by id
 *
 * @param int $CID - currency id
 * @param mixed $name
 * @param mixed $display_template
 * @param string $currency_iso_3
 * @param float $value
 * @param int $sort_order
 */
function currUpdateCurrency( $CID, $name, $display_template, $currency_iso_3, $value, $sort_order ){

	$sql = '
		UPDATE ?#CURRENCY_TYPES_TABLE 
		SET '.LanguagesManager::sql_prepareFieldUpdate('Name', $name).','.LanguagesManager::sql_prepareFieldUpdate('display_template', $display_template).', 
			currency_value=?,sort_order=?,currency_iso_3=? WHERE CID=?
	';
	db_phquery($sql,$value,$sort_order,$currency_iso_3,$CID);
}

/**
 * Add new currency
 *
 * @param mixed $name
 * @param mixed $display_template
 * @param string $currency_iso_3
 * @param float $value
 * @param int $sort_order
 */
function currAddCurrency( $name, $display_template, $currency_iso_3, $value, $sort_order ){

	$name_inj = LanguagesManager::sql_prepareFieldInsert('Name', $name);
	$tpl_inj = LanguagesManager::sql_prepareFieldInsert('display_template', $display_template);
	$sql = "
		 INSERT ?#CURRENCY_TYPES_TABLE ({$name_inj['fields']}, {$tpl_inj['fields']}, currency_iso_3, currency_value, sort_order) 
		 VALUES({$name_inj['values']}, {$tpl_inj['values']}, ?,?,?)
	";
	db_phquery($sql,$currency_iso_3,$value,$sort_order);
}
?>