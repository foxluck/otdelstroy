<?php
global $picture_columns_count;
global $extra_columns_count;
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();

function __exportProducts()
{
	$exportData = ClassManager::getInstance('ExportData');
	/*@var $exportData ExportData*/
	$whereClause = '';
	$exportData->sqlWhereClause = $whereClause;
		
	$orderClause = "";
	$exportData->sqlOrderClause = $orderClause;
		
	$exportData->charset = $_GET['charset'];
	$exportData->setHeaders(array_map('translate',
	array('')));
	$exportData->sqlQuery = '';
		
		
		
	//$exportData->setRowHandler('foreach($row as $key=>&$value){$value = "[{$key}]{$value}";}');
	//	$exportData->setRowHandler('$row[\'orderID\'] = {$value = "[{$key}]{$value}";}');
	$exportData->setRowHandler('$row[\'order_time\'] = Time::standartTime($row[\'order_time\']);return $row;');
		
		
	//$exportData->fileName = DIR_TEMP.'/orders.csv';
		
	$time = microtime(true);
	$res = $exportData->exportDataToFile(DIR_TEMP.'/orders.csv');
}

function _exportCategoryLine($categoryID, $level, &$f, $delimiter = ";",$product_fields){ //writes a category line into CSV file.

	global $picture_columns_count;
	global $extra_columns_count;

	//$defaultLanguage = &LanguagesManager::getDefaultLanguage();

	$q = db_phquery("SELECT * FROM ?#CATEGORIES_TABLE WHERE categoryID=?", $categoryID);
	$cat_data = db_fetch_assoc($q);
	if (!$cat_data) return;

	$lev = "";
	$lang_name_fields = LanguagesManager::ml_getLangFieldNames('name');
	for ($i=0;$i<$level;$i++) $lev .= "!";
	foreach($lang_name_fields as $lang_name_field){
		$cat_data[$lang_name_field] = $lev.$cat_data[$lang_name_field];
	}

	$data = array();
	if(!isset($cat_data['slug'])||!$cat_data['slug']){
		$cat_data['slug'] = $cat_data['categoryID'];
	}
	$product_fields['picture'] = 'picture';
	foreach ($product_fields as $_k=>$_t){

		$data[$_k] = '';
		if(!isset($cat_data[$_k]))continue;

		$data[$_k] = $cat_data[$_k];
		if (strstr($data[$_k],"\"") || strstr($data[$_k],"\n") || strstr($data[$_k],$delimiter)){
			
				
			$data[$_k] = '"'.str_replace('"','""', str_replace("\r\n"," ",$data[$_k]) ).'"';
		}
	}
	fputs($f, implode($delimiter, $data));
	for ($i=1;$i<$picture_columns_count+$extra_columns_count;$i++)fputs($f,$delimiter);

	fputs($f,"\n");
}

