<?php
//	$Register = &Register::getInstance();
//	$smarty = &$Register->get(VAR_SMARTY);
/* @var $smarty Smarty */

$local_settings = &$Args[0]['local_settings'];
$global_settings = &$Args[0]['global_settings'];
$theme_id = isset($_GET['theme_id'])?$_GET['theme_id']:CONF_CURRENT_THEME;
$code_info = htmlCodesManager::getCodeInfo($local_settings['code'],$theme_id);

if(!isset($code_info['code']) || !$code_info['code']){

	//DEBUG:
	if(false){
		if($fp = fopen(DIR_TEMP.'/missed_customhtml.log','a')){
			$path = isset($_GET['theme_id'])?$_GET['theme_id']:CONF_CURRENT_THEME;
			fwrite($fp,"{$local_settings['code']}\t{$path}\n");
			fclose($fp);
		}
		print "<div>MISSED BLOCK [{$local_settings['code']}]</div>";
	}

	return ;
}
if(!$code_info['title']){
	$code_info['title'] = $theme_id;
	htmlCodesManager::updateCode($code_info['key'],$code_info);
}

if((strpos($code_info['code'], '{lbl_')!==false) && preg_match_all('/\{lbl_([^}]+)\}/u', $code_info['code'], $sp)){
	foreach ($sp[1] as $string){
		$code_info['code'] = str_replace('{lbl_'.$string.'}', translate($string), $code_info['code']);
	}
}
if((strpos($code_info['code'], '{$smarty.const.')!==false) && preg_match_all('/\{\$smarty.const.([^}]+)\}/u', $code_info['code'], $sp)){
	foreach ($sp[1] as $string){
		if(defined($string)){
			$code_info['code'] = str_replace('{$smarty.const.'.$string.'}', constant($string), $code_info['code']);
		}
	}
}

print $code_info['code'];
?>