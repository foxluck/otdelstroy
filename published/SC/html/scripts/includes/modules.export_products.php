<?php
	$ACTION = '';
	$_POST['expandID'] 				= isset($_POST['expandID'])?intval($_POST['expandID']):'';
	$_POST['unexpandID'] 			= isset($_POST['unexpandID'])?intval($_POST['unexpandID']):'';
	$_POST['updateCategory'] 			= isset($_POST['updateCategory'])?intval($_POST['updateCategory']):'';
	$_POST['CHECKED_CATEGORIES'] 	= isset($_POST['CHECKED_CATEGORIES'])?$_POST['CHECKED_CATEGORIES']:array();
	$_POST['save_products'] 			= isset($_POST['save_products'])?$_POST['save_products']:'';
	$_POST['PRODUCTS'] 			= isset($_POST['PRODUCTS'])?$_POST['PRODUCTS']:array();
	$flagUpdateSubs 				= 0;
	
	$Debug = 0;
	
	if($_POST['expandID']){
		
		$ACTION = 'EXPAND_CATEGORY';
	}elseif ($_POST['unexpandID']){
		
		$ACTION = 'UNEXPAND_CATEGORY';
	}elseif ($_POST['updateCategory']) {
		
		$ACTION = 'UPDATE_CATEGORY_STATE';
	}elseif ($_POST['save_products']){
		
		$ACTION = 'UPDATE_SELECTED_PRODUCTS';
	}
	
	/**
	 * nulling session arrayes by request
	 */
	if(isset($_POST['clear_session']))
	if($_POST['clear_session']){
		
		/**
		 * Checked categories array
		 * Key category id, value checked or not
		 * if isset() for category false category werent displayed
		 */
		session_unregister('checkedCategories');
		unset($_SESSION['checkedCategories']);
		
		/**
		 * Expanded categories array
		 */
		session_unregister('explortExpandedIDs');
		unset($_SESSION['explortExpandedIDs']);
		
		/**
		 * Number of selected  products in category
		 * Key is id of category
		 * If isset() for category false products didnt selected
		 */
		session_unregister('selectedProducts');
		unset($_SESSION['selectedProducts']);
		
		/**
		 * Number of selected products in unexpanded category
		 * Key category id, value number of products
		 */
		session_unregister('selectedProductsIncSub');
		unset($_SESSION['selectedProductsIncSub']);

		session_unregister('isExpanded');
		unset($_SESSION['isExpanded']);
		
		Redirect($_SERVER['REQUEST_URI']);
	}
	
	/**
	 * registering necessary arrayes in session
	 */
	if(!session_is_registered('explortExpandedIDs')){
		
		session_register('explortExpandedIDs');
		$_SESSION['explortExpandedIDs'] = array(1=>1);
	}
	
	if (!session_is_registered('checkedCategories')) {
		
		session_register('checkedCategories');
		$_SESSION['checkedCategories'] = array();
	}
	
	if (!session_is_registered('selectedProducts')) {
		
		session_register('selectedProducts');
		$_SESSION['selectedProducts'] = array();
	}
	
	if (!session_is_registered('selectedProductsIncSub')) {
		
		session_register('selectedProductsIncSub');
		$_SESSION['selectedProductsIncSub'] = array();
	}
	
	if (!session_is_registered('isExpanded')) {
		
		session_register('isExpanded');
		$_SESSION['isExpanded'] = array();
	}
	
	/**
	 * counting number of selected products in category and subcategories
	 * @param integer $_CategoryID - category id
	 */
	function countSelectedProductsInCat($_CategoryID, &$VisibleCategories){
		
		$_FlagChecked =  isset($_SESSION['selectedProducts'][$_CategoryID])?1:$_SESSION['checkedCategories'][$_CategoryID];
		$_FlagExpanded = isset($_SESSION['explortExpandedIDs'][$_CategoryID])?$_SESSION['explortExpandedIDs'][$_CategoryID]:0;
		
		$ProductsCounter = 0;
		$_t = 0;
		$_FlagHandler = '0.5';
		$_TestingCategory = 6;
		$Debug = 0;
		
		if ($_FlagChecked && !$_FlagExpanded){
			
			$_FlagHandler = '1.5';
			if (isset($_SESSION['selectedProducts'][$_CategoryID])){
			
				$_FlagHandler = 1;
				$ProductsCounter += $_SESSION['selectedProductsIncSub'][$_CategoryID];
				
			}else {
				
				$_FlagHandler = 2;
				$ProductsCounter += $VisibleCategories[$_CategoryID]['products_count']
					+(isset($_SESSION['selectedProducts'][$_CategoryID])?(count($_SESSION['selectedProducts'][$_CategoryID]) 
					- $VisibleCategories[$_CategoryID]['products_count']):0);
			}
		}elseif ($_FlagChecked && $_FlagExpanded){
			
			$_FlagHandler = 3;
			$ProductsCounter += (isset($_SESSION['selectedProducts'][$_CategoryID])?count($_SESSION['selectedProducts'][$_CategoryID]):$VisibleCategories[$_CategoryID]['products_count_category']);
			
			$Subs 		= catGetSubCategoriesSingleLayer($_CategoryID);
			$i 			= 0;
			$alength 	= count($Subs);
			
			for(; $i<$alength; $i++){
				
				$ProductsCounter += countSelectedProductsInCat($Subs[$i]['categoryID'], $VisibleCategories);
			}
		}elseif (!$_FlagChecked && !$_FlagExpanded){
			
				$_FlagHandler = 4;
				$ProductsCounter += (isset($_SESSION['selectedProductsIncSub'][$_CategoryID])?$_SESSION['selectedProductsIncSub'][$_CategoryID]:0);
		}elseif (!$_FlagChecked && $_FlagExpanded){
			
			$_FlagHandler = 5;
			$Subs 		= catGetSubCategoriesSingleLayer($_CategoryID);
			$i 			= 0;
			$alength 	= count($Subs);
			
			for(; $i<$alength; $i++){
				
				$ProductsCounter += countSelectedProductsInCat($Subs[$i]['categoryID'], $VisibleCategories);
			}
		}
		
		if($Debug && (1 || $_CategoryID == $_TestingCategory)){
			
			print "ID - $_CategoryID; Checked - $_FlagChecked; Expanded - $_FlagExpanded; Handler - $_FlagHandler; SelIncS - ".$_SESSION['selectedProductsIncSub'][$_CategoryID].'------'.$ProductsCounter.'<br />';
		}
		return $ProductsCounter;
	}

	
	$_t = array();
	foreach($_SESSION['explortExpandedIDs'] as $_ID=>$_expanded)
		if($_expanded)$_t[] = $_ID;
	$ProductCategories = catGetCategoryCList($_t, 'ASSOC', true);
	
	/**
	 * Handlers
	 */
	switch ($ACTION){
		/**
		 * Handler for expand message
		 */
		case 'EXPAND_CATEGORY':
			
			$_cID = intval($_POST['expandID']);
			if(!isset($_SESSION['isExpanded'][$_cID]))
				$_SESSION['isExpanded'][$_cID] = 0;
			
			if(!$_SESSION['isExpanded'][$_cID])
				$flagUpdateSubs = $_cID;
	
			$_SESSION['explortExpandedIDs'][$_cID] = 1;
			$_SESSION['selectedProductsIncSub'][$_cID] = 0;
			$_SESSION['isExpanded'][$_cID] = 1;
			break;
		/**
		 * Handler for unexpand message
		 */
		case 'UNEXPAND_CATEGORY':
		
			$unexpID = intval($_POST['unexpandID']);
			$_SESSION['selectedProductsIncSub'][$unexpID] = countSelectedProductsInCat($unexpID, $ProductCategories);
			
			$_SESSION['explortExpandedIDs'][$unexpID] = 0;
						
			break;
		/**
		 * Handler for updateing category state
		 */
		case 'UPDATE_CATEGORY_STATE':
			$_CategoryID = $_POST['updateCategory'];
			
			if(!isset($_SESSION['isExpanded'][$_CategoryID]))
				$_SESSION['isExpanded'][$_CategoryID] = 0;

			$_SESSION['checkedCategories'][$_CategoryID] = key_exists($_CategoryID, $_POST['CHECKED_CATEGORIES']);
			$_t = '';
			
			/**
			 * Count old selected products number in category
			 */
			if(isset($_SESSION['selectedProducts'][$_CategoryID]))
				$_oldSelProd = count($_SESSION['selectedProducts'][$_CategoryID]);
			else
				$_oldSelProd = 0;
			
			/**
			 * Update selected products number in category
			 */
			if(!$_SESSION['checkedCategories'][$_CategoryID]){
				
				$_SESSION['selectedProducts'][$_CategoryID] = array();
			}else {
				
				$Products = prdGetProductByCategory( array('categoryID'=>$_CategoryID, 'fullFlag'=>false), $_t );
				$c 	= count($Products);
				$_oldSelProd = 0;
				for($_t=0; $_t<$c; $_t++){
					
					if ($Products[$_t]['enabled']){
						
						$_oldSelProd++;
						$_SESSION['selectedProducts'][$_CategoryID][$Products[$_t]['productID']] = 1;
					}
				}
			}
			
			/**
			 * Update selected products number in category and subcategories
			 */
			if(!$_SESSION['isExpanded'][$_CategoryID]){
				
				$_SESSION['selectedProductsIncSub'][$_CategoryID] = $_SESSION['checkedCategories'][$_CategoryID]?$ProductCategories[$_CategoryID]['products_count']:0;
	$_SubC 	= catGetSubCategories($_CategoryID);
	$c 		= count($_SubC);
	
	for ($i=0; $i<$c;$i++){
		
		$_SESSION['checkedCategories'][$_SubC[$i]] = $_SESSION['checkedCategories'][$_CategoryID];
		$_SESSION['selectedProductsIncSub'][$_SubC[$i]] = $_SESSION['checkedCategories'][$_CategoryID]?(isset($ProductCategories[$_SubC[$i]]['products_count'])?$ProductCategories[$_SubC[$i]]['products_count']:0):0;
		
		if(!$_SESSION['checkedCategories'][$_SubC[$i]]){
			
			$_SESSION['selectedProducts'][$_SubC[$i]] = array();
		}else {
			
			$Products = prdGetProductByCategory( array('categoryID'=>$_SubC[$i], 'fullFlag'=>false), $_t );
			$_c 	= count($Products);
			for($_t=0; $_t<$_c; $_t++){
				
				if ($Products[$_t]['enabled'])
					$_SESSION['selectedProducts'][$_SubC[$i]][$Products[$_t]['productID']] = 1;
			}
		}
	}
			}elseif (!$_SESSION['explortExpandedIDs'][$_CategoryID]){
				
				$_SESSION['selectedProductsIncSub'][$_CategoryID] = $_SESSION['checkedCategories'][$_CategoryID]?$ProductCategories[$_CategoryID]['products_count']:0;
				$_SESSION['isExpanded'][$_CategoryID] = 0;
			}
			
			break;
		/**
		 * Handler for updateing selected products list
		 */
		case 'UPDATE_SELECTED_PRODUCTS':
			if(!count($_POST['PRODUCTS']))
				$_SESSION['checkedCategories'][$_POST['cIDForProducts']] = 0; 
			if(isset($_SESSION['selectedProductsIncSub'][$_POST['cIDForProducts']]))
				$_SESSION['selectedProductsIncSub'][$_POST['cIDForProducts']] += (-count($_SESSION['selectedProducts'][$_POST['cIDForProducts']]) + count($_POST['PRODUCTS']));
			$_SESSION['selectedProducts'][$_POST['cIDForProducts']] = $_POST['PRODUCTS'];
			break;
	}

	/**
	 * getting category tree
	 */
	$_t = array();
	foreach($_SESSION['explortExpandedIDs'] as $_ID=>$_expanded)
		if($_expanded)$_t[] = $_ID;
	$ProductCategories = catGetCategoryCList($_t, 'ASSOC', true);

	
