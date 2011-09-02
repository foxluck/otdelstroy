<?php
if(class_exists('moduleadmin',false))return;

class ModuleAdmin extends Module {
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'modules_list' => array(
				'name' 	=> 'Список модулей',
				),
			'module_installation' => array(
				'name' 	=> 'Установка модуля',
				),
			'config_settings' => array(
				'name' 	=> 'Настройки конфигурации',
				),
		);
	}
	
	function ActionsHandler(){
		
		if(!isset($_POST['fACTION']))return true;
		
		switch ($_POST['fACTION']){
			case 'INSTALL_CONFIG':
				$ClassName = isset($_POST['DATA']['ModuleClass'])?$_POST['DATA']['ModuleClass']:'';
				$ClassFile = isset($_POST['DATA']['ModuleFile'])?$_POST['DATA']['ModuleFile']:'';
				if(preg_match('|([\/\\a-z]+\.php)|msi', $ClassFile,$ClassFile))$ClassFile = $ClassFile[1];
				else $ClassFile = null;
				
				if (!file_exists(DIR_MODULES.'/'.$ClassFile))$ClassFile = null;
				
				if(is_null($ClassFile)){
					$this->assign2template('Message',array('type'=>'error','text'=>FILE_DOESNT_EXISTS.': '.$_POST['DATA']['ModuleFile']));
					return false;
				}
				include_once(DIR_MODULES.'/'.$ClassFile);
				
				if(!class_exists($ClassName)){
					$this->assign2template('Message',array('type'=>'error','text'=>CLASS_DOESNT_EXISTS));
					return false;
				}
			
				eval('$Module = new '.$ClassName.';');
				/* @var $Module Module */
				$Module->installConfig($ClassFile, strtolower($ClassName), $_POST['DATA']['ConfigTitle'], $_POST['DATA']['ConfigDescr'], INIT_LOCAL);
				RedirectSQ('?ukey=modules_list'.(!$Module->SingleInstallation?'&showConfigs='.$Module->ID:''));
			break;
			case 'ENABLE_CONFIGS':
			
				if(!count($_POST['CONFIGIDS']))return;
				$sql = '
					UPDATE ?#TBL_MODULE_CONFIGS SET ConfigEnabled=1 WHERE ModuleConfigID IN(?@)
				';
				db_phquery($sql,$_POST['CONFIGIDS']);
				RedirectSQ();
			break;
			case 'DISABLE_CONFIGS':
			
				if(!count($_POST['CONFIGIDS']))return;
				$sql = '
					UPDATE ?#TBL_MODULE_CONFIGS SET ConfigEnabled=0 WHERE ModuleConfigID IN(?@)
				';
				db_phquery($sql,$_POST['CONFIGIDS']);
				RedirectSQ();
			break;
			case 'DEINSTALL_CONFIGS':
			
				if(!count($_POST['CONFIGIDS']))return;
				
				foreach ($_POST['CONFIGIDS'] as $_ID){
					
					$ModuleConfig = new Module($_ID);
					$ModuleConfig->uninstallConfig();
				}
				RedirectSQ();
			break;
		}
	}
}
?>