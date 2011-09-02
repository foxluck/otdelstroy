<?php
class View extends Smarty {
	
	function __construct(){
		
		parent::Smarty();
		
		$this->security = true;
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

		return $this;
	}
}
?>