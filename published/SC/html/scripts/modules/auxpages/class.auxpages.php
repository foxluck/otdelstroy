<?php
function cptsettingview_auxpagegroup($params){

	$moduleInstance = &ModulesFabric::getModuleObjByKey('aux_pages');
	/*@var $moduleInstance AuxPages*/

	$pages = $moduleInstance->__getEnabledPages();
	$params['options'] = array();
	foreach ($pages as $page){
		$params['options'][$page['id']] = $page['name'];
	}
	
	if(is_string($params['value']))$params['value'] = explode(':', $params['value']);
	return cptsettingview_checkboxgroup($params);
}

function cptsettingserializer_auxpagegroup($params, $post){

	$Register = &Register::getInstance();
	
	if(!$Register->is_set('__AUXNAV_SERIALIZED') && is_array($post[$params['name']])){
		$post[$params['name']] = implode(':', $post[$params['name']]);
		$reg = 1;
		$Register->set('__AUXNAV_SERIALIZED', $reg);
	}
	return cptsettingserializer_checkboxgroup($params, $post);
}

class AuxAdministrationController extends ActionsController {
	
	function save_order(){
		
		$scan_result = scanArrayKeysForID($_POST, 'priority');
		$sql = '
			UPDATE ?#AUX_PAGES_TABLE SET aux_page_priority=? WHERE aux_page_ID=?
		';
		
		foreach ($scan_result as $aux_id=>$scan_info){
			
			db_phquery($sql, $scan_info['priority'], $aux_id);
		}
		
		Message::raiseAjaxMessage(MSG_SUCCESS, '', 'order_saved');
		die;
	}
	
	function main(){
		
		$moduleEntry = &$this->__params['module'];
		/*@var $moduleEntry AuxPages*/

		global $smarty;
		set_query('safemode=','',true);
				
		if ( isset($_GET['delete']) ){
			
			safeMode(true);
			$moduleEntry->auxpgDeleteAuxPage( $_GET['delete'] );
			RedirectSQ('delete=');
		}
		
		if ( isset($_GET['add_new']) ){
			
			if ( isset($_POST['save']) ){
				$AuxDivision = new Division();
				$max_priority = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT MAX(aux_page_priority) FROM ?#AUX_PAGES_TABLE')+1;
				if(!isset($_POST['aux_page_slug'])||trim($_POST['aux_page_slug'])==''){
					$_POST['aux_page_slug'] = LanguagesManager::ml_getFieldValue('aux_page_name',$_POST);
					$_POST['aux_page_slug'] = make_clean_slug($_POST['aux_page_slug'],'',AUX_PAGES_TABLE,'aux_page_slug');
				}else{
					$_POST['aux_page_slug'] = make_clean_slug($_POST['aux_page_slug'],'',AUX_PAGES_TABLE,'aux_page_slug');
				}
				$AuxID = $moduleEntry->auxpgAddAuxPage( $_POST, $_POST, $_POST, $_POST, isset($_POST['aux_page_enabled'])?1:0, $max_priority,$_POST['aux_page_slug'] );
				$TitlePageID = DivisionModule::getDivisionIDByUnicKey('TitlePage');

				$moduleEntry->addAuxPageNameLocal($AuxID, $_POST);
				
				$AuxDivision->setName($moduleEntry->getAuxPageLocalID($AuxID));
				$AuxDivision->setEnabled(0);
				$AuxDivision->setParentID($TitlePageID);
				
				$AuxDivision->setUnicKey('auxpage_'.$_POST['aux_page_slug']);
				//$AuxDivision->setUnicKey('auxpage_'.$AuxID);//or set aux_slug
				$AuxDivision->save();
				$AuxDivision->addInterface($moduleEntry->getConfigID().'_auxpage_'.$AuxID);
				
				RedirectSQ('add_new=');
			}
			$smarty->assign( 'add_new', 1 );
		}elseif ( isset($_GET['edit']) ){
			if ( isset($_POST['save']) ){
				
				safeMode(true);
				if(!isset($_POST['aux_page_slug'])||strlen(trim($_POST['aux_page_slug']))==0){
					$_POST['aux_page_slug'] = LanguagesManager::ml_getFieldValue('aux_page_name',$_POST);
					$_POST['aux_page_slug'] = make_clean_slug($_POST['aux_page_slug'],'auxpage_',DIVISIONS_TBL,'xUnicKey','xName',$moduleEntry->getAuxPageLocalID($_GET['edit']));
				}else{
					$_POST['aux_page_slug'] = make_clean_slug($_POST['aux_page_slug'],'auxpage_',DIVISIONS_TBL,'xUnicKey','xName',$moduleEntry->getAuxPageLocalID($_GET['edit']));
				}
				$moduleEntry->auxpgUpdateAuxPage( $_GET['edit'], $_POST, 	$_POST,$_POST, $_POST, isset($_POST['aux_page_enabled'])?1:0, $_POST['aux_page_slug']);
				$moduleEntry->updateAuxPageNameLocal( $_GET['edit'], $_POST);
				RedirectSQ('edit=');
			}
			
			
			
			$aux_page = $moduleEntry->auxpgGetAuxPage( $_GET['edit'] );
					
			$smarty->assign( 'aux_page', $aux_page );

			$smarty->assign( 'edit', 1 );
		}else{
			
			$aux_pages = $moduleEntry->auxpgGetAllPageAttributes();
			$smarty->assign( 'aux_pages', $aux_pages );
		}

		//set sub-department template
		$smarty->assign('admin_sub_dpt', 'conf_aux_pages.tpl.html');		
	}
}


