<?php
	$extraParametrsTemplate = null;
	$searchParamName = null;
	$rangePrice = null;
	if ( !isset($_GET["categoryID"]) && isset($_GET["search_with_change_category_ability"]) ){
		
		//$categories = catGetCategoryCList();
		$categories = catGetCategoryCompactCList( 1 );
		array_shift($categories);
		$smarty->assign( "categories_to_select", $categories );
	}

	if ( isset($_GET['categoryID']) ){
		
		$_GET['categoryID'] = (int)$_GET['categoryID'];

		if  (  !catGetCategoryById($_GET['categoryID'])  ){
			//RedirectSQ('?ukey=page_not_found');
			error404page();
		}else{
			
			if ( isset($_GET["search_with_change_category_ability"]) )
			{
				//$categories = catGetCategoryCList();
				$categories = catGetCategoryCompactCList( (int)$_GET["categoryID"] );
				array_shift($categories);
				$smarty->assign( "categoryID1", (int)$_GET["categoryID"] );
				$smarty->assign( "categories_to_select", $categories );
			}

			$getData = null;
			if ( isset($_GET['advanced_search_in_category']) ){
				
				$extraParametrsTemplate = array();
				$extraParametrsTemplate['categoryID'] = $_GET['categoryID'];

				if ( isset($_GET['search_name']) )if ( trim($_GET['search_name']) != '' )$searchParamName = array( $_GET['search_name'] );

				$rangePrice = array();
				if($_GET['search_price_from']!='')$rangePrice['from'] = $_GET['search_price_from'];
				if($_GET['search_price_to']!='')$rangePrice['to'] = $_GET['search_price_to'];
				if(!count($rangePrice))$rangePrice = null;
				$getData = scanArrayKeysForID($_GET, array('param') );
				foreach( $getData as $optionID => $value ){
					
					$res = schOptionIsSetToSearch( $_GET['categoryID'], $optionID );
	
					if ( $res['set_arbitrarily']==0 && (int)$value['param'] == 0 )continue;

					$item = array();
					$item['optionID']	= $optionID;
					$item['value']		= xStripSlashesGPC($value['param']);
					$extraParametrsTemplate[] = $item;
				}
			}

			$params = array();

			$categoryID = $_GET["categoryID"];
			$options = optGetOptions();
			foreach( $options as $option ){
				
				$res = schOptionIsSetToSearch( $categoryID, $option["optionID"] );
				if ( !$res["isSet"] )continue;
				
				$item = array();
				$item["optionID"] = $option["optionID"];
				$item["value"] = $getData[ (string)$option["optionID"] ]["param"];

				$item["controlIsTextField"] = $res["set_arbitrarily"];
				$item["name"]				= $option["name"];
				if ( $res["set_arbitrarily"] == 0 ){
					
					$item["variants"] = array();
					$variants = optGetOptionValues( $option["optionID"] );
					foreach( $variants as $variant ){
						
						if ( !schVariantIsSetToSearch( $categoryID, $option["optionID"], $variant["variantID"] ))continue;

						$variantItem	= array(
							'variantID' => $variant["variantID"],
							'value' => $variant["option_value"],
						);
						$item["variants"][] = $variantItem;
					}
				}
				$params[] = $item;
			}

			if ( isset($_GET["search_name"]) )
				$smarty->hassign( "search_name", $_GET["search_name"]);
			if ( isset($_GET["search_price_from"]) )
				$smarty->hassign( "search_price_from", $_GET["search_price_from"]);
			if ( isset($_GET["search_price_to"]) )
				$smarty->hassign( "search_price_to", $_GET["search_price_to"] );

			$smarty->assign( "categoryID", $categoryID );
			if ( isset($_GET["advanced_search_in_category"]) )
				$smarty->assign( "search_in_subcategory", isset($_GET["search_in_subcategory"]) );
			else
				$smarty->assign( "search_in_subcategory", true );
			$smarty->assign( "show_subcategory_checkbox", 1 );
			$smarty->assign( "params", $params );
		}
	}
?>