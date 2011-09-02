<?php
	// advanced search

	if (isset($_GET["adv_search"])) //advanced search form
	{

		$k = 0;
		if (!isset($_GET["cat"])) $_GET["cat"] = 0;

		if (isset($_GET["name"])){
			
			$s = trim($_GET["name"]);
			$s = str_replace("%","",$s);
			$s = str_replace("_","",$s);
			//",',\
			$s = stripslashes($s);
			$s = str_replace("'","\'",$s);
			$_GET["name"] = $s;
		}

		if (isset($_GET["done"]) && ($_GET["name"]!="" || $_GET["cat"]!=0 || $_GET["price1"]!=0 || $_GET["price2"]!=0)) //get search results
		{
			$url = "index.php?name=".$_GET["name"]."&cat=".$_GET["cat"].
				"&price1=".$_GET["price1"]."&price2=".$_GET["price2"].
				"&done=".$_GET["done"]."&adv_search=1";

			$products = array();
			$callBackParam = array();
			$offset = 0;
			if ( $_GET["cat"] != 0 )
				$callBackParam["categoryID"] = $_GET["cat"];
			$callBackParam["name"]	= array();
			$callBackParam["price"] = array();
			if ( trim($_GET["name"]) != "" )
				$callBackParam["name"][]	= $_GET["name"];
			$callBackParam["price"][]	= array( "from" => $_GET["price1"], 
												"to" => $_GET["price2"] );
			$count = 0;
			$navigatorHtml = GetNavigatorHtml( $url, CONF_PRODUCTS_PER_PAGE, 
				'prdSearchProductByTemplate', $callBackParam, $products, $offset, $count );

			$smarty->assign("products_to_show", $products);
			$smarty->assign("search_navigator", $navigatorHtml );
			$smarty->assign("search_completed", 1);
		}

		$a = fillTheCList(0,0);
		$smarty->assign("adv_search_categories",$a);

		if (isset($_GET["name"])) $smarty->assign("adv_search_name",$_GET["name"]);
		if ($_GET["cat"]) $smarty->assign("adv_search_selected_category",$_GET["cat"]);
		if (isset($_GET["price1"])) $smarty->assign("adv_search_price1",$_GET["price1"]);
		if (isset($_GET["price2"])) $smarty->assign("adv_search_price2",$_GET["price2"]);

		$smarty->assign("main_content_template", "search_adv.tpl.html");

	}

?>