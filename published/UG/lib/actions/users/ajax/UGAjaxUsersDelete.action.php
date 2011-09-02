<?php

class UGAjaxUsersDeleteAction extends UGAjaxAction 
{
	protected $user_ids = array();
    protected $contact_ids = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->contact_ids = Env::Post('ids', Env::TYPE_ARRAY_INT, array());
		$this->delete();
	}
	
	
    public function delete()
    {
        // Getting info for optimize check permissions to delete
        $contacts_model = new ContactsModel();
        $contacts_info = $contacts_model->getByIds($this->contact_ids);
        $count = array(
            'deleted' => 0,
            'users' => 0,
            'noaccess' => 0
        );
        $users_model = new UsersModel();
        $rights = new Rights(User::getId());
        foreach ($contacts_info as $contact) {
            if ($contact['C_ID'] == User::getContactId()) {
                $count['self'] = User::getName();
                continue;
            }
            // If contact is user and current user has access to UG
            if ($contact['U_ID']) {
                if (User::hasAccess('UG')) {
                    if (!Env::Post('users_only')) {
                        $contacts_model->delete($contact['C_ID']);
                    }
                    $users_model->delete($contact['U_ID'], false);                    
                    $count['deleted']++;
                } else {
                    $count['users']++;
                }
            } else {
                if (($contact['CF_ID'] && Contact::accessFolder($contact['CF_ID']) >= 3) || User::isAdmin('CM')) {
                    $contacts_model->delete($contact['C_ID']);
                    $count['deleted']++;
                } else {
                    $count['noaccess']++;
                }
            }
        }
        $this->response = $count;
    }    
}
?>
