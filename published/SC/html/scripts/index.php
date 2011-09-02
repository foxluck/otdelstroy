<?php
	// -------------------------INITIALIZATION-----------------------------//
	@ini_set('display_errors', true);
	define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__))));
	$DebugMode = false;
	if(function_exists('microtime')){
		if(isset($_GET['debug'])&&(($_GET['debug']=='total_time')||($_GET['debug']=='time'))){
			$DebugMode = true;
		}elseif(isset($_COOKIE['debug'])&&$_COOKIE['debug']){
			$DebugMode = true;
		}
	}
	if($DebugMode){
		$debug_total_time = microtime(true);
		$debug_total_sql_query = 0;
		$debug_total_cache_query = 0;
		require_once(DIR_ROOT.'/classes/class.debug.php');
		$debuger = Debug::getInstance();
		$debuger->start();
	}else{
		$debuger = null;
	}
	
	$Warnings = array();
	include_once(DIR_ROOT.'/includes/init.php');
	include_once(DIR_CFG.'/connect.inc.wa.php');
	
	//look up for XSS
	if(!SystemSettings::is_backend()){
		$_SERVER['REQUEST_URI'] = preg_replace('/(["\'\<\>\\\\]{1,})|(%5C)|(%22)|(%27)|(%3C)|(%3E)/','',$_SERVER['REQUEST_URI']);
		foreach($_GET as $name=>$get){
			if($name != preg_replace('/(["\'\<\>\\\\]{1,})|(%5C)|(%22)|(%27)|(%3C)|(%3E)/si','',$name)){
				unset($_GET[$name]);
				continue;
			}
			$_GET[$name] = preg_replace('/(["\'\<\>\\\\]{1,})|(%5C)|(%2[27]{1})|(%3[CE]{1})/si','',$get);
		}
	}
	//support for old urls
	//hack-like method	

	if(MOD_REWRITE_SUPPORT
		&&!SystemSettings::is_backend()
		&&!isset($_GET['ukey'])
		&&(
			(isset($_GET['__furl_path'])&&preg_match('/^index.php/i',$_GET['__furl_path']))
			||!isset($_GET['__furl_path'])
			)
		){
			if(!isset($_GET['__furl_path'])){
				$_GET['__furl_path'] = '';
			}
			if(isset($_GET['productID'])){
				$_GET['__furl_path'].='/product/'.intval($_GET['productID']);
			}elseif (isset($_GET['categoryID'])){
				$_GET['__furl_path'].='/category/'.intval($_GET['categoryID']);
			}
	}
	
	//support for old auxpages urls
	if(!SystemSettings::is_backend()&&isset($_GET['show_aux_page'])){
			$_GET['ukey'] = 'auxpage_'.intval($_GET['show_aux_page']);
			unset($_GET['show_aux_page']);
	}
	
	//fix redirection
	if(isset($_GET['__furl_path'])&&strpos($_GET['__furl_path'],'published/SC/html/scripts/')===0){
		$_GET['__furl_path'] = substr($_GET['__furl_path'],strlen('published/SC/html/scripts/'));
	}

	//include_once(DIR_CLASSES.'/class.filewbs.php');
	//$fileEntry = new FileWBS();
	
	
	include(DIR_FUNC.'/setting_functions.php' );
	$DB_tree = new DataBase();
	$DB_tree->connect(SystemSettings::get('DB_HOST'), SystemSettings::get('DB_USER'), SystemSettings::get('DB_PASS'));

	$DB_tree->selectDB(SystemSettings::get('DB_NAME'));
	define('VAR_DBHANDLER','DBHandler');
	
	
	if(isset($_SESSION['__WBS_SC_DATA'])&&isset($_SESSION['__WBS_SC_DATA']["U_ID"])){
		
		if(SystemSettings::is_hosted()){
			Wbs::loadCurrentDBKey();
			User::set($_SESSION['__WBS_SC_DATA']["U_ID"]);
			User::updateLastTime();
			$fileEntry = new WbsFiles('SC');
			Functions::register($fileEntry, 'file_move_uploaded', 'moveUpload');
		}else{
			$fileEntry = new FileWBS();
			Functions::register($fileEntry, 'file_move_uploaded', 'move_uploaded');
		}
		Functions::register($fileEntry, 'file_copy', 'copy');
		Functions::register($fileEntry, 'file_move', 'move');
		Functions::register($fileEntry, 'file_remove', 'remove');
		//Functions::register($fileEntry, 'file_exists', 'exists');
	}
	
	
