<?php 

class UGUsersNotesAction extends UGUsersAction
{
	const LIMIT = 10;
	protected $contact_id;
	protected $contact_info = array();
	protected $offset = 0;
	
    public function __construct()
    {
        if (Env::isPost()) {
        	$this->contact_id = Env::Post('C_ID', Env::TYPE_BASE64, 0);
        } else {
        	$this->contact_id = Env::Get('C_ID', Env::TYPE_BASE64, 0);
        }
        
        // Yourself
        if (!$this->contact_id) {
        	$this->contact_id = User::getContactId();
        }
    	$this->contact_info = Contact::getInfo($this->contact_id);
		if (!$this->contact_info) {
			throw new UserException(_s('Contact not found'), 'Contact with ID '.$this->contact_id." not found");
		}
        parent::__construct();
		
     	$this->contact_info = Contact::getInfo($this->contact_id);
     	$this->offset = Env::Post('offset', Env::TYPE_INT, 0); 
    }

    public function getNotes()
    {
    	$contact_notes_model = new ContactNotesModel();
    	$data = $contact_notes_model->getByContactId($this->contact_id, $this->offset.", ".self::LIMIT);
    	$notes = array();
    	foreach ($data as $row) {
    		$notes[] = array(
    			'id' => $row['CN_ID'],
    			'date' => WbsDateTime::getTime(strtotime($row['CN_CREATETIME'])),
    			'author' => Contact::getName($row['CN_CREATECID']),
    			'text' => $row['CN_TEXT']
    		);
    	}
    	return $notes;
    }
    
    public function prepareData()
    {
    	$contact_notes_model = new ContactNotesModel();
    	$n = $contact_notes_model->countByContactId($this->contact_id);
    	$page = ceil($this->offset / self::LIMIT) + 1;
		$this->smarty->assign('num_pages', ceil($n / self::LIMIT));
		$this->smarty->assign('page', $page);
		$this->smarty->assign('limit', self::LIMIT);
		
		if (User::hasAccess('UG')) {
		    $r = 7;
		} elseif (User::getAppId() == 'MW') {
		    $r = User::hasAccess('MW', Rights::FUNCTIONS, 'TAB_CONTACT');
		    if ($r) $r = Rights::RIGHT_WRITE;
		} else {
		    $r = Contact::accessFolder($this->contact_info['CF_ID']);
		}

		
		$this->smarty->assign('right_edit', $r >= Rights::RIGHT_WRITE);
		    	
    	$this->smarty->assign("name", Contact::getName($this->contact_id));
    	$notes = $this->getNotes();
    	$this->smarty->assign("notes", $notes);
    	if (!$notes) {
    	    $contact_type = new ContactType($this->contact_info['CT_ID']);
    	    $contact_type_name = $contact_type->getTypeName(null);
    	    $no_notes = _("There are no notes about this ".mb_strtolower($contact_type_name));
    	    $this->smarty->assign('no_notes', $no_notes); 
    	}
    	$this->smarty->assign("contact_id", base64_encode($this->contact_id));
    	parent::prepareData();
    }
}

?>