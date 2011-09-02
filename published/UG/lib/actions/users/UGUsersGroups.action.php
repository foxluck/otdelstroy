<?php
class UGUsersGroupsAction extends UGUsersAction
{
	
	public function prepareData()
	{
        $users_model = new UsersModel();
        $groups = $users_model->getGroups($this->user_id);
        
        $groups_model = new GroupsModel();
        $groups_all = $groups_model->getAll();
        
        foreach ($groups as $group_id => $group_name) {
        	$groups_all[$group_id]['UG_F'] = 1;
        }

        $this->smarty->assign('login', base64_encode($this->user_id));
		$this->smarty->assign('groups', json_encode(array_values($groups_all)));
		
		parent::prepareData();
	}
}
?>