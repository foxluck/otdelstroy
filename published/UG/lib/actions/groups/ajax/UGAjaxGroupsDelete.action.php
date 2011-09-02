<?php 

class UGAjaxGroupsDeleteAction extends UGAjaxAction 
{
    
 
    public function prepareData()
    {
        $group_id = Env::Post("id", Env::TYPE_INT);
        $groups_model = new GroupsModel();
        $group_info  = $groups_model->get($group_id);
        if (!$group_info) {
            throw new Exception("Group " . $group_id. " not found.");
        }

        $groups_model->delete($group_id);
        
    }
}

?>