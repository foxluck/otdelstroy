<?php

function _recursiveGetCategoryCompactCList( $path, $level )
{
	if(!$path[$level-1]["categoryID"]){
		//var_dump($path,$level);exit;
		return array();
	}
	$name = LanguagesManager::sql_prepareField('name',true);
	$sql = <<<SQL
	SELECT `categoryID`, `parent`, `slug`, id_1c, {$name} 
	FROM `?#CATEGORIES_TABLE`
	WHERE `parent`=?
	ORDER BY `sort_order`, `name` 
SQL;
	$q = db_phquery($sql,$path[$level-1]["categoryID"]);
	$res = array();
	$selectedCategoryID = null;
	while( $row=db_fetch_row($q) ){

		$row["level"] = $level;
		$res[] = $row;
		if ( $level <= count($path)-1 )	{
			if ( (int)$row["categoryID"] == (int)$path[$level]["categoryID"] ){
				$selectedCategoryID = $row["categoryID"];
				$array = _recursiveGetCategoryCompactCList( $path, $level+1 );
				foreach( $array as $val ){
					$res[] = $val;
				}
			}
		}
	}

	return $res;
}

function catExpandCategory( $categoryID, $sessionArrayName )
{
	$categoryID = intval($categoryID);
	if(!isset($_SESSION[$sessionArrayName])||!is_array($_SESSION[$sessionArrayName])){
		$_SESSION[$sessionArrayName] = array();
	}
	if(!in_array($categoryID,$_SESSION[$sessionArrayName])){
		$_SESSION[$sessionArrayName][] = $categoryID;
	}
}

function catShrinkCategory( $categoryID, $sessionArrayName )
{
	$categoryID = intval($categoryID);
	if(!isset($_SESSION[$sessionArrayName])||!is_array($_SESSION[$sessionArrayName])){
		$_SESSION[$sessionArrayName] = array();
	}

	$key = array_search($categoryID,$_SESSION[$sessionArrayName]);
	if($key !== false){
		unset($_SESSION[$sessionArrayName][$key]);
	}
}

function catGetCategoryCompactCList( $selectedCategoryID )
{
	static $cached_result = array();
	$selectedCategoryID = intval($selectedCategoryID);
	if(isset($cached_result[$selectedCategoryID])){
		$res = $cached_result[$selectedCategoryID];
	}else{
		$path = catCalculatePathToCategory( $selectedCategoryID );
		$res = array();
		$res[] = array(
			"categoryID" => 1,
			"parent"	 => null,
			"name"		 => translate("prdcat_category_root"),
			"level"		 => 0,
		);
		$name = LanguagesManager::sql_prepareField('name',true);
		$sql = <<<SQL
		SELECT `categoryID`, `slug`, `parent`, {$name} 
		FROM `?#CATEGORIES_TABLE`
		WHERE `parent`=1
		ORDER BY `sort_order`, `name`
SQL;
		$q = db_phquery($sql);
		while( $row = db_fetch_row($q) ){
			$row["level"] = 1;
			$res[] = $row;
			if ( count($path) > 1 ){
				if ( $row["categoryID"] == $path[1]["categoryID"] )	{
					$array = _recursiveGetCategoryCompactCList( $path, 2 );
					foreach( $array as $val ){
						$res[] = $val;
					}
				}
			}
		}
		$cached_result[$selectedCategoryID] = $res;
	}
	return $res;
}

