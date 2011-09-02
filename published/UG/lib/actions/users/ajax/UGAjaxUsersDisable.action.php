<?php

class UGAjaxUsersDisableAction extends UGAjaxAction 
{
    protected $ids = array();
    protected $status;
    
    protected $right = array('UG', false, false);
    
    public function __construct()
    {
        parent::__construct();
        $this->ids = Env::Post('ids', Env::TYPE_ARRAY_INT, array());
        $this->status = Env::Post('status', ENV::TYPE_INT, 0);
        if ($this->status != 2) {
            $this->status = 0;
        }
    }
    
    public function prepareData()
    {
        if ($this->ids) {
            $users_model = new UsersModel();
            $n = $users_model->setStatusByContactId($this->ids, $this->status);
        } else {
            $n = 0;
        }
        $this->response['users'] = $n;
    }
    
}