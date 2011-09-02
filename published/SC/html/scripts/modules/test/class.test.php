<?php
#удалить потом
if(!function_exists('add_department')){
	function add_department($admin_dpt)
	//adds new $admin_dpt to departments list
	{
		global $admin_departments;

		$i = 0;
		while ($i<count($admin_departments) && $admin_departments[$i]["sort_order"] < $admin_dpt["sort_order"]) $i++;
		for ($j=count($admin_departments)-1; $j>=$i; $j--)
		$admin_departments[$j+1] = $admin_departments[$j];
		$admin_departments[$i] = $admin_dpt;
	}
}

if(!class_exists('test',false)){

	function cptsettingview_divisionsgroup($params){

		$params['options'] = array();
		$SubDivs = &DivisionModule::getBranchDivisions(DivisionModule::getDivisionIDByUnicKey('TitlePage'), array('xEnabled'=>1));
		foreach ($SubDivs as $_SubDiv){

			$params['options'][$_SubDiv->ID] = translate($_SubDiv->Name);
		}

		if(is_string($params['value']))$params['value'] = explode(':', $params['value']);

		return cptsettingview_checkboxgroup($params);
	}

	function cptsettingserializer_divisionsgroup($params, $post){

		$Register = &Register::getInstance();

		if(!$Register->is_set('__DIVNAV_SERIALIZED') && is_array($post[$params['name']])){
			$post[$params['name']] = implode(':', $post[$params['name']]);
			$reg = 1;
			$Register->set('__DIVNAV_SERIALIZED', $reg);
		}
		return cptsettingserializer_checkboxgroup($params, $post);
	}

	class Test extends ComponentModule {
		var $CategoriesColumns=1;
		var $show_sub_category=true;
		var $SubCategoriesDelimeter = ' ';
		function initSettings(){

			$this->Settings = array(

			array(
			'name' => 'CONF_TESTI_DAY',
			'value' => '23',
			'type' => SETTING_NUMBER,
			'descr' => 'пробная настройка'
			),
			'categories_col_num'=>array('name' => 'categories_col_num',
			'value' => '1',
			'type' => SETTING_NUMBER,
			'descr' => 'Columns per page in the list of categories'),
			'subcategories_delimiter'=>array('name' => 'subcategories_delimiter',
			'value' => ' ',
			'type' => SETTING_CUSTOM,
			'descr' => 'Sub category delimeter'),
			'show_sub_category'=>array('name' => 'show_sub_category',
			'value' => '1',
			'type' => SETTING_CUSTOM,
			'descr' => 'Show sub categories'),
			);
			parent::initSettings();
		}

		function initInterfaces(){

			$this->Interfaces = array(
			'auth' => array(
			'name' => 'Login page',
			'method' => 'method_login'
			),
			'logout' => array(
			'name' => 'Logout page',
			'method' => 'method_logout'
			),
			'b_categories_products' => array(
			'name' 	=> 'Каталог товаров (администрирование)',
			),
			'SynchronizeDB' => array(
			'name' 	=> 'SynchronizeDB',
			'method' => 'methodSynchronizeDB',
			),
			'b_regfields' => array(
			'name' 	=> 'Настройка формы регистрации',
			),
			'b_catalog_discuss' => array(
			'name' 	=> 'Обсуждения продуктов',
			),
			'b_settings' => array(
			'name' 	=> 'Настройки (администрирование)',
			),
			'FrontendTitle' => array(
			'name' 	=> 'FrontendTitle',
			'method' => 'methodFrontendTitle',
			),
			'categorytree' => array(
			'name' => 'Дерево категорий',
			'method' => 'methodCategoryTree'
			),
			'fcurrency' => array(
			'name' => 'Валюты',
			'method' => 'methodFCurrencies',
			),
			'bcurrency' => array(
			'name' => 'Валюты(администрирование)',
			'method' => 'methodBCurrencies',
			),
			'user_account' => array(
			'name' => 'Мой счет',
			'method' => 'methodUserAccount',
			),
			'short_contact_info' => array(
			'name' => 'Контакная информация - кратко',
			'method' => 'methodShortContactInfo',
			),
			'contact_info' => array(
			'name' => 'Контакная информация',
			'method' => 'methodContactInfo',
			),
			'short_address_book' => array(
			'name' => 'Адресная книга - кратко',
			'method' => 'methodShortAddressBook',
			),
			'address_book' => array(
			'name' => 'Адресная книга',
			'method' => 'methodAddressBook',
			),
			'add_address' => array(
			'name' => 'Добавить адрес',
			'method' => 'methodAddAddress',
			),
			'edit_address' => array(
			'key' => 'edit_address',
			'name' => 'Редактировать адрес',
			'method' => 'methodEditAddress',
			),
			'short_orders_history' => array(
			'name' => 'История заказов - кратко',
			'method' => 'methodShortOrdersHistory',
			),
			'orders_history' => array(
			'name' => 'pgn_order_history',
			'method' => 'methodOrdersHistory',
			),
			'order_detailed' => array(
			'name' => 'Информация о заказе(кабинет пользователя)',
			'method' => 'methodFOrderDetailed',
			),
			'order_status' => array(
			'name' => 'Информация о заказе(общий доступ)',
			'method' => 'methodFOrderDetailed',
			),
			'fregister' => array(
			'name' => 'Регистрация',
			'method' => 'methodFRegister',
			),
			'fsuccessful_registration' => array(
			'name' => 'Успешная регистрация',
			'method' => 'methodFSuccessfulRegistration',
			),
			'bcountries'=> array(
			'name' => 'Страны (администрирование)',
			'method' => 'methodBCountries',
			),
			'bzones'=> array(
			'name' => 'Области (администрирование)',
			'method' => 'methodBZones',
			),
			'btaxes'=> array(
			'name' => 'Налоги (администрирование)',
			'method' => 'methodBTaxes',
			),
			'busers_group'=> array(
			'name' => 'Группы пользователей (администрирование)',
			'method' => 'methodBUsersGroup',
			),
			'b_export_products2csv'=> array(
			'name' => 'Экспорт товаров в CSV',
			),
			'b_import2csv'=> array(
			'name' => 'Импорт товаров из CSV',
			),
			'b_product_options'=> array(
			'name' => 'Дополнительные характеристики товаров',
			),
			'b_reports_vcategories'=> array(
			'name' => 'Самые просматриваемые категории',
			),
			'b_reports_products'=> array(
			'name' => 'Отчет по продуктам',
			),
			'b_reports_custlog'=> array(
			'name' => 'Журнал авторизации пользователей',
			),
			'b_category_settings'=> array(
			'name' => 'Настройки категории',
			),
			'printable'=> array(
			'name' => 'Версия для печати',
			),
			'search_simple'=> array(
			'name' => 'Simple search',
			),
			'category_search_result'=> array(
			'name' => 'Результаты поиска в категории',
			),
			);

			$this->__registerComponent('maincontent', 'cpt_lbl_main_content', array(TPLID_GENERAL_LAYOUT));
			$this->__registerComponent('category_tree', 'cpt_lbl_category_tree', array(TPLID_GENERAL_LAYOUT, TPLID_HOMEPAGE));
			$this->__registerComponent('authorization', 'cpt_lbl_authorization', array(TPLID_GENERAL_LAYOUT, TPLID_HOMEPAGE), '__display_template');
			$this->__registerComponent('shopping_cart_info', 'cpt_lbl_shopping_cart_info', array(TPLID_GENERAL_LAYOUT));
			$this->__registerComponent('logo', 'cpt_lbl_logo', array(TPLID_GENERAL_LAYOUT), null, array('file' => array('type' => 'image_file', 'params' => array('name' => 'file', 'current_file_title'=> 'Current logo image:', 'upload_file_title'=> 'Upload new logo:', 'value'=>'logo.gif'))));
			$this->__registerComponent('product_search', 'cpt_lbl_product_search', array(TPLID_GENERAL_LAYOUT, TPLID_HOMEPAGE), '__display_template');
			$this->__registerComponent('currency_selection', 'cpt_lbl_currency_selection', array(TPLID_GENERAL_LAYOUT, TPLID_HOMEPAGE), '__display_template');
			$this->__registerComponent('language_selection', 'cpt_lbl_language_selection', array(TPLID_GENERAL_LAYOUT, TPLID_HOMEPAGE), null,
			array('view' => array('type' => 'radiogroup', 'params' => array('name' => 'view', 'title'=> 'Select view:','value'=> 'select', 'options'=> array('select' => 'Select', 'flags' => 'Flags')))));
			$this->__registerComponent('product_category_info', 'cpt_lbl_category_info', array(), '__display_template');
			$this->__registerComponent('product_images', 'cpt_lbl_product_images', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('product_details_request', 'cpt_lbl_product_details_request', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('product_related_products', 'cpt_lbl_product_related_products', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('product_add2cart_button', 'cpt_lbl_product_add2cart_button', array(TPLID_PRODUCT_INFO),'__display_template',//null,
			array(
			'request_product_count' =>
			array('type' => 'checkboxgroup', 'params' =>
			array('name' => 'request_product_count',
			'options'=>array('request_product_count'=>translate('cpt_lbl_request_product_count')),
			'value'=>array()),
			)
			));// '__display_template');
			$this->__registerComponent('product_description', 'cpt_lbl_product_description', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('product_discuss_link', 'cpt_lbl_product_discuss_link', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('product_rate_form', 'cpt_lbl_product_rate_form', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('product_name', 'cpt_lbl_product_name', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('product_price', 'cpt_lbl_product_price', array(TPLID_PRODUCT_INFO), '__display_template');
			$this->__registerComponent('root_categories', 'cpt_lbl_root_categories',
		 array(TPLID_HOMEPAGE),null,
		 array(
		 'categories_col_num' => array('type' => 'text', 'params' => array('name' => 'categories_col_num', 'title'=> translate('cpt_lbl_category_col_count'), 'value'=>$this->CategoriesColumns)),
		 'show_sub_category' => array('type' => 'checkboxgroup', 'params' => array('name' => 'show_sub_category', 'options'=>array('enable_sub_category'=>translate('cpt_lbl_show_sub_category')),'value'=>!$this->show_sub_category?array('enable_sub_category'):array())),
		 'subcategories_numberlimit' => array('type' => 'text', 'params' => array('name' => 'subcategories_numberlimit', 'title'=> translate('subcategories_numberlimit'), /*'value'=>$this->CategoriesColumns*/)),
		 'subcategories_delimiter' => array('type' => 'text', 'params' => array('name' => 'subcategories_delimiter', 'title'=> translate('subcategories_delimiter'), 'value'=>$this->SubCategoriesDelimeter))
		 ));
		 $this->__registerComponent('product_params_fixed', 'cpt_lbl_product_params_fixed', array(TPLID_PRODUCT_INFO), '__display_template');
		 $this->__registerComponent('product_params_selectable', 'cpt_lbl_product_params_selectable', array(TPLID_PRODUCT_INFO), '__display_template');

		 $this->__registerComponent('divisions_navigation', 'cpt_lbl_divisions_navigation', array(TPLID_GENERAL_LAYOUT), null,
			array(
			'divisions' => array('type' => 'divisionsgroup', 'params' => array('name' => 'divisions', 'title'=> 'cpt_lbl_selectdivisions','value'=> '', 'options'=> array())),
			'view' => array('type' => 'radiogroup', 'params' => array('name' => 'view', 'title'=> 'cpt_lbl_view','value'=> 'vertical', 'options'=> array('vertical' => 'cpt_lbl_vertical', 'horizontal' => 'cpt_lbl_horizontal'))),
			));
		}
		function cpt_category_tree(){

			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/*@var $smarty Smarty*/

			if(!is_array($smarty->get_template_vars('categories_tree'))){

				$this->methodCategoryTree();
			}

			print $smarty->fetch('category_tree.html');
		}

		function cpt_root_categories($call_settings = null){
			$local_settings = isset($call_settings['local_settings'])?$call_settings['local_settings']:array();

			//'subcategories_numberlimit'
			if(isset($local_settings['subcategories_numberlimit'])){
				$subcategories_numberlimit = $local_settings['subcategories_numberlimit'];
			}else{
				$subcategories_numberlimit = 0;
			}
			if(isset($local_settings['categories_col_num'])&&$local_settings['categories_col_num']){
				$this->CategoriesColumns = $local_settings['categories_col_num'];
			}
			if(isset($local_settings['subcategories_delimiter'])&&$local_settings['subcategories_delimiter']){
				$this->SubCategoriesDelimeter = $local_settings['subcategories_delimiter'];
			}
			if(isset($local_settings['show_sub_category'])&&$local_settings['show_sub_category']){
				$this->show_sub_category = in_array('enable_sub_category',explode(':',$local_settings['show_sub_category']));
			}else{
				$this->show_sub_category = false;
			}
			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$smarty = &$Register->get(VAR_SMARTY);
			/*@var $smarty Smarty*/

			// front-end homepage
			//get root categories to be shown in the front-end homepage
			$q = db_query("SELECT categoryID, ".LanguagesManager::sql_prepareField('name')." AS name, products_count, picture, slug FROM ".
			CATEGORIES_TABLE." WHERE categoryID<>0 and parent=1 ORDER BY sort_order, name") or die (db_error());
			$root = array();
			while ($row = db_fetch_assoc($q)){

				if (!file_exists(DIR_PRODUCTS_PICTURES."/{$row['picture']}"))$row['picture'] = '';
				$root[] = $row;
			}

			//get subcategories of root categories
			$result = array();
			if($this->show_sub_category){
				$rootIDs = array();
				foreach($root as $rootItem){
					$rootIDs[] = $rootItem['categoryID'];
				}
				/*for ($i = count($root)-1; $i>=0; $i--){

				$q = db_query(
				"SELECT categoryID, ".LanguagesManager::sql_prepareField('name')." AS name, products_count, slug FROM ".CATEGORIES_TABLE.
				" WHERE categoryID<>0 and parent=".xEscapeSQLstring($root[$i]['categoryID'])." ORDER BY sort_order, name".($subcategories_numberlimit>0?' LIMIT '.intval($subcategories_numberlimit+1):'')) or die (db_error());
					
				while ($row = db_fetch_assoc($q)){
				if(!is_array($result[$root[$i]['categoryID']]))$result[$root[$i]['categoryID']] = array();
				$result[$root[$i]['categoryID']][] = $row;
				}
				}*/
				if(count($rootIDs)){
					$q = db_phquery(
					"SELECT parent,categoryID, ".LanguagesManager::sql_prepareField('name').
					" AS name, products_count, slug FROM ?#CATEGORIES_TABLE WHERE categoryID<>0 and parent IN (?@) ORDER BY sort_order, name",
					$rootIDs);
					//($subcategories_numberlimit>0?' _LIMIT '.intval($subcategories_numberlimit+1):''),
					while ($row = db_fetch_assoc($q)){
						if(!isset($result[$row['parent']]))$result[$row['parent']] = array();
						$result[$row['parent']][] = $row;
					}
				}
			}
			//$this->CategoriesColumns=$this->getSettingValue('categories_col_num');
			$smarty->assign("columnCount",$this->CategoriesColumns);;
			//print $this->CategoriesColumns;
			//exit;
			$smarty->assign("root_categories",$root);
			$smarty->assign("root_categories_subs",$result);
			$smarty->assign("subcategories_numberlimit",$subcategories_numberlimit?$subcategories_numberlimit+1:'');
			$smarty->assign("subcategories_delimiter",$this->SubCategoriesDelimeter);

			print $smarty->fetch('root_categories.html');
		}

		function cpt_shopping_cart_info(){
			//TODO: move it into modules/cart

			list($local_settings) = $this->__getFromStack('call_params');
			if(isset($local_settings['local_settings']))$local_settings = $local_settings['local_settings'];

			$interface_info = $this->__getFromStack('info');
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			$smarty->assign('__cpt_local_settings', $local_settings);
			//include(DIR_INCLUDES.'/shopping_cart_info.php');
			include(DIR_MODULES.'/cart/scripts/shopping_cart_info.php');

			print $smarty->fetch($interface_info['id'].'.html');
			//print ":D";exit;
		}

		function cpt_divisions_navigation(){

			list($local_settings) = $this->__getFromStack('call_params');
			if(isset($local_settings['local_settings']))$local_settings = $local_settings['local_settings'];
			$pages = array();
			$SubDivs = &DivisionModule::getBranchDivisions(DivisionModule::getDivisionIDByUnicKey('TitlePage'), array('xEnabled'=>1));
			foreach ($SubDivs as $_SubDiv){

				if($_SubDiv->UnicKey == 'order_status' && isset($_SESSION["log"])){
					continue;
				}
				if($_SubDiv->UnicKey == 'auth' && isset($_SESSION["log"])){

					$login_id = $_SubDiv->ID;
					$_SubDiv = DivisionModule::getDivisionByUnicKey('logout');
					$_SubDiv->ID = $login_id;
				}
				if($_SubDiv->UnicKey == 'register' && isset($_SESSION["log"])){

					$register_id = $_SubDiv->ID;
					$_SubDiv = DivisionModule::getDivisionByUnicKey('office');
					$_SubDiv->ID = $register_id;
				}
				/* @var $_SubDiv Division*/
				$pages[] = array(
				'id' => $_SubDiv->ID,
				'name' => translate($_SubDiv->Name),
				'ukey' => $_SubDiv->UnicKey,
				);

				if(isset($local_settings['divisions']) && $local_settings['divisions']=='mobile' && ($_SubDiv->UnicKey=='office'||$_SubDiv->UnicKey=='register')){
					$_SubDiv = DivisionModule::getDivisionByUnicKey('cart');
					$pages[] = array(
					'id' => $_SubDiv->ID,
					'name' => translate(translate($_SubDiv->Name)),
					'ukey' => $_SubDiv->UnicKey,
					);
				}
			}

			if(!count($pages))return ;

			$allowed_pages = explode(':', $local_settings['divisions']);

			print '<ul class="'.($local_settings['view'] == 'horizontal'?'horizontal':'vertical').'">';
			foreach ($pages as $page){
				if(!in_array($page['id'], $allowed_pages) && !(isset($allowed_pages[0]) && $allowed_pages[0]=='mobile'))continue;

				print '<li><a href="'.xHtmlSetQuery($page['ukey']?'?ukey='.$page['ukey']:'?did='.$page['id']).'">'.xHtmlSpecialChars($page['name']).'</a></li>';
			}
			print '</ul>';
		}

		function __display_template(){

			list($local_settings) = $this->__getFromStack('call_params');
			if(isset($local_settings['local_settings']))$local_settings = $local_settings['local_settings'];


			$interface_info = $this->__getFromStack('info');
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			$smarty->assign('__cpt_local_settings', $local_settings);
			print $smarty->fetch($interface_info['id'].'.html');
		}

		function cpt_language_selection($call_settings = null){

			$local_settings = isset($call_settings['local_settings'])?$call_settings['local_settings']:array();

			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			if(count($smarty->get_template_vars('lang_list'))<=1)return;

			$currentLanguage = &LanguagesManager::getCurrentLanguage();

			$smarty->assign('current_language', $currentLanguage->id);
			$smarty->assign('language_selection_view', $local_settings['view']);
			print $smarty->fetch('language.tpl.html');
		}

		function cpt_logo($call_settings = null){

			$local_settings = isset($call_settings['local_settings'])?$call_settings['local_settings']:array();

			if(!isset($local_settings['file']))return;

			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			if(SystemSettings::is_hosted()){
				$logo_url =	URL_IMAGES.'/'.$local_settings['file'];
			}else{
				if(file_exists(DIR_IMG.'/'.$local_settings['file'])){
					$logo_url =	URL_IMAGES.'/'.$local_settings['file'];
				}elseif(file_exists(DIR_ROOT.'/images/'.$local_settings['file'])){
					$logo_url = URL_IMAGES_DEF.'/'.$local_settings['file'];
				}else{
					$logo_url =	URL_IMAGES.'/'.$local_settings['file'];
				}
			}
			/* @var $smarty Smarty */
			print '<a href="'.xHtmlSetQuery('?ukey=home').'"><img src="'.$logo_url.'" alt="'.xHtmlSpecialChars(CONF_SHOP_NAME).'" /></a>';
		}

		function cpt_maincontent(){

			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			if($smarty->get_template_vars('CPT_CONSTRUCTOR_MODE')){

				$tpl_id = &$Register->get('__CPT_TPL_ID');
				$smarty->assign('main_content_template', 'home.html');
			}
			print $smarty->fetch($smarty->get_template_vars('main_content_template'));
		}

		function methodBUsersGroup(){

			global $smarty;

			if ( isset($_GET['delete']) ){

				safeMode(true, 'delete=');

				DeleteCustGroup( $_GET['delete'] );
				RedirectSQ('delete=');
			}
			if ( isset($_POST['save_custgroups']) ){

				safeMode(true);

				// add new group
				if ( !LanguagesManager::ml_isEmpty('custgroup_name', $_POST) ){
					AddCustGroup( $_POST, $_POST['new_custgroup_discount'], $_POST['new_sort_order'] );
				}

				$data = scanArrayKeysForID($_POST, array('custgroup_name_\w{2}', 'custgroup_discount', 'sort_order'));

				foreach( $data as $key => $val ){

					UpdateCustGroup($key,  $val, $val['custgroup_discount'], $val['sort_order'] );
				}
				RedirectSQ();
			}
			$smarty->assign('custgroups', GetAllCustGroups());
			$smarty->assign('admin_sub_dpt', 'custord_custgroup.tpl.html');
		}

		function methodBTaxes(){

			global $smarty;
			include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/conf_taxes.php');
		}

		function methodBZones(){

			global $smarty;
			include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/conf_zones.php');
		}

		function methodBCountries(){

			global $smarty;
			include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/conf_countries.php');
		}

		function methodBCurrencies(){

			global $smarty;

			include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/conf_currencies.php');
		}

		function methodFSuccessfulRegistration(){

			global $smarty;
			$smarty->assign('main_content_template', 'reg_successful.tpl.html');
		}

		function methodFRegister(){

			if(isset($_SESSION['log']))RedirectSQ('?ukey=page_not_found');
			if (isset($_GET['order']))$order = $_GET['order'];
			global $smarty,$smarty_mail;
			include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/register.php');
		}

		function methodFOrderDetailed()
		{
			global $smarty;
			if(!isset($_SESSION['log']) || !$_SESSION['log']) {
				$mode = defined('CONF_STRICT_ACCESS')?constant('CONF_STRICT_ACCESS'):'lastname';
				if($mode == 'auth') {//auth only|status only|full
					RedirectSQ('?ukey=auth');
				}else {
					$orderID = isset($_POST['orderID'])?$_POST['orderID']:(isset($_GET['orderID'])?$_GET['orderID']:0);
					$customer_email = isset($_POST['customer_email'])?$_POST['customer_email']:(isset($_GET['customer_email'])?$_GET['customer_email']:'');
					if(!strlen($customer_email)){
						if(isset($_GET['not_authorized']) && $_GET['not_authorized']){
							$guest_data = xGetData(__METHOD__);
							if(is_array($guest_data)) {
								list($customer_email,$orderID) = $guest_data;
							}else {
								RedirectSQ("?ukey=order_status");
							}
						}else {
							$customer_email = isset($_GET['code'])?base64_decode($_GET['code']):'';

							if($customer_email) {
								xSaveData(__METHOD__,array($customer_email,$orderID),300);
								RedirectSQ("?ukey=order_status&not_authorized=1");
							}

						}
					}
					$customer_email = strtolower($customer_email);
					$orderID = intval(preg_replace('/^'.CONF_ORDERID_PREFIX.'/u', '', $orderID));
					$order = ordGetOrder( $orderID );

					if (!$order || !(strcasecmp($order["customer_email"],$customer_email)==0)) { //attempt to view orders of other customers
						unset($order);
					}
					$full_info = false;
					if($order) {

						switch($mode) {
							case 'captcha': {
								$customer_last_name = isset($_POST['customer_last_name'])?$_POST['customer_last_name']:'';
								$smarty->assign('customer_last_name',$customer_last_name);
								if(isset($_POST['captcha'])) {
									$captcha = new IValidator();
									if($captcha->checkCode($_POST['captcha'])) {
										if((mb_strtolower($order["customer_lastname"],'UTF-8') == mb_strtolower($customer_last_name,'UTF-8'))) {
											$customerEntry = new Customer;
											$customerEntry->loadByID($order['customerID']);
											if($customerEntry->Login) {
												Message::raiseMessageRedirectSQ(MSG_NOTIFY,'?ukey=auth','ord_status_use_myaccount');
											}
											$full_info = true;
										}else{
											$smarty->assign('wrong_last_name',1);
										}
									}else {
										$smarty->assign('wrong_captcha',1);
									}
								}
								break;
							}
							case 'lastname': {
								$customer_last_name = isset($_POST['customer_last_name'])?$_POST['customer_last_name']:'';
								$smarty->assign('customer_last_name',$customer_last_name);
								if((mb_strtolower($order["customer_lastname"],'UTF-8') == mb_strtolower($customer_last_name,'UTF-8'))) {
									$full_info = true;
								}else{
									$smarty->assign('wrong_last_name',1);
								}
								break;
							}
							case 'code':
							default: {
								$code_name = $orderID.'-'.__METHOD__;
								if(isset($_POST['send_code']) && $_POST['send_code']) {
									$customerEntry = new Customer;
									$customerEntry->loadByID($order['customerID']);
									if($customerEntry->Login) {
										Message::raiseMessageRedirectSQ(MSG_NOTIFY,'?ukey=auth','ord_status_use_myaccount');
									}
									$mvalidator = new MValidator();
									$mvalidator->sendCode($order["customer_email"],$code_name,$order);
									$smarty->assign('code_sended',1);
								}elseif(isset($_POST['code']) && $_POST['code']) {
									$mvalidator = new MValidator();
									if($mvalidator->check($_POST['code'],$code_name)) {
										$full_info = true;
									}else {
										$smarty->assign('wrong_code',1);
										$smarty->assign('code_sended',$mvalidator->sended($code_name));
									}
								}else {
									$mvalidator = new MValidator();
									$smarty->assign('code_sended',$mvalidator->sended($code_name));
								}
								break;
							}
						}
						if($full_info) {
							$storage = Cache::getInstance('order_status',Cache::SESSION);
							$storage->set($orderID,$order["customerID"],1200);
						}
					}



					$smarty->assign('mode', $mode);
					$smarty->assign('full_info', $full_info);
					$smarty->assign('customer_email', $customer_email);
					$smarty->assign('order_id', isset($_POST['orderID'])?CONF_ORDERID_PREFIX.$orderID:false);
					$smarty->assign('edited',isset($_POST['orderID'])?true:false);
					$smarty->assign('main_content_template', 'search_order_info.tpl.html');
				}
			}else {//authorized access
				$this->_initUserAccountSubs();
				$orderID = isset($_POST['orderID'])?$_POST['orderID']:(isset($_GET['orderID'])?$_GET['orderID']:0);
				//digital prefix workaround
				$customerID = regGetIdByLogin($_SESSION["log"]);
				$pattern = '/^'.CONF_ORDERID_PREFIX.'/u';
				if(preg_match('/^\d+$/',CONF_ORDERID_PREFIX)){
					$replaced = false;
					do {
						$order = ordGetOrder( $orderID );
						if($order && ($order["customerID"] != $customerID)) {
							unset($order);
						}
						if(!$order && preg_match($pattern,$orderID)) {
							$orderID = preg_replace($pattern,'', $orderID);
							$replaced = true;
						}elseif(!$order && $replaced) {
							$orderID = false;
						}else {
							$replaced = false;
						}
					}while(!$order&&$orderID);
					$orderID = intval($orderID);
				}else{
					$orderID = intval(preg_replace($pattern, '', $orderID));
					$order = ordGetOrder( $orderID );
				}
				if (!$order || ($order["customerID"] != $customerID)) { //attempt to view orders of other customers
					unset($order);
					$smarty->assign('full_info', 0);
				}else {
					$smarty->assign('full_info', 1);
				}
			}
			if ($order) {
				$status = ost_getOrderStatuses();

				$order['status_info'] = $status[$order['statusID']];
				$order['order_time_encoded'] = base64_encode($order['order_time_mysql']);
				$order['customer_email_encoded'] = base64_encode($order['customer_email']);
				$order_status_report = stGetOrderStatusReport( $orderID);

				$smarty->assign( "order_status_report", $order_status_report );
				$after_processing_html = '';
				if($smarty->get_template_vars('full_info')==1) {

					$curr_language = LanguagesManager::getCurrentLanguage();
					$iso23_map = array('ru'=>'rus','en'=>'eng',);
					$_language = isset($iso23_map[$curr_language->iso2])?$iso23_map[$curr_language->iso2]:'';

					$properties = array('type'=>Forms::GENERIC_FORM,'language'=>$_language,'side'=>'frontend');
					$print_forms = Forms::listConnectedModules($properties);

					$currentPaymentModule = PaymentModule::getInstance($order['payment_module_id']);
					if($currentPaymentModule instanceof PaymentModule) {
						if (!in_array($order['statusID'],array(CONF_ORDSTATUS_DELIVERED,CONF_ORDSTATUS_CANCELLED))){
							$after_processing_html = $currentPaymentModule->after_processing_html($orderID,false);
						}
						$properties['type'] = Forms::MODULE_FORM;
						$properties['sub_type']=$currentPaymentModule->getConnectedPrintforms();
						$print_forms = array_merge($print_forms,Forms::listConnectedModules($properties));
					}

					$currentShippingModule = ShippingRateCalculator::getInstance($order['shipping_module_id']);
					if($currentShippingModule instanceof ShippingRateCalculator) {
						$properties['type'] = Forms::MODULE_FORM;
						$properties['sub_type']=$currentShippingModule->getConnectedPrintforms();
						$print_forms = array_merge($print_forms,Forms::listConnectedModules($properties));
					}

					$smarty->assign( "print_forms", $print_forms );
					$orderContent = ordGetOrderContent( $orderID );
					ordCalculateOrderTax($order, $orderContent);

					$smarty->assign( "orderContent", $orderContent );
				}
				$smarty->assign( "order", $order );
				$smarty->assign( "payment_html", $after_processing_html );
				$smarty->assign( "order_detailed", 1 );
			}
			$smarty->assign( 'CurrentSubTpl', 'order_detailed.html' );
		}

		function methodOrdersHistory(){

			if(!$_SESSION['log'])return;
			$this->_initUserAccountSubs();
			global $smarty;

			if(isset($_GET['show_all'])){

				$Register = &Register::getInstance();
				/*@var $Register Register*/
				renderURL('show_all=', '', true);
				$Register->assign('show_all', 1);
			}

			$order_statuses = ostGetOrderStatues();
			$smarty->assign( 'completed_order_status', ostGetCompletedOrderStatus() );

			$callBackParam = array( "customerID" => regGetIdByLogin($_SESSION["log"]) );
			if ( isset($_GET["sort"]) ){
				$callBackParam["sort"] = $_GET["sort"];
				if ( isset($_GET["direction"]) )
				$callBackParam["direction"] = $_GET["direction"];
			}else{
				$callBackParam["sort"] = "order_time";
				$callBackParam["direction"] = "desc";
			}
			$callBackParam["orderStatuses"] = array();

			if ( isset($_GET["order_search_type"])  )$smarty->assign( "order_search_type", $_GET["order_search_type"] );
			if ( isset($_GET["orderID_textbox"]) )$smarty->assign( "orderID", (int)$_GET["orderID_textbox"] );
			$data = scanArrayKeysForID($_GET, array("checkbox_order_status") );
			for( $i=0; $i<count($order_statuses); $i++ )
			$order_statuses[$i]["selected"] = 0;
			foreach( $data as $key => $val ){

				if ( $val["checkbox_order_status"] == "1" ){

					for( $i=0; $i<count($order_statuses); $i++ )
					if ( (int)$order_statuses[$i]["statusID"] == (int)$key )
					$order_statuses[$i]["selected"] = 1;
				}
			}

			$orders = array();
			$offset = 0;
			$count = 0;

			$navigatorHtml = GetNavigatorHtml( '', 20,
			'ordGetOrders', $callBackParam, $orders, $offset, $count );

			$smarty->assign( 'orders_navigator', $navigatorHtml );
			$smarty->assign( 'user_orders', $orders );

			$gridEntry  = new Grid();
			$gridEntry->registerHeader('ordr_id');
			$gridEntry->registerHeader('ordr_order_time', 'order_time', true, 'desc');
			$gridEntry->registerHeader('ordr_status', '', false, 'asc', 'center');
			/*
			 $gridEntry->registerHeader('ordr_ordered_products');
			 $gridEntry->registerHeader('payment');
			 $gridEntry->registerHeader('shipping');
			 */
			$gridEntry->registerHeader('ordr_order_total', 'order_amount', false, 'desc', 'right');

			$gridEntry->default_sort_field = 'ordr_order_time';
			$gridEntry->default_sort_direction = 'desc';

			$gridEntry->prepare_headers();

			$smarty->assign( 'order_statuses', $order_statuses);

			$smarty->assign( 'CurrentSubTpl', 'order_history.tpl.html' );
		}

		function methodEditAddress(){

			if(!$_SESSION['log'])return;
			$this->_initUserAccountSubs();
			global $smarty;

			$address_editor = 'yes';
			$addressID = isset($_GET['addressID'])?$_GET['addressID']:0;
			$_POST['addressID'] = $addressID;
			include(DIR_ROOT.'/includes/address_editor.php');
		}

		function methodAddAddress(){

			if(!$_SESSION['log'])return;
			$this->_initUserAccountSubs();
			global $smarty;

			$add_new_address = 'yes';
			include(DIR_ROOT.'/includes/address_editor.php');
		}

		function methodAddressBook(){

			if(!$_SESSION['log'])return;

			if ( isset($_GET["delete"]) ){
				if ( regGetAddressByLogin($_GET["delete"],$_SESSION["log"])) // delete address only if belongs to customer
				{
					redDeleteAddress($_GET["delete"]);
				}
				RedirectSQ('delete=');
			}
			if(isset($_GET['set_default'])){

				if (regGetAddressByLogin($_GET['set_default'],$_SESSION["log"])){
					regSetDefaultAddressIDByLogin( $_SESSION["log"], $_GET['set_default']);
				}
				Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'set_default=', 'msg_information_saved');
			}

			global $smarty;
			$this->_initUserAccountSubs();

			$addresses = regGetAllAddressesByLogin( $_SESSION["log"] );
			for( $i=0; $i<count($addresses); $i++ )
			$addresses[$i]["addressStr"] = regGetAddressStr( $addresses[$i]["addressID"] );

			$defaultAddressID = regGetDefaultAddressIDByLogin( $_SESSION["log"] );

			$smarty->assign("defaultAddressID", $defaultAddressID );
			$smarty->assign("addresses", $addresses );
			$smarty->assign("CurrentSubTpl", "address_book.tpl.html");
		}

		function _initUserAccountSubs(){

			global $smarty, $CurrDivision;
			$smarty->assign('main_content_template', 'user_account_sub.tpl.html');
			$UserAccountDivs = &DivisionModule::getChildDivisions(DivisionModule::getDivisionIDByUnicKey('office'), array('xEnabled'=>1));
			$_TC = count($UserAccountDivs);
			$UserAccountDivsInfo = array();
			for($j = 0; $j<$_TC;$j++){

				$UserAccountDivsInfo[] = array(
				'name' => $UserAccountDivs[$j]->Name,
				'id' => $UserAccountDivs[$j]->ID,
				'ukey' => $UserAccountDivs[$j]->UnicKey,
				);
			}
			$smarty->assign('UserAccountDivs', $UserAccountDivsInfo);
		}

		function methodContactInfo(){

			if (!isset($_SESSION["log"])){
				return ;
			}
			global $smarty;
			$this->_initUserAccountSubs();

			include(DIR_ROOT.'/includes/contact_info.php');
		}

		function methodShortOrdersHistory(){

			global $smarty;
			$smarty->assign("status_distribution", ordGetDistributionByStatuses( $_SESSION["log"] ) );
			return 'short_order_history.tpl.html';
		}

		function methodShortAddressBook(){

			global $smarty;
			$smarty->assign("addressStr", regGetAddressStr( regGetDefaultAddressIDByLogin( $_SESSION["log"] ) ) );

			return 'short_address_book.tpl.html';
		}

		function methodShortContactInfo(){

			global $smarty;

			$cust_password				= null;
			$Email						= null;
			$first_name					= null;
			$last_name					= null;
			$subscribed4news			= null;
			$additional_field_values	= null;
			regGetContactInfo( $_SESSION["log"], $cust_password, $Email, $first_name,
			$last_name, $subscribed4news, $additional_field_values );
			$smarty->assign("additional_field_values", $additional_field_values );
			$smarty->assign("first_name", $first_name );
			$smarty->assign("last_name", $last_name );
			$smarty->assign("Email", $Email );
			$smarty->assign("login", $_SESSION["log"] );

			$customerID = regGetIdByLogin( $_SESSION["log"] );
			$custgroup = GetCustomerGroupByCustomerId( $customerID );
			$smarty->assign( "custgroup_name", $custgroup["custgroup_name"] );

			if ( CONF_DISCOUNT_TYPE == '2' )
			if ( $custgroup["custgroup_discount"] > 0 )
			$smarty->assign( "discount", $custgroup["custgroup_discount"] );

			if ( CONF_DISCOUNT_TYPE == '4' || CONF_DISCOUNT_TYPE == '5' )
			if ( $custgroup["custgroup_discount"] > 0 )
			$smarty->assign( "min_discount", $custgroup["custgroup_discount"] );

			return 'short_contact_info.tpl.html';
		}

		function methodUserAccount(){

			include_once(DIR_FUNC.'/affiliate_functions.php');
			if (!isset($_SESSION["log"])) return ''; //show user's account


			global $smarty, $CurrDivision, $ConnectedModules;

			$ChildDivs = DivisionModule::getChildDivisions($CurrDivision->ID, array('xEnabled'=>1));
			$ChildShortHTMLs = array();
			$_TC = count($ChildDivs);
			for ($_j = 0; $_j<$_TC;$_j++){

				$ChildDivs[$_j]->loadCustomSettings();
				$ChildShortHTMLs[$_j]['name'] = $ChildDivs[$_j]->Name;
				if(!is_null($ChildDivs[$_j]->getCustomSetting('short_info'))){

					@list($ModuleKey, $Interface) = explode('->', $ChildDivs[$_j]->getCustomSetting('short_info'));
					if(isset($ConnectedModules[$ModuleKey])){
						$ChildShortHTMLs[$_j]['tpl'] = $ConnectedModules[$ModuleKey]->getInterface($Interface);
					}else {
						$Mod = ModulesFabric::getModuleObjByKey($ModuleKey);
						$ChildShortHTMLs[$_j]['tpl'] = $Mod->getInterface($Interface);
					}
				}
			}

			$customerEntry = Customer::getAuthedInstance();
			if(is_null($customerEntry))RedirectSQ('?ukey=home');

			$addresses_num = $customerEntry->getAddressesNumber();

			$smarty->assign('addresses_num', $customerEntry->addressID && regGetAddress($customerEntry->addressID)?($addresses_num-1):$addresses_num);
			$smarty->assign('orders_num', $customerEntry->getOrdersNumber());
			$Commissions 	= affp_getCommissionsAmount($customerEntry->customerID);
			$Payments 		= affp_getPaymentsAmount($customerEntry->customerID);
			$smarty->assign('CommissionsNumber', count($Commissions));
			$smarty->assign('PaymentsNumber', count($Payments));
			$smarty->assign('CommissionsAmount', $Commissions);
			$smarty->assign('PaymentsAmount', $Payments);
			$smarty->assign('CurrencyISO3', currGetAllCurrencies());

			$smarty->assign('ChildShortHTMLs', $ChildShortHTMLs);
			$smarty->assign("main_content_template", "user_account.html");
		}

		function methodFCurrencies(){

			if(isset($_POST["current_currency"])){

				currSetCurrentCurrency( $_POST["current_currency"] );
				RedirectSQ();
			}

			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$smarty = &$Register->get(VAR_SMARTY);
			/*@var $smarty Smarty*/
			$selectedCurrency = Currency::getSelectedCurrencyInstance();
			$currencies = currGetAllCurrencies();

			$smarty->assign("current_currency", $selectedCurrency->CID);
			$smarty->assign("currency_name", $selectedCurrency->Name);
			$smarty->assign("currencies", $currencies);
			$smarty->assign("currencies_count", count($currencies));
		}

		function methodCategoryTree(){

			global $smarty;
			$Register = &Register::getInstance();
			$categoryID = isset($_GET['categoryID'])?$_GET['categoryID']:$Register->get('categoryID');
			/*@var $Register Register*/
			// category navigation form
			if ($categoryID){
				$category = new Category();
				if(!$category->loadByID($categoryID)){
					error404page();
					//RedirectSQ('ukey=page_not_found');
				}
				$out = catGetCategoryCompactCList($categoryID);
			}else{
				$out = catGetCategoryCompactCList( 1 );
			}
			$smarty->assign( "categories_tree", $out );
		}

		function methodSynchronizeDB(){

			global $smarty;
			include(DIR_ROOT.'/includes/admin/sub/catalog_dbsync.php');
		}

		function methodFrontendTitle(){

			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			if ( isset($_GET['categoryID']) && !isset($_GET["search_with_change_category_ability"]) && !isset($dontshowcategory)){

				/*
				 @features "Search products by params"
				 */
				include(DIR_ROOT.'/includes/advanced_search_in_category.php');
				/*
				 @features
				 */
				include(DIR_ROOT.'/includes/category.php');
			}else{

				$smarty->assign('main_content_template', 'home.html');
			}
		}

		function method_login(){

			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			Message::loadData2Smarty();
			$smarty->assign('admin_login_url', str_replace('//','/',(WBS_INSTALL_PATH.'/login/')));
			$smarty->assign('main_content_template', 'login.html');
		}

		function method_logout(){

			unset($_SESSION["log"]);
			unset($_SESSION["pass"]);
			session_unregister("log"); //calling session_unregister() is required since unset() may not work on some systems
			session_unregister("pass");

			$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
			$checkoutEntry->clean();

			RedirectSQ('?ukey=home');
		}
	}
}
?>