// Purpose	gets category tree to render it on HTML page
// Inputs
//			$parent - must be 0
//			$level	- must be 0
//			$expandedCategoryID_Array - array of category ID that expanded
// Remarks
//			array of item
//				for each item
//					"products_count"			-		count product in category including
//															subcategories excluding enabled product
//					"products_count_admin"		-		count product in category
//															without count product subcategory
//					"products_count_category"	-
// Returns	nothing
function _recursiveGetCategoryCList( $parent, $level, $expandedCategoryID_Array, $_indexType = 'NUM', $_countEnabledProducts = false )
{
	$name = LanguagesManager::sql_prepareField('name', true);//, `id_quickbooks` 
	$sql = <<<SQL
	SELECT `categoryID`, {$name}, `products_count`, `products_count_admin`, `parent`, `id_1c`
	FROM `?#CATEGORIES_TABLE` 
	WHERE `parent`=? 
	ORDER BY `sort_order`, `name`
SQL;
	$q = db_phquery($sql,$parent);
	$result = array(); //parents
	while ($row = db_fetch_row($q))
	{
		$row["level"] = $level;
		$row["ExpandedCategory"] = false;
		if ( $expandedCategoryID_Array != null ) {
			foreach( $expandedCategoryID_Array as $categoryID )	{
				if ( (int)$categoryID == (int)$row["categoryID"] )	{
					$row["ExpandedCategory"] = true;
					break;
				}
			}
		}else{
			$row["ExpandedCategory"] = true;
		}

		$row["products_count_category"] = catGetCategoryProductCount( $row["categoryID"], $_countEnabledProducts );


		$count = catGetSubCategoriesNumber((int)$row["categoryID"]);
		$row["ExistSubCategories"] = ( $count != 0 );

		if($_indexType=='NUM'){
			$result[] = $row;
		}elseif ($_indexType=='ASSOC'){
			$result[$row['categoryID']] = $row;
		}


		if ( $row["ExpandedCategory"] ){
			//process subcategories
			$subcategories = _recursiveGetCategoryCList( $row["categoryID"],
			$level+1, $expandedCategoryID_Array, $_indexType, $_countEnabledProducts  );

			if($_indexType=='NUM'){

				//add $subcategories[] to the end of $result[]
				for ($j=0; $j<count($subcategories); $j++)
				$result[] = $subcategories[$j];
			} elseif ($_indexType=='ASSOC'){

				//add $subcategories[] to the end of $result[]
				foreach ($subcategories as $_sub){

					$result[$_sub['categoryID']] = $_sub;
				}
			}

		}
	}
	return $result;
}

// Purpose	gets category tree to render it on HTML page
function catGetCategoryCList( $expandedCategoryID_Array = null, $_indexType='NUM', $_countEnabledProducts = false )
{
	return _recursiveGetCategoryCList( 1, 0, $expandedCategoryID_Array, $_indexType, $_countEnabledProducts );
}

// Purpose	gets product count in category
// Remarks  this function does not keep in mind subcategories
// Returns	nothing
function catGetCategoryProductCount( $categoryID, $_countEnabledProducts = false )
{
	static $resCache;
	$categoryID = (int)$categoryID;
	if (!$categoryID) return 0;

	if(is_array($resCache)&&isset($resCache[$_countEnabledProducts])&&is_array($resCache[$_countEnabledProducts])){
		return isset($resCache[$_countEnabledProducts][$categoryID])?$resCache[$_countEnabledProducts][$categoryID]:0;
	}

	$res = 0;
	$sql = 'SELECT count(*), `categoryID` FROM `?#PRODUCTS_TABLE` GROUP BY `categoryID`';
	//.($_countEnabledProducts?'WHERE enabled<>0':'');
	$q = db_phquery($sql);
	while($row = db_fetch_row($q)){
		$resCache[$_countEnabledProducts][$row[1]] += $row[0];
	}
	if($_countEnabledProducts)
	$sql = "
			SELECT COUNT(*),catprot.categoryID FROM ".PRODUCTS_TABLE." AS prot
			LEFT JOIN ".CATEGORIY_PRODUCT_TABLE." AS catprot
			ON prot.productID=catprot.productID
			WHERE prot.enabled<>0 GROUP BY catprot.categoryID
		";
	else
	$sql = "select count(*),categoryID from ".CATEGORIY_PRODUCT_TABLE.
			" GROUP BY categoryID";
	$q1 = db_query($sql);
	while($row = db_fetch_row($q1)){
		$resCache[$_countEnabledProducts][$row[1]] += $row[0];
	}
	return isset($resCache[$_countEnabledProducts][$categoryID])?$resCache[$_countEnabledProducts][$categoryID]:0;
	///OLD CODE


	$res = 0;
	$sql = "
		SELECT count(*) FROM ".PRODUCTS_TABLE." 
		WHERE categoryID=$categoryID".($_countEnabledProducts?" AND enabled<>0":"")."
	";
	$q = db_query($sql);
	$t = db_fetch_row($q);
	$res += $t[0];
	if($_countEnabledProducts)
	$sql = "
			SELECT COUNT(*) FROM ".PRODUCTS_TABLE." AS prot
			LEFT JOIN ".CATEGORIY_PRODUCT_TABLE." AS catprot
			ON prot.productID=catprot.productID
			WHERE catprot.categoryID='{$categoryID}' AND prot.enabled<>0
		";
	else
	$sql = "
			select count(*) from ".CATEGORIY_PRODUCT_TABLE.
			" where categoryID=$categoryID
		";
	$q1 = db_query($sql);
	$row = db_fetch_row($q1);
	$res += $row[0];
	return $res;
}

