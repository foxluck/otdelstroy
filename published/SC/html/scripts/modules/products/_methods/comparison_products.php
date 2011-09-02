<?php
$Register = &Register::getInstance();
/*@var $Register Register*/
$PostVars = $Register->get(VAR_POST);
$smarty = &$Register->get(VAR_SMARTY);
/*@var $smarty Smarty*/

$productIDArray = explode( " ", trim(isset($PostVars["comparison_products"])?$PostVars["comparison_products"]:'') );

$showProductCategoryPath = true;
$products = array();

foreach( $productIDArray as $_productID ){
	
	$product = GetProduct($_productID);
	if(!$product) continue;
		
	if(isset($_tCategory)&&$showProductCategoryPath){
		
		if($_tCategory!=$product['categoryID']){
			
			$showProductCategoryPath = false;
		}
	}
	$_tCategory = $product['categoryID'];
	// picture
	$product["thumbnail"] = GetThumbnail( $_productID );
	// price
	$product["saveWithUnit"] = show_price($product["list_price"] - $product["Price"]);
	if ( $product["list_price"] != 0 )
		$product["savePercent"] = ceil((($product["list_price"] - $product["Price"])/$product["list_price"] )*100);
	$product["list_priceWithUnit"] = show_price($product["list_price"]);
	$product["PriceWithUnit"] = show_price($product["Price"]);

	$products[] = $product;
}

$options = cfgGetOptions();
$definedOptions = array();
foreach( $options as $option )
{
	$optionIsDefined = false;
	foreach( $products as $product )
	{
		foreach( $product["option_values"] as $optionValue )
		{
			if ( $optionValue["optionID"]==$option["optionID"] )
			{
				if ( $optionValue["option_type"] == 0 && $optionValue["option_value"]!=""
					||
					 $optionValue["option_type"] == 1 )
				{
					$optionIsDefined = true;
					break;
				}
			}
		}
	}
	if ( $optionIsDefined )
		$definedOptions[] = $option['optionID'];
}

$sql = '
	SELECT optionID,'.LanguagesManager::sql_prepareField('name').' AS name FROM '.PRODUCT_OPTIONS_TABLE.' 
	WHERE optionID IN("'.implode('", "', xEscapeSQLstring($definedOptions)).'")
	ORDER BY sort_order ASC, name ASC
';
$definedOptions = array();
$Result = db_query($sql);
while ($_Row = db_fetch_row($Result)) {
	
	$definedOptions[] = $_Row;
}

$optionIndex = 0;
foreach( $definedOptions as $option )
{
	$productIndex = 0;
	foreach( $products as $product )
	{
		$existFlag = false;

		foreach( $product["option_values"] as $optionValue )
		{
			if ( $optionValue["optionID"]==$option["optionID"] )
			{
				if ( $optionValue["option_type"] == 0 && $optionValue["option_value"]!="" )
					$value = $optionValue["value"];
			 	else if ( $optionValue["option_type"] == 1 ) 
				{
					$value = "";
					$extra = GetExtraParametrs( $product["productID"] );

					foreach( $extra as $item )
					{
						if ( $item["option_type"] == 1 && $item["optionID"] == $optionValue["optionID"] && isset($item["values_to_select"]) && count( $item["values_to_select"] ) > 0 )
							//if option is defined
						{
							foreach( $item["values_to_select"] as $value_to_select )
							{
								if ( $value != "" )
									$value .= " / ".$value_to_select["option_valueWithOutPrice"];
								else
									$value .= $value_to_select["option_valueWithOutPrice"];
							}
						}
					}
				}
				else
					$value = '&ndash;';

				// $item = array( "name" => $option["name"], "value" => $value );
				$products[ $productIndex ][ $optionIndex ] = $value;
				$existFlag = true;
				break;
			}
		}
		if ( !$existFlag )
			$products[ $productIndex ][ $optionIndex ] = '&ndash;';

		$productIndex++;
	}
	$optionIndex++;
}

if ( count($products) > 0 && $showProductCategoryPath){
	
	$smarty->assign('product_category_path', catCalculatePathToCategory( $products[0]['categoryID'] ) );
	$category = catGetCategoryById( $products[0]['categoryID'] );
	if ($category)$smarty->assign('category_description', $category['description']);
}

$smarty->assign('definedOptions', $definedOptions );
$smarty->assign('products', $products );
$smarty->assign('products_count', count($products) );
$smarty->assign('main_content_template', 'comparison_products.html' );
?>