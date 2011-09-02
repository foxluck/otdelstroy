<?php
	/**
	 * Enter description here...
	 *
	 * @param array $params: ('name' => string, 'values'=>array, 'table'=>string)
	 * @param Smarty $smarty
	 * @return string
	 */
	function smarty_function_html_text($params, &$smarty){
		$uid = rand_name(2);
		extract($params);
		
		$langManager = &LanguagesManager::getInstance();
		$languages = $langManager->languages;
		$default = $langManager->getDefaultLanguage();
		$html_langs_hidden = '';
		$html_langs = '';
		$dbfield = isset($dbfield)?$dbfield:$name;

		$lang_num = count($languages);
		foreach ($languages as $languageEntry){
			/*@var $languageEntry Language*/
			$lang_iso2 = $languageEntry->iso2;
			$input_name = $name.'_'.$lang_iso2;
			if(strpos($name,'%lang%') !== false){
				$input_name = str_replace('%lang%', $lang_iso2, $name);
			}
			if(isset($namespace)){
				$input_name = "{$namespace}[$input_name]";
			}
			
			$lang_html = "\n".'
			<tr>
				'.($lang_num>1?'
				<td style="vertical-align:top; padding-top:5px!important;'.(isset($style)&&preg_match('@width:\s?100\%@', $style)?' width:1%;':'').'" >'.($languageEntry->getThumbnailURL()?'<img style="margin: auto 4px;" src="'.xHtmlSpecialChars($languageEntry->getThumbnailURL()).'" alt="'.xHtmlSpecialChars($languageEntry->getName()).'">':xHtmlSpecialChars($languageEntry->getName())).'</td>
				':'').'
				<td>
					<input type="text" lang="'.$lang_iso2.'"'.(isset($id)?" id=\"{$id}_{$lang_iso2}\"":'').' name="'.xHtmlSpecialChars($input_name).'" value="'.xHtmlSpecialChars($values[$dbfield.'_'.$lang_iso2]).'" '.(isset($size)?"size=\"{$size}\"":'').' '.(isset($style)?"style=\"{$style}\"":'').(isset($class)?" class=\"{$class}\"":'').'>
				</td>
			</tr>';
			if($values[$dbfield.'_'.$lang_iso2] || ($default->iso2 == $lang_iso2)|| ($languageEntry->isDefault())){
				
				$html_langs .= $lang_html;
			}else{
				
				$html_langs_hidden .= $lang_html;
			}
		}
		$id = $input_name.'s_hd-'.$uid;
		$id = preg_replace('/[^\w%]+/s','_',$id);

		return '
		<table'.(isset($style)?' style="'.$style.'"':'').' cellpadding="0" cellspacing="0" class="lang_fields">
		<tr>
			<td><table'.(isset($style)?' style="'.$style.'"':'').' cellpadding="0" cellspacing="0">'.$html_langs.'</table>
			'.($lang_num>1&&$html_langs_hidden?'
				
				<div id="'.xHtmlSpecialChars($id).'" style="display:none;">
				<table'.(isset($style)?' style="'.$style.'"':'').' cellpadding="0" cellspacing="0">'.$html_langs_hidden.'</table>
				</div></td>
				
			<td valign="bottom" '.(isset($style)&&preg_match('@width:\s?100\%@', $style)?' style="width: 1%;"':'').'><a title="'.xHtmlSpecialChars(translate('loc_show_empty_translations')).'" href="#expand" class="expand_languages" rel="'.xHtmlSpecialChars($id).'"><img style="margin: auto 4px;" src="./images_common/show_other_languages.gif" alt="'.xHtmlSpecialChars(translate('loc_show_empty_translations')).'" width="11" height="11"></a>':'').'</td>
		</tr>
		</table>
		';
	}
?>