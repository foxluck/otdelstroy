<?php 

class UGAjaxUsersNotesAction extends UGUsersNotesAction
{
	protected $do;
	
	public function __construct()
	{
		parent::__construct();
		if (User::hasAccess('UG')) {
		    $r = 7;
		} elseif (User::getAppId() == 'MW') {
		    $r = User::hasAccess('MW', Rights::FUNCTIONS, 'TAB_CONTACT');
		    if ($r) $r = Rights::RIGHT_WRITE;
		} else {
		    $r = Contact::accessFolder($this->contact_info['CF_ID']);
		}
		if ($r >= Rights::RIGHT_WRITE) {
			$this->do = Env::Get('do');
			switch ($this->do) {
				case "add": 
					$this->addNote();
					break;
				case "edit": 
					$this->editNote();
					break;		
				case "delete": 
					$this->deleteNote();
					break;
			}
		}
	}
	
	public function addNote()
	{
		$contact_notes_model = new ContactNotesModel();
		$contact_notes_model->add($this->contact_id, Env::Post('note'), User::getContactId());
	}

	public function editNote()
	{
		$note_id = Env::Post('id');
		$note = Env::Post('note');
		$contact_notes_model = new ContactNotesModel();
		$contact_notes_model->save($note_id, $note);
	}
	
	public function deleteNote()
	{
		$note_id = Env::Post('id');
		$contact_notes_model = new ContactNotesModel();
		$contact_notes_model->delete($note_id);
	}
}

?>