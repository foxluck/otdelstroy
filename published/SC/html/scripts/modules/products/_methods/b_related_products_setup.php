<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

if (!isset($_GET["owner"])) //'owner product' not set
{
	echo "<center><font color=red>".translate("err_cant_find_required_page")."</font>\n<br><br>\n";
	echo "<a href=\"javascript:window.close();\">".translate("btn_close")."</a></center></body>\n</html>";
	exit(1);
}

$owner = $_GET['owner'];
$categoryID = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0;

if (isset($_GET['select_product'])){ //add 2 wish-list (related items list)
	if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
		RedirectSQ('select_product=&safemode=yes');
	}

	$q = db_phquery('SELECT COUNT(*) FROM ?#RELATED_PRODUCTS_TABLE WHERE productID=? AND Owner=?',$_GET['select_product'],$owner);
	$cnt = db_fetch_row($q);
	if ($cnt[0] == 0)
		db_phquery('INSERT INTO ?#RELATED_PRODUCTS_TABLE (productID, Owner) VALUES (?,?)',$_GET['select_product'], $owner);
	RedirectSQ('select_product=');
}

if (isset($_GET['delete'])){ //remove from wish-list
	if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
		RedirectSQ('safemode=yes&delete=');
	}
	db_phquery('DELETE FROM ?#RELATED_PRODUCTS_TABLE WHERE productID=? AND Owner=?',$_GET['delete'],$owner);
	RedirectSQ('delete=');
}

$RelatedProducts = array();
$q = db_phquery('SELECT prd_tbl.productID,'.LanguagesManager::sql_prepareField('prd_tbl.name').' AS name FROM ?#PRODUCTS_TABLE as prd_tbl LEFT JOIN ?#RELATED_PRODUCTS_TABLE relprd_tbl ON prd_tbl.productID=relprd_tbl.productID WHERE relprd_tbl.Owner=?',$owner);
while ($_Row = db_fetch_assoc($q)){

	$RelatedProducts[] = $_Row;
}

$smarty->assign('RelatedProducts', $RelatedProducts);
$smarty->assign('Categories', catGetCategoryCompactCList( $categoryID ));
$smarty->assign('CategoryProducts', prdGetProductByCategory(array('categoryID'=>$categoryID),$count_row));
$smarty->assign('categoryID', $categoryID);
?>