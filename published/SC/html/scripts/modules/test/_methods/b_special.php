<?php
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();
//catalog: products extra parameters list

if (isset($_GET["save_successful"])) //update was successful
{
	$smarty->assign( "save_successful", translate("msg_update_successful") );
}

set_query('&save_successful=&safemode=', '', true);

if (isset($_POST["save_offers"])) //save extra product options
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		Redirect(set_query('&safemode=yes'));
	}

	//save existing
	db_query("delete from ".SPECIAL_OFFERS_TABLE) or die (db_error());

	$offers = array();
	foreach ($_POST as $key => $val)
	{
	  if(strstr($key, "offer_productID_") != false)
	  {
		$a = str_replace("offer_productID_","",$key);
		$offers[$a]["productID"] = $val;
	  }
	  if(strstr($key, "offer_sort_") != false)
	  {
		$a = str_replace("offer_sort_","",$key);
		$offers[$a]["sort"] = $val;
	  }
	}
	foreach ($offers as $key => $value)
	{
		$value["sort"] = (int)$value["sort"];
		db_query("insert into ".SPECIAL_OFFERS_TABLE." (offerID, productID, sort_order) ".
			" values ('$key', '".$value["productID"]."', '".$value["sort"]."')") or die (db_error());
	}
	Redirect(set_query('&save_successful=yes'));
}

if (isset($_GET["new_offer"])) //add new special offer
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		RedirectSQ("new_offer=&safemode=yes");
	}

	db_query("insert into ".SPECIAL_OFFERS_TABLE." (productID, sort_order) values ('".$_GET["new_offer"]."',0)") or die (db_error());
	Redirect("new_offer=");
}

if (isset($_GET["delete"])) //delete special offer
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{	Redirect(set_query('&safemode=yes&delete='));  }

	db_query("delete from ".SPECIAL_OFFERS_TABLE." where offerID='".$_GET["delete"]."'") or die (db_error());
	Redirect(set_query('delete='));
}

//now select all available product options
$q = db_query("select offerID, productID, sort_order from ".SPECIAL_OFFERS_TABLE." order by sort_order") or die (db_error());
$result = array();
while ($row = db_fetch_row($q)){
	
	//get product name
	$q1 = db_query("select categoryID, ".LanguagesManager::sql_prepareField('name')." name from ".PRODUCTS_TABLE." where productID={$row[1]}") or die (db_error());
	if ($row1 = db_fetch_row($q1))
	{
		$row[3] = $row1[0];
		$row[4] = $row1[1];
		$result[] = $row;
	}
}
$smarty->assign("offers", $result);

//set sub-department template
$smarty->assign("admin_sub_dpt", "catalog_special.tpl.html");
?>