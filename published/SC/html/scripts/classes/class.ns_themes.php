<?php
	class ns_themes{
		
		function __getThemeDataFromFile($file){
			
			$theme = array();
			
			ClassManager::includeClass('xmlNodeX');
			$xnTheme = ClassManager::getInstance('xmlNodeX');
			/*@var $xnTheme xmlNodeX*/
			
			$xnTheme->renderTreeFromFile($file);
			
			$theme = array(
				'id' => $xnTheme->attribute('id'),
				'title' => $xnTheme->getChildData('title'),
				'author' => $xnTheme->getChildData('author'),
				'thumbnail_url' => (strpos($file, DIR_REPOTHEMES)===false?URL_THEMES:URL_REPOTHEMES).'/'.$xnTheme->attribute('id')."/thumbnail.jpg",
			);

			if(realpath(DIR_THEMES.'/'.$theme['id'].'/theme.xml') == realpath($file))
				$theme['last_modified'] = $xnTheme->attribute('last_modified');
			else
				$theme['last_modified'] = '';
			
			return $theme;
		}
		
		function __getThemesInDir($dir){
			
			$themes = array();
			
			if(!file_exists($dir))return $themes;
			$dirEntry = dir($dir);
			while ($file_name = $dirEntry->read()){
				
				if($file_name=='.' || $file_name=='..' || !is_dir($dir."/{$file_name}") || !file_exists($dir."/{$file_name}/theme.xml"))continue;
				
				$theme = ns_themes::__getThemeDataFromFile($dir."/{$file_name}/theme.xml");
				$themes[$theme['id']] = $theme;
			}
			
			return $themes;
		}
		
		function getThemes(){

			$themes = array_merge(ns_themes::__getThemesInDir(DIR_REPOTHEMES), ns_themes::__getThemesInDir(DIR_THEMES));
			
			$sort_function = create_function('$a, $b', 
				'
				$a = strtotime($a["last_modified"]);
				$b = strtotime($b["last_modified"]);
		    if ($a == $b) {
		        return 0;
		    }
		    return ($a > $b) ? -1 : 1;
				');
			
				uasort($themes, $sort_function);
				
				return $themes;
		}
	}
?>