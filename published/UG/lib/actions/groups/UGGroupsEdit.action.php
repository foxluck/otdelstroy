<?php

class UGGroupsEditAction extends UGGroupsUsersAction
{
    
    public function prepareData() 
    {
         $this->smarty->assign('active', 'users');
         $this->smarty->assign('group', $this->group_info);
         parent::prepareData();
    }
}

?>