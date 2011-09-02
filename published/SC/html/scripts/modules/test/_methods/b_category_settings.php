<?php
class CategorySettingsController extends ActionsController{

	function __getOptions()
	{
		$categoryID = isset($_GET['categoryID'])?intval($_GET['categoryID']):false;
		$options = optGetOptions();
		$option_values = optGetOptionValues();

		foreach($options as &$option)
		{
			$optionID = $option['optionID'];
			if ( $categoryID ){
				$res = schOptionIsSetToSearch($categoryID, $optionID );
			}else{
				$res = array( 'isSet' => false, 'set_arbitrarily' => 1 );
			}
			if ( $res['isSet'] ){
				$option['isSet'] = true;
				$option['set_arbitrarily'] = $res['set_arbitrarily'];
			}else{
				$option['isSet'] = false;
				$option['set_arbitrarily'] = 1;
			}

			$option['variants'] = array();
			if(isset($option_values[$optionID])){
				$option['variants'] = $option_values[$optionID];
				foreach($option['variants'] as &$variant){
					$variant['isSet'] = $categoryID&&schVariantIsSetToSearch($categoryID, $optionID, $variant['variantID'] );
				}
			}
			unset($variant);

		}
		unset($option);
		return $options;
	}

	function remove_picture(){
			
		safeMode(true);

		$sql = '
				SELECT picture FROM ?#CATEGORIES_TABLE WHERE categoryID=? and categoryID<>1
			';
		$r = db_phquery_fetch(DBRFETCH_ROW, $sql, $_GET["categoryID"]);
		if ($r[0] && file_exists(DIR_PRODUCTS_PICTURES."/{$r[0]}"))
		Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/{$r[0]}"));

		$sql = '
				UPDATE ?#CATEGORIES_TABLE SET picture="" WHERE categoryID=?
			';
		db_phquery($sql, $_GET["categoryID"]);
		RedirectSQ('');
	}

	function delete_category(){
			
		safeMode(true);
		$res = catDeleteCategory( $_GET['categoryID'] );
		RedirectSQ('?ukey=categorygoods');
	}