function _exportProducts($categoryID, &$f, $delimiter = ";",$product_fields,$option_fields) //writes all products inside a single category to a CSV file
{
	global $picture_columns_count;
	global $extra_columns_count;

	$defaultLanguage = &LanguagesManager::getDefaultLanguage();
	//products
	$q1 = db_phquery('SELECT *, '.LanguagesManager::sql_constractSortField(PRODUCTS_TABLE, 'name').' FROM ?#PRODUCTS_TABLE WHERE categoryID=? ORDER BY sort_order, '.LanguagesManager::sql_getSortField(PRODUCTS_TABLE, 'name'), $categoryID);
	while ($row1 = db_fetch_assoc($q1)){
		
		if(!isset($row1['slug'])||!$row1['slug']){
			$row1['slug'] = $row1['productID'];
		}
		$row1['invisible'] = (isset($row1['enabled'])&&!$row1['enabled'])?"1":"";

		$data = array();
		foreach($product_fields as $_k=>$_v){
						
			$data[$_k] = '';

			if(!isset($row1[$_k])||$_k=='picture')continue;
			$data[$_k] = $row1[$_k];
			if (strstr($data[$_k],"\"") || strstr($data[$_k],"\n") || strstr($data[$_k],$delimiter)){
				$data[$_k] = '"'.str_replace('"','""',str_replace("\r\n"," ",$data[$_k])).'"';
			}elseif($_k == 'product_code' && $data[$_k]){
				$data[$_k] = '"'.$data[$_k].'"';
			} 
			if (!strcmp($_k,"Price") || !strcmp($_k,"list_price")){

				$data[$_k] = round(100*$data[$_k])/100;
				if (round($data[$_k]*10) == $data[$_k]*10 && round($data[$_k])!=$data[$_k])
				$data[$_k] = (string)$data[$_k]."0"; //to avoid prices like 17.5 - write 17.50 instead
			}
			if($_k == 'classID'){
				$class = taxGetTaxClassById($data[$_k]);
				$data[$_k] = $class['name'];
			}
			
		}

		//write primary product information
		fputs($f, implode($delimiter, $data));

		//pictures
		//at first, fetch default picture
		$cnt = 0;
		if (!$row1["default_picture"]) $row1["default_picture"] = 0; //no default picture defined;
		$qp = db_query("select filename, thumbnail, enlarged from ".PRODUCT_PICTURES." where productID=".$row1["productID"]." and photoID=".$row1["default_picture"]);
		$rowp = db_fetch_row($qp);
		$s = "";
		if ($rowp)
		{
			if ($rowp[0]) $s .= $rowp[0];
			if ($rowp[1]) $s .= ",".$rowp[1];
			if ($rowp[2]) $s .= ",".$rowp[2];
		}
		fputs($f,$delimiter.'"'.str_replace('"','""',str_replace("\r\n"," ",$s)).'"');
		$cnt++;
		/**
		 * @features "Multiple image-sets for product"
		 * @state begin
		 */
		//the rest of the photos
		$qp = db_query("select filename, thumbnail, enlarged from ".PRODUCT_PICTURES." where productID=".$row1["productID"]." and photoID <> ".$row1["default_picture"].' ORDER BY priority');
		while ($rowp = db_fetch_row($qp))
		{
			$s = "";
			if ($rowp)
			{
				if ($rowp[0]) $s .= $rowp[0];
				if ($rowp[1]) $s .= ",".$rowp[1];
				if ($rowp[2]) $s .= ",".$rowp[2];
			}
			fputs($f,$delimiter.'"'.str_replace('"','""',str_replace("\r\n"," ",$s)).'"');
			$cnt++;
		}

		if ($cnt < $picture_columns_count)
		for ($i=$cnt; $i<$picture_columns_count; $i++) fputs($f,$delimiter);
		/**
		 * @features "Multiple image-sets for product"
		 * @state end
		 */


		//extra options
		$q2 = db_query("select optionID, ".LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'name')." from ".PRODUCT_OPTIONS_TABLE." ORDER BY sort_order, ".LanguagesManager::sql_getSortField(PRODUCT_OPTIONS_TABLE, 'name'));
		//browse options list

		$option_values = array();
		while ($row2 = db_fetch_assoc($q2)){
				
			//browser all option values of current product
			$q3 = db_query("select * from ".PRODUCT_OPTIONS_VALUES_TABLE." where productID=".$row1['productID']." and optionID=".$row2['optionID']);
			//			$q3 = db_query("select option_value, option_type, variantID from ".PRODUCT_OPTIONS_VALUES_TABLE." where productID=".$row1['productID']." and optionID=$row2[0]");
			$row3 = db_fetch_assoc($q3);
			if(!$row3) $row3 = array('optionID' => 0, 'option_type' => 0, 'variantID' => 0);
				
			/*
			 @features "Extra options values"
			 @state begin
			 */
			if ((int)$row3['option_type'] == 1){ //selectable option - prepare a string to insert into a CSV file, e.g {red=3,blue=1,white}

				//prepare an array of available option variantIDs. the first element (0'th) is the default varinatID
				$available_variants = array();
				$available_variants[] = array('variantID'=>$row3['variantID']);

				$q4 = db_query( "select variantID, price_surplus from ".PRODUCTS_OPTIONS_SET_TABLE." where productID=".$row1['productID']." and optionID=".$row2['optionID'] );
				while ($row4 = db_fetch_assoc($q4))
				{
					if ($row4['variantID'] == $row3['variantID']){ //is it a default variantID
						$available_variants[0] = $row4;
					}
					else{
						$available_variants[] = $row4; //add this value to array
					}
				}
				//now write all variants
				$s = "{";
				$tmp = "";
				foreach ($available_variants as $key => $val)
				if ($val['variantID'])
				{
					$qvar = db_query("select ".LanguagesManager::ml_getLangFieldName('option_value', $defaultLanguage)." from ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE." where optionID=".$row2['optionID']." and variantID=".$val['variantID']);
					$rowvar = db_fetch_row($qvar);
					$s .= $tmp;
					$s .= $rowvar[0]."";
					if ($val['price_surplus']) $s .= "=".$val['price_surplus'];
					$tmp = ",";
				}else{
					$s .= ',';
				}
				$s .= "}";

				$row3[LanguagesManager::ml_getLangFieldName('option_value', $defaultLanguage)] = $s;
				$row3['only_default'] = true;
			}
			/*
			 @features "Extra options values"
			 @state end
			 */

			$option_values[$row3['optionID']] = $row3;
			//write a string into CSV file
			/*
			 if (strstr($row3[0],"\"") || strstr($row3[0],"\n") || strstr($row3[0],$delimiter))
			 {
				$row3[0] = '"'.str_replace('"','""',str_replace("\r\n"," ",$row3[0])).'"';
				}
				fputs($f, $delimiter."$row3[0]");
				*/
		}
		$option_data = array();
		foreach($option_fields as $_k=>$_t){

			$option_data[$_k] = '';
			list($optionID, $lang_iso2) = _parseOptionKey($_k);
			if(isset($option_values[$optionID]['only_default'])){

				if($lang_iso2 == $defaultLanguage->iso2){
						
					$option_data[$_k] = $option_values[$optionID][LanguagesManager::ml_getLangFieldName('option_value', $defaultLanguage)];
				}
				continue;
			}else{
					
				$option_data[$_k] = $option_values[$optionID]['option_value_'.$lang_iso2];
			}
				
			if (strstr($option_data[$_k],"\"") || strstr($option_data[$_k],"\n") || strstr($option_data[$_k],$delimiter)){

				$option_data[$_k] = '"'.str_replace('"','""',str_replace("\r\n"," ",$option_data[$_k])).'"';
			}
		}

		if(count($option_data)){
			fputs($f, $delimiter.implode($delimiter, $option_data));
		}
		fputs($f,"\n");
	}
}

