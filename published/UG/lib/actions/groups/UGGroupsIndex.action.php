<?php 

class UGGroupsIndexAction extends UGViewAction 
{
    
    
    public function prepareData()
    {
        // Get all groups
        $groups = Groups::getGroups();
        
        // Count normal groups
        $groups_model = new GroupsModel();
        $count_users = $groups_model->countAllUsers();
        foreach ($count_users as $group_id => $info) {
            $groups[$group_id]['users'] = $info['NUM'];
        }
        
        
        
        // Get all users and count users in system groups
        $users_model = new UsersModel();
        $users = $users_model->getAll();
        
        $last_time = $users_model->getAllLastTime();
        
        foreach ($users as $user) {
            if (isset($last_time[$user['U_ID']])) {
                $user['LAST_TIME'] = $last_time[$user['U_ID']]['LAST_TIME'];
            } else {
                $user['LAST_TIME'] = 0;
            }
            $user_groups = Groups::getSystemGroups($user);
            foreach ($user_groups as $group_id) {
                $groups[$group_id]['users'] = isset($groups[$group_id]['users']) ? $groups[$group_id]['users'] + 1 : 1;
            }
        }
        
        // Assign vars to template
        $this->smarty->assign("groups", $groups);
        $this->smarty->assign("users", $users);
    }
}
	
?>