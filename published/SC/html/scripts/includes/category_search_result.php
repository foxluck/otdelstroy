<?php
	if ( isset($_GET["search_with_change_category_ability"]) )
	{
		$smarty->assign( "allow_products_search", 1 );
		$smarty->assign( "main_content_template", "category_search_result.tpl.html" );
	}


	if ( isset($categoryID) && isset($_GET["search_with_change_category_ability"]) &&  
			isset($_GET["advanced_search_in_category"]) )
	{


		function _getUrlToNavigate( $categoryID )
		{
			$url = "index.php?categoryID=$categoryID";
			if ( isset($_GET["search_name"]) )
				$url .= "&search_name=".$_GET["search_name"];
			if ( isset($_GET["search_price_from"]) )
				$url .= "&search_price_from=".$_GET["search_price_from"];
			if ( isset($_GET["search_price_to"]) )
				$url .= "&search_price_to=".$_GET["search_price_to"];
			$data = scanArrayKeysForID($_GET, array("param") );
			foreach( $data as $key => $val )
			{
				$url .= "&param_".$key;
				$url .= "=".$val["param"];
			}
			if ( isset($_GET["sort"]) )
				$url .= "&sort=".$_GET["sort"];
			if ( isset($_GET["direction"]) )
				$url .= "&direction=".$_GET["direction"];
			if ( isset($_GET["search_in_subcategory"]) )
				$url .= "&search_in_subcategory=1";
			if ( isset($_GET["search_with_change_category_ability"]) ){
				$_GET["search_with_change_category_ability"] = $_GET["search_with_change_category_ability"]?1:'0';
				$url .= "&search_with_change_category_ability=".$_GET["search_with_change_category_ability"];
			}
			if ( isset($_GET["advanced_search_in_category"]) ){
				$_GET["advanced_search_in_category"] = $_GET["advanced_search_in_category"]?1:'0';
				$url .= "&advanced_search_in_category=".$_GET["advanced_search_in_category"];
			}
			if ( isset($_GET["categorySelect"]) ){
				$_GET["categorySelect"] = $_GET["categorySelect"]?1:'0';
				$url .= "&categorySelect=".$_GET["categorySelect"];
			}
			return $url;
		}

		function _getUrlToSort( $categoryID )
		{
			$Register = &Register::getInstance();
			$url = "index.php?categoryID=$categoryID";
			if ( isset($_GET["search_name"]) )
				$url .= "&search_name=".$_GET["search_name"];
			if ( isset($_GET["search_price_from"]) )
				$url .= "&search_price_from=".$_GET["search_price_from"];
			if ( isset($_GET["search_price_to"]) )
				$url .= "&search_price_to=".$_GET["search_price_to"];
			$data = scanArrayKeysForID($_GET, array("param") );
			foreach( $data as $key => $val )
			{
				$url .= "&param_".$key;
				$url .= "=".$val["param"];
			}
			if ( isset($_GET["offset"]) )
				$url .= "&offset=".$_GET["offset"];
			if ( $Register->is_set("show_all") && $Register->get("show_all") )
				$url .= "&show_all=1";
			if ( isset($_GET["search_in_subcategory"]) )
				$url .= "&search_in_subcategory=1";
			if ( isset($_GET["advanced_search_in_category"]) ){
				$_GET["advanced_search_in_category"] = $_GET["advanced_search_in_category"]?1:'0';
				$url .= "&advanced_search_in_category=".$_GET["advanced_search_in_category"];
			}
			if ( isset($_GET["search_with_change_category_ability"]) ){
				$_GET["search_with_change_category_ability"] = $_GET["search_with_change_category_ability"]?1:'0';
				$url .= "&search_with_change_category_ability=".$_GET["search_with_change_category_ability"];
			}
			if ( isset($_GET["categorySelect"]) ){
				$_GET["categorySelect"] = $_GET["categorySelect"]?1:'0';
				$url .= "&categorySelect=".$_GET["categorySelect"];
			}
			return $url;
		}

		function _sortSetting( &$smarty, $urlToSort )
		{
			$sort_string = translate("prd_sort_main_control_string");
			$sort_string = str_replace( "{ASC_NAME}",   "<a rel=\"nofollow\" href='".$urlToSort."&amp;sort=name&amp;direction=ASC'>".translate("str_ascending")."</a>",	$sort_string );
			$sort_string = str_replace( "{DESC_NAME}",  "<a rel=\"nofollow\" href='".$urlToSort."&amp;sort=name&amp;direction=DESC'>".translate("str_descending")."</a>",	$sort_string );
			$sort_string = str_replace( "{ASC_PRICE}",   "<a rel=\"nofollow\" href='".$urlToSort."&amp;sort=Price&amp;direction=ASC'>".translate("str_ascending")."</a>",	$sort_string );
			$sort_string = str_replace( "{DESC_PRICE}",  "<a rel=\"nofollow\" href='".$urlToSort."&amp;sort=Price&amp;direction=DESC'>".translate("str_descending")."</a>",	$sort_string );
			$sort_string = str_replace( "{ASC_RATING}",   "<a rel=\"nofollow\" href='".$urlToSort."&amp;sort=customers_rating&amp;direction=ASC'>".translate("str_ascending")."</a>",	$sort_string );
			$sort_string = str_replace( "{DESC_RATING}",  "<a rel=\"nofollow\" href='".$urlToSort."&amp;sort=customers_rating&damp;irection=DESC'>".translate("str_descending")."</a>",	$sort_string );
			$smarty->assign( "string_product_sort", $sort_string );
		}


		//get selected category info
		$category = catGetCategoryById( $categoryID );

		if ( !$category || $categoryID == 1) //do not show root category
		{
			header("Location: index.php");
		}
		else //show category
		{
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

			if (!file_exists(DIR_PRODUCTS_PICTURES."/".$category["picture"])) 
					$category["picture"] = "";
			$smarty->assign("selected_category", $category );

			if ( $category["allow_products_search"] )
				$smarty->assign( "allow_products_search", 1 );

			$callBackParam = array();
			$products = array();
			$callBackParam["categoryID"]	= $categoryID;
			$callBackParam["enabled"]		= 1;

			if (  isset($_GET["search_in_subcategory"])   )
				if ( $_GET["search_in_subcategory"] == 1 )
				{
					$callBackParam["searchInSubcategories"] = true;
					$callBackParam["searchInEnabledSubcategories"] = true;
				}				

			if ( isset($_GET["sort"]) )
				$callBackParam["sort"] = $_GET["sort"];
			if ( isset($_GET["direction"]) )
				$callBackParam["direction"] = $_GET["direction"];

			// search parametrs to advanced search
			if ( $extraParametrsTemplate != null )
					$callBackParam["extraParametrsTemplate"] = $extraParametrsTemplate;
			if ( $searchParamName != null )
					$callBackParam["name"] = $searchParamName;
			if ( $rangePrice != null )
					$callBackParam["price"] = $rangePrice;

			$count = 0;
			$navigatorHtml = GetNavigatorHtml( 
						_getUrlToNavigate( $categoryID ), CONF_PRODUCTS_PER_PAGE, 
						'prdSearchProductByTemplate', $callBackParam, 
						$products, $offset, $count );
			$cat = catGetCategoryById( $categoryID );
			if($cat["allow_products_comparison"]){
				
				$show_comparison = 0;
				for($i=0; $i<count($products); $i++)
				{
					$products[$i]["allow_products_comparison"] = 1;
					$show_comparison++;
				}
				$smarty->assign('show_comparison', $show_comparison);
			}
			
			if ( CONF_PRODUCT_SORT == '1' )
				_sortSetting( $smarty, _getUrlToSort($categoryID) );

			//calculate a path to the category
			$smarty->assign( "product_category_path", 
								catCalculatePathToCategory($categoryID) );
			$smarty->assign( "search_with_change_category_ability", 1 );
			$smarty->assign( "catalog_navigator", $navigatorHtml );
			$smarty->assign( "products_to_show", $products);
			$smarty->assign( "main_content_template", "category_search_result.tpl.html");



		}
	}
?>