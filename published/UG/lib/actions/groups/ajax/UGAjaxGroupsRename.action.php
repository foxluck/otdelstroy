<?php 

class UGAjaxGroupsRenameAction extends UGAjaxAction 
{

    public function prepareData()
    {
        $groups_model = new GroupsModel();
        $group_id = Env::Post('id', Env::TYPE_INT, 0);
        $name = Env::Post('newName', Env::TYPE_STRING, "");
        if ($group_id && trim($name)) {
            $group_id = $groups_model->rename($group_id, $name);
        } elseif ($group_id) {
        	$this->errors = _("This feld is required");
        }

	    $this->response = $name;
    }
    
}

?>