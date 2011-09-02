<?php

class UGUsersMailAction extends MailAction
{
	protected $contact_id;
	protected $contact_info = array();
	protected $user_id = array();
	
	public function __construct()
	{
		$this->title = _('Send invitation');
		$this->contact_id = Env::Get('id', Env::TYPE_BASE64_INT, 0);
		$this->contact_info = Contact::getInfo($this->contact_id);
		$this->user_id = $this->contact_info['U_ID'];
		
		parent::__construct('UG', 'UGSmarty', $this->getTemplate());
		
		//$this->fields['from'][MailAction::VALUE] = Contact::getName(User::getContactId(), Contact::FORMAT_NAME_EMAIL, false, false, false);
		$this->smarty->assign('send_to',$this->contact_info['C_EMAILADDRESS'][0]);
        $this->smarty->assign('subject', _("Invitation to login")." ".Company::getName());
		
		$this->smarty->assign('uid', base64_encode($this->contact_id));
    	$this->smarty->assign('send_from', Contact::getName(User::getContactId(), Contact::FORMAT_NAME_EMAIL));
		$this->smarty->assign('redactor_id', 'invite');
		
        $url = array(
            'common' => Url::get("/common/"),
            'published' => Url::get("/"),
            'app' => Url::get("/".User::getAppId()."/"),
        	'css' => Url::get('/UG/css/')
        );
        $this->smarty->assign("url", $url);		

        
        $this->smarty->assign('title', $this->title);
        
		// Generate editor instance
		$dsrte = new dsRTE('invite', false, false);
		$this->smarty->assign('editor_scripts', $dsrte->getScripts());
		
		$key = User::getSetting("INVITEKEY", 'UG', $this->user_id);
		if (!$key) {
			$key = substr(md5(uniqid("INVITE")), -6);
			User::setSetting("INVITEKEY", $key, "UG", $this->user_id);
		}
		$key = substr(md5($this->contact_id), 0, 6).$this->contact_id.$key;
		
        $smarty = new WbsSmarty(WBS_APP_PATH."/templates", User::getAppId(), User::getLang());
		$smarty->assign('URL', Url::get("/invite.php?key=".$key.(Wbs::isHosted() ? "" : "&DB_KEY=".base64_encode(Wbs::getDbkeyObj()->getDbkey())), true));
		$content = nl2br($smarty->fetch('mail/Invite.html'));
		
		$this->smarty->assign('editor_HTML', $dsrte->getHTML($content));

		$parts = explode("&", Env::Server('HTTP_REFERER'));
		$mode = $id = "";
		foreach ($parts as $p) {
		    $v = explode("=", $p, 2);
		    if ($v[0] == 'mode') {
		        $mode = $v[1];    
		    }
		    if ($v[0]=='id') {
		        $id = $v[1];
		    }
		}
		$_SERVER['HTTP_REFERER'] = '?mod=users&C_ID='.base64_encode($this->contact_id);
		if ($mode) {
		    $_SERVER['HTTP_REFERER'] .= "&mode=".$mode;
		}
		if ($id) {
		    $_SERVER['HTTP_REFERER'] .= "&id=".$id;
		}
		$_SERVER["HTTP_REFERER"] .= "&tab=settings";
		
		$this->smarty->assign('referer', $_SERVER["HTTP_REFERER"]);
	}
	
	public function getValue($field)
	{
	    return isset($_POST['data'][$field]) ? $_POST['data'][$field] : "";
	}
	
	public function onSend()
	{
	    $t = time();
	    User::setSetting("INVITETIME", $t, 'UG', $this->user_id);
	    
	    // Go to settings page
	    if (Env::Post('referer')) {
	        $url = Env::Post('referer');
	    } else {
	        $url = '?mod=users&C_ID='.base64_encode($this->contact_id)."&tab=settings";
	    }
	    
	    header("Location: ".$url);
	    exit;
	}
	
	
	public function getContent()
	{
		return Env::Post('invite_text');
	}
	
	public function getTemplate()
	{
		$type = Env::Get('type', Env::TYPE_INT, 0);
		switch ($type) {
			// Invite User
			case 1: {
				return 'UsersMail.html';
			}
			// Empty mail
			default: {
				return 'UsersMail.html';
			}
		}
		
	}
	
	
}
?>