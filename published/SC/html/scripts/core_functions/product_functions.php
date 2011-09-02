<?php
/**
 * @param int $productID
 * @return bool
 */
function prdProductExists( $productID ){

	return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#PRODUCTS_TABLE WHERE productID=?', $productID);
}

// Purpose	gets product
// Inputs   $productID - product ID
// Returns	array of fieled value
//			"name"				- product name
//			"product_code"		- product code
//			"description"		- description
//			"brief_description"	- short description
//			"customers_rating"	- product rating
//			"in_stock"			- in stock (this parametr is persist if CONF_CHECKSTOCK == 1 )
//			"option_values"		- array of
//					"optionID"		- option ID
//					"name"			- name
//					"value"	- option value
//					"option_type" - option type
//			"ProductIsProgram"		- 1 if product is program, 0 otherwise
//			"eproduct_filename"		- program filename
//			"eproduct_available_days"	- program is available days to download
//			"eproduct_download_times"	- attempt count download file
//			"weight"			- product weigth
//			"meta_description"		- meta tag description
//			"meta_keywords"			- meta tag keywords
//			"free_shipping"			- 1 product has free shipping,
//							0 - otherwise
//			"min_order_amount"		- minimum order amount
//			"classID"			- tax class ID
function GetProduct( $productID){

	$productID = (int)$productID;
	$q = db_query('SELECT * FROM '.PRODUCTS_TABLE.' WHERE productID='.$productID);
	
	if ( $product=db_fetch_assoc($q) ){
		
		LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $product);
		$product["ProductIsProgram"] = 	(trim($product["eproduct_filename"]) != "");
		$sql = '
			SELECT pot.*,povt.* FROM '.PRODUCT_OPTIONS_VALUES_TABLE.' as povt
			LEFT JOIN '.PRODUCT_OPTIONS_TABLE.' as pot ON pot.optionID=povt.optionID 
			WHERE productID='.$productID.'
		';
		$Result = db_query($sql);
		$product['option_values'] = array();
		
		while ($_Row = db_fetch_assoc($Result)) {

			LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $_Row);
			LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_VALUES_TABLE, $_Row);
			$_Row['value'] = $_Row['option_value'];
			$product['option_values'][] = $_Row;
		}
		if($product['vkontakte_update_timestamp']>0){
			$product['vkontakte_update_timestamp']=Time::standartTime( $product['vkontakte_update_timestamp'] );
		}else{
			$product['vkontakte_update_timestamp'] = '';
		}
		$product['date_modified']=Time::standartTime( $product['date_modified'] );
		$product['date_added']=Time::standartTime( $product['date_added'] );
		return $product;
	}
	return false;
}

//Purpose sets product file
function SetProductFile( $productID, $eproduct_filename ){
	db_phquery('UPDATE ?#PRODUCTS_TABLE SET eproduct_filename=? WHERE productID=?', $eproduct_filename, $productID );
}

/**
 * Create empty product and return productID
 *
 * @return int
 */
function prdCreateEmptyProduct(){
	
	$Register = &Register::getInstance();
	$DBHandler = &$Register->get(VAR_DBHANDLER);
	/* @var $DBHandler DataBase */
	
	$val = 'no name';
	$dbq_name = LanguagesManager::sql_prepareFieldInsert('name', $val);
	$dbq = '
		INSERT ?#PRODUCTS_TABLE (categoryID, '.$dbq_name['fields'].', date_added) VALUES(1, '.$dbq_name['values'].', ?)
	';
	
	$DBRes = $DBHandler->ph_query($dbq,Time::dateTime());
	if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
		include_once(WBS_DIR.'/kernel/classes/class.metric.php');
			
		$DB_KEY=SystemSettings::get('DB_KEY');
		$U_ID = sc_getSessionData('U_ID');
			
		$metric = metric::getInstance();
		$metric->addAction($DB_KEY, $U_ID, 'SC','ADDPRODUCT','ACCOUNT', '');
	}
	return $DBRes->getInsertID();
}

