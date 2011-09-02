<?php 

class UGGroupsRenameController extends UGController 
{

    public function exec()
    {
        $this->layout = false;
        $this->actions[] = new UGAjaxGroupsRenameAction();
    }
}

?>