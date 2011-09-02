<?php
function export_exportSubcategories($_pCategoryID, &$exportCategories, &$params){
	
	if(!$_pCategoryID){
		
		foreach ($_SESSION['checkedCategories'] as $_categoryID=>$_checked){
			
			if(in_array($_categoryID, $exportCategories[0]))continue;
			if(in_array($_categoryID, $exportCategories[1]))continue;
			$exportCategories[intval($_checked)][] = $_categoryID;
			if($_checked){
				
				if(isset($_SESSION['selectedProducts'][$_categoryID])){
					
					foreach ($_SESSION['selectedProducts'][$_categoryID] as $__ProductID=>$__Checked){
						
						if($params['exprtUNIC']['mode'] == 'toarrays'){
							
							$params['exprtUNIC']['expProducts'][] = $__ProductID;
							continue;
						}
						__exportProduct($__ProductID, $params);
					}
				}else {
					
					$_Products = prdGetProductByCategory( array('categoryID'=>intval($_categoryID), 'fullFlag'=>false), $_t );
					foreach ($_Products as $__Product){
						
						if(!$__Product['enabled'])continue;
						if($params['exprtUNIC']['mode'] == 'toarrays'){
							
							$params['exprtUNIC']['expProducts'][] = $__Product['productID'];
							continue;
						}
						__exportProduct($__Product['productID'], $params);
					}
				}
			}
			export_exportSubcategories($_categoryID, $exportCategories, $params);
		}
		return 1;
	}
	
	
	$_subs = catGetSubCategoriesSingleLayer($_pCategoryID);
	foreach ($_subs as $__Category){
		
		$_CategoryID = $__Category['categoryID'];
		if(isset($_SESSION['checkedCategories'][$_CategoryID])){
			
			$_t = intval($_SESSION['checkedCategories'][$_CategoryID])?intval($_SESSION['checkedCategories'][$_CategoryID]):isset($_SESSION['selectedProducts'][$_CategoryID]);
			$exportCategories[$_t][] = $_CategoryID;
		} elseif (in_array($_pCategoryID, $exportCategories[1]) ){ 
			
			$exportCategories[1][] = $_CategoryID;
		}
		
		if(isset($exportCategories[1][count($exportCategories[1])-1]))
		if($exportCategories[1][count($exportCategories[1])-1] == $_CategoryID){
			
			if(isset($_SESSION['selectedProducts'][$_CategoryID])){
				
				foreach ($_SESSION['selectedProducts'][$_CategoryID] as $__ProductID=>$__Checked){
					
					if($params['exprtUNIC']['mode'] == 'toarrays'){
						
						$params['exprtUNIC']['expProducts'][] = $__ProductID;
						continue;
					}
					__exportProduct($__ProductID, $params);
				}
			}else {
				
				$_Products = prdGetProductByCategory( array('categoryID'=>intval($_CategoryID), 'fullFlag'=>false), $_t );
				foreach ($_Products as $__Product){
					
					if(!$__Product['enabled'])continue;
					if($params['exprtUNIC']['mode'] == 'toarrays'){
						
						$params['exprtUNIC']['expProducts'][] = $__Product['productID'];
						continue;
					}
					__exportProduct($__Product['productID'], $params);
				}
			}
		}else {
			if(!isset($_SESSION['isExploded'][$_CategoryID]))continue;
			if(!$_SESSION['isExploded'][$_CategoryID] && !$_SESSION['checkedCategories'][$_CategoryID])continue;
		}
		
		export_exportSubcategories($_CategoryID, $exportCategories, $params);
	}
}

?>