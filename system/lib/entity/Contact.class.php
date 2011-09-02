<?php

/**
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: Contact.class.php 10146 2011-05-20 11:46:01Z alexmuz $
 */
class Contact
{
    
    const FORMAT_NAME_COMPANY_EMAIL = 1;
    const FORMAT_NAME_COMPANY = 2;
    const FORMAT_NAME_EMAIL = 3;
    
    protected static $formats = array(
        self::FORMAT_NAME_COMPANY_EMAIL => array(
            array('field' => 'NAME'),
            array('field' => 'C_COMPANY', 'split' => ' — '),
            array('field' => 'C_EMAILADDRESS', 'split' => ' ', 'prefix' => '<', 'postfix' => '>')    
        ),
        self::FORMAT_NAME_COMPANY => array(
            array('field' => 'NAME'),
            array('field' => 'C_COMPANY', 'split' => ' — ')   
        ),
        self::FORMAT_NAME_EMAIL => array(
            array('field' => 'NAME', 'prefix' => '"', 'postfix' => '"'),
            array('field' => 'C_EMAILADDRESS', 'split' => ' ', 'prefix' => '<', 'postfix' => '>')   
        )
    ); 
    
    protected static $type_fields = array();
    
    public static $methods = array(
        'CM' => array(
            'ADD' => 'Added by user',
            'FORM' => 'Subscription via Sign-up form'
        ), 
        'UG' => array(
            'ADD' => 'Added by administrator',
            'INVITED' => 'Subscription via invitation'
        ),
        'MM' => array(
            'GET-EMAIL' => 'Auto-added when receiving a message from a new sender',
            'SEND-EMAIL' => 'Auto-added when sending a message to a new recipient'
        ),
        'ST' => array(
            'ADD' => 'Added by user',
            'EMAIL' => 'Auto-added when receiving an Email request from a new client',
            'FORM' => 'Receiving a new request from a new client via web-form'
        ),
        'SC' => array(
            'ADD' => 'Added by user',
            'CHECKOUT' => 'Registration when checking out on the shop website'
        ),
        'MT' => array(
        	'CUSTOMER' => 'Customer'
        )
    );
    
	protected static $use_store = true;
	protected static $store = array();
	
	public static $known_extensions = array("jpg", "jpeg", "gif", "png");	

	
	public static function useStore($use_store)
	{
		self::$use_store = $use_store;	
	}

	/**
	 * Returns id or info od the contact by email address
	 * 
	 * @param string $email - need email
	 * @param bool $info - if true function returns full info of the contact
	 * @return int|array
	 */
	public static function getByEmail($email, $info = false) 
	{
		$contacts_model = new ContactsModel();
		$contact_id = $contacts_model->getIdByEmail($email);
		if ($info) {
			return self::getInfo($contact_id);
		} else {
			return (int)$contact_id;
		}
	}
	
	/**
	 * Returns available folders of contacts for current user 
	 * @return unknown_type
	 */
	public static function getFolders()
	{
        $rights = new Rights(User::getId());
        $folders = $rights->getFolders('CM', false, true, Rights::FLAG_ARRAY_OFFSET | Rights::FLAG_RIGHTS_INT | Rights::FLAG_NOT_EMPTY);
        // Advanced folders
        $add_folders = array();
        if (User::hasAccess('CM', Rights::FOLDERS, 'PRIVATE')) {
	        $add_folders[] = array(
	        	'ID' => 'PRIVATE'.User::getContactId(), 
	            'NAME' => _s("Private"),
	            'RIGHTS' => 3,
	            'OFFSET' => 1,
	        );
        }
        if (User::getSetting('PUBLIC', 'CM', '')) {
	        $add_folders[] = array(
	        	'ID' => 'PUBLIC', 
	            'NAME' => _s("Public"),
	            'RIGHTS' => 3,
	            'OFFSET' => 1,
	        );        
        }
        return array_merge($add_folders, $folders);
	} 
	
	public static function accessFolder($folder_id)
	{
	    if ($folder_id == 'PUBLIC') {
	        return User::getSetting('PUBLIC', 'CM', '') ? Rights::RIGHT_WRITE : 0;
	    } elseif ($folder_id == 'PRIVATE'.User::getContactId()) {
	        return User::hasAccess('CM', 'FOLDERS', 'PRIVATE') ? Rights::RIGHT_WRITE : 0;
	    } else {
          	$rights = new Rights(User::getId());
        	return $rights->get('CM', Rights::FOLDERS, $folder_id, Rights::MODE_ONE);	        
	    }
	}
	
