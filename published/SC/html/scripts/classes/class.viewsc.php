<?php
class ViewSC extends Smarty {
	
	function __construct(){
		
		parent::Smarty();
		//TODO use APP_MODE
		$optimize_enable = true;
		
		$this->security = true;
		$this->compile_check = true||!$optimize_enable;
		/**
		 * TODO: Replace all constants in templates and set false
		 */
		$this->security_settings['ALLOW_CONSTANTS'] = true;
		
		$this->security_settings['PHP_HANDLING'] = false;
		$this->security_settings['MODIFIER_FUNCS'] = array();
		$this->security_settings['PHP_TAGS'] = false;
		$this->security_settings['IF_FUNCS'] = array('true', 'false', 'null', 'NULL');
		$this->security_settings['INCLUDE_ANY'] = false;
		$this->secure_dir = array(DIR_THEMES, DIR_TPLS, DIR_REPOTHEMES, DIR_MODULES.'/shipping/templates');
		$this->php_handling = SMARTY_PHP_QUOTE;
		$compile_dir = DIR_COMPILEDTEMPLATES;
		if($optimize_enable){
			$compile_dir .= '/'.LanguagesManager::getCurrentLanguage()->iso2.'/';
		}
		if(SystemSettings::is_hosted()){//&&!is_backend()){
			$demo_theme_id = sc_getSessionData('demo_theme_id');
			if(strlen($demo_theme_id)){
				$compile_dir .= '/'.$demo_theme_id;
			}
		}
		checkPath($compile_dir);
		$this->compile_dir = $compile_dir;
		$this->template_dir = DIR_TPLS;
		
		$this->cache_dir = DIR_SMARTY_CACHE;
		$this->caching = false;
		
		if($optimize_enable){
			$this->register_prefilter(array(__CLASS__,'optimizeTranslate'));
		}

		return $this;
	}
	



	/**
	 *
	 * @param $matches
	 * @return string
	 */
	static function optimizeTranslateCallback($matches){
		if(!isset($matches[2])){
			$matches[2] = 'translate';
		}
		$result = '';
		switch($matches[2]){
			case 'translate':{
				$result = translate($matches[1]);
				//$result = preg_replace('/<br\s*\/>/i','<br>',translate($matches[1]));
				$result = '{literal}'.$result.'{/literal}';
				break;
			}
			case 'transcape':{
				$result = htmlspecialchars(translate($matches[1]), ENT_QUOTES);
				$result = '{literal}'.$result.'{/literal}';
				break;
			}
			default:{
				$result = $matches[0];
				break;
			}
		}
		if(true){
		//	$result = "\n<!-- start static translate {$matches[1]} -->\n{$result}\n<!-- end static translate {$matches[1]} -->\n";
		}
		return $result;
	}

	static function optimizeTranslate($source,&$smarty)
	{
		$patterns = array(
			'@\{["\']{0,1}([\w_]+)["\']{0,1}\|(translate|transcape)\}@',
			'@\{lbl_(\w+)\}@',
		);
		foreach($patterns as $pattern){
			$source = preg_replace_callback($pattern,array(__CLASS__,'optimizeTranslateCallback'),$source);
		}
		return $source;
	}
}
?>