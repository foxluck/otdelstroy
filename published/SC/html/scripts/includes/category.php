<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	
	if(isset($_GET['show_all'])){
		
		set_query('show_all=','',true);
		$show_all = true;
		$Register->set('show_all', $show_all);
	}
	
	$categoryID = isset($_GET['categoryID'])?$_GET['categoryID']:0;
	
	if(!function_exists('_sortSetting')){function _sortSetting( &$smarty ){
		
		$sort_fields = array(
			array('name', 'NAME'),
			array('Price', 'PRICE'),
			array('customers_rating', 'RATING')
		);
		$sort_string = translate("prd_sort_main_control_string");
		$current_sort_field = isset($_GET['sort'])?$_GET['sort']:'';
		$current_sort_direction = isset($_GET['direction'])?$_GET['direction']:'';
		
		foreach ($sort_fields as $field){
			
			$sort_string = str_replace( "{ASC_".$field[1]."}", $field[0] == $current_sort_field && $current_sort_direction == 'ASC'?translate("str_ascending"):"<a rel='nofollow' href='".xHtmlSetQuery("&sort={$field[0]}&direction=ASC")."'>".translate("str_ascending")."</a>",	$sort_string );
			$sort_string = str_replace( "{DESC_".$field[1]."}", $field[0] == $current_sort_field && $current_sort_direction == 'DESC'?translate("str_descending"):"<a rel='nofollow' href='".xHtmlSetQuery("&sort={$field[0]}&direction=DESC")."'>".translate("str_descending")."</a>",	$sort_string );
		}
		$smarty->assign( "string_product_sort", $sort_string );
	}}
	
	//get selected category info
	$category = catGetCategoryById( $categoryID );
	if ( $categoryID == 1 || (!isset($category['categoryID'])))return;
		
	IncrementCategoryViewedTimes($categoryID);
	
	//category thumbnail
	if (!file_exists(DIR_PRODUCTS_PICTURES.'/'.$category['picture']))$category['picture'] = '';
	$smarty->assign('selected_category', $category );
	
	if ( $category['show_subcategories_products'] == 1 )
		$smarty->assign( 'show_subcategories_products', 1 );
	
	$callBackParam = array();
	$products	= array();
	$callBackParam['categoryID']	= $categoryID;
	$callBackParam['enabled']		= 1;
	
	if (  isset($_GET['search_in_subcategory'])&&$_GET['search_in_subcategory'] == 1 ){
		$callBackParam['searchInSubcategories'] = true;
		$callBackParam['searchInEnabledSubcategories'] = true;
	}				
	
	if ( isset($_GET['sort']) )$callBackParam['sort'] = $_GET['sort'];
	if ( isset($_GET['direction']) )$callBackParam['direction'] = $_GET['direction'];
	
	
	// search parametrs to advanced search
	if ( $extraParametrsTemplate != null )$callBackParam['extraParametrsTemplate'] = $extraParametrsTemplate;
	if ( $searchParamName != null )$callBackParam['name'] = $searchParamName;
	if ( $rangePrice != null )$callBackParam['price'] = $rangePrice;
	
	if ( $category['show_subcategories_products'] )$callBackParam['searchInSubcategories'] = true;
	
	$count = 0;
	$navigatorHtml = GetNavigatorHtml( 'categoryID='.$categoryID, CONF_PRODUCTS_PER_PAGE, 'prdSearchProductByTemplate', $callBackParam, $products, $offset, $count );
	
	$show_comparison = $category['allow_products_comparison'];
	for($i=0; $i<count($products); $i++)$products[$i]['allow_products_comparison'] = $show_comparison;
	if ( CONF_PRODUCT_SORT == '1' )_sortSetting( $smarty );
	
	$smarty->assign( 'subcategories_to_be_shown', catGetSubCategoriesSingleLayer($categoryID));
	//calculate a path to the category
	$smarty->assign( 'product_category_path',catCalculatePathToCategory($categoryID) );
	$smarty->assign( 'show_comparison', $show_comparison && count($products)>1 );
	if ( $category['allow_products_search']&&count($products)>1)$smarty->assign( 'allow_products_search', 1 );
	$smarty->assign( 'catalog_navigator', $navigatorHtml );
	$smarty->assign( 'products_to_show', $products);
	$smarty->assign( 'categoryID', $categoryID);
	$smarty->assign( 'main_content_template', 'category.tpl.html');
?>