	public static function getCurrentFolder($right = false)
	{
	    $folder_id = User::getSetting('LASTFOLDER', 'CM');
	    if ($folder_id && $right) {
	        if (User::hasAccess('CM', Rights::FOLDERS, $folder_id) < $right) {
	            return 'ALL';
	        }
	    }
	    return $folder_id;
	}
	
	
	/**
	 * Add new subscriber
	 * 
	 * @param array $contact_info
	 * @param array $lists
	 * @param bool $doptin
	 * @return int
	 */
	public static function addSubscriber($folder_id, $contact_info, $lists, $widget_id, &$errors = array()) 
	{
	    WbsDateTime::init();
	    $widget = new ContactWidget($widget_id);
	    $params = $widget->getParam();
		$contact_info['CF_ID'] = $folder_id;
		$contact_info['C_SUBSCRIBER'] = "0";
		$contact_info['C_CREATEAPP_ID'] = 'CM';
		$contact_info['C_CREATEMETHOD'] = 'FORM'; 
		$contact_info['C_CREATESOURCE'] = Env::Post('source');
		$email = $contact_info['C_EMAILADDRESS'];
		if (is_array($email)) {
			$email = array_shift($email);
		}
	    $type_id = $widget->getParam('CT_ID', 1);
	    GetText::load($widget->getInfo('WG_LANG'), SYSTEM_PATH . "/locale", 'system', false);
	    
	    $contact_id = null;
	    if (!isset($params['EMAILSEND']) || !$params['EMAILSEND']) {
	    	// if email is unique
	    	$contact_type = new ContactType($type_id);
	    	$fields = $contact_type->getFields();
	    	$email_id = ContactType::getFieldId('C_EMAILADDRESS');
	    	$email_field = $fields[$email_id];
	    	if ($email_field['unique']) {
	    		// try find contact with same email
	    		$contact_id = Contact::getByEmail($email);
	    	}
	    } 
	    
	    if (!$contact_id) {
			$contact_id = self::add($type_id, $contact_info, $errors, false);
	    }
	    
		if ($contact_id) {
			$contact_lists_model = new ContactListModel();
			$contact_lists_model->addToLists($contact_id, $lists);
			
			if ($params['EMAILSEND'] || $params['DOPTIN']) {
				// Send mail
				$message = Mailer::composeMessage();
				// Ignore unsubscriber
				$message->addAppID('-u');
				$message->addSubject($params['EMAILSUBJECT']);
				$content = $params['EMAILTEXT'];
	
				$email = self::getInfo($contact_id, false, 'C_EMAILADDRESS');
				$email= $email[0];
				
				$name = self::getName($contact_id);
				
				$content = str_replace('{COMPANY_NAME}', Company::getName(), $content);
				$content = str_replace('{NAME}', $name, $content);
				$content = str_replace('{EMAILADDRESS}', $email, $content);
				$url = self::getSubscribeLink($contact_id)."&confirm=1";
				$content = str_replace('{CONFIRM_SUBSCRIPTION_URL}', '<a href="'.$url.'">'.$url.'</a>', $content);
				$message->addContent($content);
				$message->addTo($name." <".$email.">");
				$from = isset($params['EMAILFROMNAME']) && $params['EMAILFROMNAME'] ? $params['EMAILFROMNAME'].' ' : "";
				$from_email = isset($params['EMAILFROM']) && $params['EMAILFROM'] ? $params['EMAILFROM'] : Wbs::getSystemObj()->getEmail();
				$message->addFrom($from.'<'.$from_email.'>');
				$message->addReplyTo($from.'<'.$from_email.'>');
				Mailer::send($message);
			}
			if (isset($params['REDIRECT']) && $params['REDIRECT']) {
			    if (Env::Post('from') == 'form') {
			        Url::go($params['REDIRECT'], true);
			    } else {
			        if (isset($params['NEWWINDOW']) && $params['NEWWINDOW']) {
    			        echo <<<HTML
<script type="text/javascript">
    window.open('{$params['REDIRECT']}');
</script>			        
HTML; 
			        } else {
    			        echo <<<HTML
<script type="text/javascript">
	var d = parent ? parent.document : document;
	d.location.href = "{$params['REDIRECT']}";
</script>			        
HTML;
                        exit;
                    }
                }
			}
			return $contact_id;
		} else {
			return false;
		}
	}	
	
	public static function getSubscribeLink($contact_id, $fix = false)
	{
		$contacts_model = new ContactsModel();
		$contact_info = $contacts_model->get($contact_id);
		if (defined('STRONG_AUTH') && STRONG_AUTH) {
	    	$md5 = md5($contact_id.$contact_info['C_CREATEDATETIME']);
		} else {
			$md5 = md5($contact_info['C_CREATEDATETIME']);
		}
	    $key = substr($md5, 0, 6).$contact_id.substr($md5, -6);
		if (defined('STRONG_AUTH') && STRONG_AUTH) {
			$key = $key.'-'.(is_array($contact_info['C_EMAILADDRESS']) ? $contact_info['C_EMAILADDRESS'][0] : $contact_info['C_EMAILADDRESS']);
		}
		$url = "/personal.php?key=".$key;
		if (!Wbs::isHosted()) {
			$url .= "&DB_KEY=".base64_encode(Wbs::getDbkeyObj()->getDbkey());
		}
	
		return Url::get($url, $fix ? 2 : true);
	}
	public static function subscribe($contact_id)
	{
	    $email = self::getInfo($contact_id, false, 'C_EMAILADDRESS');
	    if (is_array($email)) {
	        $email = $email[0];
	    }
	    $sql = "DELETE FROM UNSUBSCRIBER WHERE ENS_EMAIL = s:email";
	    $model = new DbModel();
	    $model->prepare($sql)->exec(array('email' => $email));
		$errors = array();
		$contacts_model = new ContactsModel();
		$contacts_model->save($contact_id, array('C_SUBSCRIBER' => '1'));
	}
	
	public static function unSubscribe($contact_id, $full = false)
	{
		// Saving email to unsubscriber table
		$email = self::getInfo($contact_id, false, 'C_EMAILADDRESS');
		$email = array_shift($email);
		$sql = "REPLACE INTO UNSUBSCRIBER SET ENS_EMAIL = s:email, ENS_DATETIME = s:time";
		$model = new DbModel();
		$model->prepare($sql)->exec(array('email' => $email, 'time' => date("YmdHis")));
		
		// Delete contact
		$contacts_model = new ContactsModel();
		$contacts_model->save($contact_id, array('C_SUBSCRIBER' => $full ? '-2' : '-1'));			
	}
	
