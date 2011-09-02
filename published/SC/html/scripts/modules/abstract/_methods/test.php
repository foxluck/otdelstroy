<?php
	if(class_exists('TestController'))return;
	
	include_once(DIR_ROOT.'/../../classes/class.httpmessageserver.php');
	
	class TestController extends ActionsController {

		var $_debug = true;
		var $_request_session_data = array();
		/**
		 * @var HttpMessageServer
		 */
		var $_msgServer;
		
		function registerStandardSources(){
			$msgServer = new HttpMessageServer();
			$this->registerSource('msgserver', $msgServer->__request_data);
			$this->_msgServer = &$msgServer;
		}
		
		function preAction($action){
			
			$session_id = $this->getData('session_id');
			
			if(!$session_id)return $this->_badAuth(1);
			
			$current_session_id = session_id();
			session_write_close();

			session_id($session_id);
			session_start();
			$this->_request_session_data = $_SESSION['__HTTPMSG'];
			session_write_close();
			
			session_id($current_session_id);
			session_start();
			if(!isset($this->_request_session_data['verify_part']))return $this->_badAuth(2);
			if($this->getData('auth_key') != md5($this->_request_session_data['verify_part'].':'.$this->getData('auth_part')))return $this->_badAuth(3);
		}
		
		function postAction($action){
			
			$this->_response('success', 1);
			die;
		}
		
		function _badAuth($meta = ''){
			
			$this->_response('public::meta', $meta);
			$this->__action = 'main';
			return;
		}
		
		function _response($key, $value){
			
			$this->_msgServer->putData($key, $value);
		}
		
		function _error_message($message){
			$this->_msgServer->putData('errorr_message', $message);
		}
		
		function main(){
			$this->_response('success', 1);
			$this->_response('message', 'Public response');
			die;
		}
		
		function pecho(){
			
			$this->_response('answer', 'your message is: '.$this->getData('message'));
		}
		
		function update(){
			
			if(!$this->getData('script') || !file_exists(DIR_ROOT.'/_update/'.$this->getData('script')))return $this->_error_message('Update script doesnt exists');
			
			$result = include(DIR_ROOT.'/_update/'.$this->getData('script'));
			
			if(PEAR::isError($result))$this->_error_message($result->getMessage());
			else $this->_msgServer->putData('answer', $result);
		}
	}
	

	ActionsController::exec('TestController', array('msgserver'));
	mysql_close();
	die;
/*
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
/*
	$sort = isset($_GET['sort'])?$_GET['sort']:'en';

	global $local_ids, $__locals, $scanned_files;
			
		$dbq = '
			SELECT ssl1.id,ssl1.value,ssl1.lang_id,ssl1.`group`,ssl2.`value` AS alt_value FROM ?#LOCAL_TABLE ssl1 
			LEFT JOIN ?#LOCAL_TABLE ssl2 
			ON ssl1.id=ssl2.id AND ssl2.lang_id=?
			WHERE ssl1.`lang_id`=?
			GROUP BY `id` ORDER BY '.($sort=='ru'?'value':'alt_value').'
		';
		
		$dbres = db_phquery($dbq, 2, 1);
		$__locals = array();
		$local_ids = array();
		while ($row = db_fetch_assoc($dbres)){
	
			$local_ids[] = $row['id'];
			$__locals[$row['id']] = array(
				'id' => $row['id'],
				'value' => $row['lang_id']!=2?$row['value']:$row['alt_value'],
				'defvalue' => $row['lang_id']==2?$row['value']:$row['alt_value'],
				'group' => $row['group'],
				'num' => 0
			);
		}
		
	function __count_consts($f_path){
		
		global $local_ids, $__locals, $scanned_files;
		
		if(!preg_match('@.php$|.tpl$|.html$|.htm$@', $f_path))return;
		if(isset($scanned_files[$f_path]))return;
		
		$f_contents = file_get_contents($f_path);
		foreach ($local_ids as $loc_id){
			
//			$cnt = preg_match_all('@(?<![a-z_])(?:'.$loc_id.'|lbl_'.$loc_id.')(?![a-z_])@msu', $f_contents, $sp);
//			$cnt = preg_match_all('@[\'"]'.$loc_id.'[\'"]|lbl_'.$loc_id.'(?![a-z_])@msu', $f_contents, $sp);
			$cnt = preg_match_all('@[\'"]'.$loc_id.'[\'"]@msu', $f_contents, $sp);
//			if($loc_id == 'btn_add' && $cnt){
//				;
//				print $f_path.' - '.$cnt;
//			}
			$__locals[$loc_id]['num'] += intval($cnt);
		}
	}
	
	function __scan_dirs($path){
		
		$dp = opendir($path);
		while($f = readdir($dp)){
			
			if($f == '.' || $f == '..')continue;
			
			$f_path = $path.'/'.$f;
			if(is_dir($f_path)){
				
				if(in_array($f, array('smarty', 'pppro', 'templates_c', '.svn', 'xhina', 'tinymce'))){
					continue;
				}
print 'Scanning '.$f_path."\n<br>";
flush();
				__scan_dirs($f_path);
			}elseif (is_file($f_path)){
print 'Parsing '.$f_path."\n<br>";
flush();
				__count_consts($f_path);
			}
		}
		closedir($dp);
	}
	
	__scan_dirs('.');
?>
<table width="100%" border="2">
<tr>
	<td>string id</td>
	<td><a href="<?=xHtmlSetQuery('sort=en')?>">english value</a></td>
	<td><a href="<?=xHtmlSetQuery('sort=ru')?>">russian value</a></td>
	<td>department</td>
	<td>number of uses</td>
</tr>
<? foreach ($__locals as $local){ ?>
<tr>
	<td><?=$local['id']?></td>
	<td><?=htmlspecialchars($local['defvalue'])?></td>
	<td><?=htmlspecialchars($local['value'])?></td>
	<td><?=htmlspecialchars($local['group'])?></td>
	<td><?=$local['num']?></td>
</tr>
<? } ?>
</table>
<?
*/	
//	$moduleInstance = &ModulesFabric::getModuleObjByKey('wrapper');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_params_fixed');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_params_selectable');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'root_categories');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'auxpages_navigation');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_images');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_category');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_details_request');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_related_products');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_add2cart_button');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_description');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_discuss_link');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_rate_form');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_name');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_price');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'product_custom_parameters');
	
	
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'shop_logo');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installModuleComponent', $moduleInstance->getConfigID(), 'main_content_template');
//	ModulesFabric::callModuleInterface('cptmanager', 'cpt_installStandaloneComponent', 'htmlcode');
//	
//	$smarty->assign('compiled_html', ob_get_contents());
//	ob_end_clean()
?>