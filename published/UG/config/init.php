<?php 

    define("WBS_APP_PATH", realpath(dirname(__FILE__)."/../"));
       	
	// Init system
	include_once(WBS_APP_PATH."/../../system/init.php");
	
	// Include UG autoload
    include_once(WBS_APP_PATH."/config/autoload.php");
	
	// Authorize
    if (defined('PUBLIC_AUTHORIZE') && PUBLIC_AUTHORIZE) {
   		// For contact authorization
   		if (Env::Get('key')) {
   			$contact_info = Contact::getInfo(substr(Env::Get('key'), 6, -6));
   			if (isset($contact_info['C_LANGUAGE'])) {
   				$_GET['lang'] = $contact_info['C_LANGUAGE'];
   			} 		    	
   			if (!isset($app_id)) {
   			    $app_id = "CM";
    	    }   			
   		}
    } else {
    	if (!isset($app_id)) {
    		$app_id = "UG";
    	}
    	if (User::getId() && Env::Get('mod') == 'users' && Env::Get('act') == 'settings' && Env::Get('ajax') && !Env::Post('C_ID')) {
    		Wbs::authorizeUser($app_id, true);
    	} else {
			Wbs::authorizeUser($app_id);
    	}
    }   
		
	// Localization (Gettext)
    $lang = Env::Get('lang', Env::TYPE_STRING, false);
    if (!$lang) {
    	$lang = User::getLang();
    }
    $lang = substr($lang, 0, 2);
    
    GetText::load($lang, WBS_APP_PATH."/locale", 'webasystUG');
         
    $smarty = new WbsSmarty(WBS_APP_PATH."/templates", $app_id, $lang);		
	// Set smarty
	Registry::set("UGSmarty", $smarty);
	
	
?>