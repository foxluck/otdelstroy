<?php

// compare two configuration

function CompareConfiguration($variants1, $variants2)

{

	if ( count($variants1) != count($variants2) )

	return false;



	foreach($variants1 as $variantID)

	{

		$count1 = 0;

		$count2 = 0;



		for($i=0; $i<count($variants1); $i++)

		if ( (int)$variants1[$i] == (int)$variantID )

		$count1 ++;



		for($i=0; $i<count($variants1); $i++)

		if ( (int)$variants2[$i] == (int)$variantID )

		$count2 ++;



		if ( $count1 != $count2 )

		return false;

	}

	return true;

}



// search configuration in session variable

function SearchConfigurationInSessionVariable($variants, $productID)

{

	foreach( $_SESSION["configurations"] as $key => $value )

	{

		if ( (int)$_SESSION["gids"][$key] != (int)$productID )

		continue;

		if ( CompareConfiguration($variants, $value) )

		return $key;

	}

	return -1;

}



// search configuration in database

function SearchConfigurationInDataBase($variants, $productID)

{

	$q=db_query( "select itemID from ".SHOPPING_CARTS_TABLE.

	" where customerID='".regGetIdByLogin($_SESSION["log"])."'" );

	while( $r = db_fetch_row($q) )

	{

		$q1=db_query( "select COUNT(itemID) from ".SHOPPING_CART_ITEMS_TABLE.

		" where productID='".$productID."' AND itemID='".$r["itemID"]."'" );

		$r1=db_fetch_row($q1);

		if ( $r1[0] != 0 )

		{

			$variants_from_db=GetConfigurationByItemId( $r["itemID"] );

			if ( CompareConfiguration($variants, $variants_from_db) )

			return $r["itemID"];

		}



	}

	return -1;

}





function GetConfigurationByItemId($itemID)

{
	static $variants_cache = array();
	$itemID = intval($itemID);
	if(!isset($variants_cache[$itemID])){
		$variants_cache[$itemID] = array();
		if($q=db_phquery("select variantID from ?#SHOPPING_CART_ITEMS_CONTENT_TABLE where itemID=?",$itemID)){
			while( $r=db_fetch_row( $q ) ){
				$variants_cache[$itemID][]=$r["variantID"];
			}
		}
	}
	return $variants_cache[$itemID];
}



function InsertNewItem(

$variants,

$productID)

{

	db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE.

	"(productID) values('".$productID."')" );

	$itemID=db_insert_id();

	foreach( $variants as $var )

	{

		db_query("insert into ".

		SHOPPING_CART_ITEMS_CONTENT_TABLE."(itemID, variantID) ".

		"values( '".$itemID."', '".$var."')" );

	}

	return $itemID;

}



function InsertItemIntoCart($itemID)

{

	db_query("insert ".SHOPPING_CARTS_TABLE.

	"(customerID, itemID, Quantity)".

	"values( '".regGetIdByLogin($_SESSION["log"])."', '".$itemID."', 1 )" );

}



function GetStrOptions($variants)

{
	
	static $res_cache = array();
	$variants = array_map('intval',$variants);
	$dbq = 'SELECT '.LanguagesManager::sql_prepareField('option_value', true).
			',variantID FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE variantID IN(?@)';

	$is_cached = true;
	$non_cached_variants = array();
	foreach($variants as $variantID){
		if(!isset($res_cache[$variantID])){
			$non_cached_variants[]=$variantID;
		}
	}
	if(count($non_cached_variants)){
		if($q=db_phquery($dbq, $non_cached_variants)){
			while( $r=db_fetch_row($q)){
				if($r['option_value']){
					$res_cache[$r['variantID']]=$r['option_value'];
				}
			}
		}
	}
	
	$result = array();
	foreach($variants as $variantID){
		$result[] = $res_cache[$variantID];
	}
	return count($result)?implode(', ',$result):"";
}



function CodeItemInClient($variants, $productID)

{

	$array=array();

	$array[]=$productID;

	foreach($variants as $var)

	$array[]=$var;

	return implode("_", $array);

}



function DeCodeItemInClient($str)

{

	// $variants, $productID

	$array=explode("_", $str );

	$productID=$array[0];

	$variants=array();

	for($i=1; $i<count($array); $i++)

	$variants[]=$array[$i];

	$res=array();

	$res["productID"]=$productID;

	$res["variants"]=$variants;

	return $res;

}



function GetProductInStockCount($productID)

