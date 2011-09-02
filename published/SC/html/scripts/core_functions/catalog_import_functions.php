<?php
/**
 * @return array - 0 => optionID, 1 => language iso2
 */
function _parseOptionKey($key){

	return explode('__', $key);
}

function _getOptionFields(){

	static $options;
	if(is_array($options))return $options;

	$options = array();
	$dbres = db_phquery('
		SELECT optionID, '.LanguagesManager::sql_prepareField('name').' as name
		FROM ?#PRODUCT_OPTIONS_TABLE ORDER BY sort_order, name');
	$r_languageEntry = LanguagesManager::getLanguages();
	while($option = db_fetch_assoc($dbres)){

		for($j=0,$j_max=count($r_languageEntry); $j<$j_max; $j++){
			/*@var $r_languageEntry Language*/
			$options[$option['optionID'].'__'.$r_languageEntry[$j]->iso2] = $option['name'].($j_max>1?' ('.$r_languageEntry[$j]->getName().')':'');
		}
	}

	return $options;
}

function _getProductFields(){

	$product_fields = array(
		'product_code' => translate("prdset_product_code"),
		'name' => translate("prdset_product_name"),
		'slug' => translate('prdset_slug'),
	);
	if (defined("CONF_1C_ON") && constant('CONF_1C_ON') ) {
		$product_fields['id_1c'] = translate("prdset_1c_sync");
	}
	$product_fields = array_merge($product_fields, array(
		'Price' => translate("str_price"),
		'classID' => translate('tax_name'),
		'invisible' => translate('prdset_str_invisible'),
		'ordering_available' => translate('prdset_str_ordering_available'),
		'list_price' => translate("prdset_product_listprice"),
		'in_stock' => translate("str_in_stock"),
		'items_sold' => translate("prdset_product_sold"),
		'description' => translate("str_description"),
		'brief_description' => translate("prdset_description_brief"),
		'sort_order' => translate("str_sort_order"),
		'meta_title' => translate("prdset_meta_title"),
		'meta_keywords' => translate("prdset_meta_keywords"),
		'meta_description' => translate("prdset_meta_description"),
		'shipping_freight' => translate("prdset_handling_charge"),
		'weight' => translate("prdset_weight"),
		'free_shipping' => translate("prdset_free_shipping_2"),
		'min_order_amount' => translate("prdset_min_qunatity_to_order"),
		'eproduct_filename' => translate("prdset_product_filename"),
		'eproduct_available_days' => translate("prdset_download_is_available_for_2"),
		'eproduct_download_times' => translate("prdset_download_max_number_allowed"),
		'picture' => translate("prdset_product_picture"),
		
	));
	

	if(!CONF_CHECKSTOCK){

		unset($product_fields['in_stock']);
	}
	$r_languageEntry = LanguagesManager::getLanguages();
	$fields = array();

	foreach ($product_fields as $field=>$title){

		if(!LanguagesManager::ml_isMLField(PRODUCTS_TABLE, $field)){

			$fields[$field] = $title;
			continue;
       	}

		for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
			/*@var $r_languageEntry Language*/

			$fields[LanguagesManager::ml_getLangFieldName($field, $r_languageEntry[$_j])] = $title.($_j_max>1?' ('.$r_languageEntry[$_j]->getName().')':'');
		}
	}

	return $fields;
}

function _getUniqueColumns(){

	$product_fields = array(
		'name' => translate("prdset_product_name"),
		'product_code' => translate("prdset_product_code"),
		'slug' => translate('prdset_slug'),
		);
		
	if (defined("CONF_1C_ON") && constant('CONF_1C_ON')) {
		$product_fields['id_1c'] = translate("prdset_1c_sync");
	}

	$r_languageEntry = LanguagesManager::getLanguages();
	$defaultLanguage = &LanguagesManager::getDefaultLanguage();
	$fields = array();

	foreach ($product_fields as $field=>$title){

		if(!LanguagesManager::ml_isMLField(PRODUCTS_TABLE, $field)){

			$fields[$field] = $title;
			continue;
       	}

       	$_j_max = count($r_languageEntry);
		$fields[LanguagesManager::ml_getLangFieldName($field, $defaultLanguage)] = $title.($_j_max>1?' ('.$defaultLanguage->getName().')':'');
       	for($_j = 0; $_j<$_j_max; $_j++){
			/*@var $r_languageEntry Language*/

			$fields[LanguagesManager::ml_getLangFieldName($field, $r_languageEntry[$_j])] = $title.($_j_max>1?' ('.$r_languageEntry[$_j]->getName().')':'');
		}
	}

	return $fields;
}

