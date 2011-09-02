<?php

class UGAjaxUsersInviteAction extends UGAjaxAction 
{
	protected $user_id;
	protected $user_info = array(); 
	
	public function __construct()
	{

		parent::__construct();
		$this->hash = Env::Get('key');
		$this->find();
		$this->confirmUser();
	}
	
	public function find()
	{
		$sql = "SELECT U.U_ID, U.C_ID 
				FROM WBS_USER U JOIN 
				CONTACT C ON U.C_ID = C.C_ID 
				WHERE U_STATUS = i:status AND C.C_ID = i:id";
		$users_model = new UsersModel();
		
		$this->user_info = $users_model->prepare($sql)->query(array('status' => Groups::USER_INVITED, 'id' => substr($this->hash, 6, -6)))->fetch();

		if (!$this->user_info || 
			substr(md5($this->user_info['C_ID']), 0, 6) != substr($this->hash, 0, 6) || 
			User::getSetting('INVITEKEY', 'UG', $this->user_info['U_ID']) != substr($this->hash, -6)) {
            header("HTTP/1.0 404 Not Found");
            exit;
		}
		$this->user_id = $this->user_info['U_ID'];
	}	
	
	public function confirmUser()
	{
		$login = Env::Post('login', Env::TYPE_STRING_TRIM, "");
	    if (!$login) {
	        $this->addError(_("Please fill login name."), "login");
        }
		$password = Env::Post('password');
		$confirm_password = Env::Post('confirm_password');
		if (strcmp($password, $confirm_password)) {
		    $this->addError(_("The password and confirmation password do not match."), array("password", "confirm_password"));
		}
		
		if ($this->errors) {
		    return false;
		}
		
        $errors = array();
        $info = Env::Post('info');
        $is_fill = false;
        foreach ($info as $i) {
            if ($i) {
                $is_fill = true;
                break;
            }
        }
        if (!$is_fill) {
            $keys = array_keys($info);
            $info[array_shift($keys)] = $login;
        }
        Contact::save($this->user_info['C_ID'], $info, &$errors);
        if ($errors) {
            foreach ($errors as $e) {
                $this->addError($e['text'], 'info[' . ContactType::getDbName($e['id']) . ']');
            }
            return false;
        } 
        
        try {
            if (trim(strtolower($login)) == 'administrator') {
                throw new Exception(_("This login name is already in use. Please try another name."));
            }
	        $users_model = new UsersModel();
	        $users_model->set($this->user_id, $login, $password, 0);
        } catch (Exception $e) {
        	$this->addError($e->getMessage(), 'login');
        } 		
        
        User::authorizeByLogin($login);
	}
		
    public function prepareData()
    {
        if (!$this->errors) {
            $this->response['url'] = Url::get('/');
        }
    }
    
}
?>