{

	$q=db_query("select in_stock from ".

	PRODUCTS_TABLE.

	" where productID='".$productID."'" );

	$is=db_fetch_row($q);

	return $is[0];

}



function GetPriceProductWithOption($variants, $productID)

{

	$q=db_query("select Price from ".PRODUCTS_TABLE." where productID='".$productID."'");

	$r=db_fetch_row($q);

	$base_price = (float)$r[0];

	$full_price = (float)$base_price;

	/*foreach($variants as $var)

	{

		$q1=db_query("select price_surplus from ".PRODUCTS_OPTIONS_SET_TABLE.

		" where productID='".$productID."' AND variantID='".$var."'");

		$r1=db_fetch_row($q1);

		$full_price += $r1["price_surplus"];

	}*/
	$variantsPrice = array();
	if(count($variants)){
		$q1=db_phquery("select price_surplus,variantID from ?#PRODUCTS_OPTIONS_SET_TABLE where productID=? AND variantID IN(?@)",$productID,$variants);
	
		while($r1=db_fetch_row($q1)){
			$variantsPrice[$r1['variantID']] = $r1['price_surplus'];
		}
	}
	foreach($variants as $var){
		$full_price += isset($variantsPrice[$var])?$variantsPrice[$var]:0;
	}

	return $full_price;

}





function GetProductIdByItemId($itemID){

	$q=db_phquery('SELECT productID FROM '.SHOPPING_CART_ITEMS_TABLE.' WHERE itemID=?',$itemID);

	$r=db_fetch_assoc($q);

	return $r['productID'];

}
// *****************************************************************************

// Purpose	clear cart content

// Inputs

// Remarks

// Returns

function cartClearCartContet($mode='succes'){

	$customerEntry = Customer::getAuthedInstance();

	if(!is_null($customerEntry)){
		if($mode=='erase')
		{
			$itemIDs = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT itemID FROM ?#SHOPPING_CARTS_TABLE WHERE customerID=?', $customerEntry->customerID);
			if(is_array($itemIDs)&&count($itemIDs)){
				db_phquery("DELETE FROM ?#SHOPPING_CART_ITEMS_CONTENT_TABLE WHERE itemID IN (?@)", $itemIDs);
				db_phquery("DELETE FROM ?#SHOPPING_CART_ITEMS_TABLE WHERE itemID IN (?@)", $itemIDs);
			}

		}
		if($mode!='recalculate')
		db_phquery("DELETE FROM ?#SHOPPING_CARTS_TABLE WHERE customerID=?", $customerEntry->customerID);
		else
		db_phquery("UPDATE ?#SHOPPING_CARTS_TABLE SET Quantity=0 WHERE customerID=?", $customerEntry->customerID);

	}else{
		if($mode=='recalculate' && isset($_SESSION["counts"]) && is_array($_SESSION["counts"])){
			$i=0;
			foreach( $_SESSION["counts"] as $counts){$_SESSION["counts"][$i++]=0;}
		}else{

			unset($_SESSION["gids"]);
			unset($_SESSION["counts"]);
			unset($_SESSION["configurations"]);
			session_unregister("gids"); //calling session_unregister() is required since unset() may not work on some systems
			session_unregister("counts");
			session_unregister("configurations");
		}
	}

}

/**
 * @return array
 */