	function save_category(){

		$res = null;do{

			if(LanguagesManager::ml_isEmpty('name', $this->getData())){
				$res = PEAR::raiseError('catset_empty_name');
				break;
			}

			$categoryID = isset($_GET['categoryID'])?intval($_GET['categoryID']):0;
			$make_slug = false;
			if(!$categoryID){

				db_phquery('INSERT ?#CATEGORIES_TABLE (sort_order) VALUES(0)');
				$categoryID = db_insert_id('CATEGORIES_GEN');
				renderURL('categoryID='.$categoryID, '', true);

				if(!$this->getData('slug')){
					$make_slug = true;
				}
			}
			//if category is being moved to any of it's subcategories - it's
			//neccessary to 'lift up' all it's subcategories
			if($this->getData('parent') == $categoryID){

				$original_category_info = catGetCategoryById($categoryID);
				$this->setData('parent', $original_category_info['parent']);
			}
			if (catMoveBranchCategoriesTo($categoryID, $this->getData('parent'))){
				//lift up is required

				//get parent
				$r = db_phquery_fetch(DBRFETCH_FIRST ,'SELECT parent FROM ?#CATEGORIES_TABLE WHERE categoryID<>1 and categoryID=?', $categoryID);
				//lift up
				db_phquery('UPDATE ?#CATEGORIES_TABLE SET parent=? WHERE parent=?', $r, $categoryID);
			}

			if($make_slug){

				$this->setData('slug', make_slug(LanguagesManager::ml_getFieldValue('name', $this->getData())));
				$make_slug = $this->getData('slug')!=='';
			}else{

				$this->setData('slug', make_slug($this->getData('slug')));
			}

			$categoryEntry = new Category;
			$categoryEntry->loadByID($categoryID);
			$categoryEntry->loadFromArray($this->getData());
			$categoryEntry->allow_products_comparison = $this->getData('allow_products_comparison');
			$categoryEntry->allow_products_search = $this->getData('allow_products_search');
			$categoryEntry->show_subcategories_products = $this->getData('show_subcategories_products');

			$max_i = 50;$_slug = $categoryEntry->slug;
			while($max_i-- && $make_slug && !$categoryEntry->__isAvailableSlug($_slug)){
				$_slug = $categoryEntry->slug.'_'.rand_name(2);
			}
			if(!$max_i){
				$_slug .= '_'.rand_name(2);
			}
			$categoryEntry->slug = $_slug;

			$res = $categoryEntry->checkInfo();
			if(PEAR::isError($res))break;

			$res = $categoryEntry->save();
			if(PEAR::isError($res))break;

			//update products count value if defined
			if (CONF_UPDATE_GCV == 1){
				update_products_Count_Value_For_Categories(1);
			}

			// update search option settings
			schUnSetOptionsToSearch( $categoryID );
			$data = scanArrayKeysForID($_POST, array("checkbox_param") );
			foreach( $data as $optionID => $val ){

				schUnSetVariantsToSearch( $categoryID, $optionID );
				$set_arbitrarily = $this->existsData("select_arbitrarily_$optionID")?$this->getData("select_arbitrarily_$optionID"):1;
				schSetOptionToSearch( $categoryID, $optionID, $set_arbitrarily );
				if ( $set_arbitrarily != 0 )continue;

				$variants = optGetOptionValues( $optionID );
				foreach( $variants as $var ){
					if(!$this->existsData("checkbox_variant_".$var["variantID"]))continue;
					schSetVariantToSearch( $categoryID, $optionID, $var["variantID"] );
				}
			}

			if (isset($_FILES["picture"]) && $_FILES["picture"]["name"] && is_image($_FILES["picture"]["name"])){ //upload category thumbnail

				//old picture
				$old_picture = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT picture FROM ?#CATEGORIES_TABLE WHERE categoryID=? and categoryID<>0', $categoryID);
				if($old_picture)Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/{$old_picture}"));

				//upload new photo
				$picture_name = str_replace(" ","_", $_FILES["picture"]["name"]);
				$picture_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $picture_name), DIR_PRODUCTS_PICTURES);

				$res = Functions::exec('file_move_uploaded', array($_FILES["picture"]["tmp_name"], DIR_TEMP."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());

				$res = Functions::exec('img_resize', array(DIR_TEMP."/$picture_name", CONF_CATPICT_SIZE, CONF_CATPICT_SIZE, DIR_TEMP."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
					
				$res = Functions::exec('file_copy', array(DIR_TEMP."/$picture_name", DIR_PRODUCTS_PICTURES."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());

				Functions::exec('file_remove', array(DIR_TEMP."/$picture_name"));

				SetRightsToUploadedFile( DIR_PRODUCTS_PICTURES."/$picture_name" );
				$sql = '
					UPDATE ?#CATEGORIES_TABLE SET picture=? WHERE categoryID=?
				';
				db_phquery($sql, $picture_name, $categoryID);
			}
		}while(0);

		if(PEAR::isError($res)){

			Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '', array('post_data' => $this->getData()));
		}else{

			Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=categorygoods&categoryID=', 'msg_information_save');
		}
	}

	function main(){
			
		safeMode( isset($_POST) && count($_POST)>0 );
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */
			
		$categoryID = intval($this->getData('categoryID'));
		$CategoryInfo = catGetCategoryById($categoryID);

		if(!isset($CategoryInfo['categoryID'])){

			$smarty->assign('Title', translate("prdcat_category_new"));
			$CategoryInfo = array(
					'picture' => '',
					'sort_order' => 0, 
					'allow_products_comparison' => 1,
					'show_subcategories_products' => 1,
					'allow_products_search' => 1,
					'parent' => isset($_GET['parent'])?$_GET['parent']:1,
			);
		}else {
			$smarty->assign('Title', $CategoryInfo['name']);
		}

		if ($CategoryInfo['picture'] != '' && file_exists(DIR_PRODUCTS_PICTURES."/".$CategoryInfo['picture'])){

			list( $width, $height, $type, $attr ) = getimagesize( DIR_PRODUCTS_PICTURES."/".$CategoryInfo['picture'] );
			$width += 40;
			$height += 40;
			$CategoryInfo['picture_href'] = "open_window('products_pictures/{$CategoryInfo['picture']}',$width,$height);return false;";
		}else {
			$CategoryInfo['picture'] = '';
		}

		$options = $this->__getOptions();
		$showSelectParametrsTable = 0;
		if ( isset($_GET["SelectParametrsHideTable_hidden"]) )$showSelectParametrsTable = $_GET["SelectParametrsHideTable_hidden"];
			
		$smarty->assign('Options', $options);
		$smarty->assign('showSelectParametrsTable', $showSelectParametrsTable);

		$messageEntry = $Register->get(VAR_MESSAGE);
		if(Message::isMessage($messageEntry) && isset($messageEntry->post_data)){

			$smarty->assign('CategoryInfo', $messageEntry->post_data);
		}else{

			$smarty->assign('CategoryInfo', $CategoryInfo);
		}
		$CategoryInfo = $smarty->get_template_vars('CategoryInfo');
			
		$parent_category = catGetCategoryById($CategoryInfo['parent']) ;
		$parent_category['calculated_path'] = catCalculatePathToCategory($parent_category['categoryID']);
		if(defined('CONF_VKONTAKTE_ENABLED')&&constant('CONF_VKONTAKTE_ENABLED')){
			$vkontakte_types = array(
			1 => 'Другое',
			2 => 'Электроника',
			3 => 'Мобильные телефоны',
			4 => 'Фото- и видеокамеры',
			5 => 'Компьютеры',
			6 => 'CD/DVD/BluRay',
			7 => 'Книги',
			8 => 'Мебель',
			9 => 'Одежда и обувь',
			10 => 'Спортивный инвентарь',
			11 => 'Музыкальные инструменты',
			);
			$smarty->assign('vkontakte_types', $vkontakte_types);
		}
			
		$smarty->assign('categoryID', $categoryID);
		$smarty->assign('parent_category', $parent_category);
		$smarty->assign('admin_sub_dpt', 'category.html');
	}
}

ActionsController::exec('CategorySettingsController');
?>