<?php
$Register = &Register::getInstance();
/*@var $Register Register*/
$Message = $Register->get(VAR_MESSAGE);
/*@var $Message Message*/
$productID = isset($_GET['productID'])?intval($_GET['productID']):0;

if(isset($_POST["add2cart_x"])||isset($_POST["add2cart"])) { //add product to cart

	$variants=array();
	foreach($_POST as $key => $val ) {
			
		if(!strstr($key, 'option_'))continue;
		$variants[] = $val;
	}

	$qty = max(1,isset($_POST['product_qty'])?intval($_POST['product_qty']):1);

	$res = cartAddToCart($productID, $variants, $qty );

	$Register = &Register::getInstance();
	/*@var $Register Register*/
	if($res === false) {
		RedirectSQ('?ukey=product_not_found&view=&external=');
	}elseif($res === 0) {
		RedirectSQ('?ukey=product_out_of_stock&view=&external=');
	}else {
		RedirectSQ('?ukey=cart&view=&external=');
	}
}


// product detailed information view
if (isset($_GET["vote"]) && isset($productID)) { //vote for a product
	if (isset($_SESSION["vote_completed"][ $productID ]) && !$_SESSION["vote_completed"][ $productID ] && isset($_GET["mark"]) && strlen($_GET["mark"])>0) {
		$mark = (float) $_GET["mark"];

		if ($mark>0 && $mark<=5) {
			db_query("UPDATE ".PRODUCTS_TABLE." SET customers_rating=(customers_rating*customer_votes+'".$mark."')/(customer_votes+1), customer_votes=customer_votes+1 WHERE productID='".$productID."'") or die (db_error());
		}
	}
	$_SESSION["vote_completed"][ $productID ] = 1;
	RedirectSQ('vote=&mark=', '', true);
}elseif(isset($productID)) {
	if(!isset($_SESSION["vote_completed"])) {
		$_SESSION["vote_completed"] = array();
	}
	if(!isset($_SESSION["vote_completed"][ $productID ])) {
		$_SESSION["vote_completed"][ $productID ] = 0;
	}
}



if (isset($_POST["request_information"])) { //email inquiry to administrator
	$customer_name = trim( $_POST["customer_name"] );
	$customer_email = trim( $_POST["customer_email"] ) ;
	$message_subject = trim( $_POST["message_subject"] ) ;
	$message_text = trim( $_POST["message_text"] );

	if(!valid_email($customer_email)){
		Message::raiseMessageRedirectSQ(MSG_ERROR, '#product-request', 'msg_error_wrong_email', '', array('name' => 'prd_request', 'prd_request' => $_POST));
	}
	//validate input data
	if ($customer_email && $customer_name && $message_subject && $message_text && valid_email($customer_email)){
			
		if(CONF_ENABLE_CONFIRMATION_CODE) {
			$iVal = new IValidator();
			if(!$iVal->checkCode($_POST['fConfirmationCode'])) {
				Message::raiseMessageRedirectSQ(MSG_ERROR, '#product-request', 'err_wrong_ccode', '', array('name' => 'prd_request', 'prd_request' => $_POST));
			}
		}
			
		$customer_name = str_replace(array('@','<',"\n"), array('[at]', '', ''), $customer_name);
		$customer_email = str_replace(array("\n",'<'), '', $customer_email);
		$message_text = "{$customer_name} ({$customer_email}):\n{$message_text}";
		$headers = array('From'=>$customer_email,'Sender'=>$customer_email,'FromName'=>$customer_name);
		//send a message to store administrator
		ss_mail(CONF_GENERAL_EMAIL,$message_subject,$message_text,false,$headers);

		RedirectSQ('sent=yes#product-request');
	}elseif(isset($_POST["request_information"]))Message::raiseMessageRedirectSQ(MSG_ERROR, '#product-request', 'err_input_all_required_fields', '', array('name' => 'prd_request', 'prd_request' => $_POST));;
}




