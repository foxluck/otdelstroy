<?php 

class UGUsersAddAction extends UGViewAction
{
	
    public function __construct()
    {
        parent::__construct();
        User::checkLimits();
    }
    
    public function prepareData()
    {

        $contact_type = new ContactType();
        $main_fields = $contact_type->getMainFields();

        $fields = array();
        foreach ($main_fields as $field_id) {
            $fields[] = ContactType::getField($field_id, User::getLang());
        }
        $this->smarty->assign('fields', $fields);
    }
}

?>