	public static function unsetInfo($contact_id)
	{
	    if (isset(self::$store[$contact_id])) {
	        unset(self::$store[$contact_id]);
	    }
	}
	/**
	 * Returns info of the contact and save it in the static store
	 * 
	 * @param $contact_id
	 * @return array
	 */
	public static function getInfo($contact_id, $info = false, $dbname = false, $escape = false)
	{
		if (!$contact_id || !isset(self::$store[$contact_id]) || $info) {
			if (!$info) { 
				$users_model = new UsersModel();
				$info = $users_model->getByContactId($contact_id);
				if (!$info) {
					return null;
				}
				// Check multi email
				if ($info['C_EMAILADDRESS']) {
					$contacts_model = new ContactsModel();
					$info['C_EMAILADDRESS'] = array($info['C_EMAILADDRESS']) + $contacts_model->getEmail($contact_id, $info['C_EMAILADDRESS']);
					$info['C_EMAILADDRESS'] = array_unique($info['C_EMAILADDRESS']);
				} else {
					$info['C_EMAILADDRESS'] = array();
				}
			}
			if (isset($info['U_SETTINGS'])) {
				unset($info['U_SETTINGS']);
			}
            
			if (!isset(self::$type_fields[$info['CT_ID']])) {
			    $contact_type = new ContactType($info['CT_ID']);
			    self::$type_fields[$info['CT_ID']] = $contact_type->getFields();
			}
			$fields = self::$type_fields[$info['CT_ID']];
			foreach ($fields as $field) {
				if (isset($info[$field['dbname']])) {
					$info[$field['dbname']] = ContactType::getValue($field, $info[$field['dbname']], $escape);
				} else {
					$info[$field['dbname']] = "";
				}
			}
			// system fields
    		if ($info['C_CREATEDATETIME']) {
    	        $info['C_CREATEDATETIME'] = WbsDateTime::getTime(strtotime($info['C_CREATEDATETIME']));   
    		}

            if (!$info['C_CREATEAPP_ID'] || !isset(self::$methods[$info['C_CREATEAPP_ID']])) {
                $info['C_CREATEAPP_ID'] = 'CM';
            }    		
    		if (!$info['C_CREATEMETHOD']) {
    		    $info['C_CREATEMETHOD'] = 'ADD';
    		}
		    $method = self::$methods[$info['C_CREATEAPP_ID']][$info['C_CREATEMETHOD']];
	        $method = _s($method);
	        $info['C_CREATEMETHOD'] = $method;
	        if (isset($info['C_CREATESOURCE']) && $info['C_CREATESOURCE']) {
	            $info['C_CREATEMETHOD'] .= " (".$info['C_CREATESOURCE'].")";
	        }	    
	        $info['APP_ID'] = $info['C_CREATEAPP_ID']; 
        	if ($info['C_CREATEAPP_ID']) {
    		    $app_info = Rights::getApplicationInfo($info['C_CREATEAPP_ID']);
    		    $info['C_CREATEAPP_ID'] = $app_info['TITLE'];
    		}

        	if ($info['C_CREATECID'] && !isset($info['C_CREATENAME'])) {
        	    $use_store = self::$use_store;
        	    if (self::$use_store && $contact_id) {
        	    	self::$store[$contact_id] = $info;
        	    }        	     
        	    self::$use_store = true;
        	    if ($info['C_CREATECID'] == $contact_id) {
            	    $info['C_CREATENAME'] = $info['C_FULLNAME'];
        	    } else {
        	    	 
        	        $info['C_CREATENAME'] = self::getName($info['C_CREATECID']);
        	    }
    		    self::$use_store = $use_store;
    		} else {
    		    $info['C_CREATENAME'] = '';
    		}		
		
			// Use Store must be false for using in the cycles
			if (self::$use_store && $contact_id) {
				self::$store[$contact_id] = $info;
			} else {
				return $dbname ? (isset($info[$dbname]) ? $info[$dbname] : false) : $info;
			}
		}  
		return $dbname ? (isset(self::$store[$contact_id][$dbname]) ? self::$store[$contact_id][$dbname] : false) : self::$store[$contact_id];
	}
	
	/**
	 * Save contact info to static store
	 * 
	 * @param $contact_id
	 * @param $contact_info
	 */
	public static function setInfo($contact_id, $contact_info)
	{
		self::$store[$contact_id] = $contact_info;
	}
	
	/**
	 * Returns name of the contact 
	 * 
	 * @param $contact_id
	 * @param $n - number of the format, if $n == false then returns first non empty name
	 * @return string
	 */
	public static function getName($contact_id, $n = false, $info = false, $encode = true, $first = false)
	{
		$info = self::getInfo($contact_id, $info);
		if (!isset($info['CT_ID'])) {
		    return "";
		}
		$contact_type = new ContactType($info['CT_ID']);
		if ($n) {
		    $format = self::$formats[$n];
		    $name = "";
		    foreach ($format as $part) {
		        $field = $part['field'];
		        if ($field == 'NAME') {
		            $value = $contact_type->getDisplayName($info, false, false, $first);
		        } else {
		            $field_id = ContactType::getFieldId($field);
		            if (!$field_id) {
		                continue;
		            }
		            if (in_array($field_id, $contact_type->getMainFields())) {
		                continue;
		            }
		            $value = $info[$field];
		            if (is_array($value)) {
		                $value = array_shift($value);
		            }
		        }
		        if (!$value) {
		           continue;
		        }
		        if ($name && isset($part['split'])) {
		            $name .= $part['split'];
		        }
		        if (isset($part['prefix'])) {
		            $name .= $part['prefix']; 
		        }
		        $name .= $value;
		        if (isset($part['postfix'])) {
		            $name .= $part['postfix']; 
		        }
		    }
		} else {
		    $name = $contact_type->getDisplayName($info, false, false, $first);
		}
		if ($encode) {
		    return htmlspecialchars($name);    
		} else {
		    return $name;
		}
		
	}
	

	public static function getByName($fullname, $email = false)
	{
	    $contacts_model = new ContactsModel();
        $names = explode(" ", trim($fullname));
        $where = array();
        foreach ($names as $n) {
            $where[] = "C_FULLNAME LIKE '%".$contacts_model->escape(trim($n))."%'";
        }
        if ($email) {
            $where[] = "EC_EMAIL LIKE '".$contacts_model->escape($email)."'";
        }
        if (!$where) {
            return array();
        }
        $sql = "SELECT C.*".($email ? ", EC.EC_EMAIL C_EMAILADDRESS" : "")." FROM CONTACT C ".
               ($email ? " JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID " : "").
               " WHERE ".implode(" AND ", $where). " LIMIT 1";
        $contact = $contacts_model->query($sql)->fetch();
        if ($contact) {
            return $contact;
        }
        if ($email) {
            $contact = self::getByEmail($email, true);
            if ($contact) {
                $contact['C_EMAILADDRESS'] = $email;
                return $contact;
            } else {
                return array();
            }
        }
        return array();
	}
	
	public static function addByNameEmail($name, $email, $method, &$errors = array()) 
	{
		$info = array(
			'C_EMAILADDRESS' => $email,
		    'C_CREATEMETHOD' => $method
		);
	    $name = explode(" ", $name);
		if (count($name) >= 3) {
		 	$name = array_reverse($name);
		 	$info["C_LASTNAME"] = $name[0];
			$contact_type = new ContactType(1);
			$dbfields = $contact_type->getTypeDbFields();
			$i = 1;
			if (in_array('C_MIDDLENAME', $dbfields)) {
		    	$info["C_MIDDLENAME"] = $name[1];
		    	$i++;
			} 
			for ($j = 0; $j < $i; $j++) {
				unset($name[$j]);
			}
			$name = array_reverse($name);
			$info["C_FIRSTNAME"] = implode(" ", $name);
		} elseif (count($name) == 2) {
		     $info["C_FIRSTNAME"] = $name[0];
		     $info["C_LASTNAME"] = $name[1];						     
		} else {
		     $info['C_FIRSTNAME'] = $name[0];
		}
		 
		return self::add(1, $info, $errors);
	}
	