function _exportSubCategoriesAndProducts($parent, $level, &$f, $delimiter=";",$product_fields,$option_fields) //exports products and subcategories of $parent to a CSV file $f
//a recurrent function
{
	$cnt = 0;
	$q = db_query("select categoryID,".LanguagesManager::sql_constractSortField(CATEGORIES_TABLE, 'name')." from ".CATEGORIES_TABLE." where parent=$parent order by sort_order, ".LanguagesManager::sql_getSortField(CATEGORIES_TABLE, 'name'));

	//fetch all subcategories
	while ($row = db_fetch_row($q))
	{
		_exportCategoryLine($row[0], $level, $f, $delimiter,$product_fields);
		_exportProducts($row[0], $f, $delimiter,$product_fields,$option_fields);
			
		//process all subcategories
		_exportSubCategoriesAndProducts($row[0], $level+1, $f, $delimiter,$product_fields,$option_fields);
	}

} //_exportSubCategoriesAndProducts

function escapeCSVField($string, $delimiter){

	if (strstr($string,'"') || strstr($string,"\n") || strstr($string,$delimiter)){
		$string = '"'.str_replace('"','""',str_replace(array("\r\n", "\n", "\r")," ",$string)).'"';
	}

	return $string;
}

//products and categories catalog import from MS Excel .CSV files

