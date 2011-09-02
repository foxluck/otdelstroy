<?php
	$Register = &Register::getInstance();
	/*@var $Register Register*/
	$Message = $Register->get(VAR_MESSAGE);
	/*@var $Message Message*/

	// product discussion page
	if(isset($_GET['productID'])){
		$product = new Product();
		$productID = max(intval($_GET['productID']),0);
		if (!$product->loadByID($productID)||!$product->enabled){
			error404page();
			//RedirectSQ("?ukey=product_not_found");
			
		}elseif(CONF_ENABLE_CONFIRMATION_CODE) {
			if(false) {
				if(!isset($_SESSION['ss.product_discussion'])) {
					$_SESSION['ss.product_discussion'] = array();
				}
				if(!isset($_SESSION['ss.product_discussion_timestamps'])) {
					$_SESSION['ss.product_discussion_timestamps'] = array();
				}
				if(!isset($_SESSION['ss.product_discussion'][$productID])) {
					$_SESSION['ss.product_discussion'][$productID] = time();
				}
			}
		}
	}else{
		error404page();
		//RedirectSQ("?ukey=product_not_found");
	}

	if (isset($_POST["add_topic"])){ // add post to the product discussion

		if(CONF_ENABLE_CONFIRMATION_CODE) {
			if(true) {
				$iVal = new IValidator();
				if(!$iVal->checkCode($_POST['fConfirmationCode'])){
					Message::raiseMessageRedirectSQ(MSG_ERROR, '#add-review', "err_wrong_ccode", '', array('topic_data' => $_POST));
				}
			}else {
				$time = time();
				//TODO add settings
				$iVal = new IValidator();
				if(!$iVal->checkCode($_POST['fConfirmationCode'])){
					Message::raiseMessageRedirectSQ(MSG_ERROR, '#add-review', "err_wrong_ccode", '', array('topic_data' => $_POST));
				}else if($time - ($_SESSION['ss.product_discussion'][$productID]) < 45) { //limit time between page view and POST 
					Message::raiseMessageRedirectSQ(MSG_ERROR, '#add-review', "err_too_fast", '', array('topic_data' => $_POST));
				}elseif(count($_SESSION['ss.product_discussion_timestamps'])>3) { //limit reviews count by time period
					$qty = 0;
					foreach($_SESSION['ss.product_discussion_timestamps'] as $id=>$timestamp) {
						if(($time-$timestamp)>3600) {
							unset($_SESSION['ss.product_discussion_timestamps'][$id]);
						}elseif(($time-$timestamp)<300) { //count reviews per 5 minut
							++$qty;
						}
						if($qty>3) {
							//Too fast 
							Message::raiseMessageRedirectSQ(MSG_ERROR, '#add-review', "err_too_much_posts", '', array('topic_data' => $_POST));
						}
					}
						
				}else {
					$_SESSION['ss.product_discussion'][$productID] = time();
				}
			}
		}
		discAddDiscussion( $productID, $_POST["nick"], $_POST["topic"], $_POST["body"] );
		RedirectSQ('productID='.$productID.'&ukey=discuss_product');
	}

	$smarty->assign('productID', $productID);
	$smarty->assign("discuss","yes");
	
	$smarty->hassign("product_name", $product->name);

	$gridEntry = new Grid();
	
	$gridEntry->rows_num = 10;
	$gridEntry->show_rows_num_select = false;
	
	$gridEntry->registerHeader('', 'add_time', true, 'desc');
	
	$gridEntry->query_select_rows = 'SELECT * FROM ?#DISCUSSIONS_TABLE WHERE productID='.intval($productID);
	$gridEntry->query_total_rows_num = 'SELECT COUNT(*) FROM ?#DISCUSSIONS_TABLE WHERE productID='.intval($productID);
	$gridEntry->setRowHandler('
		$row["add_time_str"] = Time::standartTime($row["add_time"]);
		return $row;
	');

	$gridEntry->prepare();
	
	if(Message::isMessage($Message) && $Message->is_set()){
		
		$smarty->assign('new_topic', $Message->topic_data);
	}
	
	$smarty->assign('conf_image', URL_ROOT.'/imgval.php?'.generateRndCode(4).'=1');

	
	//$product_info = GetProduct($productID);
	$product_info = $product->getVars();
	$q = db_query("SELECT categoryID, ".LanguagesManager::sql_prepareField('name')." AS name, ".LanguagesManager::sql_prepareField('description')." AS description, picture FROM ".CATEGORIES_TABLE." WHERE categoryID=".intval($product_info['categoryID'])) or die (db_error());
	$row = db_fetch_row($q);
	if ($row){
		
		if (!file_exists(DIR_PRODUCTS_PICTURES."/".$row[3])) $row[3] = "";
		$smarty->assign("selected_category", $row);
	}
	$smarty->assign("product_category_path",  catCalculatePathToCategory( $product_info['categoryID'] ) );
	$smarty->assign("main_content_template", "product_discussion.html");

?>
