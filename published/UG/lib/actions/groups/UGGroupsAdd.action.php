<?php

class UGGroupsAddAction extends UGViewAction
{
    public function prepareData()
    {
        $users_model = new UsersModel();
        $users_all = $users_model->getAll(true, false, 'C_FULLNAME');
        
        $users = array();
        
        foreach ($users_all as $u) {
            $users[] = array($u['U_ID'], $u['C_FULLNAME']);
        }

        $this->smarty->assign('users_out', json_encode($users));
        $this->smarty->assign('users_in', '[]');
    }
}
?>