function cartGetCartContent(){

	$cart_content 	= array();
	$total_price 	= 0;
	$freight_cost	= 0;
	$variants 		= '';

	$currencyEntry = Currency::getSelectedCurrencyInstance();
	$customerEntry = Customer::getAuthedInstance();

	if(!is_null($customerEntry)){//get cart content from the database

		$q = db_phquery('
			SELECT t3.*, t1.itemID, t1.Quantity, t4.thumbnail FROM ?#SHOPPING_CARTS_TABLE t1 
				LEFT JOIN ?#SHOPPING_CART_ITEMS_TABLE t2 ON t1.itemID=t2.itemID
				LEFT JOIN ?#PRODUCTS_TABLE t3 ON t2.productID=t3.productID
				LEFT JOIN ?#PRODUCT_PICTURES t4 ON t3.default_picture=t4.photoID
			WHERE customerID=?', $customerEntry->customerID);

		while ($cart_item = db_fetch_assoc($q)){

			// get variants
			$variants=GetConfigurationByItemId( $cart_item["itemID"] );

			LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $cart_item);

			$costUC = GetPriceProductWithOption( $variants, $cart_item["productID"]);

			$tmp = array(
			"productID" => $cart_item["productID"],
			"slug" => $cart_item["slug"],
			"id" =>	$cart_item["itemID"],
			"name" =>	$cart_item["name"],
			'thumbnail_url' => $cart_item['thumbnail']&&file_exists(DIR_PRODUCTS_PICTURES.'/'.$cart_item['thumbnail'])?URL_PRODUCTS_PICTURES.'/'.$cart_item['thumbnail']:'',
			"brief_description"	=>	$cart_item["brief_description"],
			"quantity"	=>	$cart_item["Quantity"],
			"free_shipping"	=>	$cart_item["free_shipping"],
			"costUC"	=>	$costUC,
			"cost" => show_price($cart_item["Quantity"]*$costUC),
			"product_code" => $cart_item["product_code"],
			);

			if($tmp['thumbnail_url']){

				list($thumb_width, $thumb_height) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$cart_item['thumbnail']);
				list($tmp['thumbnail_width'], $tmp['thumbnail_height']) = shrink_size($thumb_width, $thumb_height, round(CONF_PRDPICT_THUMBNAIL_SIZE/2), round(CONF_PRDPICT_THUMBNAIL_SIZE/2));
			}

			$freight_cost += $cart_item["Quantity"]*$cart_item["shipping_freight"];

			$strOptions=GetStrOptions(GetConfigurationByItemId( $tmp["id"] ));
			if(trim($strOptions) != "")
			$tmp["name"].="  (".$strOptions.")";

			if ( $cart_item["min_order_amount"] > $cart_item["Quantity"] )
			$tmp["min_order_amount"] = $cart_item["min_order_amount"];

			$total_price += $cart_item["Quantity"]*$costUC;

			$cart_content[] = $tmp;
		}
	}else{ //unauthorized user - get cart from session vars

		$total_price 	= 0; //total cart value
		$cart_content	= array();

		//shopping cart items count

		if ( isset($_SESSION["gids"]) )

		for ($j=0; $j<count($_SESSION["gids"]); $j++)

		{

			if ($_SESSION["gids"][$j])

			{

				$session_items[] = CodeItemInClient($_SESSION["configurations"][$j], $_SESSION["gids"][$j]);



				$q = db_phquery("SELECT t1.*, p1.thumbnail FROM ?#PRODUCTS_TABLE t1 LEFT JOIN ?#PRODUCT_PICTURES p1 ON t1.default_picture=p1.photoID WHERE t1.productID=?", $_SESSION["gids"][$j]);

				if ($r = db_fetch_row($q)){



					LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $r);

					$costUC = GetPriceProductWithOption(

					$_SESSION["configurations"][$j],

					$_SESSION["gids"][$j])/* * $_SESSION["counts"][$j]*/;



					$id = $_SESSION["gids"][$j];

					if (count($_SESSION["configurations"][$j]) > 0)

					{

						for ($tmp1=0;$tmp1<count($_SESSION["configurations"][$j]);$tmp1++) $id .= "_".$_SESSION["configurations"][$j][$tmp1];

					}



					$tmp = array(

					"productID"	=>  $_SESSION["gids"][$j],

					"slug"	=>  $r['slug'],

					"id"		=>	$id, //$_SESSION["gids"][$j],

					"name"		=>	$r['name'],

					'thumbnail_url' => $r['thumbnail']&&file_exists(DIR_PRODUCTS_PICTURES.'/'.$r['thumbnail'])?URL_PRODUCTS_PICTURES.'/'.$r['thumbnail']:'',

					"brief_description"	=> $r["brief_description"],

					"quantity"	=>	$_SESSION["counts"][$j],

					"free_shipping"	=>	$r["free_shipping"],

					"costUC"	=>	$costUC,

					"cost"		=>	show_price($costUC * $_SESSION["counts"][$j])

					);

					if($tmp['thumbnail_url']){



						list($thumb_width, $thumb_height) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$r['thumbnail']);

						list($tmp['thumbnail_width'], $tmp['thumbnail_height']) = shrink_size($thumb_width, $thumb_height, round(CONF_PRDPICT_THUMBNAIL_SIZE/2), round(CONF_PRDPICT_THUMBNAIL_SIZE/2));

					}

					$strOptions=GetStrOptions( $_SESSION["configurations"][$j] );

					if ( trim($strOptions) != "" )

					$tmp["name"].="  (".$strOptions.")";





					$q_product = db_query( "select min_order_amount, shipping_freight from ".PRODUCTS_TABLE.

					" where productID=".

					$_SESSION["gids"][$j] );

					$product = db_fetch_row( $q_product );

					if ( $product["min_order_amount"] > $_SESSION["counts"][$j] )

					$tmp["min_order_amount"] = $product["min_order_amount"];



					$freight_cost += $_SESSION["counts"][$j]*$product["shipping_freight"];



					$cart_content[] = $tmp;



					$total_price += GetPriceProductWithOption(

					$_SESSION["configurations"][$j],

					$_SESSION["gids"][$j] )*$_SESSION["counts"][$j];

				}

			}

		}

	}

	return array(
	"cart_content"	=> $cart_content,
	"total_price"	=> $total_price,
	"freight_cost"	=> $freight_cost
	);
}





