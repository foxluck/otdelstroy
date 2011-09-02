<?php 

class UGUsersListsController extends UGController 
{
 
    public function exec()
    {
        $this->actions[] = new UGUsersListsAction();
    }
    
}

?>