if(!__USE_OLD_UPDATE){
	//DEBUG:||true
	if(SystemSettings::is_hosted()||true){
		$update = false;
		// If cannot load dbkey settings
		try {
		//	@session_start();
			if(!defined('GET_DBKEY_FROM_URL')){
				define('GET_DBKEY_FROM_URL', 1);
			}
		
			if (Wbs::loadCurrentDBKey()) {
				$update = true;
			}
			

		} catch (Exception $ex) {
			trigger_error($ex->getMessage (), E_USER_ERROR);
			var_dump($ex);
		}
		if ($update) {
			try {
				$updater = new WbsUpdater("SC");
				$updater->check();
			}
			catch (Exception $ex) {
				trigger_error($ex->getMessage (), E_USER_ERROR);
				var_dump($ex);
				//....
			}
		}
	}
}
	
	$Register = &Register::getInstance();
	$Register->set(VAR_DBHANDLER, $DB_tree);

	if(isset($_GET['widgets'])){
		renderURL('view=noframe&external=1', '', true);
	}
	if(isset($_GET['view']) && $_GET['view'] == 'noframe' && isset($_GET['external'])){
		$widgets = 1;
		$Register->set('widgets', $widgets);
	}

	

	settingDefineConstants();

	define('FURL_ENABLED', 1);
	$urlEntry = new URL();
	$urlEntry->loadFromServerInfo();
	
	define('VAR_URL', 'URL');
	$Register->set(VAR_URL, $urlEntry);

	$_urlEntry = new URL();
	$_urlEntry->loadFromServerInfo();
	
	$furl_path = isset($_GET['__furl_path'])?$_GET['__furl_path']:'';
	
	if(strpos($furl_path,'/')===0){//it's not work properly on apache 1.xx when string start on '/' so it deleted
		$furl_path=substr($furl_path,1);
		if(!SystemSettings::is_hosted())$_GET['__furl_path']=$furl_path;
	}
	$Register->set('FURL_PATH', $furl_path);
	
	if($furl_path == 'robots.txt')
	{
	    header("HTTP/1.1 404 Not Found");
	    header("Status: 404 Not Found");
	    header("Content-type: text/html; charset=ISO-8859-1");
	    echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'.
	            '<html><head>'.
	            '<title>404 Not Found</title>'.
	            '</head><body>'.
	            '<h1>Not Found</h1>'.
	            '<p>The requested URL was not found on this server.</p>'.
	            '</body></html>';
	    die();
	};
	//$_urlEntry->setPath('/');
	$_furl_path=$furl_path?substr($_SERVER["REQUEST_URI"],0,strpos($_SERVER["REQUEST_URI"],$furl_path)):$_SERVER["REQUEST_URI"];
	$_furl_path=substr($_furl_path,strlen(WBS_INSTALL_PATH));
	if(strpos($_furl_path,'/')===0){//it's not work properly on apache 1.xx when string start on '/' so it deleted
		$_furl_path=substr($_furl_path,1);
		if(!SystemSettings::is_hosted())$_GET['__furl_path']=$_furl_path;
	}
	while (!strpos($_furl_path,'//')===false){
		$_furl_path=str_replace('//','/',$_furl_path);
	}
	$_furl_path=explode('/',$_furl_path);
	if((isset($_furl_path[0])&&strcmp(strtolower($_furl_path[0]),'shop')===0)){
		$_furl_path='/shop/';
	}else{
		$_furl_path='/';
	}
	
	if(SystemSettings::is_hosted()){
		$_furl_path='/shop/';
		$_urlEntry->setPath('/shop/');
	}else{
		$_urlEntry->setPath(str_replace('//','/',WBS_INSTALL_PATH.$_furl_path));
	}

	$_urlEntry->setQuery('?');
	$__url =  preg_replace('/\/[^\/]+$/', '', $_urlEntry->getURI());
	//define('CONF_FULL_SHOP_URL', $__url.(SystemSettings::is_hosted()||(SystemSettings::get('FRONTEND')!='SC')?'shop/':''));
	$CONF_FULL_SHOP_URL = $__url.(SystemSettings::is_hosted()||(SystemSettings::get('FRONTEND')!='SC')?'shop/':'');
	
	$__wa_url = $__url;
	if(SystemSettings::is_hosted()){
		$matches = null;
		if(preg_match('/^(.+)shop\/$/',$__url,$matches)){
			$__wa_url = $matches[1];
		}
	}