	/**
	 * Add contact 
	 * 
	 * @param $type_id - id of the contact type
	 * @param $info - array with contact info 
	 * 	array(
	 * 		field_id => value, 
	 * 		...
	 * 	)
	 * @param $errors - array
	 * @return bool
	 */
	public static function add($type_id, $info, &$errors = array(), $check = false)
	{
			$contact_type = new ContactType($type_id);
			// Get fields of type
			$fields = $contact_type->getFields();
			$data = array();
			// Validate data
			foreach ($info as $field => $value) {
				$field_id = is_numeric($field) ? $field : ContactType::getFieldId($field);
				if ($field_id) {
				    if (!isset($fields[$field_id])) {
				        continue;
				    }
					try {
						if ($fields[$field_id]['type'] == 'EMAIL') {
							if (!is_array($value)) {
								$value = array($value);
							}
							foreach ($value as $i => $v) {
								if ($v) {
									$value[$i] = ContactType::validateValue($fields[$field_id], $v, $check);
								} else {
									unset($value[$i]);
								}							
							}
							$value = array_unique($value);
						} else {
							$value = ContactType::validateValue($fields[$field_id], $value, $check);
						}
						if ($value) {
							$data[$fields[$field_id]['dbname']] = $value;
						}
					} catch (Exception $e) {
					    $message = $e->getMessage();
					    $message = sprintf($message, $fields[$field_id]['name']);
						if ($fields[$field_id]['type'] == 'EMAIL' && is_array($value)) {
							$errors[] = array("id" => $field_id, "i" => $i, "text" => $message);
						} else {
							$errors[] = array("id" => $field_id, "text" => $message);
						}
					}
				} elseif (substr($field, 0, 1) == 'C') {
					$data[$field] = $value;					
				}	
			}

			if ( User::getAppId() == 'CM' && (!isset($data['CF_ID']) || !$data['CF_ID']) && User::getContactId()) {
			    $data['CF_ID'] = 'PRIVATE'.User::getContactId();
			} 
			if ($check && User::getAppId() == 'CM' && isset($data['CF_ID']) && $data['CF_ID']) {
				$r = User::hasAccess('CM', 'FOLDERS', $data['CF_ID']);
				if ($r < 3 && substr($data['CF_ID'], 0, 1) !== 'P') {
					$errors[] = array("id" => "CF_ID", "text" => _("Select folder"));
				}
			}
		
			// Check main fields only
			$main_fields = $contact_type->getMainFields();
			$main_exists = false;
			foreach ($main_fields as $field_id) {
				if (isset($fields[$field_id]) && isset($data[$fields[$field_id]['dbname']])) {
					$main_exists = true; 
					break;
				}
			}
			// If set only email, then auto fill first required field of the contact type
			if (!$main_exists && isset($data['C_EMAILADDRESS']) && $data['C_EMAILADDRESS']) {
				$data[$fields[$main_fields[0]]['dbname']] = array_shift(explode("@", $data['C_EMAILADDRESS'][0], 2));
				$main_exists = true;
			}
			if (!$main_exists) {
				$error_text = count($main_fields) == 1 ? _s("Primary name must be filled.") : _s("At least one of the primary name fields must be filled.");
				$errors[] = array("ids" => $main_fields, "text" => $error_text);
				return false;	
			}
			
			$contacts_model = new ContactsModel();
			if ($check) {
				// Check unique fields
				$unique_fields = $contact_type->getUniqueFields();
				$dbfields = array();
				foreach ($unique_fields as $key => $field_id) {
					if (isset($data[$fields[$field_id]['dbname']]) && $data[$fields[$field_id]['dbname']]) {
						if ($contacts_model->getByField($fields[$field_id]['dbname'], $data[$fields[$field_id]['dbname']])) {
							$error = sprintf(_("This %s already in use."), $fields[$field_id]['name']);
							$errors[] = array("id" => $field_id, "text" => $error);
						}
					} 
				}
			}
			// Check errors
			if (!$errors && $data) {
				// Add info
				$data['CT_ID'] = $type_id;
				if (isset($data['CF_ID'])) {
                    $contact_folder_model = new ContactFolderModel();
                    $folder = $contact_folder_model->get($data['CF_ID']);
                    if (!$folder && User::getContactId()) {				    
                        $data['CF_ID'] = 'PRIVATE' . User::getContactId();
                    }
				} 
				// Create info
				if (!isset($data['C_CREATECID'])) {
					$data['C_CREATECID'] = (int)User::getContactId();
				}
				if (!isset($data['C_CREATEAPP_ID'])) {
				    $data['C_CREATEAPP_ID'] = User::getAppId();
				}
				if (!isset($data['C_CREATEMETHOD'])) {
				     $errors[] = array('text' => _('Field C_CREATEMETHOD is required'));
				     return false;
				} else {
				    if (!isset(self::$methods[$data['C_CREATEAPP_ID']][$data['C_CREATEMETHOD']])) {
				        $errors[] = array('text' => _('Unknown C_CREATEMETHOD'));
				        return false;
				    }
				}
				if (!isset($data['C_CREATEDATETIME'])) {
					$data['C_CREATEDATETIME'] = date("YmdHis");
				}
				// Save data to Database
				try {

					// Fill fullname of the contact
					$fullname = self::getName(false, false, $data);
					$data['C_FULLNAME'] = trim(strip_tags($fullname));
					return $contacts_model->add($data);
				} catch (Exception $e) {
					if (defined('DEVELOPER')) {
						throw $e;	
					} else {
						$errors[] = array("text" =>  _("Database error"));
						return false;
					}
				}
			} else {
				return false;
			}
	}
	
