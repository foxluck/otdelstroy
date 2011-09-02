<?php
//loks as unused

	$Register = &Register::getInstance();
	/*@var $Register Register*/
	$smarty = &$Register->get(VAR_SMARTY);
	/*@var $smarty Smarty*/
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);

	// simple search
	$in_results = false;
	if(isset($GetVars["in_results"]))$in_results = true;
	if(isset($PostVars["in_results"]))$in_results = true;
	
	$smarty->assign("search_in_results", $in_results);
	$searchstring = '';
	if(isset($GetVars['searchstring']))$searchstring = $GetVars['searchstring'];
	if(isset($PostVars['searchstring']))$searchstring = $PostVars['searchstring'];
	
	$searchstrings = array();
	$tmp = explode(" ", $searchstring);
	foreach( $tmp as $key=> $val ){
		if ( strlen( trim($val) ) > 0 )$searchstrings[] = $val;
	}

	if($in_results){
		
		$data = ScanPostVariableWithId(array("search_string"));
		foreach( $data as $key => $value )$searchstrings[] = $value["search_string"];
	}
	
	$smarty->hassign( "searchstrings", $searchstrings );

	$callBackParam	= array();
	$products		= array();
	$callBackParam["search_simple"] = $searchstrings;

	if ( isset($_GET["sort"]) )$callBackParam["sort"] = $_GET["sort"];
	if ( isset($_GET["direction"]) )$callBackParam["direction"] = $_GET["direction"];

	$countTotal = 0;
	$navigatorHtml = GetNavigatorHtml('', CONF_PRODUCTS_PER_PAGE, 'prdSearchProductByTemplate', $callBackParam, $products, $offset, $countTotal );

	if(CONF_ALLOW_COMPARISON_FOR_SIMPLE_SEARCH){
		
		$show_comparison = 0;
		foreach ($products as $_Key=>$_Product){
			
			$products[$_Key]['allow_products_comparison'] = 1;
		}
		$smarty->assign("show_comparison", count($products)>1);
	}
//	if ( CONF_PRODUCT_SORT == '1' )_sortSetting( $smarty, _getUrlToSort() );

	$smarty->assign( "products_to_show",  $products );
	$smarty->assign( "products_found", $countTotal );
	$smarty->assign( "products_to_show_count", $countTotal );
	$smarty->assign( "search_navigator", $navigatorHtml );
	$smarty->assign( "main_content_template", "search_simple.html" );
?>