function update_products_Count_Value_For_Categories($parent)
{
	//XXX
/*
	$sqls =array();
	$sqls[] = <<<SQL
	 UPDATE `SC_categories` AS t1 SET
	 `products_count_admin` = (
		(SELECT COUNT(1) FROM `SC_products` as t2 WHERE t2.categoryID =t1.categoryID )
		+
		(SELECT COUNT(1) FROM `SC_category_product` as t3 where t3.categoryID=t1.categoryID))
SQL;
	$sqls[] = <<<SQL
		UPDATE `SC_categories` AS t1 SET
		`products_count` = (
		(SELECT COUNT(1) FROM `SC_products` AS t2 WHERE t2.categoryID =t1.categoryID AND t2.enabled=1)
		+
		(SELECT COUNT(1) FROM `SC_category_product` AS t3 LEFT JOIN `SC_products` as t4 ON (t3.productID = t4.productID )  where t3.categoryID=t1.categoryID AND t4.enabled=1))
SQL;
	foreach($sqls as $sql){
		db_query($sql);
	}
	return;
	$sqls[] = <<<SQL
		UPDATE  `SC_categories` AS t1 JOIN `SC_categories` AS t2 ON (t2.`parent`= t1.`categoryID`)
		SET
		`t1`.`products_count_admin` = (t1.`products_count_admin` + t2. `products_count_admin`),
		`t1`.`products_count` = (t1.`products_count` + t2. `products_count`)
		WHERE
		(t1.`parent`> t1.`categoryID`)
		ORDER BY `t1`.`parent` ASC
SQL;
	$sqls[] = <<<SQL
		UPDATE  `SC_categories` AS t1, `SC_categories` AS t2
		SET
		`t1`.`products_count_admin` = (t1.`products_count_admin` + t2. `products_count_admin`),
		`t1`.`products_count` = (t1.`products_count` + t2. `products_count`)
		WHERE
		(t2.`parent`= t1.`categoryID`)
		AND
		(t1.`parent`< t1.`categoryID`)
		-- ORDER BY `t1`.`parent` DESC
SQL;

	foreach($sqls as $sql){
		db_query($sql);
	}
	return;
*/
	$q = db_query("SELECT categoryID FROM ".CATEGORIES_TABLE.
		" WHERE parent=$parent AND categoryID<>1");
	$cnt = array();
	$cnt["admin_count"] = 0;
	$cnt["customer_count"] = 0;

	// process subcategories
	while( $row=db_fetch_row($q) )
	{
		$t = update_products_Count_Value_For_Categories( $row["categoryID"] );
		$cnt["admin_count"]		+= $t["admin_count"];
		$cnt["customer_count"]  += $t["customer_count"];
	}

	// to administrator
	$q = db_query("SELECT count(*) FROM ".PRODUCTS_TABLE.
			" WHERE categoryID=$parent");
	$t = db_fetch_row($q);
	$cnt["admin_count"] += $t[0];

	// to customer
	$q = db_query("SELECT count(*) FROM ".PRODUCTS_TABLE.
			" WHERE categoryID=$parent AND enabled=1");
	$t = db_fetch_row($q);
	$cnt["customer_count"] += $t[0];
	$q1 = db_query("select productID, categoryID from ".CATEGORIY_PRODUCT_TABLE.
			" where categoryID=$parent");

	$admin_plus = 0;
	while( $row = db_fetch_row($q1) )
	{

		$q2 = db_query("select productID, categoryID from ".PRODUCTS_TABLE.
				" where productID=".$row["productID"]." AND enabled=1 " );
		$res = db_fetch_row($q2);

		if(!$res){

			if ($res['categoryID'] == $parent)$admin_plus++;
			continue;
		}

		if ($res['categoryID'] == $parent){

			$cnt["admin_count"]++;
			$cnt["customer_count"] ++;
		}

	}

	$cnt["admin_count"] += $admin_plus;

	$sql = "UPDATE ".CATEGORIES_TABLE.
			" SET products_count=".$cnt["customer_count"].", products_count_admin=".
	$cnt["admin_count"]." ".
			" WHERE categoryID=".$parent;
	db_query($sql);
	catCountProductDuplicates($parent);

	return $cnt;
}

