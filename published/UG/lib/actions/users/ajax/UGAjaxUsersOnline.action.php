<?php

class UGAjaxUsersOnlineAction extends UGAjaxAction
{
    public function prepareData()
    {
        $this->response = Groups::countUsers(Groups::ONLINE); 
    }
}

?>