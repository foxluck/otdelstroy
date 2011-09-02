<?php

class GoogleMapsAPIKeyChecker extends ActionsController
{
    function main()
    {
        $Register = &Register::getInstance();
        $smarty = $Register->get(VAR_SMARTY);
        
        $gmapi_key = $this->getData('gmapi_key');
        
        $smarty->assign('gmapi_key', $gmapi_key);
        $smarty->assign('admin_sub_dpt', 'google_api/gmapi_key_checker.html');
    }
};

ActionsController::exec('GoogleMapsAPIKeyChecker');

?>