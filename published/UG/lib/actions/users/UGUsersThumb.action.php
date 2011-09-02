<?php 

class UGUsersThumbAction extends UGViewAction
{
	protected $contact_id;
	protected $field_id;
	
	public function __construct()
	{
		parent::__construct();
		$this->contact_id = Env::Get('id', Env::TYPE_BASE64_INT, 0);
		$this->field_id = Env::Get('fid', Env::TYPE_INT, 0);
	}

	public function prepareData()
	{
		if ($this->contact_id && $this->field_id) {
			$contact_info = Contact::getInfo($this->contact_id);
			$title = Contact::getName($this->contact_id)." &#151; ";
			$field = ContactType::getField($this->field_id, User::getLang());
			$title .= $field['name'];
			$img = $contact_info[$field['dbname']];
			if (!$img) {
				$img = Url::get('/UG/img/empty-contact'.$contact_info['CT_ID'].".gif");
			}
			$this->smarty->assign('img', $img);
			$this->smarty->assign('title', $title);
		}
	}
}

?>