/**
 * init first time
 */
if(1 && !count($_SESSION['checkedCategories'])){
	
	$_t 	= array_keys($ProductCategories);
	$_tt 	= '';
	foreach ($_t as $_key){
		
		$_SESSION['checkedCategories'][$_key] 		= 1;
		$_SESSION['selectedProductsIncSub'][$_key] 	= $ProductCategories[$_key]['products_count'];
		
		$Products 	= prdGetProductByCategory( array('categoryID'=>$_key, 'fullFlag'=>false), $_tT );
		$c 			= count($Products);
		
		for($_tt=0; $_tt<$c; $_tt++){
			
			if ($Products[$_tt]['enabled'])
				$_SESSION['selectedProducts'][$_key][$Products[$_tt]['productID']] = 1;
		}
	}
}

if($flagUpdateSubs){
	
	$_cID = &$flagUpdateSubs;
	
	$_SubC 	= catGetSubCategories($_cID);
	$c 		= count($_SubC);
	
	for ($i=0; $i<$c;$i++){
		
		$_SESSION['checkedCategories'][$_SubC[$i]] = $_SESSION['checkedCategories'][$_cID];
		$_SESSION['selectedProductsIncSub'][$_SubC[$i]] = $_SESSION['checkedCategories'][$_cID]?(isset($ProductCategories[$_SubC[$i]]['products_count'])?$ProductCategories[$_SubC[$i]]['products_count']:0):0;
		
		if(!$_SESSION['checkedCategories'][$_SubC[$i]]){
			
			$_SESSION['selectedProducts'][$_SubC[$i]] = array();
		}else {
			
			$Products = prdGetProductByCategory( array('categoryID'=>$_SubC[$i], 'fullFlag'=>false), $_t );
			$_c 	= count($Products);
			for($_t=0; $_t<$_c; $_t++){
				
				if ($Products[$_t]['enabled'])
					$_SESSION['selectedProducts'][$_SubC[$i]][$Products[$_t]['productID']] = 1;
			}
		}
	}
}

/**
 * getting products by request
 */
if(isset($_POST['showProducts'])){
	
	$_POST['showProducts'] = intval($_POST['showProducts']);
	$Products = prdGetProductByCategory( array('categoryID'=>intval($_POST['showProducts']), 'fullFlag'=>false), $_t );				
	foreach ($Products as $_ind=>$_Product)
		if(!$_Product['enabled'])
			unset($Products[$_ind]);
	$smarty->assign('showProducts', $_POST['showProducts']);
	$smarty->assign('Products', $Products);
	$smarty->assign('ProductsNum', count($Products));
}

$smarty->assign('ProductCategories', $ProductCategories);
$smarty->assign('session_selectedProducts', $_SESSION['selectedProducts']);
$smarty->assign('session_checkedCategories', $_SESSION['checkedCategories']);
?>