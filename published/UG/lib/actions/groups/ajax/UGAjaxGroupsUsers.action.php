<?php

class UGAjaxGroupsUsersAction extends UGAjaxAction 
{
	public $group_id;
	public $users = array();
	
	public function __construct()
	{
		$this->group_id = Env::Post('group_id', Env::TYPE_INT, "");
		$this->users = Env::Post('users');
		$this->save();
	}	
	
	public function save()
	{
		// Get groups of the user
        $groups_model = new GroupsModel();
        if (Env::Post('action') == 'del') {
            // Delete old users
            $groups_model->delUsers($this->group_id, $this->users);
        }  else {
	        // Add new users
	        $groups_model->addUsers($this->group_id, $this->users);
        }
	}
	
	
    public function prepareData()   
    {        
    }	
	
}


?>