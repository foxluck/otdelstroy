<?php
/**
 * add new link category and return new category id 
 *
 * @param array $_category
 * @return integer
 */
function le_addCategory($_category){
	
	if(empty($_category['le_cName']))return false;
	$sql = '
		SELECT `le_cID` FROM ?#LINK_EXCHANGE_CATEGORIES_TABLE WHERE `le_cName`=?
	';
	list($_le_cID) = db_fetch_row(db_phquery($sql,$_category['le_cName']));
	if(!empty($_le_cID)) return false;
	
	$sql = '
		INSERT '.LINK_EXCHANGE_CATEGORIES_TABLE.' (`'.implode('`, `', xEscapeSQLstring(array_keys($_category))).'`)
		VALUES("'.implode('", "', xEscapeSQLstring($_category)).'")
	';
	db_query($sql);
	return db_insert_id();
}

/**
 * save links category
 *
 * @param array $_category
 * @return bool
 */
function le_saveCategory($_category){
	
	if(empty($_category['le_cName']))return false;
	$sql = '
		SELECT `le_cID` FROM ?#LINK_EXCHANGE_CATEGORIES_TABLE WHERE `le_cName`=?
	';
	list($_le_cID) = db_fetch_row(db_phquery($sql,$_category['le_cName']));
	if(!empty($_le_cID)) return false;
	
	$sql = '
		UPDATE ?#LINK_EXCHANGE_CATEGORIES_TABLE SET le_cName=? WHERE le_cID=?
	';
	db_phquery($sql,$_category['le_cName'],$_category['le_cID']);
	return true;
}

/**
 * delete links category
 *
 * @param integer links category id
 * @return bool
 */
function le_deleteCategory($_le_cID){
	
	$sql = "
		DELETE FROM ".LINK_EXCHANGE_CATEGORIES_TABLE." WHERE `le_cID`='{$_le_cID}'
	";
	db_query($sql);
	return true;
}

/**
 * return array of categories by requested params 
 *
 * @return array 
 */
function le_getCategories($_where = '1', $_what = 'le_cID, le_cName, le_cSortOrder', $_order = "le_cSortOrder ASC, le_cName ASC"){
	
	$categories = array();
	if(is_array($_where)){
		
		foreach ($_where as $_col=>$_val)
			$_where[$_col] = "`".xEscapeSQLstring($_col)."` = '".xEscapeSQLstring($_val)."'";
		$_where = implode(" AND ", $_where);
	}
	if(is_array($_what))
		$_what = "`".implode("`, `", xEscapeSQLstring($_what))."`";

	$sql = "
		SELECT {$_what} FROM ".LINK_EXCHANGE_CATEGORIES_TABLE."
		WHERE {$_where}
		ORDER BY {$_order}
	";
	$result = db_query($sql);
	while ($_row = db_fetch_row($result)) 
		$categories[] = $_row;
	return $categories;
}

/**
 * return array of links by requested params 
 *
 * @return array 
 */
function le_getLinks($_offset = 0, $_lpp = '20', $_where = '1', $_what = 'le_lID, le_lText, le_lURL, le_lCategoryID, le_lVerified', $_order = '`le_lURL` ASC'){
	
	$_offset = ($_offset-1)*$_lpp;
	$links = array();
	if(is_array($_where)){
		
		foreach ($_where as $_col=>$_val)
			$_where[$_col] = "`".xEscapeSQLstring($_col)."` = '".xEscapeSQLstring($_val)."'";
		$_where = implode(" AND ", $_where);
	}
	if(is_array($_what))
		$_what = "`".implode("`, `", xEscapeSQLstring($_what))."`";
	$sql = "
		SELECT {$_what} FROM ".LINK_EXCHANGE_LINKS_TABLE."
		WHERE {$_where}
		ORDER BY {$_order}
	";
	$result = db_query($sql);
	$i = 0;
	while($_row = db_fetch_row($result))
		if(($_offset+$_lpp)>$i&&$_offset<=$i++){
			
			if(isset($_row['le_lVerified'])){
				
				$_row['le_lVerified'] = Time::standartTime($_row['le_lVerified']);
			}
			$links[] = $_row;
		}
	return $links;
}

/**
 * return number of links by requested params 
 *
 * @return integer 
 */
function le_getLinksNumber($_where = '1'){
	
	if(is_array($_where)){
		
		foreach ($_where as $_col=>$_val)
			$_where[$_col] = "`".xEscapeSQLstring($_col)."` = '".xEscapeSQLstring($_val)."'";
		$_where = implode(" AND ", $_where);
	}
	$sql = "
		SELECT COUNT(*) FROM ".LINK_EXCHANGE_LINKS_TABLE."
		WHERE {$_where}
	";
	$result = db_query($sql);
	list($links_number) = db_fetch_row($result);
	return $links_number;
}

/**
 * add new link to category and return new link id
 *
 * @return integer
 */
function le_addLink($_link){
	
	$sql = '
		SELECT le_lID FROM ?#LINK_EXCHANGE_LINKS_TABLE WHERE le_lURL=?
	';
	list($_le_lID) = db_fetch_row(db_phquery($sql,$_link['le_lURL']));
	if(!empty($_le_lID))return false;
	
	$sql = '
		INSERT '.LINK_EXCHANGE_LINKS_TABLE.' (`'.implode('`, `', xEscapeSQLstring(array_keys($_link))).'`)
		VALUES("'.implode('", "', xEscapeSQLstring($_link)).'")
	';
	db_query($sql);
	return db_insert_id();
}

/**
 * update link
 *
 * @param array of new values
 * @return bool
 */
function le_SaveLink($_link){
	
	if(key_exists('le_lURL', $_link)){
		$sql = '
			SELECT le_lID FROM ?#LINK_EXCHANGE_LINKS_TABLE WHERE le_lURL=? AND le_lID<>?
		';
		list($_le_lID) = db_fetch_row(db_phquery($sql,$_link['le_lURL'],$_link['le_lID']));
		if($_le_lID) return false;
		$_le_lID = $_link['le_lID'];
	}
	else $_le_lID = $_link['le_lID'];
	
	foreach($_link as $_col => $_val){
		
		if($_val == 'NULL' && $_col=='le_lVerified'){
			
			$_link[$_col] = '`'.xEscapeSQLstring($_col).'` = NULL';
		}else{
			
			$_link[$_col] = '`'.xEscapeSQLstring($_col).'` = "'.xEscapeSQLstring($_val).'"';
		}
	}
	
	$sql = '
		UPDATE ?#LINK_EXCHANGE_LINKS_TABLE SET '.implode(', ', $_link).'
		WHERE le_lID=?
	';
	db_phquery($sql,$_le_lID);
	return true;
}

function le_DeleteLink($_le_lID){
	
	db_phquery('DELETE FROM ?#LINK_EXCHANGE_LINKS_TABLE WHERE le_lID=?',$_le_lID);
}
?>