<?php
class UGGroupsUsersAction extends UGViewAction 
{
    protected $group_id;
	protected $group_info = array();
    protected $time = 0;
    
    public function __construct()
    {
        parent::__construct();
        $this->group_id = Env::Get('id', Env::TYPE_INT);
		$this->group_info = Groups::get($this->group_id);
    }

    public function prepareData()
    {        
        // Get groups of the user
        $users_model = new UsersModel();
        $users_all = $users_model->getAll(true, false, 'C_FULLNAME');
        $users = Groups::getUsers($this->group_id, 'C_FULLNAME');
        $users_include = array();
    	foreach ($users as $user_info) {
    		$users_include[$user_info['U_ID']] = array(
    			$user_info['U_ID'],
    			$user_info['C_FULLNAME']
    		);
    	}    
    	$users_exclude = array();
    	foreach ($users_all as $user_info) {
    		if (!isset($users_include[$user_info['U_ID']])) {
	    		$users_exclude[] = array(
	    			$user_info['U_ID'],
	    			$user_info['C_FULLNAME']
	    		);
    		}
    	}
    	$this->smarty->assign('group_id', $this->group_id);
    	$this->smarty->assign('users_in', json_encode(array_values($users_include)));
    	$this->smarty->assign('users_out', json_encode($users_exclude));        
    }
}
?>