	public static function save($contact_id, $info, &$errors, $check = false)
	{
		$contact_info = self::getInfo($contact_id);
		$contact_type = new ContactType($contact_info['CT_ID']);
		$fields = $contact_type->getFields($check);
		$data = array();
		// Validate data
		foreach ($info as $field => $value) {
			$field_id = is_numeric($field) ? $field : $contact_type->getFieldId($field);
			if ($field_id) {
				if (!isset($fields[$field_id])) {
					continue;
				}
				try {
					if ($fields[$field_id]['type'] == 'EMAIL') {
						if (!is_array($value)) {
							$value = array($value);
						}
						foreach ($value as $i => $v) {
							if (trim($v)) {
								$value[$i] = ContactType::validateValue($fields[$field_id], $v, $check);
							} else {
								unset($value[$i]);
							}							
						}
						$value = array_unique($value);
					} else {
						$value = ContactType::validateValue($fields[$field_id], $value, $check);
					}
					if ($value !== $contact_info[$fields[$field_id]['dbname']]) {
						$contact_info[$fields[$field_id]['dbname']] = $data[$fields[$field_id]['dbname']] = $value;
					}
				} catch (Exception $e) {
				    $message = $e->getMessage();
				    $message = sprintf($message, $fields[$field_id]['name']);
				    
					if ($fields[$field_id]['type'] == 'EMAIL') {
						$errors[] = array("id" => $field_id, "i" => $i, "text" => $message);
					} else {
						$errors[] = array("id" => $field_id, "text" => $message);
					}
				}
			} else {
				$contact_info[$field] = $data[$field] = $value;					
			}	
		}
		
				
		// Check main fields 
		$main_fields = $contact_type->getMainFields();
		$main_exists = false;
		foreach ($main_fields as $field_id) {
			if ($contact_info[$fields[$field_id]['dbname']]) {
				$main_exists = true; 
				break;
			}
		}
				
		if (!$main_exists) {
			$error_text = count($main_fields) == 1 ? _s("Primary name must be filled.") : _s("At least one of the primary name fields must be filled.");
			$errors[] = array("ids" => $main_fields, "text" => $error_text);
			return false;	
		}

		$contacts_model = new ContactsModel();
		if ($check) {
			// Check unique fields
			$unique_fields = $contact_type->getUniqueFields();
			$dbfields = array();
			foreach ($unique_fields as $key => $field_id) {
				if (isset($data[$fields[$field_id]['dbname']]) && $data[$fields[$field_id]['dbname']]) {
					
					if ($contact_ids = $contacts_model->getByField($fields[$field_id]['dbname'], $data[$fields[$field_id]['dbname']])) {
						unset($contact_ids[$contact_id]);
						if ($contact_ids) {
							$error = sprintf(_("This %s already in use."), $fields[$field_id]['name']);
							if ($fields[$field_id]['type'] == 'EMAIL') {
								$errors[] = array("id" => $field_id, "i" => array_search(array_shift($contact_ids), $data[$fields[$field_id]['dbname']]), "text" => $error);
							} else {
								$errors[] = array("id" => $field_id, "text" => $error);
							}
						}
					}
				} 
			}
		}		
		
		// Check errors
		if (!$errors && $data) {
			// Modify info
			$contact_info['C_MODIFYCID'] = $data['C_MODIFYCID'] = User::getContactId();	
			$contact_info['C_MODIFYDATETIME'] = $data['C_MODIFYDATETIME'] = date("YmdHis");	
			// Save data to Database
			try {			
				$old_info = self::getInfo($contact_id);
				self::setInfo($contact_id, $contact_info);
				// Fill fullname of the contact
				$fullname = self::getName($contact_id);
				$data['C_FULLNAME'] = trim(strip_tags($fullname));		
				self::setInfo($contact_id, $old_info);	
				if ($contact_info['U_ID']) {
				    $contacts_model->addCacheCleaner(new DbCacher('USER_'.$contact_info['U_ID']));				
				}
				if ($contacts_model->save($contact_id, $data)) {
					self::unsetInfo($contact_id);
					return self::getInfo($contact_id);	
				} else {
					return false;
				}
			} catch (Exception $e) {
				$errors[] = defined('DEVELOPER') ? $e->getMessage() : _("Database error"); 
				return false;
			}
		} elseif (!$errors) {
			return $contact_info;
		} else {
			return false;
		}		
	}
	
	public static function delete($contact_id, $check = true)
	{
		// Check contact_id
		if (!$contact_id) {
			throw new Exception(_("Contact id is empty"));
		}
		
		if ($check) {
    		// If contact is user
    		$users_model = new UsersModel();
    		if ($users_model->getIdByContactId($contact_id)) {
    			throw new Exception(_("This contact is user!"));
    		}
		}	
		
		// Delete contact
		$contacts_model = new ContactsModel();
		$contacts_model->delete($contact_id);
	}
	
