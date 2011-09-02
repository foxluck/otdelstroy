<?php 

class UGUsersAction extends UGViewAction 
{
    protected $contact_id = null;
    protected $contact_info = array();
    protected $user_id = false;
    protected $ajax = true;
    
    public function __construct($ajax = true)
    {
        parent::__construct();
        $this->ajax = $ajax;
        if (!$this->contact_id) {
        	$this->contact_id = Env::Get('C_ID', Env::TYPE_BASE64, 0);
        }

        if (!$this->contact_id) {
        	$this->contact_id = User::getContactId();
        }
        $this->contact_info = Contact::getInfo($this->contact_id);    
		if (!$this->contact_info) {
			throw new UserException(_('Contact not found'), 'Contact with ID '.$this->contact_id." not found");
		}
        $this->user_id = $this->contact_info['U_ID'];
    }
   
   public function getUserRight()
   { 
       $app_id = User::getAppId();
       
       if ($app_id == 'MW') {
           $user = User::hasAccess('UG');
           $tab_contact = User::hasAccess('MW', Rights::FUNCTIONS, 'TAB_CONTACT');
           $tab_user =  User::hasAccess('MW', Rights::FUNCTIONS, 'TAB_USER'); 
           return array(
               'contact' => $user ? 3 : ($tab_contact ? 3 : 1),
               'user' =>  $user ? 7 : ($tab_user ? 3 : 1),
               'notes' => Wbs::getDbkeyObj()->appExists('CM') && User::hasAccess('CM'),
           	   'admin'    => false,
               'delete' => false
           );
       } elseif ($app_id == 'CM') {
           if (isset($this->contact_info['SC_ID']) && $this->contact_info['SC_ID']) {
               $folder_right = 1;
           } else {
               $folder_right = Contact::accessFolder($this->contact_info['CF_ID']);
           }
           return array(
               'contact' => $folder_right,
               'user' => User::hasAccess('UG') ? 7 : 0,
               'notes' => Wbs::getDbkeyObj()->appExists('CM') && User::hasAccess('CM'),
           	   'admin'    => User::hasAccess('CM', Rights::FUNCTIONS, 'ADMIN'),
               'delete' => ($folder_right >= 3) && ($this->contact_id != User::getContactId()),
           );
           
       } elseif ($app_id == 'UG') {
           return array(
               'contact' => 3,
               'user' => 7,
               'delete' => $this->contact_id != User::getContactId(),
               'notes' => User::hasAccess('CM'),
           	   'admin'    => Wbs::getDbkeyObj()->appExists('CM') && User::hasAccess('CM', Rights::FUNCTIONS, 'ADMIN'),
           );
       }
   }
   
    public function prepareData()
    {	
		$last_time = User::getLastTime($this->user_id);
		$status = (time() - User::getSetting('LAST_TIME', '', $this->user_id) <= User::ONLINE_TIMEOUT) ? 1 : 0;
	    if ($this->contact_info['U_STATUS'] == Groups::USER_LOCKED) {
	        $status = -1;
	    }
		if ($last_time) {
			$last_time = WbsDateTime::getTime($last_time). " " . WbsDateTime::ago($last_time);	
		}		

		if (Env::Session('MESSAGE')) {
		    $this->smarty->assign('message', Env::Session('MESSAGE'));
			Env::unsetSession('MESSAGE');
		}
		
		$this->smarty->assign('user_id', $this->user_id);						
		$this->smarty->assign('contact_id', base64_encode($this->contact_id));
		$this->smarty->assign('name', Contact::getName($this->contact_id));
		$this->smarty->assign('user_status', $this->contact_info['U_STATUS']);
        $this->smarty->assign('type_id', $this->contact_info['CT_ID']);		
		$this->smarty->assign('last_time', $last_time);
		$this->smarty->assign("status", $status);
		
		if (!$this->ajax) {
			$this->smarty->assign("user_groups", User::getGroups($this->user_id));		
			$rights = new Rights($this->user_id);
			$access = $rights->getApps();
			$this->smarty->assign("access", $access);
		    $this->smarty->assign('contact_types', json_encode(ContactType::getTypeNames()));						
		}

		$right = $this->getUserRight();
    	
    	$this->smarty->assign('app_id', User::getAppId());
    	$this->smarty->assign('is_mw', User::getAppId() == 'MW');
		$this->smarty->assign('right', $right);
		$this->smarty->assign('mode', Env::Get('mode'));		
		$this->smarty->assign('tab', Env::Get('tab'));
		$this->smarty->assign('cm_url', Url::get('/CM/'));
    	$this->smarty->assign('contact_info', $this->contact_info);

		if(Wbs::isHosted() && Wbs::getDbkey() == 'WEBASYST') {
			$model = new DbModel();
			$sql = "SELECT * FROM CP_PARTNER WHERE CP_C_ID=i:C_ID";
			$this->smarty->assign('partner_info', $model->prepare($sql)->query(array('C_ID'=>$this->contact_id))->fetchAssoc());		
		}
		
    }
}

?>