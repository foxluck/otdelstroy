<?php 

class UGIndexIndexController extends UGController 
{
    public function exec()
    {
        $this->layout = false;
        
        if (User::getAppId() == 'UG') {
            $this->actions[] = new UGIndexUsersAction();
        } else {
            $this->actions[] = new UGIndexContactsAction();
        } 
    }
}

?>