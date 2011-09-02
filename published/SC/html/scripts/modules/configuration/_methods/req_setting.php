<?php
class ConfigController extends ActionsController
{
    function ajax_get_setting()
    {
        $val = configuration::getSettingValue($this->getData('setting_name'));
        
        $GLOBALS['_RESULT'] = array(
            'value' => $val
        );
        
        die();
    }
    
    function ajax_set_setting()
    {
        $name = $this->getData('setting_name');
        $val = $this->getData('setting_value');
        
        configuration::setSettingValue($name, $val);
        
        die();
    }
    
    function main()
    {
        die();
    }
};

ActionsController::exec('ConfigController');
?>