<?php
class LocalizationAdmin extends Module {
	
	var $SingleInstallation = true;
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'localizations_list' => array(
				'name' 	=> 'Список локализаций',
				),
			'languages_list' => array(
				'name' 	=> 'Администрирование языков',
				),
			'addmod_language' => array(
				'name' 	=> 'Редактирование настроек языка',
				),
			'add_language' => array(
				'name' 	=> 'Добавление языка',
				),
			'change_default_language' => array(
				'name' 	=> 'Изменить основной язык',
				),
		);
	}
}	
?>