// Purpose	deletes product
// Inputs   $productID - product ID
// Returns	true if success, else false otherwise
function DeleteProduct($productID, $updateGCV = 1){
		
	$productID = intval($productID);
	$whereClause = ' where productID='.$productID;
	//removing images from filesystem
	$q=db_query("select photoID from ".PRODUCT_PICTURES.$whereClause );
	
	while ( $picture=db_fetch_row($q) ){
		DeleteThreePictures( $picture['photoID'] );	
	}
	
	$q = db_query( "select itemID from ".SHOPPING_CART_ITEMS_TABLE." ".$whereClause );
	while( $row=db_fetch_row($q) )
	db_query( "delete from ".SHOPPING_CARTS_TABLE." where itemID=".$row["itemID"] );

	// delete all items for this product
	db_query("update ".SHOPPING_CART_ITEMS_TABLE." set productID=NULL ".$whereClause);

	// delete all product option values
	db_query("delete from ".PRODUCTS_OPTIONS_SET_TABLE.$whereClause);
	db_query("delete from ".PRODUCT_OPTIONS_VALUES_TABLE.$whereClause);

	// delete pictures
	db_query("delete from ".PRODUCT_PICTURES.$whereClause);

	// delete additional categories records
	db_query("delete from ".CATEGORIY_PRODUCT_TABLE.$whereClause);

	// delete discussions
	db_query("delete from ".DISCUSSIONS_TABLE.$whereClause);

	// delete related items
	db_query("delete from ".RELATED_PRODUCTS_TABLE.$whereClause );
	db_query("delete from ".RELATED_PRODUCTS_TABLE." where Owner=$productID");

	//removing files from filesystem
	$q=db_query("select eproduct_filename as filename from ".PRODUCTS_TABLE.$whereClause);	
	if ( $file=db_fetch_row($q) ){
		if($file["filename"]!=null && strlen($file["filename"])>0){
			if ( file_exists(DIR_PRODUCTS_FILES."/".$file["filename"]) ){
				Functions::exec('file_remove', array(DIR_PRODUCTS_FILES."/".$file["filename"]));
			}
		}

	}
	
	// delete product
	db_query("delete from ".PRODUCTS_TABLE.$whereClause);

	TagManager::removeObjectTags('product', $productID);
	ProductList::stc_deleteProductFromLists($productID);

	if ( $updateGCV == 1 && CONF_UPDATE_GCV == '1')
	update_products_Count_Value_For_Categories(1);

	return true;
}

// Purpose	gets extra parametrs
// Inputs   $productID - product ID
// Returns	array of value extraparametrs
//				each item of this array has next struture
//					first type "option_type" = 0
//						"name"					- parametr name
//						"option_value"			- value
//						"option_type"			- 0
//					second type "option_type" = 1
//						"name"					- parametr name
//						"option_show_times"		- how times does show in client side this
//												parametr to select
//						"variantID_default"		- variant ID by default
//						"values_to_select"		- array of variant value to select
//							each item of "values_to_select" array has next structure
//								"variantID"			- variant ID
//								"price_surplus"		- to added to price
//								"option_value"		- value
function GetExtraParametrs( $productID ){

	static $ProductsExtras = array();
	if(!is_array($productID)){

		$ProductIDs = array($productID);
		$IsProducts = false;
	}elseif(count($productID)) {

		$ProductIDs = &$productID;
		$IsProducts = true;
	}else {

		return array();
	}
	$ProductIDsCached = array_keys($ProductsExtras);
	$ProductIDs = array_diff($ProductIDs,$ProductIDsCached);
	if(count($ProductIDs)){
		$sql = '
			SELECT pot.*, '.LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'pot.name').', povt.*
			FROM ?#PRODUCT_OPTIONS_VALUES_TABLE as povt LEFT JOIN  ?#PRODUCT_OPTIONS_TABLE as pot ON pot.optionID=povt.optionID 
			WHERE povt.productID IN (?@) ORDER BY pot.sort_order, '.LanguagesManager::sql_getSortField(PRODUCT_OPTIONS_TABLE, 'pot.name').'
		';
		$Result = db_phquery($sql, $ProductIDs);
	
		while ($_Row = db_fetch_assoc($Result)) {
	
			LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_VALUES_TABLE, $_Row);
			LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $_Row);
			$b=null;
			if (($_Row['option_type']==0 || $_Row['option_type']==NULL) && !LanguagesManager::ml_isEmpty('option_value', $_Row['option_value'])){
	
				$ProductsExtras[$_Row['productID']][] = $_Row;
			}
			else if ( $_Row['option_type']==1 ){
	
				//fetch all option values variants
				$sql = '
					SELECT povvt.*, '.LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'povvt.option_value').', post.price_surplus
					FROM '.PRODUCTS_OPTIONS_SET_TABLE.' as post
					LEFT JOIN '.PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.' as povvt
					ON povvt.variantID=post.variantID
					WHERE povvt.optionID='.$_Row['optionID'].' AND post.productID='.$_Row['productID'].' AND povvt.optionID='.$_Row['optionID'].' 
					ORDER BY povvt.sort_order, '.LanguagesManager::sql_getSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'povvt.option_value').'
				';
	
				$q2=db_query($sql);
				$_Row['values_to_select']=array();
				$i=0;
				while( $_Rowue = db_fetch_assoc($q2)  ){
	
					LanguagesManager::ml_fillFields(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, $_Rowue);
					$_Row['values_to_select'][$i]=array();
					$_Row['values_to_select'][$i]['option_value'] = xHtmlSpecialChars($_Rowue['option_value']);
					if ( $_Rowue['price_surplus'] > 0 )$_Row['values_to_select'][$i]['option_value'] = $_Row['values_to_select'][$i]['option_value'].' (+ '.show_price($_Rowue['price_surplus']).')';
					elseif($_Rowue['price_surplus'] < 0 )$_Row['values_to_select'][$i]['option_value'] = $_Row['values_to_select'][$i]['option_value'].' (- '.show_price(-$_Rowue['price_surplus']).')';
	
					$_Row['values_to_select'][$i]['option_valueWithOutPrice'] = $_Rowue['option_value'];
					$_Row['values_to_select'][$i]['price_surplus'] = show_priceWithOutUnit($_Rowue['price_surplus']);
					$_Row['values_to_select'][$i]['variantID']=$_Rowue['variantID'];
					$i++;
				}
				$ProductsExtras[$_Row['productID']][] = $_Row;
			}
		}
	}
	if(!$IsProducts){

		if(!count($ProductsExtras))return array();
		else {
			return $ProductsExtras[$productID];
		}
	}
	
	return $ProductsExtras;
}