//	define('WIDGET_SHOP_URL', preg_replace('/\/[^\/]+$/', '', $_urlEntry->getURI().(SystemSettings::is_hosted()?'':'shop/')));
//	$_base_url = substr($__url,0,strlen($__url)-strlen(WBS_INSTALL_PATH));
	
	$pattern = '|^((http[s]{0,1}://([^/]+)/)'.substr(WBS_INSTALL_PATH,1).')|msi';
	if(preg_match($pattern,$__url,$matches)){
		$_base_url = $matches[2];
		$WIDGET_SHOP_URL = $matches[1];
	}else{
		$_base_url = $__url;
	}
	
	define('BASE_URL', $_base_url);
	define('BASE_WA_URL', $WIDGET_SHOP_URL);
	define('WIDGET_SHOP_URL',$WIDGET_SHOP_URL.(SystemSettings::is_hosted()&&(preg_match('/\.webasyst\./',$WIDGET_SHOP_URL))||!SystemSettings::is_hosted()&&(SystemSettings::get('FRONTEND')!='SC')?'shop/':''));
	define('CONF_FULL_SHOP_URL',WIDGET_SHOP_URL);
	unset($_base_url);
	define('CONF_WAROOT_URL', WBS_INSTALL_PATH);
	define('CONF_ON_WEBASYST', SystemSettings::is_hosted());
	
	
	//DEBUG:
	/*foreach(array('BASE_URL','BASE_WA_URL','WIDGET_SHOP_URL','CONF_FULL_SHOP_URL','CONF_WAROOT_URL') as $const){
		print $const."=".constant($const)."<br>\n";
	}*/

	
	require_once(DIR_CFG.'/language_list.php');
	require_once(DIR_FUNC.'/category_functions.php');
	require_once(DIR_FUNC.'/product_functions.php');
	require_once(DIR_FUNC.'/statistic_functions.php');//*
	require_once(DIR_FUNC.'/country_functions.php' );//*
	require_once(DIR_FUNC.'/zone_functions.php' );//*
	require_once(DIR_FUNC.'/datetime_functions.php' );
	require_once(DIR_FUNC.'/picture_functions.php' );//*
	require_once(DIR_FUNC.'/configurator_functions.php' );
	require_once(DIR_FUNC.'/option_functions.php' );//*
	require_once(DIR_FUNC.'/discount_functions.php' );
	require_once(DIR_FUNC.'/custgroup_functions.php' );//*
	require_once(DIR_FUNC.'/currency_functions.php' );
	require_once(DIR_FUNC.'/module_function.php' );
	require_once(DIR_FUNC.'/registration_functions.php' );
	require_once(DIR_FUNC.'/order_amount_functions.php' );
	require_once(DIR_FUNC.'/catalog_import_functions.php');//*
	require_once(DIR_FUNC.'/cart_functions.php');
	require_once(DIR_FUNC.'/subscribers_functions.php' );
	require_once(DIR_FUNC.'/discussion_functions.php' );//*
	require_once(DIR_FUNC.'/order_status_functions.php' );//*
	require_once(DIR_FUNC.'/order_functions.php' );
	require_once(DIR_FUNC.'/shipping_functions.php' );//*
	require_once(DIR_FUNC.'/payment_functions.php' );//*
	require_once(DIR_FUNC.'/reg_fields_functions.php' );//*
	require_once(DIR_FUNC.'/tax_function.php' );//*
	require_once(DIR_CLASSES.'/class.virtual.shippingratecalculator.php');
	//require_once(DIR_CLASSES.'/class.virtual.paymentmodule.php');
	if(false){//SMARTY SC
		require_once(DIR_ROOT.'/smarty/smarty.class.php');
		require_once(DIR_ROOT.'/smarty/resources/resource.rfile.php');
		require_once(DIR_ROOT.'/smarty/resources/resource.register.php');
	}else{//USE MERGED SMARTY
		require_once(WBS_DIR.'/kernel/includes/smarty/Smarty.class.php');
		require_once(WBS_DIR.'/kernel/includes/smarty/resources/resource.rfile.php');
		require_once(WBS_DIR.'/kernel/includes/smarty/resources/resource.register.php');
	}
	
	require_once(DIR_FUNC.'/search_function.php' );
	
	if(!file_exists(DIR_COMPILEDTEMPLATES)){
		mkdir(DIR_COMPILEDTEMPLATES,0777,true);
	}
	/*
	$products_res = db_query("SELECT COUNT( * ) AS 'cnt', `product_code`
FROM `SC_products`
GROUP BY `product_code`
ORDER BY `cnt` DESC");
	while($row = db_fetch_row($products_res)){
		if($row['cnt']==1){
			break;
		}
		db_phquery('DELETE FROM `SC_products` WHERE `product_code` =? LIMIT ?',$row['product_code'],($row['cnt']-1));
	}
	*/
	
	
	//select a new language?
	if (isset($_POST['lang'])){
		LanguagesManager::setCurrentLanguage($_POST['lang']);
		RedirectSQ();
	}
	if (isset($_GET['lang'])){
		LanguagesManager::setCurrentLanguage($_GET['lang']);
		RedirectSQ('lang=');
	}

	if (!MOD_REWRITE_SUPPORT and isset($_GET['lang_iso2']))
	{
	    $lang = LanguagesManager::getLanguageByISO2($_GET['lang_iso2']);
	    if($lang != null)
	    {
	        LanguagesManager::setCurrentLanguage($lang->id);
	    };
		RedirectSQ('lang_iso2=');
	}

