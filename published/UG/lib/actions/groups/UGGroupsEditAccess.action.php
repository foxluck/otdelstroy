<?php

class UGGroupsEditAccessAction extends UGGroupsRightsAction
{
    public function __construct()
    {
        parent::__construct();
        $this->template = 'GroupsEdit.html';
    }
    
    
    public function prepareData() 
    {
         $this->smarty->assign('active', 'access');
         $this->smarty->assign('group', $this->group_info);
         parent::prepareData();
    }
}

?>