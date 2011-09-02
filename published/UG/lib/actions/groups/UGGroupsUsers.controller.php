<?php	
 
class UGGroupsUsersController extends UGController 
{
    
    public function exec()
    {       
    	$this->layout = 'Empty';
       	if ($this->ajax) {
       		$this->actions[] = new UGAjaxGroupsUsersAction();	
       	} else {
       		$this->actions[] = new UGGroupsUsersAction();
       	}
    }

}

?>