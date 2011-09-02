<?php
include_once DIR_FUNC.'/func.product_settings.php';

class prdsetActions extends ActionsController {

	function save_product(){
		$action_source = $this->__action_source;
		$productID = $this->getData('productID');
		$categoryID = $this->getData('categoryID');
			

		$errors = array();
		$category_need_update = array();

		do{
			/**
			 * Product info
			 */
			

			$make_slug = false;
			$product = GetProduct($productID);
			if(!isset($product['productID'])||!$product['productID']){

				$productID = prdCreateEmptyProduct();
			}elseif(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
				include_once(WBS_DIR.'/kernel/classes/class.metric.php');

				$DB_KEY=SystemSettings::get('DB_KEY');
				$U_ID = sc_getSessionData('U_ID');

				$metric = metric::getInstance();
				$metric->addAction($DB_KEY, $U_ID, 'SC','EDITPRODUCT','ACCOUNT', '');

			}
			if($this->getData('make_slug') && !$this->getData('slug')){

				$this->setData('slug', make_slug(LanguagesManager::ml_getFieldValue('name', $this->getData())));
				$make_slug = $this->getData('slug')!=='';
			}else{

				$this->setData('slug', make_slug($this->getData('slug')));
			}

			$this->setData('productID', $productID);

			$productEntry = new Product();
			$productEntry->loadByID($productID);

			$category_need_update[] = $productEntry->categoryID;
			if($this->getData('categoryID') != $productEntry->categoryID)$category_need_update[] = $this->getData('categoryID');

			$productEntry->loadFromArray($this->getData());
			$productEntry->enabled = !$this->getData('product_invisible');
			$productEntry->ordering_available = $this->getData('ordering_available');
			$productEntry->free_shipping = $this->getData('free_shipping');

			if(intval($productEntry->min_order_amount)<1)$productEntry->min_order_amount = 1;
			$max_i = 50;$_slug = $productEntry->slug;
			while($max_i-- && $make_slug && !$productEntry->__isAvailableSlug($_slug)){
				$_slug = $productEntry->slug.'_'.rand_name(2);
			}
			if(!$max_i){
				$_slug .= '_'.rand_name(2);
			}
			$productEntry->slug = $_slug;
			$res = $productEntry->checkInfo();
			if(PEAR::isError($res))break;
			$isDownloadable = $this->getData('ProductIsProgram');
			if(!$isDownloadable&&$productEntry->eproduct_filename){
				$file_path = DIR_PRODUCTS_FILES.'/'.$productEntry->eproduct_filename;
				if(file_exists($file_path)){
					$res = Functions::exec('file_remove', array($file_path));
					if(PEAR::isError($res))break;
				}
				$productEntry->eproduct_filename = '';
			}
			$res = $productEntry->save();
			if(PEAR::isError($res))break;

			/**
			 * Tags
			 */
			$TagManager = &ClassManager::getInstance('tagmanager');
			/* @var $TagManager TagManager */
			$TagManager->saveTags(TAGGEDOBJECT_PRODUCT, $productID, 'tags', $this->getData());

			/**
			 * Related products
			 */
			$related_products = $this->getData('related_products');
			db_phquery('DELETE FROM ?#RELATED_PRODUCTS_TABLE WHERE Owner=?', $productID);
			if(is_array($related_products)){
				for ($r = count($related_products)-1;$r>=0;$r--){

					db_phquery('INSERT INTO ?#RELATED_PRODUCTS_TABLE (productID, Owner) VALUES (?,?)',$related_products[$r], $productID);
				}
			}

			/**
			 * Appended categories
			 */
			$old_app_cats = catGetAppendedCategoriesToProduct($productID);
			foreach ($old_app_cats as $old_app_cat){

				catRemoveProductFromAppendedCategory($productID, $old_app_cat['categoryID'] );
				catUpdateProductCount($productID, $old_app_cat['categoryID'], -1);
			}

			$appended_categories = $this->getData('appended_categories');
			if(is_array($appended_categories)){
				for ($r = count($appended_categories)-1;$r>=0;$r--){

					catAddProductIntoAppendedCategory($productID, $appended_categories[$r] );
					if ( CONF_UPDATE_GCV == '1' )catUpdateProductCount($productID, $appended_categories[$r]);
				}
			}

			/**
			 * Upload picture
			 */
			if(!intval($this->getData('skip_image_upload'))){
				$res = $this->upload_picture(ACTCTRL_POST);
				if(PEAR::isError($res))break;
			}

			$res = $this->update_pictures_priority(ACTCTRL_POST);
			if(PEAR::isError($res))break;

			/**
			 * Extra options
			 */
			cfgUpdateOptionValue($productID, scanArrayKeysForID($this->getData(), array( 'option_value_\w{2}', 'option_radio_type' )));

			/**
			 * Downloadable options
			 */
			if($action_source != ACTCTRL_AJAX){
				$res = $this->upload_file(ACTCTRL_POST);
				if(PEAR::isError($res))break;
			}
			if(LanguagesManager::ml_isEmpty('name', $this->getData())){

				$errors[] = prdset_msg_fill_productname;
				break;
			}

		}while (0);
		if($action_source == ACTCTRL_AJAX){
			if(PEAR::isError($error)){

				Message::raiseAjaxMessage(MSG_ERROR, 0, $error->getMessage());die;
			}
			die;
		}

		if(PEAR::isError($res) || count($errors)){

			$error_message = (PEAR::isError($res)?$res->getMessage():'');
			foreach ($errors as $error){

				$error_message .= '<div>'.translate($error).'</div>';
			}
			if($action_source == ACTCTRL_AJAX){
				Message::raiseAjaxMessage(MSG_ERROR, 0, $error->getMessage());die;
			}else{
				Message::raiseMessageRedirectSQ(MSG_ERROR, 'productID='.$productID.'&categoryID='.$categoryID, $error_message);
			}
		}

		foreach($category_need_update as $_category)
		update_products_Count_Value_For_Categories($_category);
		$sorting = '';
		if(isset($_GET['sort'])){
			$sorting .= '&sort='.$_GET['sort'];
			if(isset($_GET['sort_order'])){
				$sorting .= '&sort_order='.$_GET['sort_order'];
			}
		}
		if($action_source == ACTCTRL_AJAX){
			die;
		}else{
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'ukey=categorygoods&productID='.$sorting, 'msg_information_save');
		}
	}

	function upload_file(){

		$product = GetProduct($this->getData('productID'));
		if(!isset($product['productID'])||!$product['productID']){

			$this->setData('productID', prdCreateEmptyProduct());
		}

		checkPath(DIR_PRODUCTS_FILES);
		$Register = &Register::getInstance();
		$FilesVar = &$Register->get(VAR_FILES);
			
		$error = null;
			
		do{

			if(!isset($FilesVar['eproduct_filename']))return ;
			if(!isset($FilesVar['eproduct_filename']['name']))return ;
			if(!$FilesVar['eproduct_filename']['name'])return;

			if($FilesVar['eproduct_filename']['error']){
				switch ($FilesVar['eproduct_filename']['error']){
					case 1:$error='Target file exceeds maximum allowed size.';break;
					case 2:$error='Target file exceeds the MAX_FILE_SIZE value specified on the upload form.';break;
					case 3:$error='Target file was not uploaded completely.';break;
					case 4:$error='No target file was uploaded.';break;
					case 6:$error='Missing a temporary folder.';break;
					case 7:$error='Failed to write target file to disk.';break;
					case 8:$error='File upload stopped by extension.';break;
				}
				if(isset($error)){
					return PEAR::raiseError($error);
				}
			}

			$file_name = $FilesVar['eproduct_filename']['name'];
			if(file_exists(DIR_PRODUCTS_FILES.'/'.$file_name))
			$file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $file_name), DIR_PRODUCTS_FILES);
			if(PEAR::isError($res = File::checkUpload($FilesVar['eproduct_filename']))||
					PEAR::isError($res = Functions::exec('file_move_uploaded', array($FilesVar['eproduct_filename']["tmp_name"], DIR_PRODUCTS_FILES.'/'.$file_name)))
			){
			/*$res = Functions::exec('file_move_uploaded', array($FilesVar['eproduct_filename']['tmp_name'], DIR_PRODUCTS_FILES.'/'.$file_name));
			if(PEAR::isError($res)){*/
				$error = $res;break;
			}

			$productEntry = new Product();
			$productEntry->loadFromArray($product);

			if( $productEntry->eproduct_filename!=$file_name && $productEntry->eproduct_filename && file_exists(DIR_PRODUCTS_FILES.'/'.$productEntry->eproduct_filename)){
				
				Functions::exec('file_remove', array(DIR_PRODUCTS_FILES.'/'.$productEntry->eproduct_filename));
			}

			$productEntry->eproduct_filename = $file_name;
			$productEntry->save();

		}while(0);
			
		if($action_source == ACTCTRL_AJAX){
			if(PEAR::isError($error)){

				Message::raiseAjaxMessage(MSG_ERROR, 0, $error->getMessage());die;
			}
			die;
		}else{

			return $error;
		}
	}

	function delete_product(){

		DeleteProduct( $this->getData('productID'));
		RedirectSQ('ukey=categorygoods&productID=');
	}

	function set_default_picture(){

		prdSetProductDefaultPicture( $this->getData('productID'), $this->getData('photoID'));
		die;
	}

	function update_pictures_priority($action_source = ACTCTRL_AJAX){
		$productID = $this->getData('productID');
		$scan_result = scanArrayKeysForID($_POST, 'priority');
		$sql = 'UPDATE ?#PRODUCT_PICTURES SET priority=? WHERE photoID=? AND productID=?';

		foreach ($scan_result as $photo_id=>$scan_info){
			if($scan_info['priority']==0){
				prdSetProductDefaultPicture($productID,$photo_id);
			}
			db_phquery($sql, $scan_info['priority'], $photo_id,$productID);
		}
		if($action_source == ACTCTRL_AJAX){
			Message::raiseAjaxMessage(MSG_SUCCESS, '', 'order_saved');
			die;
		}
	}

	function upload_picture($action_source = ACTCTRL_AJAX){

		

		$Register = &Register::getInstance();
		$FilesVar = &$Register->get(VAR_FILES);
		$FilesPostVar = &$Register->get(VAR_POST);
		$error = null;

		do{


			if(
			isset($FilesPostVar['image_source'])&&
			($FilesPostVar['image_source'] == 'file')&&
			isset($FilesVar['upload_picture'])&&
			isset($FilesVar['upload_picture']['name'])&&
			strlen($FilesVar['upload_picture']['name'])){
				$file_name = $FilesVar['upload_picture']['name'];
			}elseif(
			isset($FilesPostVar['image_source'])&&
			($FilesPostVar['image_source'] == 'url')&&
			isset($FilesPostVar['upload_picture_url'])
			&&strlen($FilesPostVar['upload_picture_url'])
			&&($FilesPostVar['upload_picture_url']!='URL')
			&&($FilesPostVar['upload_picture_url']!='http://')){

				$file_info = pathinfo($FilesPostVar['upload_picture_url']);
				$file_name = $file_info['basename'];
			}elseif($action_source == ACTCTRL_AJAX){
				$error = PEAR::raiseError(translate('str_image_not_uploaded'));
				break;
			}else{
				return;
			}

			if(!is_image($file_name)){
				$error = PEAR::raiseError(translate('prdset_msg_onlyimages'));
				break;
			}
			//print $file_name."\n";
			$file_name = xStripSlashesGPC($file_name);
			//print 'stripslashes: '.$file_name."\n";
			$file_name = str_replace('#','',urldecode($file_name));
			//print 'urldecode: '.$file_name."\n";

			if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$file_name))
			$file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $file_name), DIR_PRODUCTS_PICTURES);

			$orig_file = DIR_TEMP.'/'.getUnicFile(4, '%s', DIR_TEMP);
			if(isset($FilesVar['upload_picture'])&&strlen($FilesVar['upload_picture']['name'])){
				if(PEAR::isError($res = File::checkUpload($FilesVar['upload_picture']))||
					PEAR::isError($res = Functions::exec('file_move_uploaded', array($FilesVar['upload_picture']["tmp_name"], $orig_file)))
				){
				/*$res = Functions::exec('file_move_uploaded', array($FilesVar['upload_picture']['tmp_name'], $orig_file));
				if(PEAR::isError($res)){*/
					$error = $res;
					break;
				}
			}elseif(isset($FilesPostVar['upload_picture_url'])&&strlen($FilesPostVar['upload_picture_url'])){
				$res = Functions::exec('file_copy', array($FilesPostVar['upload_picture_url'], $orig_file));
				if(PEAR::isError($res)){
					$error = $res;
					break;
				}
			}
			
			if(!file_exists($orig_file)){
				$error = PEAR::raiseError("{$orig_file} not found", 1);
				break;
			}


			/**
			 * Standard picture
			 */
			$temp_file = DIR_TEMP.'/'.getUnicFile(4, 'img.s.%s.temp', DIR_TEMP);
			$standard_file_name = $file_name;

			if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name))
			$standard_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $file_name), DIR_PRODUCTS_PICTURES);

			if(
			PEAR::isError($res = Functions::exec('img_resize', array($orig_file, CONF_PRDPICT_STANDARD_SIZE, CONF_PRDPICT_STANDARD_SIZE, $temp_file)))
			||
			PEAR::isError($res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$standard_file_name)))
			){
				$error = $res;
				if(file_exists($temp_file)){
					unlink($temp_file);
				}
				Functions::exec('file_remove', array($orig_file));
				break;
			}
			if(file_exists($temp_file)){
				unlink($temp_file);
			}

			/**
			 * Thumbnail picture
			 */
			$temp_file = DIR_TEMP.'/'.getUnicFile(4, 'img.t.%s.temp', DIR_TEMP);
			$thumbnail_file_name = preg_replace('@\.([^\.]+)$@', '_thm.$1', $file_name);
			if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name))
			$thumbnail_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $thumbnail_file_name), DIR_PRODUCTS_PICTURES);

			if(
			PEAR::isError($res = Functions::exec('img_resize', array($orig_file, CONF_PRDPICT_THUMBNAIL_SIZE, CONF_PRDPICT_THUMBNAIL_SIZE, $temp_file)))
			||
			PEAR::isError($res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name)))
			){
				$error = $res;
				if(file_exists($temp_file)){
					unlink($temp_file);
				}
				Functions::exec('file_remove', array($orig_file));
				Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name));
				break;
			}
			if(file_exists($temp_file)){
				unlink($temp_file);
			}

			/**
			 * Enlarged picture
			 */
			$temp_file = DIR_TEMP.'/'.getUnicFile(4, 'img.e.%s.temp', DIR_TEMP);
			$orig_size = getimagesize($orig_file);
			$standard_size = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name);
			
			if(($orig_size[0]>$standard_size[0]) || ($orig_size[1]>$standard_size[1])){

				$enlarged_file_name = preg_replace('@\.([^\.]+)$@', '_enl.$1', $file_name);
				if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name))
				$enlarged_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $enlarged_file_name), DIR_PRODUCTS_PICTURES);

				if(
				PEAR::isError($res = Functions::exec('img_resize', array($orig_file, CONF_PRDPICT_ENLARGED_SIZE, CONF_PRDPICT_ENLARGED_SIZE, $temp_file)))
				||
				PEAR::isError($res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name)))
				){
					$error = $res;
					if(file_exists($temp_file)){
						unlink($temp_file);
					}
					Functions::exec('file_remove', array($orig_file));
					Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name));
					Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name));
					Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name));
					break;
				}
			}else {

				$enlarged_file_name = '';
			}
			if(file_exists($temp_file)){
				unlink($temp_file);
			}

			$product = GetProduct($this->getData('productID'));
			if(!isset($product['productID'])||!$product['productID']){
				$this->setData('productID', prdCreateEmptyProduct());
			}
			db_phquery("
				INSERT ?#PRODUCT_PICTURES (productID, filename, thumbnail, enlarged, priority)
				VALUES( ?, ?, ?, ?,?)", $this->getData('productID'), $standard_file_name, $thumbnail_file_name, $enlarged_file_name,$this->getData('upload_picture_priority'));

			/*db_phquery("
			 INSERT ?#PRODUCT_PICTURES (productID, filename, thumbnail, enlarged)
			 VALUES( ?, ?, ?, ?)", $this->getData('productID'), $standard_file_name, $thumbnail_file_name, $enlarged_file_name);
			 */
			global $_RESULT;
			global $DB_KEY;

			$_RESULT['picture']['photoID'] = db_insert_id();
			$_RESULT['picture']['thumbnail_url'] =  URL_PRODUCTS_PICTURES.'/'.$thumbnail_file_name;

			$_RESULT['picture']['thumbnail_picture']['file'] = $thumbnail_file_name;
			$_RESULT['picture']['thumbnail_picture']['size'] =sprintf('%0.0f kB',round(filesize(DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name)/1024));
			$_RESULT['picture']['thumbnail_picture']['url'] = URL_PRODUCTS_PICTURES.'/'.$thumbnail_file_name;
			list($_RESULT['picture']['thumbnail_picture']['width'], $_RESULT['picture']['thumbnail_picture']['height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name);

			/*if($this->getData('set_default')){

				prdSetProductDefaultPicture($this->getData('productID'), $_RESULT['picture']['photoID']);
				$_RESULT['picture']['is_default'] = 1;
			}*/

			$_RESULT['picture']['large_picture']['file'] = $standard_file_name;
			$_RESULT['picture']['large_picture']['size'] =sprintf('%0.0f kB',round(filesize(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name)/1024));
			$_RESULT['picture']['large_picture']['url'] = URL_PRODUCTS_PICTURES.'/'.$standard_file_name;
			list($_RESULT['picture']['large_picture']['width'], $_RESULT['picture']['large_picture']['height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name);

			if($enlarged_file_name){
				$_RESULT['picture']['enlarged_picture']['file'] = $enlarged_file_name;
				$_RESULT['picture']['enlarged_picture']['size'] =sprintf('%0.0f kB',round(filesize(DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name)/1024));
				$_RESULT['picture']['enlarged_picture']['url'] = URL_PRODUCTS_PICTURES.'/'.$enlarged_file_name;
				list($_RESULT['picture']['enlarged_picture']['width'], $_RESULT['picture']['enlarged_picture']['height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name);
			}else{
				$_RESULT['picture']['enlarged_picture'] = $_RESULT['picture']['large_picture'];
			}


			$_RESULT['productID'] = $this->getData('productID');
			if(isset($FilesPostVar['set_default'])&&intval($FilesPostVar['set_default'])==1){
				prdSetProductDefaultPicture( $this->getData('productID'), $_RESULT['picture']['photoID']);
			}
			if(file_exists($temp_file)){
				unlink($temp_file);
			}
			Functions::exec('file_remove', array($orig_file));

		}while(0);
		if($action_source == ACTCTRL_AJAX){
			if(PEAR::isError($error)){

				Message::raiseAjaxMessage(MSG_ERROR, 0, $error->getMessage());die;
			}
			die;
		}else{

			return $error;
		}
	}

	function fix_pictures()
	{
		$error = null;
		do{
			$temp_file = DIR_TEMP.'/'.getUnicFile(4, '%s', DIR_TEMP);
			/**
			 * Standard picture
			 */
			$standard_file_name = $file_name;

			if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name))
			$standard_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $file_name), DIR_PRODUCTS_PICTURES);

			$res = Functions::exec('img_resize', array($orig_file, CONF_PRDPICT_STANDARD_SIZE, CONF_PRDPICT_STANDARD_SIZE, $temp_file));

			if(PEAR::isError($res)){
				$error = $res;break;
			}

			$res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$standard_file_name));
			if(PEAR::isError($res)){

				$error = $res;
				Functions::exec('file_remove', array($temp_file));
				break;
			}
			/**
			 * Thumbnail picture
			 */
			$thumbnail_file_name = preg_replace('@\.([^\.]+)$@', '_thm.$1', $file_name);
			if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name))
			$thumbnail_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $thumbnail_file_name), DIR_PRODUCTS_PICTURES);

			$res = Functions::exec('img_resize', array(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name, CONF_PRDPICT_THUMBNAIL_SIZE, CONF_PRDPICT_THUMBNAIL_SIZE, $temp_file));
			if(PEAR::isError($res)){
				$error = $res;break;
			}

			$res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name));
			if(PEAR::isError($res)){

				$error = $res;
				Functions::exec('file_remove', array($temp_file));
				Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name));
				break;
			}

			/**
			 * Enlarged picture
			 */
		}while(0);
		if($action_source == ACTCTRL_AJAX){
			if(PEAR::isError($error)){

				Message::raiseAjaxMessage(MSG_ERROR, 0, $error->getMessage());die;
			}
			die;
		}else{

			return $error;
		}
			
	}

	function delete_picture(){

		DeleteThreePictures($this->getData('photoID'));
		die;
	}

	function main(){
		
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */
		$GetVars = &$Register->get(VAR_GET);

		$productID = isset($GetVars['productID'])? intval($GetVars['productID']): 0;

		$categoryID = isset($GetVars['categoryID'])?intval($GetVars['categoryID']):0;

		if ( $productID != 0 ){

			$product = GetProduct($productID);
		}else{
			if(SystemSettings::is_hosted()){
				$session_id = session_id();
				session_write_close();

				$messageClient = new WbsHttpMessageClient($db_key, 'wbs_msgserver.php');
				$messageClient->putData('action', 'ALLOW_ADD_PRODUCT');
				$messageClient->putData('language',(LanguagesManager::getCurrentLanguage()->iso2));
				$messageClient->putData('session_id',$session_id);
				$res=$messageClient->send();

				session_id($session_id);
				session_start();
			}else{
				$res = false;
			}

			if(!$res||$messageClient->getResult('success')==true){
				if($res&&$messageClient->getResult('msg')!=''){
					$smarty->assign('MessageBlock',"<div class='comment_block' ><span class='success_message'>".$messageClient->getResult('msg').'</span></div>');
				}

				$product = array();
				$product['categoryID'] = $categoryID;
				$product['enabled'] = 1;
				$product['priority'] = 0;
				$product['ordering_available'] = 1;
				$product['Price'] = 0;
				$product['in_stock'] = 0;
				$product['list_price'] = 0;
				$product['sort_order'] = 0;
				$product['eproduct_available_days'] = 365;
				$product['eproduct_download_times'] = 1;
				$product['weight'] = 0;
				$product['free_shipping'] = 0;
				$product['min_order_amount'] = 1;
				$product['shipping_freight'] = 0;
				$product['classID'] = CONF_DEFAULT_TAX_CLASS == '0'?'null':CONF_DEFAULT_TAX_CLASS;
			}else{
				//TO DO: localize string variable
				Message::raiseMessageRedirectSQ(MSG_ERROR,'?ukey=categorygoods&sort=&sort_dir=&search=&search_value=&categoryID='.$categoryID,$messageClient->getResult('msg')!=''?$messageClient->getResult('msg'):'Max product count ('.$messageClient->getResult('MAX_PRODUCT_COUNT').') exceeded');
			}
		}

		/**
		 * Product pictures
		 */
		$pictures = GetPictures( $productID );

		foreach ($pictures as $_ind=>$_val){
			if ( file_exists(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['filename']) && trim($pictures[$_ind]['filename']) != '' ){
				$pictures[$_ind]['picture_exists'] = 1;
				list($pictures[$_ind]['picture_width'], $pictures[$_ind]['picture_height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['filename']);
				$pictures[$_ind]['large_picture'] = array('size' => sprintf('%0.0f KB',round(filesize(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['filename'])/1024)),'file' => $pictures[$_ind]['filename'], 'width' => $pictures[$_ind]['picture_width'], 'height' => $pictures[$_ind]['picture_height']);
			}else{
				$pictures[$_ind]['large_picture'] = array('size' => 0,'file' => $pictures[$_ind]['filename'], 'width' => 0, 'height' => 0);
			}
			if ( file_exists(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['enlarged']) && trim($pictures[$_ind]['enlarged']) != '' ){
				$pictures[$_ind]['enlarged_exists'] = 1;
				list($pictures[$_ind]['enlarged_width'], $pictures[$_ind]['enlarged_height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['enlarged']);
				$pictures[$_ind]['enlarged_picture'] = array('size' => sprintf('%0.0f KB',round(filesize(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['enlarged'])/1024)),'file' => $pictures[$_ind]['enlarged'], 'width' => $pictures[$_ind]['enlarged_width'], 'height' => $pictures[$_ind]['enlarged_height']);
			}elseif($pictures[$_ind]['picture_exists'] == 1){
				$pictures[$_ind]['enlarged_exists'] = 1;
				$pictures[$_ind]['enlarged_picture'] = $pictures[$_ind]['large_picture'];
			}else{
				$pictures[$_ind]['enlarged_picture'] = array('size' =>0,'file' => $pictures[$_ind]['enlarged'], 'width' => 0, 'height' => 0);
			}

			if ( file_exists(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['thumbnail'])&& trim($pictures[$_ind]['thumbnail']) != '' ){
				$pictures[$_ind]['thumbnail_exists'] = 1;
				list($pictures[$_ind]['thumbnail_width'], $pictures[$_ind]['thumbnail_height']) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['thumbnail']);
				$pictures[$_ind]['thumbnail_picture'] = array('size' => sprintf('%0.0f KB',round(filesize(DIR_PRODUCTS_PICTURES.'/'.$pictures[$_ind]['thumbnail'])/1024)),'file' => $pictures[$_ind]['thumbnail'], 'width' => $pictures[$_ind]['thumbnail_width'], 'height' => $pictures[$_ind]['thumbnail_height']);
			}else{
				$pictures[$_ind]['thumbnail_picture'] = array('size' =>0,'file' => $pictures[$_ind]['thumbnail'], 'width' => 0, 'height' => 0);
			}

		}

		if(file_exists(DIR_PRODUCTS_FILES.'/'.$product['eproduct_filename']) && $product['eproduct_filename']!=null )$product['eproduct_exists'] = 1;

		if($productID) {

			$RelatedItems = array();
			$q = db_phquery('SELECT productID FROM ?#RELATED_PRODUCTS_TABLE WHERE Owner=?',$productID);
			while ($r = db_fetch_row($q)){

				$p = db_query('SELECT productID, '.LanguagesManager::sql_prepareField('name').' AS name FROM '.PRODUCTS_TABLE.' WHERE productID='.$r[0]);
				if ($r1 = db_fetch_row($p)){

					$RelatedItems[] = $r1;
				}
			}
			$smarty->assign('RelatedItemsNumber',count($RelatedItems));
			$smarty->assign('RelatedItems', $RelatedItems);
		}

		if($product['eproduct_filename'] && file_exists(DIR_PRODUCTS_FILES.'/'.$product['eproduct_filename'])){

			$product['eproduct_filesize'] = filesize(DIR_PRODUCTS_FILES.'/'.$product['eproduct_filename']);
			$product['eproduct_filesize_str'] = getDisplayFileSize($product['eproduct_filesize'], 'B');
		}else{
			$product['eproduct_filename'] = '';
		}

		$tagManager = &ClassManager::getInstance('tagmanager');
		/*@var $tagManager tagmanager*/
		$defaultCurrency = &Currency::getDefaultCurrencyInstance();

		$product_category = catGetCategoryById($product['categoryID']);
		$product_category['calculated_path'] = catCalculatePathToCategory($product_category['categoryID']);
		$smarty->assign('product_tags', $tagManager->getObjectTagsStrings(TAGGEDOBJECT_PRODUCT, $productID, 'tags'));
		$smarty->assign('tags_cloud', $tagManager->getTagsCloud('tags', TAGGEDOBJECT_PRODUCT));
		$smarty->assign('pictures',$pictures);
		$smarty->assign('appended_categories', catGetAppendedCategoriesToProduct($productID, true));
		$smarty->assign('eproduct_available_days',array(1,2,3,4,5,7,14,30,180,365));
		$options = cfgGetProductOptionValue( $productID );
		if(count($options)>0)$smarty->assign('options',$options);
		$smarty->assign('core_category', $core_category);
		$smarty->assign('product',$product);
		$smarty->assign('product_category', $product_category);
		$smarty->assign('tax_classes', taxGetTaxClasses());
		$smarty->assign('is',$product['in_stock']);
		$smarty->assign('default_currency', $defaultCurrency->getVars());

		$smarty->assign('admin_sub_dpt', 'product_settings.html');
	}
}

ActionsController::exec('prdsetActions');
?>