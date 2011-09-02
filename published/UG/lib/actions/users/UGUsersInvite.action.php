<?php
class UGUsersInviteAction extends UGViewAction
{
	public $user_id = "";
	public $user_info = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->title = _('Login')." ".Company::getName();
		$this->hash = Env::Get('key');
		$this->find();				
	}
	
	public function find()
	{
		$sql = "SELECT U.U_ID, C.* 
				FROM WBS_USER U JOIN 
					 CONTACT C ON U.C_ID = C.C_ID 
				WHERE U_STATUS = i:status AND C.C_ID = i:id";

		$users_model = new UsersModel();
		
		$this->user_info = $users_model->prepare($sql)->query(array(
			'status' => Groups::USER_INVITED, 
			'id' => substr($this->hash, 6, -6)
		))->fetch();

		if (!$this->user_info || 
			substr(md5($this->user_info['C_ID']), 0, 6) != substr($this->hash, 0, 6) || 
			User::getSetting('INVITEKEY', 'UG', $this->user_info['U_ID']) != substr($this->hash, -6)) {
			Url::go('/');
		}
	}
	
	public function prepareData()
	{	
    	// Check logo exists
    	$logoFilename = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("AA", "logo.gif");
    	$logoExists = file_exists($logoFilename);
    	$logoTime = ($logoExists) ? filemtime($logoFilename) : null;
    	
    	// Load viewsettings
    	$dbkeyObj = Wbs::getDbkeyObj();
    	$showLogo = ($dbkeyObj->getAdvancedParam("show_company_top") == "yes") && $logoExists;
    	$showCompanyName = ($dbkeyObj->getAdvancedParam("show_company_name_top") != "no");	    

    	$this->smarty->assign('logo_time', $logoTime);
    	$this->smarty->assign('show_logo', $showLogo);
    	$this->smarty->assign('show_company_name', $showCompanyName);
	    $this->smarty->assign('company_name', Company::getName());
	    
	    $theme = (string)($dbkeyObj->getAdvancedParam("theme"));
		if (!$theme) {
			$theme = ($showLogo) ? "1albino" : "darkblue";
		}
	    $this->smarty->assign("theme", $theme);	    
	    
	    $contact_type = new ContactType($this->user_info['CT_ID']);
	    $main_fields = $contact_type->getMainFields();
	    
	    $fields = array();
	    foreach ($main_fields as $f) {
	        $field = ContactType::getField($f, User::getLang($this->user_info['U_ID']));
	        $fields[$field['dbname']] = $field['name'];
	    }
	    $this->smarty->assign('fields', $fields);
	    $this->smarty->assign('contact', $this->user_info);
	    
	}
}
?>