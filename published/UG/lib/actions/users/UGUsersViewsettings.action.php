<?php

class UGUsersViewsettingsAction extends UGViewAction
{
	
	protected $element_id;
	protected $contact_info = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->element_id = Env::Get('mode', Env::TYPE_STRING).(Env::Get('elem') ? Env::Get('elem') : '');
	}
	
	public function prepareData()
	{
		$fields = ContactType::getAllFields(User::getLang(), ContactType::TYPE_FIELD, true, true);
		$this->smarty->assign('fields', $fields);
		if ($fs = Env::Get('f')) {
		    $fs = explode(',', $fs);
		    $visible_fields = array();
		    foreach ($fs as $f) {
		        $id = $f == 'C_NAME' ? 0 : ContactType::getFieldId($f, true);
		        if ($id) {
		            $visible_fields[] = $id;
		        }
		    }
		} else {
			$visible_fields = (array)json_decode(User::getSetting("SHOWFIELDS" . $this->element_id));
			if (!$visible_fields) {
				$visible_fields = (array)json_decode(User::getSetting("SHOWFIELDS"));	
			}
		}
		if (!$visible_fields) {
			$visible_fields = array(ContactType::getFieldId('C_EMAILADDRESS'), ContactType::getFieldId('C_COMPANY'));
		}
		if (!in_array(0, $visible_fields)) {
		    $r = array(0 => array());
		} else {
		    $r = array();
		}
		foreach ($visible_fields as $fid) {
		    $r[$fid] = array();    
		}
		$visible_fields = $r;
		$hide = array();
		$fields[] = array('id' => '0', 'name' => _s('Full name'));
		foreach ($fields as $field) {
			if (isset($visible_fields[$field['id']])) {
				$visible_fields[$field['id']] = array($field['id'], $field['name']);
			} else {
				$hide[] = array($field['id'], $field['name']);
			}
		}
		
		$this->smarty->assign('visible_fields', json_encode(array_values($visible_fields)));
		$this->smarty->assign('hide_fields', json_encode($hide));
	}
}

?>