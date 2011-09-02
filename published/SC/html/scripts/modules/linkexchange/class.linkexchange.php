<?php
include(DIR_FUNC.'/linkexchange_functions.php');

class LinkExchangeController extends ActionsController{

	function ok(){

	}

	function approve_links(){

		if(isset($_POST['LINKS_IDS']))
		foreach($_POST['LINKS_IDS'] as $_linkID){
			le_SaveLink(array('le_lID'=>$_linkID,'le_lVerified'=>date("Y-m-d H:i:s")));
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}

	function decline_links(){

		if(isset($_POST['LINKS_IDS']))
		foreach($_POST['LINKS_IDS'] as $_linkID){
			le_SaveLink(array('le_lID'=>$_linkID,'le_lVerified'=>'NULL'));
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}

	function delete_links(){

		if(isset($_POST['LINKS_IDS']))
		foreach($_POST['LINKS_IDS'] as $_le_lID)
			le_DeleteLink($_le_lID);
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}

	function save_links(){

		$error_message = '';
		foreach($this->getData('LINKS_IDS') as $_linkID){

			$_POST['LINK'][$_linkID]['le_lID'] = $_linkID;
			if(!le_SaveLink($_POST['LINK'][$_linkID])){
				$error_message = translate('le_err_link_exists');
			}
		}
		if($error_message)Message::raiseMessageRedirectSQ(MSG_ERROR, '', $error_message);

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}

	function move_links(){

		foreach($_POST['LINKS_IDS'] as $_linkID){

			le_SaveLink(array('le_lID'=>$_linkID,'le_lCategoryID'=>$_POST['new_le_lCategoryID']));
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}

	function delete_category(){

		if(!$this->getData('le_cID'))RedirectSQ();


		le_deleteCategory($_GET['le_cID']);
		$_links = le_getLinks(
			0,
			le_getLinksNumber('le_lCategoryID = '.$_GET['le_cID']),
			$_GET['le_cID'],
			'le_lID',
			'le_lVerified ASC, le_lURL ASC');
		foreach ($_links as $__link)
			le_SaveLink(array(
				'le_lID' 			=> $__link['le_lID'],
				'le_lCategoryID' 	=> 0
			));

		RedirectSQ('&le_cID=&categoryID=');
	}

	function main(){
		;
	}
}

class LinkExchange extends Module {

	function initInterfaces(){

		$this->Interfaces = array(
			'flinkexchange' => array(
				'name' => 'Обмен ссылками (пользовательская часть)',
				'method' => 'methodFLinkExchange',
				),
			'blinkexchange' => array(
				'name' => 'Обмен ссылками (администрирование)',
				'method' => 'methodBLinkExchange',
				),
		);
	}

	function methodFLinkExchange(){

		global $smarty;
		if(isset($_GET['added'])||isset($_POST['added']))$error = 'le_msg_link_added';
		set_query('added=','',true);

		if(isset($_POST['fACTION']) && $_POST['fACTION'] == 'ADD_LINK'){

			do{

				if(!strlen(str_replace('http://','',$_POST['LINK']['le_lURL']))){

					$error = 'le_err_enter_link';
					break;
				}
				if(!strlen($_POST['LINK']['le_lText'])){

					$error = 'le_err_enter_text';
					break;
				}
				if(CONF_ENABLE_CONFIRMATION_CODE){
					require_once(DIR_CLASSES.'/class.ivalidator.php');
					$iVal = new IValidator();
					if(!$iVal->checkCode($_POST['fConfirmationCode'])){
						$error = translate('err_wrong_ccode');
						break;
					}
				}
				if(strpos($_POST['LINK']['le_lURL'],'http://')){
					$_POST['LINK']['le_lURL'] = 'http://'.$_POST['LINK']['le_lURL'];
				}	
				$new_link = array(
					'le_lText'=>$_POST['LINK']['le_lText'],
					'le_lCategoryID'=>intval($_POST['LINK']['le_lCategoryID']),
					'le_lURL'=>$_POST['LINK']['le_lURL'],
				);	
				if(le_addLink($new_link)){
					break;
				}else{
					$error = 'le_err_link_exists';
				}
			}while(0);

			if(!isset($error))Redirect(set_query('added=ok', $_POST['fREDIRECT']));
		}

		#Links number per page
		$ob_per_list = 20;

		if(empty($_GET['le_categoryID']))$_GET['le_categoryID'] = 0;
		else $_GET['le_categoryID'] = intval($_GET['le_categoryID']);

		$TotalPages = ceil(le_getLinksNumber(($_GET['le_categoryID']?"le_lCategoryID = {$_GET['le_categoryID']}":'1').' AND le_lVerified IS NOT NULL')/$ob_per_list);

		if(empty($_GET['page']))$_GET['page'] = 1;
		else $_GET['page'] = intval($_GET['page'])>$TotalPages?$TotalPages:intval($_GET['page']);
		
		$_SERVER['REQUEST_URI'] = set_query('added=');
		$lister = getListerRange($_GET['page'], $TotalPages);
		$le_Categories = xHtmlSpecialChars(le_getCategories());

		if(isset($_GET['show_all'])||isset($_POST['show_all'])){

			$ob_per_list = $ob_per_list*$TotalPages;
			$smarty->assign('showAllLinks', '1');
			$_GET['page'] = 1;
		}

		$smarty->assign('REQUEST_URI', $_SERVER['REQUEST_URI']);
		$smarty->assign('url_allcategories', set_query('le_categoryID='));
		$smarty->assign('le_categories', $le_Categories);
		$smarty->assign('le_CategoryID', $_GET['le_categoryID']);
		$smarty->assign('curr_page',$_GET['page']);
		$smarty->assign('last_page', $TotalPages);
		if(isset($error)){

			if($error!=STRING_ERROR_LE_LINK_ADDED){

				$smarty->assign('error',$error);
				$smarty->hassign('pst_LINK',$_POST['LINK']);
			}
			else
				$smarty->assign('error_ok',$error);
		}
		$smarty->hassign('le_links',
			le_getLinks(
				$_GET['page'],
				$ob_per_list,
				($_GET['le_categoryID']?"le_lCategoryID = {$_GET['le_categoryID']}":'1')." AND (le_lVerified IS NOT NULL AND le_lVerified <>'0000-00-00 00:00:00' )",
				'le_lID, le_lText, le_lURL, le_lCategoryID, le_lVerified',
				'le_lVerified ASC, le_lURL ASC'));
		if($lister['start']<$lister['end'])$smarty->assign('le_lister_range', range($lister['start'], $lister['end']));
		$smarty->assign('le_categories_pr', ceil(count($le_Categories)/2));

		$smarty->assign('main_content_template', 'links_exchange.tpl.html');
		$smarty->assign('conf_image', URL_ROOT.'/imgval.php?'.generateRndCode(4).'=1');
	}

	function methodBLinkExchange(){

		ActionsController::exec('LinkExchangeController');
		global $smarty;

		$ob_per_list = 20;

		if (CONF_BACKEND_SAFEMODE && $_POST['fACTION']) //this action is forbidden when SAFE MODE is ON
		{
			Redirect( $_POST['fREDIRECT']?set_query('&safemode=yes', $_POST['fREDIRECT']):set_query('&safemode=yes') );
		}

		$msg = '';

		if(isset($_POST['fACTION'])){
		switch ($_POST['fACTION']){
			case 'NEW_LINK_CATEGORY':
				$_ncID = le_addCategory($_POST['LINK_CATEGORY']);
				if($_ncID){

					$_POST['fREDIRECT'] = set_query('categoryID='.$_ncID, $_POST['fREDIRECT']);
					$msg = 'ok';
				}
				else{ $error_message = translate('le_err_link_category_exists');}
			break;
			case 'SAVE_LINK_CATEGORY':
				if(le_saveCategory($_POST['LINK_CATEGORY']))$msg = 'ok';
				else $error_message = translate('le_err_link_category_exists');
			break;
			case 'NEW_LINK':
				if(!strlen(str_replace('http://','',$_POST['LINK']['le_lURL']))){

					$error_message = translate('le_err_enter_link');
					$show_new_link = true;
					break;
				}
				if(!strlen($_POST['LINK']['le_lText'])){

					$error_message = translate('le_err_enter_text');
					$show_new_link = true;
					break;
				}
				if(strpos($_POST['LINK']['le_lURL'],'http://'))
					$_POST['LINK']['le_lURL'] = 'http://'.$_POST['LINK']['le_lURL'];
				$_POST['LINK']['le_lVerified'] = date("Y-m-d H:i:s");
				if(!le_addLink($_POST['LINK'])){

					$show_new_link = true;
					$error_message = translate('le_err_link_exists');
					break;
				}
				$msg = 'ok';
			break;
		}
		if($_POST['fREDIRECT']&&$msg=='ok')Redirect(set_query('action='.$msg, $_POST['fREDIRECT']));
		}

		if($_GET['action'] == 'ok')Message::raiseCurrentMessage(MSG_SUCCESS, 'msg_information_saved');
		elseif($msg)Message::raiseCurrentMessage(MSG_ERROR, $msg);


		if(empty($_GET['categoryID']))$_GET['categoryID'] = 0;
		else $_GET['categoryID'] = intval($_GET['categoryID']);
		$TotalPages = ceil(le_getLinksNumber( $_GET['categoryID']?array('le_lCategoryID'=>$_GET['categoryID']):'1')/$ob_per_list);
		if(empty($_GET['page']))$_GET['page'] = 1;
		else $_GET['page'] = intval($_GET['page'])>$TotalPages?$TotalPages:intval($_GET['page']);

		if(isset($_GET['show_all'])||isset($_POST['show_all'])){

			$ob_per_list = $ob_per_list*$TotalPages;
			$smarty->assign('showAllLinks', '1');
			$_GET['page'] = 1;
		}

		$lister = getListerRange($_GET['page'], $TotalPages);

		if(isset($show_new_link)){

			$smarty->assign('show_new_link', 'yes');
			if(isset($_POST['LINK']))$smarty->hassign('pst_LINK', $_POST['LINK']);
		}
		if(isset($error_message))
			$smarty->assign('error_message', $error_message);
		if(isset($_GET['safemode'])){

			$error_message  = ADMIN_SAFEMODE_WARNING;
		}
		$_SERVER['REQUEST_URI'] = set_query('safemode=&action=');
		$le_Categories = xHtmlSpecialChars(le_getCategories());
		foreach ($le_Categories as $_ind=>$_val)
			$le_Categories[$_ind]['links_num'] = le_getLinksNumber( "le_lCategoryID = {$_val['le_cID']}" );

		$gridEntry = new Grid();

		$gridEntry->registerHeader('le_link_url');
		$gridEntry->registerHeader('le_link_text');
		$gridEntry->registerHeader('le_link_verified');
		$gridEntry->prepare_headers();

		$smarty->assign('le_LinksNumInCategories', le_getLinksNumber());
		$smarty->assign('REQUEST_URI', $_SERVER['REQUEST_URI']);
		$smarty->assign('url_allcategories', set_query('categoryID='));
		$smarty->assign('le_categories', $le_Categories);
		$smarty->assign('le_categories_num', count($le_Categories));
		$smarty->assign('le_CategoryID', $_GET['categoryID']);
		$smarty->assign('curr_page',$_GET['page']);
		$smarty->assign('last_page', $TotalPages);
		$smarty->hassign('le_links',
			le_getLinks(
				$_GET['page'],
				$ob_per_list,
				($_GET['categoryID']?array('le_lCategoryID'=>$_GET['categoryID']):'1'),
				'le_lID, le_lText, le_lURL, le_lCategoryID, le_lVerified',
				'le_lVerified ASC, le_lURL ASC'));
		$smarty->assign('le_lister_range', range($lister['start'], $lister['end']));
		$smarty->assign("admin_sub_dpt", "linkexchange.html");
	}
}
?>