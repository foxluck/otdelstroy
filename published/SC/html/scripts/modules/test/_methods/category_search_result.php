<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();
include(DIR_ROOT.'/includes/advanced_search_in_category.php');
/*
@features "Search products by params"
*/
	if ( isset($_GET['search_with_change_category_ability']) ){
		$smarty->assign( 'allow_products_search', 1 );
	}
/*
@features
*/
$categoryID = isset($_GET['categoryID'])?$_GET['categoryID']:0;

if ( isset($categoryID) /*&& isset($_GET["search_with_change_category_ability"])*/ &&  isset($_GET["advanced_search_in_category"]) ){

	function _sortSetting( &$smarty )
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
			
			$sort_string = str_replace( "{ASC_".$field[1]."}", $field[0] == $current_sort_field && $current_sort_direction == 'ASC'?translate("str_ascending"):"<a rel='nofollow' href='".set_query("&sort={$field[0]}&direction=ASC")."'>".translate("str_ascending")."</a>",	$sort_string );
			$sort_string = str_replace( "{DESC_".$field[1]."}", $field[0] == $current_sort_field && $current_sort_direction == 'DESC'?translate("str_descending"):"<a rel='nofollow' href='".set_query("&sort={$field[0]}&direction=DESC")."'>".translate("str_descending")."</a>",	$sort_string );
		}
		$smarty->assign( "string_product_sort", $sort_string );
	}


	//get selected category info
	$category = catGetCategoryById( $categoryID );

	if ( !$category || $categoryID == 1)RedirectSQ('');
	
		IncrementCategoryViewedTimes($categoryID);
		if ( isset($_GET["prdID"]) )
		{
			if (  isset($_POST["cart_".$_GET["prdID"]."_x"])  )
			{
				$variants=array();

				foreach( $_POST as $key => $val )
				{
					if ( strstr($key, "option_select_hidden") )
					{
						$arr=explode( "_", str_replace("option_select_hidden_","",$key) );
						if ( (string)$arr[1] == (string)$_GET["prdID"] )
							$variants[]=$val;
					}
				}
				unset($_SESSION["variants"]);
				$_SESSION["variants"]=$variants;
				Redirect( "index.php?shopping_cart=yes&add2cart=".$_GET["prdID"] );
			}
		}

		if (!file_exists(DIR_PRODUCTS_PICTURES.'/'.$category['picture'])) $category['picture'] = '';
		$smarty->assign('selected_category', $category );
/**
@features "Search products by params"
*/
		if ( $category['allow_products_search'] )$smarty->assign( 'allow_products_search', 1 );
/**
@features
*/
		$callBackParam = array();
		$products = array();
		$callBackParam['categoryID']	= $categoryID;
		$callBackParam['enabled']		= 1;

		if(isset($_GET['search_in_subcategory'])&&$_GET['search_in_subcategory'] == 1){
			$callBackParam['searchInSubcategories'] = true;
			$callBackParam['searchInEnabledSubcategories'] = true;
		}				

		if ( isset($_GET['sort']) )$callBackParam['sort'] = $_GET['sort'];
		if ( isset($_GET['direction']) )$callBackParam['direction'] = $_GET['direction'];

		// search parametrs to advanced search
		if ( $extraParametrsTemplate != null )$callBackParam['extraParametrsTemplate'] = $extraParametrsTemplate;
		if ( $searchParamName != null )$callBackParam['name'] = $searchParamName;
		if ( $rangePrice != null )$callBackParam['price'] = $rangePrice;
		
		$count = 0;
		if(isset($_GET['show_all'])){
			$Register->assign('show_all',1);
			renderURL('show_all=','',true);
		}
		$navigatorHtml = GetNavigatorHtml( '', CONF_PRODUCTS_PER_PAGE, 'prdSearchProductByTemplate', $callBackParam, $products, $offset, $count );
/**
* @features "Products comparison"
*/
		$cat = catGetCategoryById( $categoryID );
		if($cat['allow_products_comparison']){
			
			$show_comparison = 0;
			for($i=0; $i<count($products); $i++){
				$products[$i]['allow_products_comparison'] = 1;
				$show_comparison++;
			}
			$smarty->assign('show_comparison', $show_comparison);
		}
/**
* @features
*/
		if ( CONF_PRODUCT_SORT == '1' )_sortSetting( $smarty, set_query() );
		//calculate a path to the category
		$smarty->assign( 'product_category_path', catCalculatePathToCategory($categoryID) );
		$smarty->assign( 'search_with_change_category_ability', 1 );
		$smarty->assign( 'catalog_navigator', $navigatorHtml );
		$smarty->assign( 'products_to_show', $products);
}
$smarty->assign( 'main_content_template', 'category_search.html');
?>