<?php
	$Register = &Register::getInstance();
	/*@var $Register Register*/
	$smarty = &$Register->get(VAR_SMARTY);
	/*@var $smarty Smarty*/
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	
	$in_results = false;
//	if(isset($GetVars["in_results"]))$in_results = true;
//	if(isset($PostVars["in_results"]))$in_results = true;
	
	$smarty->assign("search_in_results", $in_results);

	$searchstring = '';
	$searchtag = '';
	if(isset($GetVars['searchstring']))$searchstring = urldecode($GetVars['searchstring']);
	if(isset($PostVars['searchstring']))$searchstring = $PostVars['searchstring'];
	
	if(isset($GetVars['tag']))$searchtag = urldecode($GetVars['tag']);
	if(isset($PostVars['tag']))$searchtag = $PostVars['tag'];
	
	function _sortSetting( &$smarty, $urlToSort, $searchstring )
	{
		$sort_fields = array(
			array('name', 'NAME'),
			array('Price', 'PRICE'),
			array('customers_rating', 'RATING')
		);
		$sort_string = translate("prd_sort_main_control_string");
		$current_sort_field = isset($_GET['sort'])?$_GET['sort']:'';
		$current_sort_direction = isset($_GET['direction'])?$_GET['direction']:'';
		
		foreach ($sort_fields as $field){
			
			$sort_string = str_replace( "{ASC_".$field[1]."}", $field[0] == $current_sort_field && $current_sort_direction == 'ASC'?translate("str_ascending"):"<a rel='nofollow' href='".set_query("&sort={$field[0]}&direction=ASC&searchstring=".urlencode($searchstring))."'>".translate("str_ascending")."</a>",	$sort_string );
			$sort_string = str_replace( "{DESC_".$field[1]."}", $field[0] == $current_sort_field && $current_sort_direction == 'DESC'?translate("str_descending"):"<a rel='nofollow' href='".set_query("&sort={$field[0]}&direction=DESC&searchstring=".urlencode($searchstring))."'>".translate("str_descending")."</a>",	$sort_string );
		}
		$smarty->assign( "string_product_sort", $sort_string );
	}

	$searchstrings = array();
	$tmp = explode(' ', $searchstring);
	foreach( $tmp as $key=> $val ){
		if ( strlen( trim($val) ) > 0 )$searchstrings[] = $val;
	}

	if($in_results){
		
		$data = scanArrayKeysForID($_POST, array('search_string'));
		foreach( $data as $key => $value )$searchstrings[] = $value['search_string'];
	}
	
	$smarty->hassign( 'searchstrings', $searchstrings );
	$smarty->hassign( 'searchstring', $searchstring);
	$smarty->hassign( 'searchtag', $searchtag);
	
	$callBackParam	= array();
	$products	= array();
	
	$searchtags = explode(',',$searchtag);
	$callBackParam['search_simple'] = $searchstrings;
	//OPTIMIZE: use false to search by tags strict
	if(true){
		$callBackParam['search_tags'] = array_merge($searchtags,$searchstrings);
	}else{
		$callBackParam['search_tags'] = $searchtags;
	}

	if ( isset($_GET['sort']) )$callBackParam['sort'] = $_GET['sort'];
	if ( isset($_GET['direction']) )$callBackParam['direction'] = $_GET['direction'];

	$Register->assign("show_all", isset($_GET['show_all']));
	renderURL('show_all=', '', true);
	$countTotal = 0;
	$navigatorHtml = GetNavigatorHtml(count($searchstrings)?('&searchstring='.urlencode($searchstring)):('&searchtag='.urlencode(implode(',',$searchtags))), CONF_PRODUCTS_PER_PAGE, 'prdSearchProductByTemplate', $callBackParam, $products, $offset, $countTotal );
	/**
	* @features "Products comparison"
	*/
	if(CONF_ALLOW_COMPARISON_FOR_SIMPLE_SEARCH){
		
		$show_comparison = 0;
		foreach ($products as $_Key=>$_Product){
			
			$products[$_Key]['allow_products_comparison'] = 1;
			$show_comparison++;
		}
		$smarty->assign( 'show_comparison', $show_comparison>1 );
	}
	/**
	* @features
	*/
	if ( CONF_PRODUCT_SORT == '1' )_sortSetting( $smarty, set_query(), $searchstring );

	$smarty->assign( 'products_to_show',  $products );
	$smarty->assign( 'products_found', $countTotal );
	$smarty->assign( 'products_to_show_count', $countTotal );
	$smarty->assign( 'search_navigator', $navigatorHtml );
	$smarty->assign( 'main_content_template', 'search_simple.html' );
?>