$error404 = false;
	ModulesFabric::initGlobalModules();

	if(!MOD_REWRITE_SUPPORT and array_key_exists('productID', $_GET) and !array_key_exists('ukey', $_GET) && !array_key_exists('did', $_GET) )
	{
	    $_GET['ukey'] = 'product';
	};
$max_cnt = 200;
$CurrDivision = null;

	$Register = &Register::getInstance();
	$Register->set(VAR_SMARTY,$smarty);


do{
	$did = isset($_GET['did'])?$_GET['did']:0;

	if(isset($_GET['ukey'])&&$_GET['ukey']){

		$did = DivisionModule::getDivisionIDByUnicKey($_GET['ukey']);
		set_query('did='.$did, '', true);
		if(!$did&&($_GET['ukey']!='category')&&(strpos($_GET['ukey'],'index.php')!==0)){
			$error404 = true;
		}
	}

	if(!$did){
		if(!isset($furl1)){
			$furl1 = true;
			fURL::exec();
			continue;
		}
		$did = DivisionModule::getDivisionIDByUnicKey('TitlePage');
	}

	$CurrDivision = &DivisionModule::getDivision($did);
	/*@var $CurrDivision Division*/
	if(!$CurrDivision->getID()){

		if(!isset($furl1)){
			$furl1 = true;
			fURL::exec();
			continue;
		}
		$CurrDivision->LinkDivisionUKey = 'TitlePage';
	}
}while(--$max_cnt>0&&(!is_object($CurrDivision) || !$CurrDivision->getID()));
	/* init Smarty */
	$smarty = new ViewSC(); //core smarty object
	$smarty_mail = new ViewSC(); //for e-mails
	$smarty->force_compile = false;
	$smarty_mail->force_compile = false;
	$smarty->register_resource('rfile', array('smarty_resource_rfile_source', 'smarty_resource_rfile_timestamp', 'smarty_resource_rfile_secure', 'smarty_resource_rfile_trusted'));
	$smarty->register_resource('register', array('smarty_resource_register_source', 'smarty_resource_register_timestamp', 'smarty_resource_register_secure', 'smarty_resource_register_trusted'));

	define('VAR_SMARTY','Smarty');
	$Register->set(VAR_SMARTY,$smarty);

	settingDefineMLConstants();
	if($error404){
		error404page();
	}
	
	checkLogin();

	$smarty->assign('lang_list', $lang_list);
	$cur_lang = LanguagesManager::getCurrentLanguage();
	/*@var $cur_lang Language*/
	$smarty->assign('lang_iso2',$cur_lang->iso2);

	//if (isset($_SESSION['current_language'])) $smarty->assign('current_language', $_SESSION['current_language']);