function _setPictures( & $product){

	if ( isset($product['default_picture'])&&!is_null($product['default_picture'])&&isset($product['productID']) ){

		$Result = db_phquery('SELECT filename, thumbnail, enlarged FROM ?#PRODUCT_PICTURES WHERE photoID=?',$product['default_picture']);
		$Picture = db_fetch_assoc($Result);
		$product['picture'] = file_exists(DIR_PRODUCTS_PICTURES.'/'.$Picture['filename'])?$Picture['filename']:0;
		$product['thumbnail']=file_exists(DIR_PRODUCTS_PICTURES.'/'.$Picture['thumbnail'])?$Picture['thumbnail']:0;
		$product['big_picture']=file_exists(DIR_PRODUCTS_PICTURES.'/'.$Picture['enlarged'])?$Picture['enlarged']:0;
	}elseif (is_array($product)){

		$Products = &$product;
		$DefaultPictures = array();
		$TC = count($Products);
		for ($j=0;$j<$TC;$j++){
			if(!is_null($Products[$j]['default_picture']))$DefaultPictures[intval($Products[$j]['default_picture'])][] = $j;
		}
		if(!count($DefaultPictures))return;
		$sql = '
			SELECT photoID,filename, thumbnail, enlarged FROM '.PRODUCT_PICTURES.' WHERE photoID IN('.implode(',',array_keys($DefaultPictures)).')
		';
		$Result = db_query($sql);
		while ($Picture = db_fetch_assoc($Result)){

			foreach ($DefaultPictures[$Picture['photoID']] as $j){

				$Products[$j]['picture'] = file_exists(DIR_PRODUCTS_PICTURES.'/'.$Picture['filename'])?$Picture['filename']:0;
				$Products[$j]['thumbnail']=file_exists(DIR_PRODUCTS_PICTURES.'/'.$Picture['thumbnail'])?$Picture['thumbnail']:0;
				$Products[$j]['big_picture']=file_exists(DIR_PRODUCTS_PICTURES.'/'.$Picture['enlarged'])?$Picture['enlarged']:0;
			}
		}
	}
}


function GetProductInSubCategories( $callBackParam, &$count_row, $navigatorParams = null )
{

	if ( $navigatorParams != null )
	{
		$offset			= $navigatorParams["offset"];
		$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
	}
	else
	{
		$offset = 0;
		$CountRowOnPage = 0;
	}

	$categoryID	= $callBackParam["categoryID"];
	$subCategoryIDArray = catGetSubCategories( $categoryID );
	$cond = "";
	foreach( $subCategoryIDArray as $subCategoryID )
	{
		if ( $cond != "" )
		$cond .= " OR categoryID=$subCategoryID";
		else
		$cond .= " categoryID=$subCategoryID ";
	}
	$whereClause = "";
	if ( $cond != "" )
	$whereClause = " where ".$cond;

	$result = array();
	if ( $whereClause == "" )
	{
		$count_row = 0;
		return $result;
	}

	$langManager = &LanguagesManager::getInstance();
	
	$q=db_query("
	SELECT * FROM ".PRODUCTS_TABLE.
	" ".$whereClause." ORDER BY sort_order, name " );
	$i=0;
	while( $row=db_fetch_row($q) ){
		
		LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $row);
		if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
		$navigatorParams == null  )
		{
			$row["PriceWithUnit"]		= show_price($row["Price"]);
			$row["list_priceWithUnit"] 	= show_price($row["list_price"]);
			// you save (value)
			$row["SavePrice"]		= show_price($row["list_price"]-$row["Price"]);

			// you save (%)
			if ($row["list_price"])
			$row["SavePricePercent"] = ceil(((($row["list_price"]-$row["Price"])/$row["list_price"])*100));

			_setPictures( $row );

			$row["product_extra"]=GetExtraParametrs($row["productID"]);
			$row["PriceWithOutUnit"]= show_priceWithOutUnit( $row["Price"] );
			$result[] = $row;
		}
		$i++;
	}
	$count_row = $i;
	return $result;
}


