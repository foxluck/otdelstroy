<?php
//this file indicates listing of all available languages

	class oldLanguage
	{
		var $description; //language name
		var $filename; //language PHP constants file
		var $template; //template filename
		var $iso2;
		var $thumbnail_url;
	}

	//a list of languages
	$lang_list = array();

	//to add new languages add similiar structures
	$langManager = &LanguagesManager::getInstance();
	foreach ($langManager->languages as $languageEntry){
		/*@var $languageEntry Language*/
		
		if(!$languageEntry->enabled())continue;
		$lang_list[$languageEntry->id] = new oldLanguage();
		$tlang = &$lang_list[$languageEntry->id];
		$tlang->description = $languageEntry->getName();
		$tlang->filename = "{$languageEntry->iso2}.php";
		$tlang->template_path = "";
		$tlang->iso2 = $languageEntry->iso2;
		$tlang->thumbnail_url = $languageEntry->getThumbnailURL();
		unset($tlang);
	}
?>