/*
----------------------------------
*/

	if($max_cnt<=0){
		die('Couldnt load divisions');
	}

	if($CurrDivision->LinkDivisionUKey !=''){

		$CurrDivision = &DivisionModule::getDivisionByUnicKey($CurrDivision->LinkDivisionUKey);
		set_query('&did='.$CurrDivision->getID().'&did=&ukey='.$CurrDivision->getUnicKey(), '', true);
	}

	$Register->set(VAR_CURRENTDIVISION, $CurrDivision);
	$AdminDivID = DivisionModule::getDivisionIDByUnicKey('admin');
	$AdminChild = $CurrDivision->isBranchOf($AdminDivID);
	$admin_mode = ($CurrDivision->UnicKey == 'admin' || $AdminChild)&&($CurrDivision->UnicKey!=='test');
	$Register->set('admin_mode', $admin_mode);

	if(!isset($furl1) && !$admin_mode and MOD_REWRITE_SUPPORT){
		$furl1 = true;
		fURL::exec();
	}
	if($admin_mode && !wbs_auth()){

		$CurrDivision = DivisionModule::getDivisionByUnicKey('TitlePage');
		
		$admin_mode = $AdminChild = false;
	}
	/*@var $CurrDivision Division*/

	$LanguageEntry = &LanguagesManager::getCurrentLanguage();

	$smarty->assign('BREADCRUMB_DELIMITER', '&raquo;');

	if( ($admin_mode || $CurrDivision->UnicKey == 'cpt_constructor') && sc_getSessionData('LANGUAGE_ID') && sc_getSessionData('LANGUAGE_ID')!=$LanguageEntry->id){
		LanguagesManager::setCurrentLanguage(sc_getSessionData('LANGUAGE_ID'));
	}

	$locals = $LanguageEntry->getLocals(array($admin_mode?LOCALTYPE_BACKEND:LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_HIDDEN), false, false);
	
	$smarty->assign('lang_direction',$LanguageEntry->direction);

	$Register->set('CURRLANG_LOCALS', $locals);
	$Register->set('CURR_LANGUAGE', $LanguageEntry);

	$DefLanguageEntry = &ClassManager::getInstance('Language');
	$DefLanguageEntry->loadById(CONF_DEFAULT_LANG);
	$deflocals = $DefLanguageEntry->getLocals(array($admin_mode?LOCALTYPE_BACKEND:LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_HIDDEN), false, false);

	$Register->set('DEFLANG_LOCALS', $deflocals);
	$Register->set('DEF_LANGUAGE', $DefLanguageEntry);

	$rMonths = array(
		1=>translate('str_month_january'), 2=>translate('str_month_february'), 3=>translate('str_month_march'), 4=>translate('str_month_april'), 5=>translate('str_month_may'), 6=>translate('str_month_june'), 7=>translate('str_month_july'), 8=>translate('str_month_august'), 9=>translate('str_month_september'), 10=>translate('str_month_october'), 11=>translate('str_month_november'), 12=>translate('str_month_december'),
	);
	$rWeekDays = array(
		0=>translate('str_week_monday'),
		1=>translate('str_week_tuesday'),
		2=>translate('str_week_wednesday'),
		3=>translate('str_week_thursday'),
		4=>translate('str_week_friday'),
		5=>translate('str_week_saturday'),
		6=>translate('str_week_sunday'),
	);
	include_once(DIR_INCLUDES.'/handler.message.php');

	checkPath(DIR_TEMP);
	checkPath(DIR_LOG);
	$CurrDivision->loadCustomSettings();
	if (isset($_SESSION['log'])) $smarty->assign('log', $_SESSION['log']);

	$smarty->assign('CurrentDivision', array(
		'id' => $CurrDivision->ID,
		'name' => $CurrDivision->Name,
		'parentID' =>$CurrDivision->ParentID,
		'ukey' => $CurrDivision->UnicKey,
		));

	$smarty_mail->template_dir = DIR_TPLS.'/email';

	if($admin_mode){
		include(DIR_FUNC.'/xml_parser.php');
		include(DIR_FUNC.'/xml_installer/xml_installer.php');
		include(DIR_CFG.'/paths.inc.php' );

		$themeEntry = &ClassManager::getInstance('theme');
		/*@var $themeEntry Theme*/

		$res = $themeEntry->load(CONF_CURRENT_THEME);
		if(PEAR::isError($res)){
			RedirectSQ('demo_theme_id='._getSettingOptionValue('CONF_CURRENT_THEME'));
		}
		$smarty->assign('url_current_theme_css', $themeEntry->getURLOffset().'/main.css');

		$AdminDeps = array();
		$SubDivs = &DivisionModule::getBranchDivisions($AdminDivID, array('xEnabled'=>1));
		foreach ($SubDivs as $_SubDiv){

			$AdminDeps[] = array(
				'id' => $_SubDiv->ID,
				'name' => $_SubDiv->Name,
			);
		}
		$BreadDivs = $CurrDivision->getBreadsToID($AdminDivID);
		if(count($BreadDivs)>1){

			$CurrDptID = $BreadDivs[1]->ID;
		}else {

			$CurrDptID = $CurrDivision->ID;
		}
		sc_checkLoggedUserAccess2Division($CurrDivision, $BreadDivs);

		if($CurrDivision->UnicKey!='admin'){
			$smarty->assign('SubDivs', DivisionModule::getBranchDivisions($CurrDptID, array('xEnabled'=>1)));
		}
		$smarty->assign('current_dpt', $CurrDptID);

		$smarty->assign('admin_departments', $AdminDeps);
		$smarty->assign('admin_departments_count', count($AdminDeps));
		$smarty->assign('admin_main_content_template', 'nav2level.tpl.html');

		$smarty->template_dir = DIR_TPLS;
	}else{
		$themeEntry = &ClassManager::getInstance('theme');
		/*@var $themeEntry Theme*/
		$res = $themeEntry->load(CONF_CURRENT_THEME);
		if(PEAR::isError($res)){
			RedirectSQ('demo_theme_id='._getSettingOptionValue('CONF_CURRENT_THEME'));
		}
		$Register->set('CURRENT_THEME_ENTRY', $themeEntry);

		$smarty->assign('PAGE_VIEW', isset($GetVars['view'])?$GetVars['view']:'');
		$smarty->assign('main_content_template', 'home.html');

		include(DIR_ROOT.'/includes/authorization.php');

		$smarty->assign('categoryID', isset($_GET['categoryID'])?intval($_GET['categoryID']):0);
		$smarty->template_dir = DIR_FTPLS;
	}
	
	$store_mode = false;
	if(isset($GetVars['store_mode'])){
		$store_mode = $GetVars['store_mode'];
		switch($store_mode){
			case 'facebook':{
				if(!$admin_mode&&defined('CONF_FACEBOOK_ENABLED')&&constant('CONF_FACEBOOK_ENABLED')){
					$Register->set('store_mode',$store_mode);
				}else{
					$store_mode = false;
					error404page();
				}
				break;
			}
			case 'vkontakte':{
				if(!$admin_mode&&defined('CONF_VKONTAKTE_ENABLED')&&constant('CONF_VKONTAKTE_ENABLED')){
					$Register->set('store_mode',$store_mode);
				}else{
					$store_mode = false;
					error404page();
				}
				break;
			}
			default:{
				$store_mode = false;
				error404page();
				break;
			}
		}
	}
	
	$modifier = '';
	switch($store_mode){
		case 'facebook':{$modifier = 'fb_';break;}
		case 'vkontakte':{$modifier = 'vk_';break;}
		default:break;
	}
	$smarty->assign('button_add2cart_small', URL_IMAGES.'/add2cart_small_'.$modifier.$LanguageEntry->iso2.'.gif');
	$smarty->assign('button_add2cart_big', URL_IMAGES.'/add2cart_'.$modifier.$LanguageEntry->iso2.'.gif');
	$smarty->assign('button_viewcart', URL_IMAGES.'/viewcart_'.$modifier.$LanguageEntry->iso2.'.gif');
	
	/*@var $CurrDivision Division*/
	$InheritableInterfaces = $CurrDivision->getInheritableInterfaces();
	$Interfaces = $CurrDivision->getInterfaces();
	
	if(!$error404){
		foreach ($InheritableInterfaces as $_Interface){
			ModulesFabric::callInterface($_Interface);
		}
	
		foreach ($Interfaces as $_Interface){
			ModulesFabric::callInterface($_Interface);
		}
	}
	
	if(!$admin_mode){

		//security warnings!
		if (file_exists(DIR_ROOT."/install.php"))$Warnings[] = translate("warning_delete_install_php");

		if (!(is_writable(DIR_TEMP)&is_writable(DIR_PRODUCTS_FILES)&is_writable(DIR_PRODUCTS_PICTURES)&is_writable(DIR_COMPILEDTEMPLATES))){
			$Warnings[] = translate("warning_wrong_chmod");
		}

		//show admin a administrative mode link
		if (isset($_SESSION['log']) && !strcmp($_SESSION['log'], ADMIN_LOGIN)){
			$Warnings[] = "<br><center><a href='".set_query('ukey=admin')."'><font color=red>".translate("lnk_administrativemode")."</font></a></center><br />";
		}
	}

	$smarty->assign('Warnings', $Warnings);
	$smarty->assign( "https_connection_flag", $urlEntry->getScheme()=='https');
	$smarty->assign('show_powered_by',SystemSettings::get('SHOW_POWERED_BY')&&!in_array($CurrDivision->UnicKey,array('cart','checkout','invoice')));
	$smarty->assign('show_powered_by_link',SystemSettings::get('SHOW_POWERED_BY')&&($CurrDivision->UnicKey == 'TitlePage'));
	
	/*
	 * $smarty->assign('main_content_template', '404.html');
	 */

	/*$undefined_smarty_vars = array('main_body_style','printable_version',
			'main_body_tpl','page_not_found404','rss_link','survey_question',
			'show_survey_results','survey_answers','error_message','searchstring',
			'subscribe','GOOGLE_ANALYTICS_SET_TRANS');
	foreach($undefined_smarty_vars as $undefined_smarty_var){
		if($smarty->get_config_vars($undefined_smarty_var)===null){
			$smarty->assign($undefined_smarty_var,null);
		}
	}
	$undefined_get_vars = array('productwidget');
	foreach($undefined_get_vars as $undefined_get_var){
		if(!isset($_GET[$undefined_get_var])){
			$_GET[$undefined_get_var] = null;
		}
	}
*/

	if($error404){
		$smarty->assign('page_title',xHtmlSpecialChars('404 '.translate('err_cant_find_required_page').' ― '.CONF_SHOP_NAME));
	}
	$mobile_version = detectPDA();
	
	$themeEntry = &$Register->get('CURRENT_THEME_ENTRY');
	if(!$admin_mode && is_object($themeEntry)){
		$smarty->assign('URL_THEME_OFFSET', $themeEntry->getURLOffset());
		$smarty->assign('overridestyles', file_exists($themeEntry->getPath().'/overridestyles.css'));
	}
	
	
	if($CurrDivision->MainTemplate && !$mobile_version && !$store_mode && (!isset($_GET['view'])||($GetVars['view']!='mobile'))){

		if(isset($GetVars['view']) && ($GetVars['view'] == 'noframe'||$GetVars['view'] == 'printable')){
			$smarty->assign('main_body_tpl', $smarty->get_template_vars('main_content_template'));
		}
		$smarty->assign('printable_version', (isset($GetVars['view']) &&($GetVars['view'] == 'printable'))?1:false);

		if($Register->is_set('widgets')&&$Register->get('widgets')){
			$smarty->assign('WIDGET_PROCESSING', 1);
		}

		
		$currencyEntry = Currency::getSelectedCurrencyInstance();
		if(is_object($currencyEntry)){
			$smarty->assign('current_currency_js', $currencyEntry->getJSCurrencyInstance());
		}
		$page = $smarty->fetch($CurrDivision->MainTemplate);
	}elseif(!$admin_mode&&$store_mode){
		$smarty->assign('PAGE_VIEW', $store_mode);
		//$smarty->assign('PAGE_VIEW', 'mobile');
		$main_body_tpl = $smarty->get_template_vars('main_content_template');
		if(!$main_body_tpl){
			$main_body_tpl = 'home.html';
		}
		$prefix = false;
		switch($store_mode){
			case 'facebook':{
				$prefix = 'fb';
				break;
			}
			case 'vkontakte':{
				$prefix = 'vk';
				break;
			}
		}
		if($prefix && file_exists(DIR_FTPLS."/{$prefix}.{$main_body_tpl}")){
			$main_body_tpl = $prefix.'.'.$main_body_tpl;
		}
		$currencyEntry = Currency::getSelectedCurrencyInstance();
		if(is_object($currencyEntry)){
			$smarty->assign('current_currency_js', $currencyEntry->getJSCurrencyInstance());
		}
		$smarty->assign('main_body_tpl', $main_body_tpl);
		$page = $smarty->fetch($prefix.($prefix?'.':'').'frame.html');
	}elseif(!$admin_mode){
		define('PDA_VERSION', 1);
		$smarty->assign('PAGE_VIEW', 'mobile');
		$main_body_tpl = $smarty->get_template_vars('main_content_template');
		if(!$main_body_tpl){
			$main_body_tpl = 'home.html';
		}
		if(file_exists(DIR_FTPLS.'/m.'.$main_body_tpl)){
			$main_body_tpl = 'm.'.$main_body_tpl;
		}
		$smarty->assign('main_body_tpl', $main_body_tpl);
		$page = $smarty->fetch('m.frame.html');
	}
	
	
	if(!$admin_mode&&true){
		if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
			include_once(WBS_DIR.'/kernel/classes/class.metric.php');
				
			$DB_KEY=SystemSettings::get('DB_KEY');
			$U_ID = sc_getSessionData('U_ID');
				
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, $U_ID,'SC', 'FRONTEND', $mobile_version?'PDA':'PC');
		}
	}
	
	if($debuger){
		$debuger->end(null,basename(__FILE__));
		onPageComplete($page);
	}else{
		print $page;
	}
?>