function catCountProductDuplicates ($_CategoryID){

	$SubCategories = catGetSubCategories($_CategoryID);
	$SubCategories[] = $_CategoryID;
	$sql = "
		SELECT prod.enabled, count(distinct prod.productID) FROM ".CATEGORIY_PRODUCT_TABLE." as catprod
		LEFT JOIN ".PRODUCTS_TABLE." as prod ON catprod.productID = prod.productID
		WHERE catprod.categoryID IN (".implode(", ",$SubCategories).") AND prod.categoryID NOT IN (".implode(", ",$SubCategories).")
		GROUP BY prod.enabled
	";
	$Result = db_query($sql);

	$cntA = 0;
	$cntU = 0;

	while ($Row = db_fetch_row($Result)){

		if(intval($Row[0])>0)
		$cntU = $Row[1];
		else
		$cntA = $Row[1];
	}
	$cntA += $cntU;

	if($cntA || $cntU){

		$sql = "
			UPDATE ".CATEGORIES_TABLE." 
			SET products_count=products_count+{$cntU}, products_count_admin=products_count_admin+{$cntA}
			WHERE categoryID=".intval($_CategoryID)."
		";
		db_query($sql);
	}
}

/**
 * update products_count and products_count_admin if necessary
 *
 * @param integer $_ProductID
 * @param integer $_ProdOrCat
 * @param integer $_ProdAddCat
 */
function catUpdateProductCount($_ProductID, $_ProdAddCat, $_State = 1, $_SourceCategoryID = 0){

	$Product = GetProduct ($_ProductID);
	$subCategories = catGetSubCategories ($_ProdAddCat);
	$subCategories[] = 1;
	if($_SourceCategoryID)
	$subCategories[] = $_ProdAddCat;
	$_State = intval($_State);

	$sql = "
		SELECT 1 FROM ".CATEGORIY_PRODUCT_TABLE."
		WHERE productID='{$_ProductID}' AND categoryID IN (".implode(", ", $subCategories).") AND categoryID<>".intval($_SourceCategoryID)."
	";
	if(!db_fetch_row(db_query($sql)) && !in_array($Product['categoryID'], $subCategories)){

		$sql = "
			UPDATE ".CATEGORIES_TABLE." 
			SET products_count=products_count".($Product['enabled']?"+{$_State}":"").", products_count_admin=products_count_admin+{$_State}
			WHERE categoryID='".intval($_ProdAddCat)."'
		";
		db_query($sql);

		$Category = catGetCategoryById($_ProdAddCat);
		if($_SourceCategoryID == 0)
		$_SourceCategoryID = $_ProdAddCat;
		catUpdateProductCount($_ProductID, $Category['parent'], $_State, $_SourceCategoryID);
	}
}

// Purpose	get subcategories by category id
// Inputs   $categoryID
//				parent category ID
// Remarks  get current category's subcategories IDs (of all levels!)
// Returns	array of category ID
function catGetSubCategories( $categoryID )
{
	$sql = 'SELECT `categoryID` FROM `?#CATEGORIES_TABLE` WHERE `categoryID`>0 AND (`parent` IN ( ?@ ))';
	$categoryID = is_array($categoryID)?$categoryID:array($categoryID);
	$categoryID = array_map('intval',$categoryID);
	$categories = db_phquery_fetch(DBRFETCH_FIRST_ALL,$sql,$categoryID);
	$categories =  array_map('intval',$categories);
	if($categories){
		$categories = array_merge($categories,catGetSubCategories($categories));
	}
	return $categories;
}