// *****************************************************************************
// Purpose 	clears database content
// Inputs
// Remarks
// Returns	nothing
function imDeleteAllProducts()
{
	db_query("UPDATE ".PRODUCT_OPTIONS_VALUES_TABLE." SET variantID=NULL");
	db_query("delete from ".SHOPPING_CARTS_TABLE);
	db_query("DELETE FROM ".PRODUCTS_OPTIONS_SET_TABLE);
	db_query("DELETE FROM ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE );
	db_query("DELETE FROM ".SHOPPING_CART_ITEMS_CONTENT_TABLE);
	db_query("DELETE FROM ".PRODUCT_OPTIONS_VALUES_TABLE);
	db_query("DELETE FROM ".PRODUCT_OPTIONS_TABLE);
	db_query("DELETE FROM ".RELATED_PRODUCTS_TABLE);
	db_query("DELETE FROM ".PRODUCT_PICTURES);
	db_query("DELETE FROM ".DISCUSSIONS_TABLE);
	db_query("UPDATE ".SHOPPING_CART_ITEMS_TABLE." SET productID = NULL");
	db_query("DELETE FROM ".PRODUCTS_TABLE);
	db_query("DELETE FROM ".CATEGORIES_TABLE);
	$defaultLanguage = LanguagesManager::getDefaultLanguage();
	db_query("INSERT INTO ".CATEGORIES_TABLE." (categoryID, ".LanguagesManager::ml_getLangFieldName('name', $defaultLanguage).", parent) VALUES (1, 'ROOT', NULL);");
	db_query("DELETE FROM ".CATEGORIY_PRODUCT_TABLE);
}


// *****************************************************************************
// Purpose 	read db_association select control
//			( see GetImportConfiguratorHtmlCode )
// Inputs
// Remarks
// Returns
function _readDb_associationSelectControl()
{
	$db_association = array(); // array select control values
	foreach( $_POST as $key => $val )
	{
		if (strstr($key, "db_association_"))
		{
			$i = str_replace("db_association_", "", $key);
			if ( $val != "pictures" )
				$db_association[$i] = $val;
		}
	}
	return $db_association;
}

// *****************************************************************************
// Purpose 	add new product extra option
// Inputs
// Remarks
// Returns
//OVERSEE::
function _addExtraOptionToDb( $db_association, $cname ){

	$updated_extra_option = array();

	foreach ($cname as $key=>$val)$updated_extra_option[$key] = 0;

	foreach( $db_association as $i => $value ){

		if(preg_match('@options\[(\d+)__(\w+)\]$@msi', $value, $sp)){

			list($sp, $option_id, $lang_id) = $sp;
			if(!is_array($updated_extra_option[$option_id]))$updated_extra_option[$option_id] = array();
			$updated_extra_option[$option_id][$lang_id] = array('lang_id' => $lang_id, 'option_id' => $option_id, 'data_index' => $i);
		}
		/*
		if(substr($value, 0, 5) != "add__")continue;

		$language_id = substr($value, 5);
		if(!$language_id)continue;
		$languageEntry = LanguagesManager::getLanguageInstance($language_id);
		$sql = '
			SELECT COUNT(*) FROM ?#PRODUCT_OPTIONS_TABLE WHERE '.LanguagesManager::ml_getLangFieldName('name', $languageEntry).' LIKE ?
		';
		$option_exists = db_phquery_fetch(DBRFETCH_FIRST, $sql, $cname[$i]);
		if (!$option_exists){// no option exists => insert new

			$sql = '
				INSERT ?#PRODUCT_OPTIONS_TABLE (name) VALUES (?)
			';
			db_phquery($sql, $cname[$i]);
			$op_id = db_insert_id("PRODUCT_OPTIONS_GEN");
		}else{ 		// get current $id

			$sql = '
				SELECT optionID FROM ?#PRODUCT_OPTIONS_TABLE WHERE name LIKE ?
			';
			$q = db_phquery($sql, $cname[$i]);
			$op_id = db_fetch_row($q);
			$op_id = $op_id[0];
		}
		//update extra options list
		$updated_extra_option[$i] = $op_id;
*/
	}
	return $updated_extra_option;
}


/**
 * @return bool
 */
function _optionValueIsCustomType($value){

	return !(substr($value, 0, 1)==='{' && substr($value, -1, 1)==='}');
}

/**
 * @return array | string
 */
function _parseOptionValue($value){

	if(_optionValueIsCustomType($value))return $value;

	return explode(',', substr($value, 1, strlen($value)-2));
}

function _importExtraOptionValues($row, $productID, $updated_extra_option){

	global $errors_options;
	$defaultLanguage = &LanguagesManager::getDefaultLanguage();

	foreach($updated_extra_option as $optionID=>$lang_variants){

		/**
		 * Ignore option if no default value
		 */
		if(!isset($lang_variants[$defaultLanguage->iso2]))continue;

		$mode = _optionValueIsCustomType($row[$lang_variants[$defaultLanguage->iso2]['data_index']])?'custom':'predefined';

		switch ($mode){
			case 'custom':
				$sql = '
					DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE optionID=? and productID=?
				';
				db_phquery($sql,$optionID,$productID);
				$option_values = array();
				foreach ($lang_variants as $_variant){

					$option_values['option_value_'.$_variant['lang_id']] = $row[$_variant['data_index']];
				}
				$ml_option_value = LanguagesManager::sql_prepareFieldInsert('option_value', $option_values);
				$sql = "
					INSERT ?#PRODUCT_OPTIONS_VALUES_TABLE (optionID, productID, {$ml_option_value['fields']}) VALUES (?,?,{$ml_option_value['values']})
				";
				db_phquery($sql, $optionID, $productID);
				break;
			case 'predefined':
				if(!isset($lang_variants[$defaultLanguage->iso2]['data_index'])){

//					$errors_options[]
					break;
				}
				$data = $row[$lang_variants[$defaultLanguage->iso2]['data_index']];
				$values_options = _parseOptionValue($data);
				//delete all current product option configuration
				db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE optionID=? AND productID=?", $optionID, $productID);
				db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE optionID=? AND productID=?", $optionID, $productID);

				$default_variantID = 0;
				foreach ($values_options as $key => $val){

					if (strstr($val,"=")) // current value is "OPTION_NAME=SURCHARGE", e.g. red=3, xl=1, s=-1, m=0
					{
						$a = explode("=",$val);
						$val_name = $a[0];
						$val_surcharge = (float)$a[1];
					}
					else // current value is a option value name, e.g. red, xl, s, m
					{
						$val_name = $val;
						$val_surcharge = 0;
					}

					//search for a specified option value in the database
					$variantID = optOptionValueExists($optionID, $val_name);
					if ( !$variantID ) //does not exist => add new variant value
					{
						$variantID = optAddOptionValue(array('optionID' => $optionID, 'sort_order'=>0, 'option_value_'.$defaultLanguage->iso2 => $val_name));
					}
					if (!$default_variantID) $default_variantID = $variantID;

					//now append this variant value to the product
					db_phquery("INSERT ?#PRODUCTS_OPTIONS_SET_TABLE (productID, optionID, variantID, price_surplus) VALUES(?,?,?,?)", $productID, $optionID, $variantID, $val_surcharge);
				}

				//assign default variant ID - first option in the variants list is default
				if ($default_variantID){

					$sql = '
						INSERT ?#PRODUCT_OPTIONS_VALUES_TABLE (optionID, productID, option_type, option_show_times, variantID) VALUES(?,?,?,?,?)
					';
					db_phquery($sql, $optionID, $productID, 1, 1, $default_variantID);
				}
				break;
		}
	}
}

function iconv_file($from, $to, $file,$use_mb = false){
	$fp_source = fopen($file ,'rb');
	$fp_target = fopen($file.'_iconv' , 'wb');

	while ($Text = fgets($fp_source)){
		fwrite($fp_target, ($use_mb&&function_exists(mb_convert_encoding))?mb_convert_encoding($Text,$to,$from):iconv($from, $to.'//IGNORE', $Text));
	}
	fclose($fp_source);
	fclose($fp_target);
	unlink($file);
	copy($file.'_iconv', $file);
	unlink($file.'_iconv');
}

global $file_encoding_charsets;
$file_encoding_charsets = array(
'ascii',
'big5',
'cp1250',
'cp1251',
'cp1252',
'cp1253',
'cp1254',
'cp1257',
'cp850',
'cp852',
'cp866',
'cp932',
'euc-kr',
'gbk',
'koi8-r',
'koi8-u',
'iso-8859-1',
'iso-8859-2',
'iso-8859-3',
'iso-8859-4',
'iso-8859-5',
'iso-8859-7',
'iso-8859-9',
'iso-8859-10',
'iso-8859-13',
'iso-8859-14',
'iso-8859-15',
'iso-8859-16',
'macgreek',
'machebrew',
'maccentraleurope',
'macroman',
'shift_jis',
'utf-8',
);

?>