<?php
class configuration extends Module
{
    function initInterfaces()
    {
        $this->Interfaces = array(
            'req_setting' => array(
				'name' => '������ (�����������������)',
				'type' => INTDIVAVAILABLE,
            )
           ,'check_gmapi_key' => array(
				'name' => '������ (�����������������)',
				'type' => INTDIVAVAILABLE,
           )
        );
    }
    
    function getSettingValue($setting_name)
    {
        return constant($setting_name);
    }
    
    function setSettingValue($setting_name, $settings_value)
    {
        $sql = 'UPDATE ?#SETTINGS_TABLE SET `settings_value` = ? WHERE `settings_constant_name` = ?';
        db_phquery($sql,$settings_value,$setting_name);
    }
};
?>