// *****************************************************************************
// Purpose	gets all products by categoryID
// Inputs     	$callBackParam item
//			"categoryID"
//			"fullFlag"
// Remarks
// Returns
function prdGetProductByCategory( $callBackParam, &$count_row, $navigatorParams = null )
{

	if ( $navigatorParams != null )
	{
		$offset			= $navigatorParams["offset"];
		$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
	}
	else
	{
		$offset = 0;
		$CountRowOnPage = 0;
	}

	$result = array();

	$categoryID	= $callBackParam["categoryID"];
	$fullFlag	= $callBackParam["fullFlag"];
	if ( $fullFlag )
	{
		$conditions = array( " categoryID=$categoryID " );
		$q = db_query("select productID from ".
		CATEGORIY_PRODUCT_TABLE." where  categoryID=$categoryID");
		while( $products = db_fetch_row( $q ) ){
			$conditions[] = " productID=".$products[0];
		}
		db_free_result($q);

		$data = array();
		$langManager = &LanguagesManager::getInstance();
		foreach( $conditions as $cond )
		{
			$q=db_query("select * from ".PRODUCTS_TABLE.
			" where ".$cond );
			while( $row = db_fetch_row($q) )
			{
				LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $row);
				$row["PriceWithUnit"]		= show_price($row["Price"]);
				$row["list_priceWithUnit"] 	= show_price($row["list_price"]);
				// you save (value)
				$row["SavePrice"]		= show_price($row["list_price"]-$row["Price"]);

				// you save (%)
				if ($row["list_price"])
				$row["SavePricePercent"] = ceil(((($row["list_price"]-$row["Price"])/$row["list_price"])*100));
				_setPictures( $row );
				$row["product_extra"]=GetExtraParametrs($row["productID"]);
				$row["PriceWithOutUnit"]= show_priceWithOutUnit( $row["Price"] );
				$data[] = $row;
			}
			db_free_result($q);
		}

		function _compare( $row1, $row2 )
		{
			if ( (int)$row1["sort_order"] == (int)$row2["sort_order"] )
			return 0;
			return ((int)$row1["sort_order"] < (int)$row2["sort_order"]) ? -1 : 1;
		}

		usort($data, "_compare");

		$result = array();
		$i = 0;
		foreach( $data as $res )
		{
			if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
			$navigatorParams == null )
			$result[] = $res;
			$i++;
		}
		$count_row = $i;
		return $result;
	}
	else
	{
		$q=db_phquery("SELECT *,".LanguagesManager::sql_constractSortField(PRODUCTS_TABLE, 'name')." FROM ?#PRODUCTS_TABLE WHERE categoryID=? AND enabled=1 order by sort_order, ".LanguagesManager::sql_getSortField(PRODUCTS_TABLE, 'name'), $categoryID );
		$i=0;
		while( $row=db_fetch_assoc($q) )
		{
			if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
			$navigatorParams == null  )
			LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $row);
			$result[] = $row;
			$i++;
		}
		db_free_result($q);
		$count_row = $i;
		return $result;
	}
}

/**
 * Fetch products from current category
 * 
 * @param string $condition
 * @param int $categoryID
 * @return string
 */
function _getConditionWithCategoryConj( $condition, $categoryID, $check_subcategories = false ){

	$categoryID_Array = $check_subcategories?catGetSubCategories( $categoryID ):array();
	$categoryID_Array[] = (int)$categoryID;
	
	$sql_category_part = 'categoryID IN ('.implode(',', xEscapeSQLstring($categoryID_Array)).')';
	
	$dbq = '
		SELECT productID FROM ?#CATEGORIY_PRODUCT_TABLE WHERE categoryID IN (?@)
	';
	$dbres = db_phquery($dbq, $categoryID_Array);
	$rel_products = array();
	while ($row = db_fetch_row($dbres)){
		$rel_products[] = $row[0];
	}
	
	$sql_product_part = count($rel_products)?'p.productID IN ('.implode(',', xEscapeSQLstring($rel_products)).')':'';

	return ($sql_product_part?'( ('.$sql_product_part.') OR ('.$sql_category_part.') )':'('.$sql_category_part.')').($condition?' AND ('.$condition.')':'');
}

