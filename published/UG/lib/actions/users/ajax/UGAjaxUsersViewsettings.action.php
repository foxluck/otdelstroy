<?php

/**
 * Saving of the view settings of users and contacts by ajax
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id$
 */
class UGAjaxUsersViewsettingsAction extends UGAjaxAction 
{
	protected $element_id;
	
	public function __construct() 
	{
		parent::__construct();
		if (Env::Post('save')) {
			$this->save();
		}		
	}

	public function save()
	{
		$this->element_id = Env::Post('mode', Env::TYPE_STRING).Env::Post('elem');
		
		$this->fields = Env::Post('fields', Env::TYPE_ARRAY_INT, array());
		if (Env::Post('elem') !== false) {
		    User::setSetting('SHOWFIELDS'.$this->element_id, json_encode($this->fields));
		} else {
		    $user_settings_model = new UserSettingsModel();
		    $user_settings_model->deleteAll(User::getId(), User::getAppId(), 'SHOWFIELDS');
		    User::setSetting('SHOWFIELDS', json_encode($this->fields));   
		}
		
        $fields = ContactType::getAllFields(User::getLang());
        if (!in_array(0, $this->fields)) {
            $show_fields = array(
            	array('C_NAME', 'Name', 1)
            );
        } else {
            $show_fields = array();
        }
        foreach ($this->fields as $f_id) {
            if (isset($fields[$f_id])) {
        	    $show_fields[] = array($fields[$f_id]['dbname'], $fields[$f_id]['name'], 1);
            } elseif ($f_id == 0) {
                array('C_NAME', _('Name'), 1);
            }
        }
		$this->response['fields'] = $show_fields;
	}
}

?>