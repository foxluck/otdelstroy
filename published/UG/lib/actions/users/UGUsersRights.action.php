<?php

/**
 * @todo: Fix get this page by contact id. Must be by user id!
 *
 */
class UGUsersRightsAction extends UGUsersAction 
{

    protected $right = array('UG', false, false);
    
    public function __construct()
    {
        parent::__construct();
        // For users only
        if (!$this->user_id) {
        	throw new UserException(_('User not found'));
        }         
    }
    
    public function prepareData()
    {        
        $rights = new Rights($this->user_id);
        // Assign data to Smarty
        $this->smarty->assign('user_status', $this->contact_info['U_STATUS']);
        $this->smarty->assign('login', base64_encode($this->user_id));
        $this->smarty->assign("apps", json_encode($rights->getAll()));
        $this->smarty->assign('self_rights', (int)(User::getId() == $this->user_id));
        
		parent::prepareData();        
    }
} 

?>