/**
 * @param array $template - array of item ("optionID"=>option ID,"value"=>value or variant ID)
 */
function _prepareSearchExtraParameters($template){

	$sqls_joins = array();
	$sqls_options = array();
	$categoryID = $template['categoryID'];
	$sqls_params = array();
	
	$cnt = 0;
	$_count = 0;
	foreach( $template as $key => $item ){
		
		if(!isset($item['optionID']))continue;
		if($item['value'] === '')continue;
		if($key === 'categoryID' )continue;

		// get value to search
		$res = schOptionIsSetToSearch( $categoryID, $item['optionID'] );

		if($res['set_arbitrarily'] && $item['value'] === '0')continue;
		
		if($res['set_arbitrarily']){
			$sqls_joins[] = '
				LEFT JOIN ?#PRODUCT_OPTIONS_VALUES_TABLE PrdOptVal'.$cnt.' ON p.productID=PrdOptVal'.$cnt.'.productID
				LEFT JOIN ?#PRODUCTS_OPTIONS_SET_TABLE PrdOptSet'.$cnt.' ON p.productID=PrdOptSet'.$cnt.'.productID
				LEFT JOIN ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE PrdOptValVar'.$cnt.' ON 
					PrdOptSet'.$cnt.'.optionID=PrdOptValVar'.$cnt.'.optionID AND
					PrdOptSet'.$cnt.'.variantID=PrdOptValVar'.$cnt.'.variantID
			';
			$search_name = 'option_value_'.($_count++);
			$sqls_params[$search_name] = '%'._searchPatternReplace($item['value']).'%';
			$sqls_options[] = '
				PrdOptVal'.$cnt.'.optionID='.intval($item['optionID']).'
				AND 
				(
				( PrdOptVal'.$cnt.'.option_type=1
				AND PrdOptValVar'.$cnt.'.'.LanguagesManager::sql_prepareField('option_value').' LIKE ?'.$search_name.
				') OR (
				PrdOptVal'.$cnt.'.option_type=0
				AND PrdOptVal'.$cnt.'.'.LanguagesManager::sql_prepareField('option_value').' LIKE ?'.$search_name.
				')
				)
			';
				
		}else{
			
			$sqls_joins[] = '
				LEFT JOIN ?#PRODUCT_OPTIONS_VALUES_TABLE PrdOptVal'.$cnt.' ON p.productID=PrdOptVal'.$cnt.'.`productID`
				LEFT JOIN ?#PRODUCTS_OPTIONS_SET_TABLE PrdOptSet'.$cnt.' ON p.productID=PrdOptSet'.$cnt.'.`productID`
			';
		/*	$sqls_options[] = '
				PrdOptVal'.$cnt.'.optionID='.intval($item['optionID']).' 
				AND PrdOptVal'.$cnt.'.option_type=1
				AND PrdOptSet'.$cnt.'.variantID='.intval($item['value']).' 
			';
			*/
			
			/* !!!!!!!!!!!!!! */			

			// get real option text value - required to search for fixed option values

			$qtmp = db_phquery( "select ".LanguagesManager::sql_prepareField('option_value')." from ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE".
					" where optionID=? and variantID=?",intval($item['optionID']),intval($item['value']));

			$rowtmp = db_fetch_row($qtmp);

			if ($rowtmp)
				$item_text_value = $rowtmp[0];
			else
				$item_text_value = "";

			//changed this fragment to support search in fixed product parameters.
			$search_name = 'option_value_'.($_count++);
			$sqls_params[$search_name] = '%'.$item_text_value.'%';
			$sqls_options[] = '
				PrdOptVal'.$cnt.'.optionID='.intval($item['optionID']).' 
				AND 
				(
					( PrdOptVal'.$cnt.'.option_type=1 AND PrdOptSet'.$cnt.'.variantID='.intval($item['value']).')
					 OR 
					(PrdOptVal'.$cnt.'.option_type=0 AND PrdOptVal'.$cnt.'.'.LanguagesManager::ml_getLangFieldName('option_value').' LIKE ?'.$search_name.')
				)';

/* !!!!!!!!!!!!!! */
		}
		$cnt++;
	}
	
	return array('where' => $sqls_options, 'join' => $sqls_joins, 'params'=>$sqls_params);
}

function _searchPatternReplace($string){
	static $patterns = array('/\\\\/',
							'/%/',
							'/_/',
							'/(^|[^\/]{1})(\?)/',
							'/([\/]{1})(\?)/',
							'/(^|[^\/]{1})(\*)/',
							'/([\/]{1})(\*)/',
							'/(^|[^\/]{1})\+/',
							'/([\/]{1})\+/',
							);
	static $replacements = array('\\\\\\\\',
								'\\%',
								'\\_',
								'\\1_',
								'?',
								'\\1%',
								'*',
								'\\1 ',
								'+');
	return preg_replace($patterns,$replacements,$string);
	//return $res;
	//return str_replace(array('%','_','?','%'),array('\\%','\\_','_','%'),$string);
}

// Purpose	gets all products by categoryID
// Inputs     	$callBackParam item
//					"search_simple"				- string search simple
//					"sort"					- column name to sort
//					"direction"				- sort direction DESC - by descending,
//												by ascending otherwise
//					"searchInSubcategories" - if true function searches
//						product in subcategories, otherwise it does not
//					"searchInEnabledSubcategories"	- this parametr is actual when
//											"searchInSubcategories" parametr is specified
//											if true this function take in mind enabled categories only
//					"categoryID"	- is not set or category ID to be searched
//					"name"			- array of name template
//					"product_code"		- array of product code template
//					"price"			-
//								array of item
//									"from"	- down price range
//									"to"	- up price range
//					"enabled"		- value of column "enabled"
//									in database
//					"extraParametrsTemplate"
function prdSearchProductByTemplate($callBackParam, &$count_row, $navigatorParams = null){
	
	$limit = $navigatorParams != null?' LIMIT '.(int)$navigatorParams['offset'].','.(int)$navigatorParams['CountRowOnPage']:'';
	$where_clause = '';
	$where_sku_clause = '';
	$_sqlParams = array();

	if ( isset($callBackParam['search_simple']) ){
		if (!count($callBackParam['search_simple'])){ //empty array
			$where_clause = ' WHERE 0';
		}else{ //search array is not empty
			$_count = 0;
			foreach( $callBackParam['search_simple'] as $value ){

				//check if $value is a word in plural, e.g. flowers, bags, players
				//in this case we should get rid of 's' at the end to make search more efficient
				if (mb_strlen($value,'UTF-8')>3 && $value{ mb_strlen($value,'UTF-8')-1 } == 's'){
					 
					$value = mb_substr( $value, 0, mb_strlen($value,'UTF-8')-1,'UTF-8');

				}
				$value = mb_strtolower($value,'UTF-8');
				if(!strlen($value))continue;
				//$value = preg_replace(array('/([\/]{1})\+/','/(^|[^\/]{1})\+/'),array('+',' '),$value);
				//$sql_value = xEscapeSQLstring($value);
				$search_name = 'search_simple_'.($_count++);
				$_sqlParams[$search_name] = '%'._searchPatternReplace($value).'%';
				$where_clause .= ($where_clause?' AND':'').' ( '.LanguagesManager::sql_prepareField('name').' LIKE ?'.$search_name.' OR
				'.LanguagesManager::sql_prepareField('description').' LIKE ?'.$search_name.' OR
				'.LanguagesManager::sql_prepareField('brief_description').' LIKE ?'.$search_name.') ';
				if(defined('CONF_ENABLE_PRODUCT_SKU')&&constant('CONF_ENABLE_PRODUCT_SKU')){
					$where_sku_clause .= ($where_sku_clause?' AND':'').' (product_code LIKE ?'.$search_name.')';
				}
			}
			if($where_sku_clause){
				if($where_clause){
					$where_clause = "(({$where_clause}) OR ({$where_sku_clause}))";
				}else{
					$where_clause = $where_sku_clause;
				}
			}
			$where_clause = ' WHERE categoryID<>1 and enabled=1'.($where_clause?' AND '.$where_clause:'');
		}
	}
	else{

		if (isset($callBackParam['enabled']))$where_clause.=($where_clause?' AND':'').' enabled='.xEscapeSQLstring($callBackParam['enabled']);
		if ( isset($callBackParam['name']) ){
			$_count = 0;
			$where_clause_name = '';
			foreach( $callBackParam['name'] as $name ){
				if (!$name)continue;
				$search_name = 'search_'.($_count++);
				$_sqlParams[$search_name] = '%'._searchPatternReplace($name).'%';
				$where_clause_name .= ($where_clause_name?' AND':'').' '.LanguagesManager::sql_prepareField('name').' LIKE ?'.$search_name;
			}
			if($where_clause_name){
				$where_clause .= ($where_clause?' AND':'').' ('.$where_clause_name.')';
			}
		}
		if(isset($callBackParam['product_code'])){
			$_count = 0;
			$where_clause_code = '';
			foreach( $callBackParam['product_code'] as $product_code ){
				$search_name = 'product_code'.($_count++);
				$_sqlParams[$search_name] = '%'._searchPatternReplace($product_code).'%';
				$where_clause_code .= ($where_clause_code?' AND':'').' product_code LIKE ?'.$search_name;
			}
			if($where_clause_code){
				$where_clause .= ($where_clause?' OR':'').' ('.$where_clause_code.')';
			}
		}
		

		if(isset($callBackParam['price']['from']))$where_clause .= ($where_clause?' AND':'').' '.ConvertPriceToUniversalUnit($callBackParam['price']['from']).'<=Price ';
		if(isset($callBackParam['price']['to']))$where_clause .= ($where_clause?' AND':'').' Price<='.ConvertPriceToUniversalUnit($callBackParam['price']['to']).' ';
		if(isset($callBackParam['!productID']))$where_clause .= ($where_clause?' AND':'').' `p`.`productID`<>'.xEscapeSQLstring($callBackParam['!productID']).' ';

		if ( isset($callBackParam['categoryID'])){
			$where_clause = _getConditionWithCategoryConj( $where_clause, $callBackParam['categoryID'], isset($callBackParam['searchInSubcategories'])&&$callBackParam['searchInSubcategories']);
		}

		$where_clause = $where_clause?'WHERE '.$where_clause:'';
	}
	$sort_field = 'name';
	$order_by_clause = ' ORDER BY sort_order, '.LanguagesManager::sql_getSortField(PRODUCTS_TABLE, $sort_field);
	
	if(isset($callBackParam['sort'])&&in_array($callBackParam['sort'],array('name','brief_description','in_stock','Price','customer_votes','customers_rating',
	'list_price','sort_order','items_sold','product_code','shipping_freight'))){

		$order_by_clause = ' ORDER BY '.LanguagesManager::sql_getSortField(PRODUCTS_TABLE, $callBackParam['sort']).' ASC ';
		if (isset($callBackParam['direction'])&&$callBackParam['direction'] == 'DESC')$order_by_clause = ' ORDER BY '.LanguagesManager::sql_getSortField(PRODUCTS_TABLE, $callBackParam['sort']).' DESC ';
	}

	/**
	 * Tags search
	 */
	$left_join = '';
	$group_by = '';
	if(isset($callBackParam['search_tags'])){
		
		$where_tags = '';
		foreach($callBackParam['search_tags'] as $value ){

			/**
			 * check if $value is a word in plural, e.g. flowers, bags, players
			 * in this case we should get rid of 's' at the end to make search more efficient
			 */
		    if (strlen($value)>3 && $value{ strlen($value)-1 } == 's'){
				$value = substr( $value, 0, strlen($value)-1 );
		    }
		    if (!$value)continue;
		    $value = mb_strtolower($value,'UTF-8');
		    //$value = xEscapeSQLstring($value);
		    $search_name = 'search_tags'.($_count++);
			$_sqlParams[$search_name] = '%'._searchPatternReplace($value).'%';
		    $where_tags .= ($where_tags?' OR':'').' t1.name LIKE ?'.$search_name;
		}
		
		if($where_tags){
			$left_join = '
				LEFT JOIN ?#TAGGED_OBJECTS_TBL t2 ON p.productID=t2.object_id 
				LEFT JOIN ?#TAGS_TBL t1 ON t2.tag_id=t1.id AND t2.object_type="product"
			';
			$where_clause = trim(str_replace('WHERE', '', $where_clause));
			$where_clause = 'WHERE ( ('.$where_tags.') AND categoryID<>1 AND enabled=1)'.($where_clause?' OR ('.$where_clause.')':'');
			
			$group_by = ' GROUP BY p.productID';
		}
	}
		
	/**
	 * Seach by extra parameters
	 */
	if(isset($callBackParam['extraParametrsTemplate'])){
		
		$_sqls = _prepareSearchExtraParameters($callBackParam['extraParametrsTemplate']);
		if(count($_sqls['where'])){

			$left_join = implode(' ', $_sqls['join']);
			$where_clause = trim(str_replace('WHERE', '', $where_clause));
			$where_clause = 'WHERE '.($where_clause?'('.$where_clause.') AND ':'').'('.implode(') AND (',$_sqls['where']).')';
			$group_by = ' GROUP BY p.productID';
		}
		$_sqlParams = array_merge($_sqlParams,$_sqls['params']);
	}
	

	//$dbq = 'SELECT COUNT(DISTINCT p.productID) as cnt FROM '.PRODUCTS_TABLE.' p '.$left_join.$where_clause;
	//$count_row = db_phquery_fetch(DBRFETCH_FIRST,$dbq,$_sqlParams);
	
	
	if($count_row&&isset($navigatorParams['offset'])&&($count_row<$navigatorParams['offset'])){
		$navigatorParams['offset'] = $navigatorParams['CountRowOnPage']*intval($count_row/$navigatorParams['CountRowOnPage']);
	}
	
	$limit = $navigatorParams != null?' LIMIT '.(int)$navigatorParams['offset'].','.(int)$navigatorParams['CountRowOnPage']:'';
	

	$dbq = 'SELECT '.($limit?'SQL_CALC_FOUND_ROWS ':'').' p.*, '.LanguagesManager::sql_constractSortField(PRODUCTS_TABLE, $sort_field).' FROM '.PRODUCTS_TABLE.' p '.$left_join.$where_clause.$group_by.' '.$order_by_clause.$limit;


	$Result = db_phquery($dbq,$_sqlParams);
	
	if($limit){
		$dbq = 'SELECT FOUND_ROWS()';
		$count_row = db_phquery_fetch(DBRFETCH_FIRST,$dbq,$_sqlParams);
	}
	

	$Products = array();
	$ProductsIDs = array();
	$Counter = 0;
	while ($_Product = db_fetch_assoc($Result)) {

		LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $_Product);
		if (!$_Product["productID"] && ($_Product[0]>0)) $_Product["productID"] = $_Product[0]; 
		$_Product['PriceWithUnit'] = show_price($_Product['Price']);
		$_Product['list_priceWithUnit'] = show_price($_Product['list_price']);
		// you save (value)
		$_Product['SavePrice'] = show_price($_Product['list_price']-$_Product['Price']);
		// you save (%)
		if($_Product['list_price'])$_Product['SavePricePercent'] = ceil(((($_Product['list_price']-$_Product['Price'])/$_Product['list_price'])*100));
		$_Product['PriceWithOutUnit']	= show_priceWithOutUnit( $_Product['Price'] );
		if ( ((float)$_Product['shipping_freight']) > 0 )
		$_Product['shipping_freightUC'] = show_price( $_Product['shipping_freight'] );
		$ProductsIDs[$_Product['productID']] = $Counter;
		$_Product['vkontakte_update_timestamp']= ($_Product['vkontakte_update_timestamp']>0)?Time::standartTime( $_Product['vkontakte_update_timestamp'] ):'';
		
		$Products[] = $_Product;
		$Counter++;
	}
	if(!$limit){
		$count_row = $Counter;
	}
	$ProductsExtra = GetExtraParametrs(array_keys($ProductsIDs));
	foreach ($ProductsExtra as $_ProductID=>$_Extra){

		$Products[$ProductsIDs[$_ProductID]]['product_extra'] = $_Extra;
	}
	_setPictures($Products);

	return $Products;
}