	public static function updateCustomers($force = false)
	{
	    
	    $model = new DbModel();
	    // Check field `SC_ID` in table `CONTACT`
	    try {
	        $model->exec("SELECT SC_ID FROM CONTACT WHERE 0");
	    } catch (Exception $e) {
	        $model->exec("ALTER TABLE CONTACT ADD SC_ID INT (11) NULL DEFAULT NULL");
	        $model->exec("ALTER TABLE CONTACT ADD UNIQUE `SC_ID` ( `SC_ID` )");
	    } 
	    
	    if (!$force) {
	        $sql = "SELECT MAX(SC_ID) C FROM CONTACT";
	        $n1 = $model->query($sql)->fetchField('C');
	        $sql = "SELECT MAX(customerID) C FROM SC_customers";
	        $n2 = $model->query($sql)->fetchField('C');
	        if ($n1 == $n2) {
	            return true;
	        }
	    }

	    //calculate only new customers
	    $sql = "SELECT COUNT(*) C FROM `SC_customers`";
	    $c = $model->query($sql)->fetchField('C');
	    $sql = "SELECT COUNT(*) C FROM `CONTACT` WHERE `SC_ID` IS NOT NULL";
	    $c = $c - $model->query($sql)->fetchField('C');
	    try {
	        self::checkLimits($c);
	    } catch (LimitException $e) {
	    	// calculate full customers count
	    	$sql = "SELECT COUNT(*) C FROM `SC_customers`";
	    	$c = $model->query($sql)->fetchField('C');
	        $message = "<strong>"._('An error occurred when we tried to import customers from the "Store" application')."</strong>:<br /><br />";
            $m = _("Your current pricing plan allows you to have not more than %s contacts, and we tried to import %s customer records.");
            $message .= sprintf($m, Limits::get('CM'), $c);
            $m = _("To import the entire customer database, please upgrade your account to the pricing plan which allows you to have more than %s contacts.");
            $message .= " ".sprintf($m, $c);
            if (User::hasAccess('AA')) {
                $message .= '<br /><br /><a target="_top" href="'.Url::get('/index.php').'?url='.Url::get('/AA/html/scripts/change_plan.php').'">'._s("Upgrade account").'</a>';
            } else {
                $message .= '<br /><br />'._s("Please refer to your account administrator.");
            }
            throw new HideException($message);
	    }
	    $sql = "SELECT settings_value FROM SC_settings WHERE settings_constant_name = 'CONF_DEFAULT_LANG'";
	    $lang_id = $model->query($sql)->fetchField('settings_value');
	    if ($lang_id) {
	        $sql = "SELECT iso2 FROM SC_language WHERE id = i:lang_id";
	        $lang = $model->prepare($sql)->query(array('lang_id' => $lang_id))->fetchField('iso2');
            if (!$lang) {
                $lang = 'en';
            }
	    } else {
	        $lang = 'en';
	    }
	    
	    $fields = array(
	        'SCC.customerID' => 'SC_ID',
	        'SCC.first_name' => 'C_FIRSTNAME',
	        'SCC.last_name' =>  'C_LASTNAME',
	        'SCC.Email' =>  'C_EMAILADDRESS',
	        'SCA.address' => 'C_HOMESTREET',
	        'SCA.city' => 'C_HOMECITY',
	    	'SCA.zip' => 'C_HOMEPOSTALCODE',
	    	'SCA.state' => 'C_HOMESTATE',
	    	'SCCN.country_name_'.$lang => 'C_HOMECOUNTRY',
	        'SCC.reg_datetime' => 'C_CREATEDATETIME',
	        '"SC"' => 'C_CREATEAPP_ID'
	        
	    );
	    foreach ($fields as $from => $to) {
	        if ($to != 'SC_ID' && !ContactType::getFieldId($to, true)) {
	            unset($fields[$from]);
	        }
	    }
	    
	    $values = array();
	    foreach ($fields as $f) {
	        if ($f != 'SC_ID') {
	            $values[] = $f." = VALUES(".$f.")";
	        }
	    }
	    $sql = "INSERT INTO CONTACT (CT_ID, ".implode(",", $fields).") 
	    		SELECT 1, ".implode(",", array_keys($fields))." 
	    		FROM SC_customers SCC JOIN 
	    			 SC_customer_addresses SCA ON SCC.addressID = SCA.addressID LEFT JOIN
	    			 SC_countries SCCN ON SCA.countryID = SCCN.countryID
	    		ON DUPLICATE KEY UPDATE ".implode(", ", $values);
	    $model->exec($sql);
	    $sql = "INSERT INTO CONTACT (CT_ID, ".implode(",", $fields).") 
	    		SELECT 1, ".implode(",", array_keys($fields))." 
	    		FROM SC_customers SCC LEFT JOIN 
	    			 SC_customer_addresses SCA ON SCC.customerID = SCA.customerID LEFT JOIN
	    			 SC_countries SCCN ON SCA.countryID = SCCN.countryID
	    		WHERE SCC.addressID = 0 OR SCC.addressID IS NULL 
	    		GROUP BY SCC.customerID
	    		ON DUPLICATE KEY UPDATE ".implode(", ", $values);
	    $model->exec($sql);
	    $sql = "UPDATE CONTACT SET C_FULLNAME = TRIM(CONCAT(IFNULL(C_FIRSTNAME, ''), ' ' ,IFNULL(C_LASTNAME, ''))) WHERE SC_ID > 0";
	    $model->exec($sql);
	    $sql = "DELETE EC FROM EMAIL_CONTACT EC JOIN CONTACT C ON EC.EC_ID = C.C_ID WHERE C.SC_ID > 0";
	    $model->exec($sql);
	    $sql = "INSERT INTO `EMAIL_CONTACT` (`EC_ID`, `EC_EMAIL`) SELECT C_ID, C_EMAILADDRESS FROM CONTACT WHERE SC_ID > 0";
	    $model->exec($sql);
	}
	
	public static function getByList($list_id, $sort = false, $limit = false)
	{
		$list_id = (int)$list_id;
		$lists_model = new ListsModel();
		$list_info = $lists_model->get($list_id);
		
		if ($list_id == ListsModel::SC_LIST) {
		    self::updateCustomers(Env::Post('force', Env::TYPE_INT, 0));
		}
			
    	if ($sort) {
    		$sort_info = explode(" ", $sort);
    		if ($sort_info[0] == 'C_EMAILADDRESS') {
    			$sort_info[0] = 'EC_EMAIL';
    			$sort = implode(" ", $sort_info);
    		}
    	} 		
    	
		if ($list_info['CL_SHARED'] && $list_info['CL_C_ID'] != User::getContactId()) {
			$suffix = "";	
		} else {
			$suffix = " AND ".self::getFoldersCondition();
		}
    	

		if ($list_info['CL_SQL']) {
		    $with_email = $sort && $sort_info[0] == 'EC_EMAIL';
		    if (!$with_email) {
		        $with_email = strpos($list_info['CL_SQL'], "EC_EMAIL") !== false;
		    }
			$sql = "SELECT U.U_ID, U.U_STATUS, C.*". ($with_email ? ",EC_EMAIL C_EMAILADDRESS " : "")."
					FROM CONTACT C LEFT JOIN 
					     WBS_USER U ON C.C_ID = U.C_ID 
					     ".($with_email ? "JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID":"")."
					WHERE (" . $list_info['CL_SQL'] .")".$suffix; 
			$count_sql = "SELECT COUNT(*) N 
						  FROM CONTACT C 
        			 	  ".($with_email ? "JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID":"")."
						  WHERE " . $list_info['CL_SQL'] .$suffix; 
		} else {		
			
			$sql = "SELECT U.U_ID, U.U_STATUS, C.* ". ($sort && $sort_info[0] == 'EC_EMAIL' ? ",EC_EMAIL  C_EMAILADDRESS" : "")." 
					FROM CONTACT C LEFT JOIN 
					     WBS_USER U ON C.C_ID = U.C_ID 
					     ".($sort && $sort_info[0] == 'EC_EMAIL' ? "JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID" : "")." 
					     JOIN CLIST_CONTACT CC ON C.C_ID = CC.C_ID 
					WHERE CC.CL_ID = ".$list_id.$suffix; 
			$count_sql = "SELECT COUNT(*) N 
						  FROM CONTACT C JOIN 
						  	   CLIST_CONTACT CC ON C.C_ID = CC.C_ID
						  WHERE CC.CL_ID = ".$list_id.$suffix; 	
		}		
		
		if ($sort) {
            if ($sort_info[0] == 'C_FULLNAME') {
                $sql .= ' ORDER BY '.$sort;
            } elseif ($sort_info[0] == 'EC_EMAIL') {
                $sql .= " ORDER BY EC_EMAIL = '', EC_EMAIL ".$sort_info[1];
            } else {
        	    $sql .= " ORDER BY ".$sort_info[0]." IS NULL, ".$sort;
            }
		}		
			
		if ($limit) {
			$sql .= " LIMIT ".$limit;
		}
		$contacts_model = new ContactsModel();
				
		return array(
			'users' => $contacts_model->query($sql)->fetchAll(),
			'count' => $contacts_model->query($count_sql)->fetchField('N'),
		);			
	}
	
