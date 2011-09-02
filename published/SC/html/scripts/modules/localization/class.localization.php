<?php
	class Localization extends Module {
		
		var $SingleInstallation = true;
		var $InitType = INIT_GLOBAL;
		
		function initInterfaces(){
			
			$this->Interfaces = array(
				'load_locals' => array(
					'name' 	=> 'Загрузка локалей',
					'method' => 'method_loadLocals',
					),
			);
		}
		
		function method_loadLocals(){
			
			$Register = &Register::getInstance();
			
			$LanguageEntry = &LanguagesManager::getCurrentLanguage();

			$locals = $LanguageEntry->getLocals(LOCALTYPE_BACKEND, false, false);
			
			$Register->set('CURRLANG_LOCALS', $locals);
			$Register->set('CURR_LANGUAGE', $LanguageEntry);
		}
	}
?>