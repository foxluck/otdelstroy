<?php

/**
 * Saving of the settings of users and contacts by ajax
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: UGAjaxUsersSettings.action.php 4582 2009-04-30 06:59:40Z alexmuz $
 */
class UGAjaxUsersSettingsAction extends UGAjaxAction 
{
	public $contact_id;
	protected $contact_info = array();
	protected $user_id = null;
			
	protected $errors = array();
	
	protected $settings = array(
	    'language',
		'TIME_ZONE_ID',
		'TIME_ZONE_DST',
	    'START_PAGE',
	);
		
	public function __construct() 
	{
		// Get params from POST
		$this->contact_id = Env::Post("C_ID", Env::TYPE_BASE64, base64_encode(User::getContactId()));
		$this->contact_info = Contact::getInfo($this->contact_id);
		$this->user_id = $this->contact_info['U_ID'];
		
		if (!$this->user_id) {
			try {
				$this->user_id = $this->createUser();
			} catch (Exception $e) {
				$this->errors[] = array('id' => 'U_ID', 'text' => $e->getMessage());
			}	
		} 
		else { 
			if (!$this->errors) {
				$info = Env::Post('info');
				if ($this->contact_info['U_STATUS'] == 3 && isset($info['U_ID'])) {
					try {
						$this->user_id = User::changeLogin($this->user_id, $info['U_ID']);
						$errors = array();
						Contact::save($this->contact_id, array('C_CREATEMETHOD' => 'ADD'), $errors);
						$users_model = new UsersModel();
						$users_model->setStatus($this->user_id, 0);
						$this->saveSettings();
					} catch (Exception $e) {
						$this->errors[] = array('id' => 'U_ID', 'text' => $e->getMessage());		
					}
				} else {
		    		$this->saveSettings();
				}
			}
		}
	}
		
	public function createUser()
	{
		$info = Env::Post('info');
		if (isset($info['U_ID']) && !$info['U_ID']) {
		    throw new Exception(_('Please fill login name.'));
		}
		$login = isset($info['U_ID']) ? $info['U_ID'] : false;
		$users_model = new UsersModel();
		if ($login) {
		    if (strtolower(trim($login)) == 'administrator') {
			    throw new Exception(_("This login name is already in use. Please try another name."));
			}
	        $user_id = $users_model->add($login, $info['U_PASSWORD'], $this->contact_id);
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
	
	
		
	public function getTime()
	{
		$user_settings_model = new UserSettingsModel();
        $time_zone = $user_settings_model->get($this->user_id, '', 'TIME_ZONE_ID');
        if (!$time_zone) {
            $time_zone = Wbs::getSystemObj()->getTimeZone()->id;
        }
        $time_dst = User::getSetting('TIME_ZONE_DST', '', $this->user_id);
        return WbsDateTime::getTime(time(), $time_zone ? new CTimeZone($time_zone, $time_dst === "" ? true : $time_dst) : false, "<b>H:i</b>");			
	}
		
	public function saveSettings()
	{
		$user_settings_model = new UserSettingsModel();
            foreach (Env::Post('info') as $name => $value) {
            if (in_array($name, $this->settings)) {
            	$this->response[$name] = array(
		            'id' => $name,
		            'value' => $value
		        );
		        if ($name == 'language' && $this->contact_info['U_STATUS'] == 3) {
                    $contacts_model = new ContactsModel();
		            $contacts_model->save($this->contact_id, array('C_LANGUAGE' => $value));
		        }
            	$user_settings_model->set($this->user_id, '', $name, $value);
            	if ($name == 'TIME_ZONE_DST') {           			
	            	$this->response["U_LOCAL_TIME"] = array(
            			'id' => 'U_LOCAL_TIME', 
            			'value' => $this->getTime()
            		); 
            	}
            }
            elseif ($name == 'U_PASSWORD') {
                $users_model = new UsersModel();
                $users_model->setPassword($this->user_id, $value);
            }
            elseif ($name == 'U_STATUS') {
            	if ($this->contact_info['U_STATUS'] != Groups::USER_INVITED && $this->contact_info['U_STATUS'] != $value) {
            		$users_model = new UsersModel();
            		$value = $value == Groups::USER_LOCKED ? Groups::USER_LOCKED : 0;
            		$users_model->setStatus($this->user_id, $value);
  					$this->response[$name] = array(
			            'id' => $name,
			            'value' => "{$value}"
			        );            		
            	} elseif ($this->contact_info['U_STATUS'] != Groups::USER_INVITED) {
  					$this->response[$name] = array(
			            'id' => $name,
			            'value' => "{$this->contact_info['U_STATUS']}"
			        );            		
            	}
            }
            elseif ($name == 'sms') {
				// Save SMS Quota
				$sms_model = new SmsModel();
				$sms_model->setBalance($this->user_id, $value);
				$this->response['sms'] = array(
					'id' => 'sms',
					'value' => $value
				);
			}
            }
            
            if (count($this->response) == 1) {
            $this->response['U_LOCAL_TIME'] = array(
            			'id' => 'U_LOCAL_TIME', 
            			'value' => $this->getTime()
            );	            		
            }
	}
		
	public function getResponse() {
		if ($this->errors) {
			$response = array(
				'status' => 'ERR',
				'error' => $this->errors,
				'data' => '' 
			);
			return json_encode($response);
		} else {
			return parent::getResponse();
		}
	}
		
}
		
?>