	public static function getAll($sort = false, $limit = false) 
	{
		$contacts_model = new ContactsModel();
		$sql = "SELECT U.U_ID, U.U_STATUS, C.* 
				FROM CONTACT C LEFT JOIN 
				     WBS_USER U ON C.C_ID = U.C_ID 
				WHERE ".self::getFoldersCondition();
		if ($sort) {
			$sql .= " ORDER BY ".$sort;
		}			
		if ($limit) {
			$sql .= " LIMIT ".$limit;
		}			
		
		$count_sql = "SELECT COUNT(*) N FROM CONTACT C 
					  WHERE ".self::getFoldersCondition();
		$result = array(
			'users' => $contacts_model->query($sql)->fetchAll(),
			'count' => $contacts_model->query($count_sql)->fetchField('N'),
		);
		return $result;
	}
	
	
	protected static function getFoldersCondition()
	{
	    $contacts_model = new ContactsModel();
	    return "(".$contacts_model->getFolderCondition().")"; 
	}
	
	public static function searchByName($name, $sort, $limit, $list_id = false, $list_title = false)
	{
		$contacts_model = new ContactsModel();

		$data = array('type' => 'simple', 'data' => $name);
    	if ($list_id) {
			$lists_model = new ListsModel();
			$where = $contacts_model->getWhereByName($name);
			if ($list_id === true) {
				$list_id = $lists_model->add($list_title ? $list_title : $name, $where, $data);
				User::addMetric('ADDSEARCHLIST');
			} else {
				$lists_model->save($list_id, $list_title, $where, $data);
			}			
		} else {
		    if (User::getAppId() == 'CM') {
		        User::setSetting('LASTSEARCH', json_encode($data), 'CM');
		    }
		}
							
		$users = $contacts_model->getByName($name, $sort, $limit);


		$sort_info = explode(" ", $sort, 2);
		if ($users && (!$sort || $sort_info[0] != 'EC_EMAIL')) {
			$contact_ids = array_keys($users);
			$emails = $contacts_model->getEmailByContactId($contact_ids);
			foreach ($emails as $contact_id => $contact_emails) {
				$users[$contact_id]['C_EMAILADDRESS'] = implode(", ", $contact_emails);
			}
		}

		$result = array(
			'users' => $users,
			'count' => $contacts_model->countByName($name)
		);
		
		if ($list_id) {
			$list_info = $lists_model->get($list_id);
			$result['list'] = array(
				'id' => $list_id,
				'name' => $list_info['CL_NAME'],
				'search' => ListsModel::getSearchDescription($list_info['CL_SEARCH'])
			);
		}
		return $result;		
	}
	