if (isset($_POST["excel_export"])) //export products
{
	@set_time_limit(0);

	if ($_POST["delimiter"]==";" || $_POST["delimiter"]=="," || $_POST["delimiter"]=="\t"|| $_POST["delimiter"]=='\t'){
		$delimiter = $_POST["delimiter"];
		if($delimiter == '\t'){
			$delimiter = "\t";
		}
	}else{
		$delimiter = ";";
	}
	$export_file_name = (CONF_SHOP_NAME?preg_replace('/[^a-z_0-9]/ui', '_', 'catalog_'.strtolower(translit(CONF_SHOP_NAME))):'catalog');
	$export_file_name_extension = '_'.strtolower($_POST['charset']).'.csv';
	
	$export_file_name = substr($export_file_name,0,(128-strlen($export_file_name_extension)));
	$export_file_name .= $export_file_name_extension;
	

	$f = fopen(DIR_TEMP."/{$export_file_name}","w");
	$product_fields = _getProductFields();
	if(isset($product_fields['picture'])){
		unset($product_fields['picture']);
	}

	//write a header line
	fputs($f, implode($delimiter, xCall('escapeCSVField', $product_fields, $delimiter)));

	//calculate the number of 'Picture' columns
	/**
	 * @features "Multiple image-sets for product"
	 * @state begin
	 */
	$sql = <<<SQL
	SELECT COUNT( `productID` ) AS 'cnt'
	FROM `?#PRODUCT_PICTURES`
	GROUP BY `productID`
	ORDER BY `cnt` DESC 
	LIMIT 1
SQL;
	$q = db_phquery($sql);
	$max = 0;
	$result = array();
	while ($row = db_fetch_row($q)){
		if ($max < $row[0]){
			$max = $row[0];
		}
	}
	/**
	 * @features "Multiple image-sets for product"
	 * @state end
	 */
	//record as many PICTURE columns in the file as located in the database
	for ($i=0;$i<$max;$i++)
	{
		fputs($f, $delimiter.xCall('escapeCSVField', translate("prdset_product_picture"), $delimiter));
	}
	$picture_columns_count = $max;
	$option_fields = _getOptionFields();
	$extra_columns_count = count($option_fields);
	if($extra_columns_count){
		fputs($f, $delimiter.implode($delimiter, xCall('escapeCSVField', $option_fields, $delimiter)));
	}
	fputs($f,"\n");

	//export selected products and categories
	//root
	if (isset($_POST["categ_1"]))
	{
		_exportProducts(1, $f, $delimiter);
	}
	//other categories
	$dbq = "
		SELECT categoryID, ".LanguagesManager::sql_constractSortField(CATEGORIES_TABLE, 'name')." 
		FROM ".CATEGORIES_TABLE." WHERE parent=1 ORDER BY sort_order, ".LanguagesManager::sql_getSortField(CATEGORIES_TABLE, 'name');
	$q = db_query($dbq);
	$result = array();
	$option_fields = _getOptionFields();
	while ($row = db_fetch_row($q))
	if (isset($_POST["categ_$row[0]"]))
	{
		_exportCategoryLine($row[0], 0, $f, $delimiter,$product_fields);
		_exportProducts($row[0], $f, $delimiter,$product_fields,$option_fields);
		_exportSubCategoriesAndProducts($row[0], 1, $f, $delimiter,$product_fields,$option_fields);
	}

	fclose($f);

	if($_POST['charset'] && $_POST['charset'] != DEFAULT_CHARSET){
		File::convert(DIR_TEMP."/{$export_file_name}",DEFAULT_CHARSET,$_POST['charset']);
		//iconv_file(DEFAULT_CHARSET, $_POST['charset'], DIR_TEMP."/{$export_file_name}");
	}
	xPopData('EXPORT_PRODUCTS2CSV');
	xSaveData('EXPORT_PRODUCTS2CSV',array('file'=>base64_encode($export_file_name)),3600);
	RedirectSQ('&export_completed=yes');
}

if (isset($_GET["export_completed"])) //show successful save confirmation message
{
	$file = xPopData('EXPORT_PRODUCTS2CSV');
	//$file = isset($_GET['file'])?base64_decode($_GET['file']):'';
	$file = isset($file['file'])?base64_decode($file['file']):'';
	set_query('export_completed=','',true);
	if ($file && file_exists(DIR_TEMP."/{$file}"))
	{
		$getFileParam = Crypt::FileParamCrypt( "GetCSVCatalog=".base64_encode($file), null );
		$smarty->assign( "getFileParam", $getFileParam );

		$smarty->assign("excel_export_successful", 1);
		$smarty->assign("excel_filesize", (string) round( filesize(DIR_TEMP."/{$file}") / 1024 ) );
	}
}else{ //prepare categories list

	$dbq = "
		SELECT categoryID, ".LanguagesManager::sql_constractSortField(CATEGORIES_TABLE, 'name')." 
		FROM ".CATEGORIES_TABLE." WHERE parent=1 ORDER BY sort_order, ".LanguagesManager::sql_getSortField(CATEGORIES_TABLE, 'name');
	$q = db_query($dbq);
	$result = array();
	while ($row = db_fetch_row($q)) $result[] = $row;
	$smarty->assign("categories",$result);
}

global $file_encoding_charsets;
$smarty->assign('charsets', $file_encoding_charsets);
$smarty->assign('default_charset', translate('prdine_default_charset'));
$smarty->assign('admin_sub_dpt', "catalog_excel_export.tpl.html");
?>