// Purpose	get subcategories by category id
// Inputs   	$categoryID
//				parent category ID
// Remarks  	get current category's subcategories IDs (of all levels!)
// Returns	array of category ID
function catGetSubCategoriesSingleLayer( $categoryID )
{
	$q = db_query("SELECT categoryID, ".LanguagesManager::sql_prepareField('name')." AS name, products_count, slug FROM ".
	CATEGORIES_TABLE." WHERE parent='$categoryID' order by sort_order, name");
	$result = array();
	while ($row = db_fetch_row($q))
	$result[] = $row;
	return $result;
}

function catGetSubCategoriesNumber( $categoryID )
{
	static $cache = false;
	if($cache === false){
		$cache = array();
		$sql = <<<SQL
		SELECT COUNT(1) AS `cnt`,`parent`
		FROM `?#CATEGORIES_TABLE`
		GROUP BY `parent`
SQL;
		$q = db_phquery($sql);
		while($row = db_fetch_assoc($q)){
			$row = array_map('intval',$row);
			$cache[$row['parent']] = $row['cnt'];
		}
	}
	return isset($cache[$categoryID])?$cache[$categoryID]:0;
}

// Purpose	get category by id
// Inputs   $categoryID
//				- category ID
function catGetCategoryById($categoryID){

	$q = db_phquery('SELECT * FROM ?#CATEGORIES_TABLE WHERE categoryID=?',$categoryID);
	$row = db_fetch_row($q);
	LanguagesManager::ml_fillFields(CATEGORIES_TABLE, $row);
	return $row;
}

// Purpose	gets category META information in HTML form
// Inputs   $categoryID - category ID
function catGetMetaTags($categoryID){

	@list($meta_description, $meta_keywords) = db_phquery_fetch(DBRFETCH_ROW, 'SELECT '.LanguagesManager::sql_prepareField('meta_description').' AS meta_description, '.LanguagesManager::sql_prepareField('meta_keywords').' AS meta_keywords FROM ?#CATEGORIES_TABLE WHERE categoryID=?',$categoryID);

	$res = '';
	if  ( $meta_description != '' )
	$res .= "<meta name=\"description\" content=\"".xHtmlSpecialChars($meta_description)."\">\n";
	if  ( $meta_keywords != '' )
	$res .= "<meta name=\"keywords\" content=\"".xHtmlSpecialChars($meta_keywords)."\" >\n";
	return $res;
}

// Purpose	adds product to appended category
// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of
//			PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended
//			categories
// Returns	array of item
//			"categoryID"
//			"category_name"
function catGetAppendedCategoriesToProduct( $productID, $calculate_path = false ){

	$dbq = "
		SELECT cat_tbl.categoryID AS categoryID, ".LanguagesManager::sql_prepareField('cat_tbl.name')." AS category_name 
		FROM ?#CATEGORIY_PRODUCT_TABLE AS catprd_tbl, ?#CATEGORIES_TABLE AS cat_tbl
		WHERE catprd_tbl.categoryID = cat_tbl.categoryID AND productID=?
	";
	$q = db_phquery( $dbq, $productID );

	$data = array();
	while( $row = db_fetch_assoc( $q ) ){

		if($calculate_path){

			$row['calculated_path'] = catCalculatePathToCategory($row['categoryID']);
		}
		$data[] = $row;
	}

	return $data;
}

