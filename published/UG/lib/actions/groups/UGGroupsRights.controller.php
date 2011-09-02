<?php	
 
class UGGroupsRightsController extends UGController 
{
    
    public function exec()
    {       
       	if ($this->ajax) {
       		$this->actions[] = new UGAjaxGroupsRightsAction();	
       	} else {
       	    $this->layout = 'Empty';
       		$this->actions[] = new UGGroupsRightsAction();
       	}
    }

}

?>