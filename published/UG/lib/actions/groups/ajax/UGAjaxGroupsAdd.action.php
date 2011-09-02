<?php 

class UGAjaxGroupsAddAction extends UGAjaxAction 
{
    
 
    public function prepareData()
    {
        $groups_model = new GroupsModel();
        $name = Env::Post('name');
        if ($name) {
	        $group_id = $groups_model->add($name);
	        User::addMetric('ADDUSERGROUP');
            $groups_model->addUsers($group_id, Env::Post('users'));
	        $this->response = $group_id;
        } else {
            $this->errors = _('Please fill group name');
        }
        
    }
}

?>