	public static function searchByFields($info, $sort = false, $limit = false, $where = "", $list_id = false, $list_title = false, $type = 'smart')
	{
		$fields = ContactType::getAllFields(User::getLang(), false, false, true);
	
		$contacts_model = new ContactsModel();
		$is_where = true;
		if ($where && is_array($where)) {
			$where = "(".implode(" AND ", $where).")";
		}
		
		$links = array(
			1 => ' AND ',
			2 => ' OR ',
		);
		$search_email = false;
		if ($sort && substr($sort, 0, 14) == 'C_EMAILADDRESS') {
			$search_email = true;
		}
		$advanced_info = array();
		$show_fields = array();
		if (!$info) {
			$info = array();
		}
		foreach ($info as $kk => $row) {
			if ($where) {
				$where .= (isset($links[$row['link']]) ? $links[$row['link']] : " AND ");
			}
			if ($type == 'advanced') {
				$advanced_info[] = array('field' => $row['field'], 'val' => $row['val']);	
			}
			if (!is_numeric($row['field']) && $type == 'advanced') {
			        if ($where) {
                        $where .= (isset($links[$row['link']]) ? $links[$row['link']] : " AND ");
			        }
                    switch ($row['field']) {
                        case "createcid": 
                            $where .= "(C_CREATECID = '".(int)$row['val']."')";
                            break;
                        case "added": 
                            $where .= "(C_SUBSCRIBER = 1)";
                            break;
                        case "days": 
                            $where .= "(C_CREATEDATETIME > NOW() - INTERVAL ".(int)$row['val']." DAY)";
                            break;
                        case "from":
                            $where .= "(C_CREATEDATETIME >= DATE('".$row['val']."'))"; 
                            break;
                        case "to": 
                            $where .= "(C_CREATEDATETIME <= DATE('".$row['val']."'))";
                            break;
                        case 'type_id':
                        	$where .= "(CT_ID = ".(int)$row['val'].")";
                        	break;
                        case "folder_id": 
                            $where .= "(CF_ID LIKE '".$contacts_model->escape($row['val'])."')";
                            break;
                    }			        
                continue;
			} elseif (is_numeric($row['field'])) {
			    $show_fields[] = $row['field'];
			    if ($fields[$row['field']]['type'] == 'DATE') {
			        if ($row['cond'] == 7) {
			            $v = explode(" || ", $row['val']);
			            $v[0] = WbsDateTime::toMySQL($v[0]);
			            $v[1] = WbsDateTime::toMySQL($v[1]);
			            $info[$kk]['val'] = $row['val'] = $v[0]." || ".$v[1];
			        } else {
			            $info[$kk]['val'] = $row['val'] = WbsDateTime::toMySQL($row['val']);
			        }
			    }
			}
			if ($row['field'] == 'type_id') {
				$dbname = 'CT_ID';
			} elseif ($row['field'] == 'folder_id') {
				$dbname = 'CF_ID';
			} elseif ($fields[$row['field']]['dbname'] == 'C_EMAILADDRESS') {
			    $search_email = true;
			    $dbname = "EC.EC_EMAIL";
			} elseif ($fields[$row['field']]['dbname'] == 'C_ID') {
			    $dbname = 'C.C_ID';
			} elseif ($fields[$row['field']]['dbname'] == 'C_CREATENAME') {
				if ($row['val'] >= 0) {
					$dbname = 'C_CREATECID';
				} else {
					$dbname = 'C_SUBSCRIBER';
					$row['cond'] = 11;
				}
			} elseif ($fields[$row['field']]['type'] == 'CHECKBOX') {
				if (!$row['val']) {
					$row['cond'] = 12;
				}
				$dbname = $fields[$row['field']]['dbname'];
			} else {
			    $dbname = $fields[$row['field']]['dbname'];
			}
			
			$where .= "(".$dbname;
			switch ($row['cond']) {
				case 1:
					$where .= " LIKE '%".$contacts_model->escape($row['val'])."%'";
					break;
				case 2:
					$where .= " LIKE '".$contacts_model->escape($row['val'])."'";
					break;					
				case 9:
					$where .= " = '".$contacts_model->escape($row['val'])."'";
					break;					
				case 3:
					$where .= " LIKE '".$contacts_model->escape($row['val'])."%'";
					break;					
				case 4:
					$where .= " NOT LIKE '%".$contacts_model->escape($row['val'])."%'";
					break;
				
                case 6: 
				case 8:
				    $where .= " > '".$contacts_model->escape($row['val'])."'";
				    break;
				case 5:
				case 10:
				    $where .= " < '".$contacts_model->escape($row['val'])."'";
				    break; 		
				case 11: 
					$where .= " IS NOT NULL";
					break;
				case 12: 
					$where .= " IS NULL OR ".$dbname." = 0";
					break;						
				case 7:
				    $v = explode(" || ", $row['val']);
					$where .= " >= '".$contacts_model->escape($v[0]). "' AND ".$dbname." <= '".$contacts_model->escape($v[1])."'";
					break;
				default:
					$where .= " LIKE '%".$contacts_model->escape($row['val'])."%'";
			}
			$where .= ")";
		}
        Registry::set('SHOWFIELDS', array_unique($show_fields));
		$data = array('type' => $type);
		$data['data'] = $type == 'advanced' ? $advanced_info : $info;
		
		if ($list_id) {
			$lists_model = new ListsModel();
			
			if ($list_id === true) {
				$name = $list_title ? $list_title : ListsModel::getSearchDescription($data, false, false);
				$list_id = $lists_model->add($name, $where, $data);
				User::addMetric('ADDSEARCHLIST');
			} else {
				$lists_model->save($list_id, $list_title, $where, $data);
			}
		} else {
		    User::setSetting('LASTSEARCH', json_encode($data), 'CM');
		}
		
        if ($where) {
		    $where = "(".$where.") AND ".self::getFoldersCondition();
        } else {
            $where = self::getFoldersCondition();
        }
						
		$sql = "SELECT U.U_ID, U.U_STATUS, C.*".($search_email ? ", EC.EC_EMAIL C_EMAILADDRESS" : "")."
				FROM CONTACT C LEFT JOIN 
				     WBS_USER U ON C.C_ID = U.C_ID
				".($search_email ? "JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID" : "")."
				WHERE ".$where; 
		$sort_info = explode(" ", $sort, 2);
		if ($sort) {
            if ($sort_info[0] == 'C_FULLNAME') {
                $sql .= ' ORDER BY '.$sort;
            } elseif ($sort_info[0] == 'C_EMAILADDRESS') {
                $sql .= " ORDER BY EC_EMAIL = '', EC_EMAIL ".$sort_info[1];
            } else {
        	    $sql .= " ORDER BY ".$sort_info[0]." IS NULL, ".$sort;
            }
		}			
	
		if ($limit) {
			$sql .= " LIMIT ".$limit;
		}

		
		$count_sql = "SELECT COUNT(*) N FROM CONTACT C
		".($search_email ? "JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID" : "")." 
		WHERE ". $where; 
		$result = array(
			'users' => $contacts_model->query($sql)->fetchAll(),
			'count' => $contacts_model->query($count_sql)->fetchField('N'),
		);		
		if ($list_id) {
			$list_info = $lists_model->get($list_id);
			$result['list'] = array(
				'id' => $list_id,
				'name' => $list_info['CL_NAME'],
				'search' => ListsModel::getSearchDescription($list_info['CL_SEARCH'])
			);
		}
		return $result;
	}
	
	
	public static function uploadFile($contact_id = false, $file_info, $field_desc) 
	{
		if ($file_info['error']) {
			return false;
		}
		$contact_id = (int)$contact_id;
		$path_parts = pathinfo($file_info['name']);
		if (isset($path_parts['extension'])) {
			$ext = trim( strtolower($path_parts['extension']));
		} else {
			$ext = "";
		}

		if (!$ext || !in_array($ext, self::$known_extensions)) {
			throw new Exception(_s("Unknown file type"));
		}
		$filename = $contact_id.uniqid(rand());
		$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", "contacts/".$filename);
		@move_uploaded_file($file_info['tmp_name'], $path.".".$ext);			
		
		$wbsImage = new WbsImage($path.".".$ext);
		$wbsImage->thumbnailImage(96, 96, true);
		$wbsImage->writeImage($path.".96.".$ext);
	
		$dom = new DOMDocument('1.0', 'utf-8');
		$root = $dom->createElement("IMAGE");
		$dom->appendChild($root);
		$root->setAttribute( "FILENAME", base64_encode($file_info['name']) );
		$root->setAttribute( "SIZE", filesize($path.".".$ext));
		$root->setAttribute( "DISKFILENAME", base64_encode($filename) );
		$root->setAttribute( "TYPE", $ext);
		$root->setAttribute( "MIMETYPE", $file_info['type']);
		$root->setAttribute( "DATETIME", time());
		
		if ($contact_id) {
			$contact_info = self::getInfo($contact_id);
			$image = $contact_info[$field_desc["dbname"]];			
			if ($image["FILENAME"]) {
				$root->setAttribute( "PREVFILENAME", $image["DISKFILENAME"]);
			}
		}
		return $dom->saveXML();
	}
	
	public static function checkLimits($n = 1, $back = false, $message = false)
	{
		$limit = Limits::get("CM");
		if ($limit) {
			$users_model = new UsersModel();
			$contacts_model = new ContactsModel();
			$count = $contacts_model->getQueryConstructor()->count() - $users_model->getQueryConstructor()->count();
			if ($count + $n > $limit) {
				$e = new LimitException(_("Number of contacts can not exceed ").$limit.".". ($message ? " ".$message : ""));
				$e->setBack($back);
				throw $e;
			}			
		}
	}	
}

?>