function prdSetProductDefaultPicture($productID, $photoID){
	
	db_phquery("UPDATE ?#PRODUCTS_TABLE SET default_picture=? WHERE productID=?", $photoID, $productID);
}

function prdGetMetaTags( $productID ){ //gets META keywords and description - an HTML code to insert into <head> section

	$productID = (int) $productID;

	$q = db_query( "SELECT ".LanguagesManager::sql_prepareField('meta_description')." AS meta_description, ".LanguagesManager::sql_prepareField('meta_keywords')." AS meta_keywords FROM ".PRODUCTS_TABLE." WHERE productID=".$productID );

	$row = db_fetch_row($q);
	$meta_description	= trim($row["meta_description"]);
	$meta_keywords = trim($row["meta_keywords"]);

	$res = '';

	if  ( $meta_description != '' )
	$res .= "<meta name=\"description\" content=\"".xHtmlSpecialChars($meta_description)."\">\n";
	if  ( $meta_keywords != '' )
	$res .= "<meta name=\"keywords\" content=\"".xHtmlSpecialChars($meta_keywords)."\" >\n";

	return $res;
}

function haveProductSelectableOptions($product_id)
{
    $sql = "select count(optionID) as cnt from ".PRODUCT_OPTIONS_VALUES_TABLE." ".
           "where productID = {$product_id} and option_type = 1";
    $res = db_query($sql);
    $row = db_fetch_assoc($res);
    
    return ($row['cnt'] > 0);
}

?>