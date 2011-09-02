<?php
 
class UGUsersContactController extends UGController 
{
    
    public function exec()
    {
        $this->layout = false;
       	$this->actions[] = new UGUsersIndexAction();
    }

}

?>