function cartCheckMinOrderAmount()

{

	$cart_content = cartGetCartContent();

	$cart_content = $cart_content["cart_content"];

	foreach( $cart_content as $cart_item )

	if ( isset($cart_item["min_order_amount"]) )

	return false;

	return true;

}



function cartCheckMinTotalOrderAmount(){



	$res = cartGetCartContent();

	$d = oaGetDiscountValue( $res, "" );

	$order["order_amount"] = $res["total_price"] - $d;

	if($order["order_amount"]<CONF_MINIMAL_ORDER_AMOUNT)

	return false;

	else

	return true;

}

function cartUpdateAddCounter($productID)
{
	db_phquery("UPDATE ?#PRODUCTS_TABLE SET add2cart_counter=(add2cart_counter+1) WHERE productID=?",$productID);
	//TODO: add_metric_code
/*
 include_once('class.metric.php');
$metric = metric::getInstance();

$metric->addAction($DB_KEY, $currentUser, 'SC', _ACTION_, _CLIENT_, _DATA_);

_ACTION_ - DOWNLOAD/UPLOAD/ADDCONTACT/etc...
_CLIENT_ - FLASH/JAVA/etc.. (default WA)
_DATA_ - данные поясняющие действие. 
 */
	if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
		include_once(WBS_DIR.'/kernel/classes/class.metric.php');
		
		$DB_KEY=strtoupper(SystemSettings::get('DB_KEY'));
		$U_ID = sc_getSessionData('U_ID');
		
		$metric = metric::getInstance();
		$metric->addAction($DB_KEY, $U_ID, 'SC', 'ADD2CART', isset($_GET['widgets'])?'WIDGET':'STOREFRONT','');
	}
}

function cartMinimizeCart()
{
	//if (/*!isset($_SESSION["log"])*/true){
	$customerEntry = Customer::getAuthedInstance();
	//print ":D";exit;
	if($customerEntry){
		$itemIDs = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT itemID FROM ?#SHOPPING_CARTS_TABLE WHERE Quantity=0 AND customerID=?', $customerEntry->customerID);
		if(is_array($itemIDs)&&count($itemIDs)){
			db_phquery("DELETE FROM ?#SHOPPING_CART_ITEMS_CONTENT_TABLE WHERE itemID IN (?@)", $itemIDs);
			db_phquery("DELETE FROM ?#SHOPPING_CART_ITEMS_TABLE WHERE itemID IN (?@)", $itemIDs);
		}
		db_phquery("DELETE FROM ?#SHOPPING_CARTS_TABLE WHERE Quantity=0 AND customerID=?",$customerEntry->customerID);
	}else{
		if(isset($_SESSION["counts"]) && is_array($_SESSION["counts"])){
			$i=0;
			for($i=0;$i<sizeof($_SESSION["counts"]);){
				if($_SESSION["counts"][$i] == 0){
					array_splice($_SESSION["gids"],$i,1);
					array_splice($_SESSION["counts"],$i,1);
					array_splice($_SESSION["configurations"],$i,1);
				}else{
					$i++;
				}
				
			}
		}
	}
}
/**

 * Add to cart product with options

 * 

 * @param int $productID

 * @param array $variants  - row is variantID

 * @param int $qty

 */

