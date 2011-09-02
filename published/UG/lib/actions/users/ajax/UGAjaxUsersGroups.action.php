<?php

class UGAjaxUsersGroupsAction extends UGAjaxAction
{
	public $user_id;
	public $groups = array();
	
	public function __construct()
	{
		$this->user_id = Env::Post('uid', Env::TYPE_BASE64, "");
		$this->groups = Env::Post('groups_in', Env::TYPE_ARRAY_INT, array());
		$this->save();
	}

	public function save()
	{
		// Get groups of the user
        $users_model = new UsersModel();
        $old_groups = $users_model->getGroups($this->user_id);
        $new_groups = array();
        foreach ($this->groups as $group_id) {
        	if (isset($old_groups[$group_id])) {
        		unset($old_groups[$group_id]);
        	} else {
        		$new_groups[] = $group_id;
        	}
        }
        // Delete user from old groups
        $users_model->delFromGroups($this->user_id, array_keys($old_groups));
        // Add user to new groups
        $users_model->addToGroups($this->user_id, $new_groups);        
	}
	
	public function prepareData()
	{
        $groups_model = new GroupsModel();
        $groups_all = $groups_model->getAll();
        
        foreach ($this->groups as $group_id) {
        	$groups_all[$group_id]['UG_F'] = 1;
        }
        
		$rights = new Rights($this->user_id);

		$this->response['apps'] = array_values($rights->getApps()); 
        $this->response['groups'] = array_values($groups_all);
	}
	
	
}

?>