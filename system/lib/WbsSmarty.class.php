<?php 

class WbsSmarty extends Smarty
{
    /**
     * Constructor 
     * 
     * @param $template_dir
     * @param $app_id  
     * @param $lang lang 
     * @return WbsSmarty
     */
	public function __construct($template_dir, $app_id = false, $lang = false)
	{
	    // Set delimiter
		$this->left_delimiter = "{{";
		$this->right_delimiter = "}}";
		
    	// Register prefilter
    	if ($app_id) {
    	    if (!function_exists('smarty_gettext_translate')) {
	    	    // Application translate
	        	function smarty_gettext_translate($str) {
	        		return _(str_replace('\"', '"', $str));	
	        	}
		    	function smarty_gettext_localization($tpl_source, &$smarty) {
		    		return preg_replace("/\[\`([^\`]+)\`\]/usie", "smarty_gettext_translate('$1')", $tpl_source);
		    	}
    	    }
	    	$this->register_prefilter('smarty_gettext_localization');			
        	
    	} else {
    	    // System translate
    	    function smarty_gettext_system_translate($str) {
        		return _s(str_replace('\"', '"', $str));	
        	}
	    	function smarty_gettext_system_localization($tpl_source, &$smarty) {
	    		return preg_replace("/\[\`([^\`]+)\`\]/usie", "smarty_gettext_system_translate('$1')", $tpl_source);
	    	}			
	    	$this->register_prefilter('smarty_gettext_system_localization');	
    	}

		$this->template_dir = $template_dir;		
		$this->compile_dir = WBS_SMARTY_DIR . '/compiled';
		$this->cache_dir = WBS_SMARTY_DIR . '/cache';
		$this->compile_id = ($app_id ? $app_id : 'COMMON').($lang ? "_".strtoupper($lang) : "");
		
		if (ini_get('safe_mode')) {
		    $this->use_sub_dirs = false;
		} else {
		    $this->use_sub_dirs = true;
		}
	}
}

?>