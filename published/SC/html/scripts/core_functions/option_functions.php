<?php
// Purpose	gets all options
// Inputs   nothing
// Returns	array of item
//					"optionID"
//					"name"
//					"sort_order"
//					"count_variants"
function optGetOptions()
{
	$name = LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'name',true);
	$sort_name = LanguagesManager::sql_getSortField(PRODUCT_OPTIONS_TABLE, 'name');
	$sql = <<<SQL
	SELECT 
		(
			SELECT COUNT(`?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE`.`variantID`) 
			FROM `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE` 
			WHERE `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE`.`optionID` = `?#PRODUCT_OPTIONS_TABLE`.`optionID`
		) as `count_variants`,
		`?#PRODUCT_OPTIONS_TABLE`.*,
		{$name} 
		
	FROM `?#PRODUCT_OPTIONS_TABLE`
	ORDER BY `?#PRODUCT_OPTIONS_TABLE`.`sort_order`, {$sort_name}
	
SQL;
/*	$sql = <<<SQL
	SELECT `?#PRODUCT_OPTIONS_TABLE`.*, COUNT(1) AS `count_variants`, {$name}
	FROM `?#PRODUCT_OPTIONS_TABLE`
	LEFT JOIN `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE`
	ON (`?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE`.`optionID` = `?#PRODUCT_OPTIONS_TABLE`.`optionID`)
	GROUP BY `?#PRODUCT_OPTIONS_TABLE`.`optionID`
	ORDER BY `?#PRODUCT_OPTIONS_TABLE`.`sort_order`, {$sort_name}
SQL;*/
	$q = db_phquery($sql);
		
	$result=array();
	while( $row=db_fetch_assoc($q) ){
		LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $row);
		$row['optionID'] = intval($row['optionID']);
		$result[] = $row;
	}
	return $result;
	//OLD CODE
	/*
		$q = db_query('
		SELECT *,'.LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'name').' 
		FROM '.PRODUCT_OPTIONS_TABLE.' ORDER BY sort_order, '.LanguagesManager::sql_getSortField(PRODUCT_OPTIONS_TABLE, 'name'));

	$result=array();
	while( $row=db_fetch_row($q) ){
		$q1=db_phquery('SELECT COUNT(variantID) FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID=?',$row["optionID"]);
		$r1=db_fetch_row($q1);
		$row['count_variants']=$r1[0];
		LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $row);
		$result[] = $row;
	}
	return $result;*/
}

// Purpose	gets all options
// Inputs   $optionID - option ID
// Returns	array of item
//					"optionID"
//					"name"
//					"sort_order"
//					"count_variants"
function optGetOptionById($optionID){
	
	$q = db_phquery('SELECT * FROM ?#PRODUCT_OPTIONS_TABLE WHERE optionID=?',$optionID);
	if ( $row=db_fetch_row($q) ){

		LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $row);
		return $row;
	}else return null;
}

// Purpose	gets all options
// Inputs   array of item
//				each item consits of			
//					"extra_option"			- option name
//					"extra_sort"			- enlarged picture
//				key is option ID
// Returns	nothig
function optUpdateOptions($updateOptions){

	foreach($updateOptions as $key => $val){

		$update_sql = LanguagesManager::sql_prepareTableUpdate(PRODUCT_OPTIONS_TABLE, $val, array('@name_(\w{2})@' => 'extra_option_${1}'));
		$val["extra_option"] = xEscapeSQLstring($val["extra_option"] );
		$val["extra_sort"] = (int)$val["extra_sort"];
		$s = "update ".PRODUCT_OPTIONS_TABLE." set {$update_sql}, sort_order='".$val["extra_sort"]."' where optionID='$key';";
		db_query($s);
	}
}

/**
 * Add new option
 *
 * @param array $params : (name_%lang%=>string, sort_order=>int)
 */
function optAddOption($params){
	
	$ml_dbqs = LanguagesManager::sql_prepareFieldInsert('name', $params);
	db_phquery('INSERT ?#PRODUCT_OPTIONS_TABLE ('.$ml_dbqs['fields'].', sort_order) VALUES('.$ml_dbqs['values'].',?)', $params['sort_order']);
}

// Purpose	get option values
function optGetOptionValues($optionID = null)
{
	
	$value = LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'option_value');
	$value_sort = LanguagesManager::sql_getSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'option_value');
	$dbq = <<<SQL
		SELECT *, {$value} 
		FROM `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE` 
SQL;
	if(!is_null($optionID)){
		$dbq .= 'WHERE `optionID` IN (?@)';
	}
	$dbq .= "ORDER BY `optionID`, `sort_order`, {$value_sort}";

	$q = db_phquery($dbq, (array)$optionID);
	
	$result=array();
	while($row=db_fetch_assoc($q)){
		
		$row['optionID'] = intval($row['optionID']);
		$row['variantID'] = intval($row['variantID']);
		$current = $row['optionID'];
		if(!isset($result[$current])){
			$result[$current] = array();
		}
		$result[$current][] = LanguagesManager::ml_fillFields(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, $row);
	}
	return (is_array($optionID)||is_null($optionID))?$result:$result[$optionID];
}

// Purpose	get option values
function optOptionValueExists($optionID, $value_name){
	
	$langfield_names = LanguagesManager::ml_getLangFieldNames('option_value');
	$q = db_phquery('SELECT variantID FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID=? AND ('.implode('="'.xEscapeSQLstring($value_name).'" OR ', $langfield_names).'="'.xEscapeSQLstring($value_name).'")', $optionID);
	$row = db_fetch_row($q);
	if ($row)
		return $row[0]; //return variant ID
	else
		return false;
}

// Purpose	updates option values
// Inputs   array of item
//				each item consits of			
//					"option_value"			- option name
//					"sort_order"			- enlarged picture
//				key is option ID
function optUpdateOptionValues($updateOptions){
	
	foreach($updateOptions as $key => $value){
		
		$sql = '
			UPDATE ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE SET '.LanguagesManager::sql_prepareFieldUpdate('option_value', $value).', sort_order=? 
			WHERE variantID=?
		';
		db_phquery($sql, $value["sort_order"], $key);
	}
}

/**
 * Add option value
 *
 * @param array $params : (optionID=>int, option_value_%lang%=>string, sort_order=>int)
 * @return int variantID
 */
function optAddOptionValue($params){
	
	if(LanguagesManager::ml_isEmpty('option_value', $params))return false;
	$ml_dbqs = LanguagesManager::sql_prepareFieldInsert('option_value', $params);
	$sql = '
		INSERT ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE (optionID, '.$ml_dbqs['fields'].', sort_order) values(?, '.$ml_dbqs['values'].',?) 
	';
	db_phquery($sql,$params['optionID'], $params['sort_order']);
	return db_insert_id();
}

?>