class AuxPages extends ComponentModule {
	
	static $enabled_pages = array();
	function getInterface(){
		
		$Args = func_get_args();
		$_InterfaceName = array_shift($Args);
		$Results = array();
		if(isset($this->Interfaces[$_InterfaceName])){
			
			$SubPatterns = array();
			if(preg_match('|auxpage_(\d+)|',$_InterfaceName, $SubPatterns)){
				
				global $smarty;
				$AuxInfo = $this->auxpgGetAuxPage($SubPatterns[1]);
				if(!$AuxInfo['aux_page_enabled'])RedirectSQ('?');
				$page_title = $AuxInfo["aux_page_name"]." ― ".CONF_SHOP_NAME;
				$meta_tags = "";
				if  ( $AuxInfo["meta_description"] != "" )
					$meta_tags .= '<meta name="description" content="'.xHtmlSpecialChars($AuxInfo["meta_description"]).'">'."\n";
				if  ( $AuxInfo["meta_keywords"] != "" )
					$meta_tags .= '<meta name="keywords" content="'.xHtmlSpecialChars($AuxInfo["meta_keywords"]).'">'."\n";
					
				$smarty->assign("page_title",	$page_title );
				$smarty->assign("page_meta_tags", $meta_tags );
				$smarty->assign('aux_page', $AuxInfo['aux_page_text']);
				$smarty->assign('main_content_template', $this->getTemplatePath('frontend/aux_page.html'));
				return '';
			}

			
			$this->__clearInterfaceStack();
			$this->__pushToStack('info', $this->Interfaces[$_InterfaceName]);
			$this->__pushToStack('call_params', $Args);
			$Results = call_user_func_array(array($this,$this->Interfaces[$_InterfaceName]['method']),$Args);
		}
		return $Results;
	}
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'fauxpage'=> array(
				'name' => 'pgn_auxpages',
				'method' => 'methodFAuxPage',
			),
			'bauxpage'=> array(
				'name' => 'pgn_auxpages_admin',
				'method' => 'methodBAuxPage',
			),
		);
		
		$sql = '
			SELECT *, '.LanguagesManager::sql_prepareField('aux_page_name').' AS aux_page_name FROM ?#AUX_PAGES_TABLE ORDER BY `aux_page_priority` ASC
		';
		$Result = $this->dbHandler->ph_query($sql);
		while ($_Row = $Result->fetchAssoc()) {
			
			$this->Interfaces['auxpage_'.$_Row['aux_page_ID']] = array(
				'name' => $_Row['aux_page_name'],
				'method' => 'auxpage_'.$_Row['aux_page_ID'],
			);
			if($_Row['aux_page_enabled']){
				self::$enabled_pages[$_Row['aux_page_ID']] = array(
					'name' => $_Row['aux_page_name'],
					'id' => $_Row['aux_page_ID'],
					'aux_page_slug' => $_Row['aux_page_slug'],
					
				);
			}
		}
		
		$this->__registerComponent('auxpages_navigation', 'cpt_lbl_auxpages_navigation', array(TPLID_GENERAL_LAYOUT), null, 
			array(
				'select_pages' => array('type' => 'select', 'params' => array('name' => 'select_pages', 'title' => '', 'options' => array('all' => 'cpt_lbl_selectaux_type_all', 'selected' => 'cpt_lbl_selectaux_type_selected'), 'onchange' => 'var objDiv = getLayer("cpt-layer-auxpages"); objDiv.style.display=select_getCurrValue(this)=="all"?"none":"";', 'default_value' => 'all')), 
				'auxpages' => array('type' => 'auxpagegroup', 'params' => array('name' => 'auxpages', 'title'=> 'cpt_lbl_selectauxpages','value'=> '', 'options'=> array(), 'before_load' => '<script type="text/javascript">var objDiv = getLayer("cpts-select_pages-select_pages");getLayer("cpt-layer-auxpages").style.display = select_getCurrValue(objDiv)=="all"?"none":"";</script>')),
				'view' => array('type' => 'radiogroup', 'params' => array('name' => 'view', 'title'=> 'cpt_lbl_view','value'=> 'vertical', 'options'=> array('vertical' => 'cpt_lbl_vertical', 'horizontal' => 'cpt_lbl_horizontal'))),
				));
	}

	function __getEnabledPages(){
		
		return self::$enabled_pages;
		$sql = 'SELECT '.LanguagesManager::sql_prepareField('aux_page_name').' AS name, `aux_page_ID` AS `id`, `aux_page_slug` FROM ?#AUX_PAGES_TABLE WHERE aux_page_enabled=1 ORDER BY `aux_page_priority` ASC';
//		return db_phquery_array($sql);
		$Register = &Register::getInstance();
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/* @var $DBHandler DataBase */
		
		$DBRes = $DBHandler->ph_query($sql);
		
		$pages = $DBRes->fetchArrayAssoc();
		
		return $pages;
	}
	
	function cpt_auxpages_navigation(){
		
		list($local_settings) = $this->__getFromStack('call_params');
		if(isset($local_settings['local_settings']))$local_settings = $local_settings['local_settings'];

		$pages = $this->__getEnabledPages();
		
		if(!count($pages))return ;
		
		$allowed_pages = explode(':', $local_settings['auxpages']);
		print '<ul class="'.($local_settings['view'] == 'horizontal'?'horizontal':'vertical').'">';
		foreach ($pages as $page){
			if($local_settings['select_pages'] == 'selected' && !in_array($page['id'], $allowed_pages))continue;
			//print '<li><a href="'.xHtmlSetQuery('?ukey=auxpage_'.$page['id']).'">'.xHtmlSpecialChars($page['name']).'</a></li>';
			print '<li><a href="'.xHtmlSetQuery('?ukey=auxpage_'.($page['aux_page_slug']?$page['aux_page_slug']:$page['id'])).'">'.xHtmlSpecialChars($page['name']).'</a></li>';
		}
		print '</ul>';
	}
	
	function methodBAuxPage(){

		ActionsController::exec('AuxAdministrationController', array(ACTCTRL_POST, ACTCTRL_GET, ACTCTRL_AJAX, ACTCTRL_CUST), array('module' => &$this));
	}
	
	function methodFAuxPage(){

		global $smarty;
		$aux_page = $this->auxpgGetAuxPage( $_GET['show_aux_page'] );

		if ( $aux_page ){
			
			$smarty->assign('page_body', $aux_page['aux_page_text'] );
			$smarty->assign('show_aux_page', $_GET['show_aux_page'] );
			$smarty->assign('main_content_template', 'show_aux_page.tpl.html' );
		}
		else
		{
			$smarty->assign('main_content_template', 'page_not_found.tpl.html' );
		}
	}

	function auxpgGetAllPageAttributes(){
		
		$sql = '
			SELECT * FROM ?#AUX_PAGES_TABLE ORDER BY aux_page_priority ASC
		';
	 	$q = db_phquery($sql);
		$data = array();
		while( $row = db_fetch_row( $q ) ){
			
			LanguagesManager::ml_fillFields(AUX_PAGES_TABLE, $row);
			$data[] = $row;
		}
		return $data;	
	}
	
	function auxpgGetAuxPage( $aux_page_ID ){
		
		$sql = '
			SELECT * FROM ?#AUX_PAGES_TABLE WHERE aux_page_ID=?
		';
	  	$q = db_phquery($sql,$aux_page_ID);
		$row=db_fetch_row($q);
		LanguagesManager::ml_fillFields(AUX_PAGES_TABLE, $row);
		if(!strlen($row['aux_page_slug'])){
			$row['aux_page_slug'] = $row['aux_page_ID'];
		}
		return $row;
	}
	
	function auxpgUpdateAuxPage( $aux_page_ID, $aux_page_name, $aux_page_text, $meta_keywords, $meta_description, $aux_page_enabled,$aux_page_slug ){
		$fields='';
		$name_inj = LanguagesManager::sql_prepareFields('aux_page_name', $aux_page_name);
		foreach ($name_inj['fields'] as $field) $fields.=$field.'=?,';
		$text_inj = LanguagesManager::sql_prepareFields('aux_page_text', $aux_page_text);
		foreach ($text_inj['fields'] as $field) $fields.=$field.'=?,';
		$mkeywords_inj = LanguagesManager::sql_prepareFields('meta_keywords', $meta_keywords);
		foreach ($mkeywords_inj['fields'] as $field) $fields.=$field.'=?,';
		$mdescription_inj = LanguagesManager::sql_prepareFields('meta_description', $meta_description);
		foreach ($mdescription_inj['fields'] as $field) $fields.=$field.'=?,';		
		$sql = 'UPDATE ?#AUX_PAGES_TABLE SET '.$fields.'`aux_page_enabled`=?, `aux_page_slug`=?	WHERE aux_page_ID=?';		
		db_phquery_array($sql,$name_inj['values'],$text_inj['values'],$mkeywords_inj['values'],$mdescription_inj['values'],$aux_page_enabled, $aux_page_slug, $aux_page_ID);	
	
	}
	
	function auxpgAddAuxPage($aux_page_name, $aux_page_text, $meta_keywords, $meta_description, $aux_page_enabled, $aux_page_priority,$aux_page_slug  ){
		
		$name_inj = LanguagesManager::sql_prepareFields('aux_page_name', $aux_page_name, true);
		$text_inj = LanguagesManager::sql_prepareFields('aux_page_text', $aux_page_text,true);
		$mkeywords_inj = LanguagesManager::sql_prepareFields('meta_keywords', $meta_keywords,true);
		$mdescription_inj = LanguagesManager::sql_prepareFields('meta_description', $meta_description,true);
		$fields=$name_inj['fields_list'].','.$text_inj['fields_list'].',';
		$fields.=$mkeywords_inj['fields_list'].','.$mdescription_inj['fields_list'];
		$values_place=str_repeat('?,',
			count($name_inj['values'])+count($text_inj['values'])+
			count($mkeywords_inj['values'])+count($mdescription_inj['values']));
			
		$sql = "INSERT ?#AUX_PAGES_TABLE ( {$fields}, aux_page_enabled, aux_page_priority, aux_page_slug ) ";
		$sql.="VALUES({$values_place}?,?,?)";
				
		db_phquery_array($sql,$name_inj['values'],$text_inj['values'],$mkeywords_inj['values'],$mdescription_inj['values'],$aux_page_enabled, $aux_page_priority,$aux_page_slug);		
		return db_insert_id();
	}
	
	function auxpgDeleteAuxPage( $aux_page_ID ){
		$DivIDs = DivisionModule::getDivisionIDsWithInterface($this->getConfigID().'_auxpage_'.$_GET['delete']);
		DivisionModule::disconnectInterfaces(array($this->getConfigID()=>array('auxpage_'.$_GET['delete'])));
		foreach ($DivIDs as $_ID){
			$Division = new Division($_ID);
			$Division->delete();
		}
		$sql = '
			DELETE FROM ?#AUX_PAGES_TABLE WHERE aux_page_ID=?
		';
		db_phquery($sql, $aux_page_ID);
		
		$languages = LanguagesManager::getLanguages();
		foreach ($languages as $languageEntry){
			/*@var $languageEntry Language*/
			$languageEntry->deleteLocal($this->getAuxPageLocalID($aux_page_ID));
		}
	}
	
	function updateAuxPageNameLocal($aux_page_ID, $data){
		$divisionID = DivisionModule::getDivisionIDByName('pgn_ap_'.$aux_page_ID);
		if($divisionID){
			$AuxDivision = new Division();
			/* @var $AuxDivision Division */
			$AuxDivision->load($divisionID);
			$AuxDivision->setUnicKey('auxpage_'.(strlen($data['aux_page_slug'])?$data['aux_page_slug']:$aux_page_ID));
			$AuxDivision->save();
		}
		
		$languages = LanguagesManager::getLanguages();
		foreach ($languages as $languageEntry){
			/*@var $languageEntry Language*/
			$languageEntry->updateLocal($this->getAuxPageLocalID($aux_page_ID), isset($data['aux_page_name'.'_'.$languageEntry->iso2])?$data['aux_page_name'.'_'.$languageEntry->iso2]:'');
		}
	}
	
	function addAuxPageNameLocal($aux_page_ID, $data){
		
		$languages = LanguagesManager::getLanguages();
		foreach ($languages as $languageEntry){
			/*@var $languageEntry Language*/
			$languageEntry->addLocal($this->getAuxPageLocalID($aux_page_ID), isset($data['aux_page_name'.'_'.$languageEntry->iso2])?$data['aux_page_name'.'_'.$languageEntry->iso2]:'', LOCALTYPE_HIDDEN, 'lsgr_general');
		}
	}
	
	function getAuxPageLocalID($aux_page_ID){
		
		return "pgn_ap_{$aux_page_ID}";
	}
}
?>