function cartAddToCart( $productID, $variants, $qty = 1 ){
	if($qty === ''){$qty = 1;}
	$qty = max(0,intval($qty));

	$productID = intval($productID);

	$product_data = GetProduct($productID);

	if(!$product_data['ordering_available'])return false;

	if(!$product_data['enabled'])return false;

	$is = intval($product_data['in_stock']);

	$min_order_amount = $product_data['min_order_amount'];
	
	//$min_order_amount = db_phquery_fetch(DBRFETCH_FIRST, "SELECT min_order_amount FROM ?#PRODUCTS_TABLE WHERE productID=?", $productID );

	if (!isset($_SESSION["log"])){ //save shopping cart in the session variables



		//$_SESSION["gids"] contains product IDs

		//$_SESSION["counts"] contains product quantities

		//($_SESSION["counts"][$i] corresponds to $_SESSION["gids"][$i])

		//$_SESSION["configurations"] contains variants

		//$_SESSION[gids][$i] == 0 means $i-element is 'empty'

		if (!isset($_SESSION["gids"])){
			$_SESSION["gids"] = array();
			$_SESSION["counts"] = array();
			$_SESSION["configurations"] = array();
		}


		//check for current item in the current shopping cart content
		$item_index=SearchConfigurationInSessionVariable( $variants, $productID );
		if ( $item_index!=-1 ){ //increase current product's quantity
			/*if($_SESSION["counts"][$item_index]+$qty<$min_order_amount){
			$qty=$min_order_amount-$_SESSION["counts"][$item_index];
			}*/
			//$qty = max($qty,$min_order_amount - $_SESSION["counts"][$item_index],0);
			if(CONF_CHECKSTOCK!=0){
				$qty = min($qty,$is - $_SESSION["counts"][$item_index]);
			}
			$qty = max($qty,0);
			if(CONF_CHECKSTOCK==0 || (($_SESSION["counts"][$item_index]+$qty <= $is)&&$is&&$qty)){
				$_SESSION["counts"][$item_index] += $qty;
			}else{
				return $_SESSION["counts"][$item_index];
			}

		}else{ //no item - add it to $gids array
			$qty = max($qty,$min_order_amount,0);
			if(CONF_CHECKSTOCK!=0){
				$qty = min($qty,$is);
			}
			$qty = max($qty,0);

			if(CONF_CHECKSTOCK==0 || ($is >= $qty&&$qty)){
				$_SESSION["gids"][] = $productID;
				$_SESSION["counts"][] = $qty;
				$_SESSION["configurations"][]=$variants;
				cartUpdateAddCounter($productID);
			}else{
				return 0;
			}

		}

	}else{ //authorized customer - get cart from database


		$itemID = SearchConfigurationInDataBase($variants, $productID );
		$customerEntry = Customer::getAuthedInstance();
		if(is_null($customerEntry))return false;

		if ( $itemID !=-1 ){ // if this configuration exists in database
			$quantity = db_phquery_fetch(DBRFETCH_FIRST, "SELECT Quantity FROM ?#SHOPPING_CARTS_TABLE WHERE customerID=? AND itemID=?", $customerEntry->customerID, $itemID);
			/*if($quantity+$qty<$min_order_amount){
			$qty=$min_order_amount-$quantity;
			}*/
			//$qty = max($qty,$min_order_amount - $quantity);
			if(CONF_CHECKSTOCK!=0){
				$qty = min($qty,$is-$quantity);
			}
			$qty = max($qty,0);
			if (CONF_CHECKSTOCK==0 || $quantity + $qty <= $is && $is){
				db_phquery("UPDATE ?#SHOPPING_CARTS_TABLE SET Quantity=? WHERE customerID=? AND itemID=?", $quantity+$qty, $customerEntry->customerID, $itemID);
			}else{
				return $quantity;
			}

		}else{ //insert new item
			
			$qty = max($qty,$min_order_amount);
			if(CONF_CHECKSTOCK!=0 && $qty > $is){
				$qty = min($qty,$is);
			}
			if ((CONF_CHECKSTOCK==0 || $is >= $qty)&&$qty>0){
				$itemID=InsertNewItem($variants, $productID );
				InsertItemIntoCart($itemID);
				db_phquery("UPDATE ?#SHOPPING_CARTS_TABLE SET Quantity=? WHERE customerID=? AND itemID=?",
				$qty, $customerEntry->customerID, $itemID);
				cartUpdateAddCounter($productID);
			}else{
				return 0;
			}

		}

	}

	//db_phquery("UPDATE ?#PRODUCTS_TABLE SET add2cart_counter=(add2cart_counter+1) WHERE productID=?",$productID);
	return true;

}







// *****************************************************************************

// Purpose

// Inputs	$customerID - customer ID

// Remarks

// Returns	returns true if cart is empty for this customer

function cartCartIsEmpty( $log )

{

	$customerID = regGetIdByLogin( $log );

	if ( (int)$customerID > 0 )

	{

		$customerID = (int)$customerID;

		$q_count = db_query( "select count(*) from ".SHOPPING_CARTS_TABLE." where customerID=".$customerID );

		$count = db_fetch_row( $q_count );

		$count = $count[0];

		return ( $count == 0 );

	}

	else

	return true;

}







?>