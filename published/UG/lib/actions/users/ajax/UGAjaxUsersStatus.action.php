<?php

class UGAjaxUsersStatusAction extends UGAjaxAction
{
    protected $contact_id;
    protected $conatct_info = array();
    protected $status = 0;
    
    public function __construct()
    {
        parent::__construct();
        $this->contact_id = Env::Post('contact_id', Env::TYPE_BASE64_INT, 0);
        $this->contact_info = Contact::getInfo($this->contact_id);
        if (!$this->contact_info || !$this->contact_info['U_ID']) {
            $this->addError(_s('User not found'));
        }
        $this->status = Env::Post('status', Env::TYPE_INT, 0);
    }
    
    public function prepareData()
    {
        $users_model = new UsersModel();
        $status = $this->status ? '0' : Groups::USER_LOCKED;
        $users_model->setStatus($this->contact_info['U_ID'], $status);
        if ($this->status) {
       		$status = (time() - User::getSetting('LAST_TIME', '', $this->contact_info['U_ID']) <= User::ONLINE_TIMEOUT) ? 1 : 0;
            $this->response = $status;
        } else {
            $this->response = -1;
        }
    }
}

?>