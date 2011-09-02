<?php

class UGAjaxUsersAddAction extends UGAjaxAction
{    
		protected $info = array();
		protected $type_id = 1;
		protected $contact_id;
		
		protected $errors = array();
		
		public function __construct() 
		{
			Contact::checkLimits(1);
			$this->info = Env::Post('info', false, array());		
			$login = isset($this->info['U_ID']) ? $this->info['U_ID'] : false;
			if (strtolower(trim($login)) == 'administrator') {
			    throw new Exception(_("This login name is already in use. Please try another name."));
			}
			unset($this->info['U_ID']);
			if ($this->info) {
			     if ($login) {
			        $is_fill = false;
			        foreach ($this->info as $k => $i) {
			            if ($i) {
			                $is_fill = true;
			                break;
			            }
			        }
			        if (!$is_fill) {
			            $keys = array_keys($this->info);
			            $this->info[array_shift($keys)] = $login;
			        }
			        $this->info['C_CREATEMETHOD'] = 'ADD';
			     } else {
			        $this->info['C_CREATEMETHOD'] = 'INVITED';
			     }
				$this->contact_id = Contact::add($this->type_id, $this->info, $this->errors, true);
				if ($this->contact_id) {
					try {
						$this->user_id = $this->createUser();
					} catch (Exception $e) {
						$contacts_model = new ContactsModel();
						$contacts_model->delete($this->contact_id);
						$this->errors = $e->getMessage();
					}	
				}				
			}
		}
		
		public function createUser()
		{
			$info = Env::Post('info');
			$login = isset($info['U_ID']) ? $info['U_ID'] : false;
			if (isset($info['U_ID']) && !$login) {
				throw new Exception(_("Please fill login name"));
			}
			$users_model = new UsersModel();
			if ($login) {
		        $user_id = $users_model->add($login, Env::Post('password'), $this->contact_id);
		        User::addMetric('ADDUSER');
			} else {
	        	$user_id = $users_model->add('$INVITED'.$this->contact_id, false, $this->contact_id, Groups::USER_INVITED);
	        	User::addMetric('INVITEUSER');				
			}
			// Set lang for new user
			User::setSetting('language', User::getLang(), false, $user_id);
			User::setSetting('TIME_ZONE_ID', User::getSetting('TIME_ZONE_ID', false), false, $user_id);
			User::setSetting('TIME_ZONE_DST', User::getSetting('TIME_ZONE_DST', false), false, $user_id);
			$contacts_model = new ContactsModel();
			$contacts_model->save($this->contact_id, array('C_LANGUAGE' => User::getLang()));			
			return $user_id;
		}		
				
		/**
		 * Returns PHP response
		 *
		 * @return array
		 */
		public function getResponse()
		{	
			$response = array(
				'status' => $this->errors ? 'ERR' : 'OK',
				'error' => $this->errors 
			);

			$response['data'] = base64_encode($this->contact_id);

			return json_encode($response);	
		}
	
}
?>