// Purpose	adds product to appended category
// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of
//			PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended
//			categories
// Returns	true if success, false otherwise
function catAddProductIntoAppendedCategory($productID, $categoryID){

	$allready_appended = db_phquery_fetch(DBRFETCH_FIRST,"SELECT COUNT(*) FROM ?#CATEGORIY_PRODUCT_TABLE WHERE productID=? AND categoryID=?", $productID, $categoryID);
	$basic_categoryID = db_phquery_fetch(DBRFETCH_FIRST, "SELECT categoryID FROM ?#PRODUCTS_TABLE WHERE productID=?", $productID);

	if ( !$allready_appended && $basic_categoryID != $categoryID ){

		db_phquery("INSERT ?#CATEGORIY_PRODUCT_TABLE ( productID, categoryID ) VALUES(?,?)", $productID, $categoryID);
		return true;
	}
	else
	return false;
}

// Purpose	removes product to appended category
// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of
//			PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended
//			categories
// Returns	nothing
function catRemoveProductFromAppendedCategory($productID, $categoryID)
{
	$productID = (int) $productID;
	$categoryID = (int) $categoryID;
	db_query("delete from ".CATEGORIY_PRODUCT_TABLE.
		" where productID = $productID AND categoryID = $categoryID");

}

// Purpose	calculate a path to the category ( $categoryID )
// Returns	path to category
function catCalculatePathToCategory( $categoryID )
{
	$categoryID = (int)$categoryID;
	if (!$categoryID) return NULL;
	static $cached_path = array();
	if(isset($cached_path[$categoryID])){
		$path = $cached_path[$categoryID];
	}else{
		$path = array();

		$q = db_query("select count(*) from ".CATEGORIES_TABLE.
				" where categoryID=$categoryID ");
		$row = db_fetch_row($q);
		if ( $row[0] == 0 )
		return $path;

		$curr = intval($categoryID);
		do
		{

			$q = db_query(
				"SELECT categoryID, slug, parent, ".LanguagesManager::sql_prepareField('name')." AS name FROM ".CATEGORIES_TABLE." 
				WHERE categoryID={$curr}");
			$row = db_fetch_row($q);
			$path[] = $row;

			if ( $curr <= 1 )
			break;

			$curr = intval($row["parent"]);
		}
		while ( 1 );
		//now reverse $path
		$path = array_reverse($path);
		$cached_path[$categoryID] = $path;
	}
	return $path;
}

/**
 * Delete category (delete also all subcategories, all prodoctes remove into root)
 *
 * @param int $categoryID - ID of category to be deleted
 * @return PEAR_Error | null
 */
function catDeleteCategory( $categoryID )
{
	$error = '';
	$categoryID = intval($categoryID);
	$categories = catGetSubCategories($categoryID);
	$categories[] = $categoryID;

	$sql = 'SELECT `picture` FROM `?#CATEGORIES_TABLE` WHERE `categoryID` IN (?@) AND `categoryID`>1';
	$q = db_phquery($sql,$categories);
	while($r = db_fetch_row($q)){
		if ($r["picture"] && file_exists(DIR_PRODUCTS_PICTURES."/".$r["picture"])){
			$res = Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$r["picture"]));
			if(PEAR::isError($res)){
				$error .= $res->getMessage();
			}
		}
	}

	$sqls = array();
	$sqls[] = 'UPDATE `?#PRODUCTS_TABLE` SET `categoryID`=1 WHERE `categoryID` IN (?@) AND `categoryID`>1';
	$sqls[] = 'DELETE FROM `?#CATEGORIY_PRODUCT_TABLE` WHERE `categoryID` IN (?@) AND `categoryID`>1';
	$sqls[] = 'DELETE FROM `?#CATEGORIES_TABLE` WHERE `categoryID` IN (?@) AND `categoryID`>1';
	foreach($sqls as $sql){
		db_phquery($sql,$categories);
	}

	if($error){
		return PEAR::raiseError($error);
	}
}


function catMoveBranchCategoriesTo($cid, $new_parent)
{
	$a = false;
	$sql = 'SELECT `categoryID` FROM `?#CATEGORIES_TABLE` WHERE `parent`=? and `categoryID`>1';
	$q = db_phquery($sql, $cid);
	while ($row = db_fetch_row($q)){
		if (!$a){
			if ($row[0] == $new_parent){
				return true;
			}else{
				$a = catMoveBranchCategoriesTo($row[0],$new_parent);
			}
		}
	}
	return $a;
}
?>