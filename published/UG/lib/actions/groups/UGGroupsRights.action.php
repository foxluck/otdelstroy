<?php


class UGGroupsRightsAction extends UGViewAction 
{
    protected $group_id;
	protected $group_info = array();
    protected $time = 0;
    
    public function __construct()
    {
        parent::__construct();
        $this->group_id = Env::Get('id', Env::TYPE_INT);
		$this->group_info = Groups::get($this->group_id);
    }
    
    public function prepareData()
    {        
        $rights = new Rights($this->group_id, Rights::GROUP);
       
        // Assign data to Smarty
        $this->smarty->assign('group', $this->group_info);
        $this->smarty->assign("apps", json_encode($rights->getAll()));
        
    }
} 

?>