//show product information
if (isset($productID) && $productID>=0 && !isset($_POST["add_topic"]) && !isset($_POST["discuss"]) ) {

	$product=GetProduct($productID);
	if(!$product||$product["enabled"]==0) {
		error404page();//RedirectSQ('?');
	}else {

		if( !isset($_GET["vote"]) ) {
			IncrementProductViewedTimes($productID);
		}

		$dontshowcategory = 1;

		$smarty->assign("main_content_template", "product_info.frame.html");

		$a = $product;
		$a["PriceWithUnit"] = show_price( $a["Price"] );
		$a["list_priceWithUnit"] = show_price( $a["list_price"] );
			
		$currencyEntry = Currency::getSelectedCurrencyInstance();
		$a["price_incurr"] = $currencyEntry->convertUnits($a["Price"]);
		$a["list_price_incurr"] = $currencyEntry->convertUnits($a["list_price"]);

		if ( ((float)$a["shipping_freight"]) > 0 ) {
			$a["shipping_freightUC"] = show_price( $a["shipping_freight"] );
		}

		if ( isset($_GET["picture_id"]) ) {
			$picture_row = db_phquery_fetch(DBRFETCH_ASSOC, "SELECT * FROM ?#PRODUCT_PICTURES WHERE photoID=?",$_GET["picture_id"] );
		}else if ( !is_null($a["default_picture"]) ) {
			$picture_row = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#PRODUCT_PICTURES WHERE photoID=?', $a["default_picture"]);
		}else{
			$picture_row = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#PRODUCT_PICTURES WHERE productID=? ORDER BY priority LIMIT 1', $productID);
			if( isset($picture_row["photoID"]) )$a["default_picture"]=$picture_row["photoID"];
			else $picture_row = null;
		}
			
		if ( $picture_row ){
			$a["picture"] = $picture_row['filename'];
			$a["thumbnail"] = $picture_row['thumbnail'];
			$a["big_picture"] = $picture_row['enlarged'];
			$a['photoID'] = $picture_row['photoID'];
		}

		if(!isset($a['productID']))RedirectSQ('?');
			
		if (!isset($categoryID)){
			$categoryID = $a["categoryID"];
			$smarty->assign('categoryID', $categoryID);
		}

		//get selected category info
		$q = db_query("SELECT categoryID, ".LanguagesManager::sql_prepareField('name')." AS name, ".LanguagesManager::sql_prepareField('description')." AS description, picture FROM ".CATEGORIES_TABLE." WHERE categoryID='$categoryID'") or die (db_error());
		$row = db_fetch_row($q);
		if ($row){

			if (!file_exists(DIR_PRODUCTS_PICTURES."/".$row[3])) $row[3] = "";
			$smarty->assign("selected_category", $row);
		}

		//calculate a path to the category
		$smarty->assign("product_category_path",  catCalculatePathToCategory( $categoryID ) );

		//reviews number
		$k = db_phquery_fetch(DBRFETCH_FIRST, "SELECT count(*) FROM ?#DISCUSSIONS_TABLE WHERE productID=?", $productID);

		//extra parameters
		$extra = GetExtraParametrs($productID);
		//related items
		$related = array();
		$related_records = db_phquery_fetch(DBRFETCH_ROW_ALL,'SELECT pr.productID as productID, '.LanguagesManager::sql_prepareField('name').' AS name, Price, slug FROM ?#PRODUCTS_TABLE AS pr JOIN ?#RELATED_PRODUCTS_TABLE AS rel ON (rel.`productID` = pr.`productID`) WHERE Owner=? AND enabled = 1',$productID);
		foreach($related_records as $r){
			$r[2] = show_price($r[2]);
			$RelatedPictures = GetPictures($r['productID']);
			foreach($RelatedPictures as $_RelatedPicture){
				if(!$_RelatedPicture['default_picture'])continue;
				if(!file_exists(DIR_PRODUCTS_PICTURES."/".$_RelatedPicture['thumbnail']))break;
				$r['pictures'] = array('default' => $_RelatedPicture);
				break;
			}
			$related[] = $r;
		}
		$smarty->assign("product_related_number", count($related));
		//related items old slow code
		/*
			$q = db_query("SELECT count(*) FROM ".RELATED_PRODUCTS_TABLE." WHERE Owner='$productID'") or die (db_error());
			$cnt = db_fetch_row($q);
			$smarty->assign("product_related_number", $cnt[0]);
			if ($cnt[0] > 0)
			{
			$q = db_query("SELECT productID FROM ".RELATED_PRODUCTS_TABLE." WHERE Owner='$productID'") or die (db_error());

			while ($row = db_fetch_row($q))
			{
			$p = db_query("SELECT productID, ".LanguagesManager::sql_prepareField('name')." AS name, Price, slug FROM ".PRODUCTS_TABLE." WHERE productID=$row[0] AND enabled = 1") or die (db_error());
			if ($r = db_fetch_row($p))
			{
			$r[2] = show_price($r[2]);
			$RelatedPictures = GetPictures($r['productID']);
			foreach($RelatedPictures as $_RelatedPicture){

			if(!$_RelatedPicture['default_picture'])continue;
			if(!file_exists(DIR_PRODUCTS_PICTURES."/".$_RelatedPicture['thumbnail']))break;
			$r['pictures'] = array('default' => $_RelatedPicture);
			break;
			}
			$related[] = $r;
			}
			}

			}
			*/

		//update several product fields
		if (!file_exists(DIR_PRODUCTS_PICTURES."/".$a["picture"] )) {
			$a["picture"] = '';
		}
		if (!file_exists(DIR_PRODUCTS_PICTURES."/".$a["thumbnail"] )) {
			$a["thumbnail"] = '';
		}
			
		if (!file_exists(DIR_PRODUCTS_PICTURES."/".$a["big_picture"] )) {
			$a["big_picture"] = '';
		}else if ($a["big_picture"]) {
			$size = getimagesize(DIR_PRODUCTS_PICTURES."/".$a["big_picture"] );
			$a['picture_width'] = $size[0]+40;
			$a['picture_height'] = $size[1]+30;
		}
			
		if(!$a['picture'] && !$a['thumbnail']) {
			$a['picture'] = '';
			$a['thumbnail'] = '';
			$a['big_picture'] = '';
		}
			
		$a[12] = show_price( $a["Price"] );
		$a[13] = show_price( $a["list_price"] );
		$a[14] = show_price( $a["list_price"] - $a["Price"]); //you save (value)
		$a["PriceWithOutUnit"]=show_priceWithOutUnit( $a["Price"] );
		if ( $a["list_price"] ) {
			$a[15] = ceil(((($a["list_price"]-$a["Price"])/$a["list_price"])*100)); //you save (%)
		}

		$all_product_pictures = array();
		$dbres = db_phquery("SELECT * FROM ?#PRODUCT_PICTURES WHERE productID=? ORDER BY priority", $productID);
		while ($row = db_fetch_assoc($dbres)) {

			if(!$row['thumbnail']||!file_exists(DIR_PRODUCTS_PICTURES.'/'.$row['thumbnail']))continue;
			if(!$row['filename']||!file_exists(DIR_PRODUCTS_PICTURES.'/'.$row['filename']))continue;
			if($row['enlarged'] && file_exists(DIR_PRODUCTS_PICTURES.'/'.$row['enlarged'])) {

				list($row['width'], $row['height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$row['enlarged']);
				$row['width'] += 40;
				$row['height'] += 30;
			}else {
				list($row['width'], $row['height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$row['filename']);
				$row['width'] += 40;
				$row['height'] += 30;
			}
			$all_product_pictures[] = $row;
		}
		if(!$a['picture'] && count($all_product_pictures)) {
			$a['picture'] = $all_product_pictures[0]['filename'];
			$a['thumbnail'] = $all_product_pictures[0]['thumbnail'];
			if(($all_product_pictures[0]['filename']==$all_product_pictures[0]['enlarged'])||
			($all_product_pictures[0]['height']==$all_product_pictures[0]['height'])||
			($all_product_pictures[0]['width']==$all_product_pictures[0]['width'])){
				$a['big_picture'] = '';
			}else {
				$a['big_picture'] = $all_product_pictures[0]['enlarged'];
			}
		}
			
		/**
		 * @features "Supporting e-products, which user can download after payment"
		 * @state start
		 */
		//eproduct
		if (strlen($a["eproduct_filename"]) > 0 && file_exists(DIR_PRODUCTS_FILES."/".$a["eproduct_filename"]) ) {
			$size = filesize(DIR_PRODUCTS_FILES."/".$a["eproduct_filename"]);
			$a["eproduct_filesize"] = $size;
			$a["eproduct_filesize_str"] = getDisplayFileSize($size, 'B');
		}else {
			$a["eproduct_filename"] = "";
		}
		/**
		 * @features "Supporting e-products, which user can download after payment"
		 * @state end
		 */

		//initialize product "request information" form in case it has not been already submitted
		if (!isset($_POST["request_information"])) {
			if (!isset($_SESSION["log"])) {
				$customer_name = "";
				$customer_email = "";
			}else {
				$custinfo = regGetCustomerInfo2( $_SESSION["log"] );
				$customer_name = $custinfo["first_name"]." ".$custinfo["last_name"];
				$customer_email = $custinfo["Email"];
			}

			$message_text = "";
		}
			
		if(Message::isMessage($Message) && $Message->is_set() && isset($Message->prd_request)) {
			$smarty->assign('prd_request', $Message->prd_request);
		}
			
		if (isset($_GET["sent"])) {
			$smarty->assign("sent",1);
		}
		set_query('&sent=', '', true);
			
		if(count($all_product_pictures)>1) {
			$smarty->assign("all_product_pictures", $all_product_pictures );
		}

		$smarty->assign("m_all_product_pictures", $all_product_pictures );
		$smarty->assign('conf_image', URL_ROOT.'/imgval.php?'.generateRndCode(4).'=1');
		$smarty->assign("product_info", $a);
		$smarty->assign("product_reviews_count", $k);
		$smarty->assign('product_last_reviews', discGetLastDiscussions($productID, 2));
			
		/*if(file_exists(DIR_RSS."/{$productID}.xml")){
		 $smarty->assign('rss_link',URL_RSS."/{$productID}.xml");
			}*/
		$smarty->assign("product_extra", $extra);
		$smarty->assign("product_related", $related);
		$smarty->assign('vote_completed', (isset($_SESSION["vote_completed"][ $productID ])&&$_SESSION["vote_completed"][ $productID ])?1:0);
	}
}

set_query('&picture_id=', '', true);
//EOF