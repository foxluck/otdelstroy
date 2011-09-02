<?php 

class ContactsModel extends DbModel 
{
    protected $table = 'CONTACT';
    
    public function countAll()
    {
        return $this->getQueryConstructor()->count();
    }

    public function get($contact_id, $field = false)
    {
    	if ($field != 'C_EMAILADDRESS') {
	    	$sql = "SELECT ".($field ? $field : "*")." FROM ".$this->table."
	    			WHERE C_ID = i:contact_id";
	    	$q = $this->prepare($sql)->query(array('contact_id' => $contact_id));
    		$result = $field ? $q->fetchField($field) : $q->fetch();
    		if (!$field) {
    			$result['C_EMAILADDRESS'] = $this->getEmail($contact_id);
    		}
    		return $result;
    	} else {
    		return $this->getEmail($contact_id);
    	}
    }
    
    public function getByIds($ids, $user_info = true, $with_emails = false)
    {
        if ($user_info) {
            $sql = "SELECT U.U_ID, C.* FROM CONTACT C LEFT JOIN WBS_USER U ON C.C_ID = U.C_ID";
        } else {
            $sql = "SELECT * FROM CONTACT C";
        }
    	$sql .= " WHERE C.C_ID IN ('".implode("', '", $this->escape($ids))."')";
        $result = $this->query($sql)->fetchAll('C_ID');
        
		$emails = $this->getEmailByContactId($ids);
		foreach ($emails as $contact_id => $contact_emails) {
			$result[$contact_id]['C_EMAILADDRESS'] = $contact_emails;
		}
		return $result;
        
    }
    
    public function fixFolderId()
    {
        $sql = "UPDATE CONTACT C LEFT JOIN CFOLDER CF ON C.CF_ID = CF.CF_ID SET C.CF_ID = NULL WHERE C.CF_ID IS NOT NULL AND C.CF_ID != 'PUBLIC' AND CF.CF_ID IS NULL";
        return $this->exec($sql);
    }
    
    public function getIdByEmail($email) 
    {
    	$sql = "SELECT EC_ID FROM EMAIL_CONTACT WHERE EC_EMAIL = s:email";
    	return $this->prepare($sql)->query(array('email' => $email))->fetchField('EC_ID');	
    }
    
    public function getEmail($contact_id, $primary_email = false)
    {
    	$sql = "SELECT EC_EMAIL FROM EMAIL_CONTACT WHERE EC_ID = i:contact_id";
    	$data = $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchAll();
    	$result = array();
    	if ($primary_email) {
    		$result[] = $primary_email;
    	}
    	foreach ($data as $row) {
    		if (!$primary_email || $row['EC_EMAIL'] != $primary_email) {
    			$result[] = $row['EC_EMAIL'];
    		}
    	}
    	return $result;
    }    
    
    public function addEmail($contact_id, $emails)
    {
    	if (!is_array($emails)) {
    		$emails = array($emails);
    	}
    	$values = array();
    	foreach ($emails as $email) {
    	    if ($email) {
    		    $values[] = "(i:contact_id, '".$this->escape($email)."')";
    	    }
    	}
    	if ($values) {
	    	$sql = "INSERT IGNORE INTO EMAIL_CONTACT (EC_ID, EC_EMAIL) VALUES ".implode(", ", $values);
    	} else {
    	    $sql = "INSERT IGNORE INTO EMAIL_CONTACT (EC_ID, EC_EMAIL) VALUES (i:contact_id, '')";
    	}
    	return $this->prepare($sql)->exec(array('contact_id' => $contact_id));    	
    }
    
    public function deleteEmail($contact_id, $emails) 
    {
        if (!is_array($emails)) {
    		$emails = array($emails);
    	}
    	// Get old email
    	$old_email = $this->prepare("SELECT C_EMAILADDRESS FROM CONTACT WHERE C_ID = i:contact_id")->query(array('contact_id' => $contact_id))->fetchField('C_EMAILADDRESS');
    	if ($old_email && in_array($old_email, $emails)) {
    		// Delete from old table
    		$sql = "UPDATE CONTACT SET C_EMAILADDRESS = NULL  WHERE C_ID = i:contact_id";
    		$this->prepare($sql)->exec(array('contact_id' => $contact_id));
    	}
    	$sql = "DELETE FROM EMAIL_CONTACT WHERE EC_ID = i:contact_id AND EC_EMAIL = s:email";
    	foreach ($emails as $email) {
    		$this->prepare($sql)->exec(array('contact_id' => $contact_id, 'email' => $email));
    	}    	
    }
    
    public function getByField($field, $value)
    {
    	if ($field == 'C_EMAILADDRESS') {
    		foreach ($value as $i => $e) {
    			$value[$i] = $this->escape($e); 
    		}
    		$sql = "SELECT EC_ID C_ID, EC_EMAIL V FROM EMAIL_CONTACT WHERE EC_EMAIL IN ('".implode("', '", $value)."') LIMIT 2"; 
    	} else {
    		$sql = "SELECT C_ID, ".$field." V FROM CONTACT WHERE `".$field."` = s:value LIMIT 2";
    	}	
    	$data = $this->prepare($sql)->query(array('value' => $value))->fetchAll();
    	$result = array();
    	foreach ($data as $row) {
    		$result[$row['C_ID']] = $row['V'];
    	}
    	return $result;
    }
    
    /**
     * Add new contact and returns id of the new contact
     * 
     * @param $first_name
     * @param $last_name
     * @param $email
     * @param $folder_id
     * @return int
     */
    public function add($data) 
    {
    	$sql = "INSERT INTO ".$this->table." SET ";
    	$set = array(); 
    	$email = array();
    	foreach ($data as $field_id => $value) {
    		if ($field_id == 'C_EMAILADDRESS') {
				$email = $value; 	
				if ($value) {
					$v = is_array($value) ? $value[0] : $value;
					$set[] = $field_id." = ".($v === null ? "''" : "'".$this->escape($v)."'");	
				}
    		} else {
    			$set[] = $field_id." = ".($value === null ? "NULL" : "'".$this->escape($value)."'");
    		}
    	}
    	$sql .= implode(", ", $set);
    	    	
        $contact_id = $this->query($sql)->lastInsertId();
        // Add email
       	$this->addEmail($contact_id, $email);

        return $contact_id;        
    }  
    
    /**
     * Save data of the contact by update fields that exists in array
     * 
     * @param $contact_id
     * @param $data
     * @return bool
     */
    public function save($contact_id, $data) 
    {
    	$sql = "UPDATE ".$this->table." SET ";
    	$set = array(); 
    	if (isset($data['CF_ID']) && !$data['CF_ID']) {
    	    $data['C_CREATECID'] = User::getContactId();
    	}
    	foreach ($data as $field_id => $value) {
    		if ($field_id == 'C_EMAILADDRESS') {
				if ($value) {
					if (!is_array($value)) {
	    				$value = array($value);
	    			}
	    			$email = self::getEmail($contact_id);
	    			$add = array_diff($value, $email);
	    			if ($add) {
	    				$this->addEmail($contact_id, $add);
	    			}
	    			$remove = array_diff($email, $value);
	    			if ($remove) {
	    				$this->deleteEmail($contact_id, $remove);
	    			}				    
				    
					$v = is_array($value) ? $value[0] : $value;
					$set[] = $field_id." = ".($v === null ? "NULL" : "'".$this->escape($v)."'");	
				} else {
				    $q = "DELETE FROM EMAIL_CONTACT WHERE EC_ID = i:contact_id";
				    $this->prepare($q)->exec(array('contact_id' => $contact_id));
				    $q = "INSERT INTO EMAIL_CONTACT SET EC_ID = i:contact_id, EC_EMAIL = ''";
                    $this->prepare($q)->exec(array('contact_id' => $contact_id));				    
				    $set[] = $field_id." = NULL";
				}
    		} else {
    			$set[] = $field_id." = ".($value === null ? "NULL" : "'".$this->escape($value)."'");
    		}
    	}
    	$sql .= ($set ? implode(", ", $set) : "")." WHERE C_ID = ".(int) $contact_id;

   		return $this->exec($sql);
    }
    
    /**
     * Returns contacts by folder id, if folder id is null then returns contacts which current user owned
     * 
     * @param $folder_id
     * @param $user_id - U_ID of the current user
     * @param $sort - field name for order
     * @param $limit - limit, etc. "10, 20"
     * @return bool
     */
    public function getByFolderId($folder_id, $contact_id, $sort = false, $limit = false)
    {
    	if ($sort) {
    		$sort_info = explode(" ", $sort);
    		if ($sort_info[0] == 'C_EMAILADDRESS') {
    			$sort_info[0] = 'EC_EMAIL';
    			$sort = implode(" ", $sort_info);
    		}
    	}

        $sql = "SELECT U.U_ID, U.U_STATUS, C.* ". ($sort && $sort_info[0] == 'EC_EMAIL' ? ", EC_EMAIL C_EMAILADDRESS" : "")."
				FROM CONTACT C LEFT JOIN 
				     WBS_USER U ON C.C_ID = U.C_ID 
				     ".($sort && $sort_info[0] == 'EC_EMAIL' ? " JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID" : "")."
				WHERE ".$this->getFolderCondition($folder_id, $contact_id);
         
        if ($sort) {
            if ($sort_info[0] == 'C_FULLNAME') {
                $sql .= ' ORDER BY '.$sort;
            } elseif ($sort_info[0] == 'EC_EMAIL') {
                $sql .= " ORDER BY EC_EMAIL = '', ".$sort;
            } else {
        	    $sql .= " ORDER BY ".$sort_info[0]." IS NULL, ".$sort;
            }
        }
        if ($limit) {
        	$sql .= " LIMIT ".$limit;
        }
        $users = $this->prepare($sql)->query(array('folder_id' => $folder_id, 'contact_id' => $contact_id))->fetchAll(($sort && $sort_info[0] == 'EC_EMAIL' ? false : 'C_ID'));
        
		if ($users && (!$sort || $sort_info[0] != 'EC_EMAIL')) {
			$contact_ids = array_keys($users);
			$emails = $this->getEmailByContactId($contact_ids);
			foreach ($emails as $contact_id => $contact_emails) {
				$users[$contact_id]['C_EMAILADDRESS'] = implode(", ", $contact_emails);
			}
		}
				
        return $users;
    }
    
    
    public function exportByFolder($folder_id, $contact_id)
    {
        // Count contacts
        $sql = "SELECT COUNT(*) c FROM CONTACT WHERE ".$this->getFolderCondition($folder_id, $contact_id);
        $c = $this->prepare($sql)->query(array('folder_id' => $folder_id, 'contact_id' => $contact_id))->fetchField('c');

        $sql = "SELECT C.* 
				FROM CONTACT C 
				WHERE ".$this->getFolderCondition($folder_id, $contact_id)."
				ORDER BY C_FULLNAME";
        /**
         * @var DbResultSelect
         */
        $data = $this->query($sql);
        
		if ($c > 0 && $c < 500) {
			$result = $data->fetchAll('C_ID');
			$contact_ids = array_keys($result);
			$emails = $this->getEmailByContactId($contact_ids);
			foreach ($emails as $contact_id => $contact_emails) {
				$result[$contact_id]['C_EMAILADDRESS'] = $contact_emails;
			}
			return $result;
		}
				
        if ($c > 0) {
            return $data;
        }
        return array();
    }    
    
    
    public function getWithEmailsByFolderId($folder_id, $limit = "100")
    {
        $sql = "SELECT * FROM ".$this->table." C
        		WHERE ".$this->getFolderCondition($folder_id)." AND C_EMAILADDRESS IS NOT NULL AND C_EMAILADDRESS != ''
                ORDER BY C_FULLNAME
                LIMIT ".$limit;
        $result = array();
        $data = $this->query($sql)->fetchAll('C_ID');
        if ($data) {
            $emails = $this->getEmailByContactId(array_keys($data));
            foreach ($data as $contact_id => $contact_info) {
                if (isset($emails[$contact_id])) {
	                foreach ($emails[$contact_id] as $email) {
	                    $c = $contact_info;
	                    $c['C_EMAILADDRESS'] = $email;
	                    $result[] = $c;
	                }
                } else {
                    $result[] = $contact_info;
                }
            }
        }
        return $result;
    }
    
    /**
     * 
     * @param $lists
     * @param $limit
     * @return DbResultSelect
     */
    public function getWithEmailsByLists($lists, $offset = 0, $user_id)
    {
        if (User::isAdmin('CM', false, $user_id)) {
        	$cond = false;
		} else {
            $user_rights_model = new UserRightsModel();
            $folders = $user_rights_model->getFolders('CM', $user_id);
            $folders[] = 'PUBLIC';
            $folders[] = 'PRIVATE'.$contact_id;
        	$cond = "CF_ID IN ('".implode("', '", $folders)."')";
        }    	
        $lists_model = new ListsModel();
        $lists_info = $lists_model->getByIds($lists);
        $sql = array();
        $implicit_lists = $implisit_shared_lists = array();
        foreach ($lists_info as $list_info) {
        	if (isset($list_info['full'])) {
        		$sql[] = $list_info['CL_SQL'];
        	} elseif ($list_info['CL_SQL']) {
                if (strpos($list_info['CL_SQL'], "EC_EMAIL") === false) {
                    $with_email = false;
                } else {
                    $with_email = true;
                }
                $sql[] = "SELECT C.* 
                		  FROM CONTACT C ".($with_email ? " JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID " : "")." 
                	      WHERE ".($cond && !$list_info['CL_SHARED'] ? "(".$cond.") AND " : "")." (".$list_info['CL_SQL'].")".($with_email ? "" : " AND C_EMAILADDRESS != ''");
            } else {
            	if ($list_info['CL_SHARED']) {
            		$implicit_shared_lists[] = $list_info['CL_ID'];
            	} else {
                	$implicit_lists[] = $list_info['CL_ID'];
            	}
            }         
        }
        if ($implicit_lists) {
            $sql[] = "SELECT C.* FROM CONTACT C JOIN 
            						  CLIST_CONTACT CC ON C.C_ID = CC.C_ID
            		  WHERE ".($cond ? "(".$cond.") AND " : "")." C.C_EMAILADDRESS != '' AND CC.CL_ID IN ('".implode("', '", $implicit_lists)."')";
        }
        
        if ($implicit_shared_lists) {
            $sql[] = "SELECT C.* FROM CONTACT C JOIN 
            						  CLIST_CONTACT CC ON C.C_ID = CC.C_ID
            		  WHERE C.C_EMAILADDRESS != '' AND CC.CL_ID IN ('".implode("', '", $implicit_shared_lists)."')";
        }        
        
        $sql = implode(" UNION ", $sql);

        if ($offset) {
            $sql .= " LIMIT ".$offset.", 3000";
        }
        return $this->query($sql);
    }

    /**
     * Count contacts or emails
     * 
     * @param $folder_id
     * @param $contact_id
     * @param $count_email
     * @return int
     */
    public function countByFolderId($folder_id, $contact_id, $count_email = false)
    {
        $sql = "SELECT COUNT(*) N 
        		FROM CONTACT C" .
        		($count_email ? " LEFT JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID" : "")."
    			WHERE ".$this->getFolderCondition($folder_id, $contact_id); 
        return $this->query($sql)->fetchField('N');
    }
    
    public function quickSearch($text, $limit = "10", $order = false)
    {
        $sql = "SELECT C.*, EC_EMAIL C_EMAILADDRESS 
        		FROM CONTACT C
        		JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID
        		WHERE EC_EMAIL != '' 
        			  AND (".$this->getWhereByName($text, true).")
        			  AND (".$this->getFolderCondition().")";
        if ($order) {
            $sql .= " ORDER BY C.C_FULLNAME ".$order." ";
        }
        $sql .= " LIMIT ".$limit;
        return $this->query($sql)->fetchAll();
    }
    
    protected function getFieldCondition($field, $text)
    {
    	$text = explode(" ", $text);
    	$where = array();
    	foreach ($text as $v) {
    		$where[] = $field . " LIKE '%".$this->escape($v)."%'";
    	}
    	return implode(' AND ', $where);
    }
    public function fastSearch($text, $limit = 10)
    {
    	$sql = "SELECT C_ID, C_FULLNAME, C_EMAILADDRESS FROM CONTACT 
    			WHERE ".$this->getFieldCondition('C_FULLNAME', $text)." AND C_EMAILADDRESS != ''
    			LIMIT ".(int)$limit;
		$contacts = $this->query($sql)->fetchAll('C_ID');
		$n = count($contacts);
		if ($n >= $limit) {
			return $contacts;
		}    	
		$contact_ids = array_keys($contacts);
		if ($n) {
			$sql = "SELECT * FROM EMAIL_CONTACT 
					WHERE EC_ID IN ('".implode("','", $contact_ids)."')";
			$emails = $this->query($sql)->fetchAll();
			foreach ($emails as $row) {
				if ($row['EC_EMAIL'] != $contacts[$row['EC_ID']]) {
					$contact = $contacts[$row['EC_ID']];
					$contact['C_EMAILADDRESS'] = $row['EC_EMAIL'];
					$contacts[] = $contact;
				}
			} 
			$n = count($contacts);
			if ($n >= $limit) {
				return $contacts;
			}    	
		}
		if (strpos($text, ' ') !== false) {
			return $contacts;
		}
		$limit = $limit - $n;
    	$sql = "SELECT * FROM EMAIL_CONTACT 
    			WHERE EC_EMAIL LIKE '".$this->escape($text)."%'
    			AND EC_ID NOT IN ('".implode("','", $contact_ids)."')
    			LIMIT ".$limit;
    	$emails = $this->query($sql)->fetchAll();
    	$ids = array();
    	foreach ($emails as $row) {
    		$ids[$row['EC_ID']] = 1;
    	} 
    	$sql = "SELECT C_ID, C_FULLNAME FROM CONTACT
    			WHERE C_ID IN ('".implode("','", array_keys($ids))."')";
    	$data = $this->query($sql)->fetchAll('C_ID');
    	foreach ($emails as $row) {
    		$contact = $data[$row['EC_ID']];
    		$contact['C_EMAILADDRESS'] = $row['EC_EMAIL'];
    		$contacts[] = $contact;
    	} 
    	return $contacts;
    }
    
    public function getByName($name, $sort = false, $limit = false, $search_email = false)
    {	
    	if ($sort) {
    		$sort_info = explode(" ", $sort);
    		if ($sort_info[0] == 'C_EMAILADDRESS') {
    			$sort_info[0] = 'EC_EMAIL';
    			$sort = implode(" ", $sort_info);
    		}
    	} 	
		$sql = "SELECT U.U_ID, U.U_STATUS, C.* ". (($sort && $sort_info[0] == 'EC_EMAIL') || $search_email ? ", EC_EMAIL C_EMAILADDRESS" : "")."  
				FROM CONTACT C LEFT JOIN 
				     WBS_USER U ON C.C_ID = U.C_ID 
				     ".(($sort && $sort_info[0] == 'EC_EMAIL') || $search_email ? " JOIN EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID" : "")."
				WHERE (".$this->getWhereByName($name, $search_email).") 
						AND 
					  (".$this->getFolderCondition().")";
		if ($sort) {
            if ($sort_info[0] == 'C_FULLNAME') {
                $sql .= ' ORDER BY '.$sort;
            } elseif ($sort_info[0] == 'EC_EMAIL') {
                $sql .= " ORDER BY EC_EMAIL = '', ".$sort;
            } else {
        	    $sql .= " ORDER BY ".$sort_info[0]." IS NULL, ".$sort;
            }
		}			
		if ($limit) {
			$sql .= " LIMIT ".$limit;
		}
		
		return $this->query($sql)->fetchAll('C_ID');
    }
    
    public function countByName($name)
    {
		$sql = "SELECT COUNT(*) N FROM CONTACT C 
		  	    WHERE (".$this->getWhereByName($name).") AND (".$this->getFolderCondition().")";
		return $this->query($sql)->fetchField('N');
    }
    
    public function getWhereByName($name, $search_email = false)
    {
        $where = array();
		$names = explode(" ", $name);
		$middle = ContactType::getFieldId('C_MIDDLENAME');
		foreach ($names as $name_part) {
			$where[] = "(C_FULLNAME LIKE '%".$this->escape($name_part)."%'
					   ".($middle ? " OR C_MIDDLENAME LIKE '%".$this->escape($name_part)."%'" : '')." 
						OR 
						C_COMPANY LIKE '%".$this->escape($name_part)."%'
						".($search_email ? " OR EC_EMAIL LIKE '%".$this->escape($name_part)."%'" : "")."
						)";
		}
		return implode(" AND ", $where);
    }
    
    
    public function getFolderCondition($folder_id = false, $contact_id = false)
    {
        if (!$contact_id) {
            $contact_id = User::getContactId();
        }
        switch ($folder_id) {
            case "ALL":
            case false:
                $user_id = Contact::getInfo($contact_id, false, 'U_ID'); 
        	    if (User::isAdmin('CM', false, $user_id)) {
        	        return "TRUE";
        	    } else {
            		$user_rights_model = new UserRightsModel();
            		$folders = $user_rights_model->getFolders('CM', $user_id);
            		$folders[] = 'PUBLIC';
            		$folders[] = 'PRIVATE'.$contact_id;
            		
        	        $result = "CF_ID IN ('".implode("', '", $folders)."')";
        	        if (User::hasAccess('CM', Rights::FOLDERS, '')) {
        	        	$result .= ' OR CF_ID IS NULL';	
        	        }
        	        return $result;
        	    }
    	    case "PRIVATE".$contact_id:
    	        if (User::hasAccess('CM', Rights::FOLDERS, 'PRIVATE')) {
        	        return "CF_ID = '".$this->escape($folder_id)."'";
    	        } else {
   	                return "FALSE";
    	        }
    	    case "PUBLIC": 
    	        return "CF_ID = 'PUBLIC'";
    	    default: 
    	        if (User::hasAccess('CM', Rights::FOLDERS, $folder_id)) {
    	            return "CF_ID = '".$this->escape($folder_id)."'";
    	        } else {
    	            return "FALSE";
    	        }  
    	}        
    }
    
    
    /**
     * Parse XML and returns array with image info
     * 
     * @param $xml
     * @return array
     */
	public static function parseImageXML($xml)
	{
		$result = array(
			"FILENAME" => "",
			"SIZE" => "",
			"DISKFILENAME" => "",
			"TYPE" => "",
			"DATETIME" => "",
			"MIMETYPE" => "",
			"MODIFIED" => "",
			"PREVFILENAME" => ""
		);
		if ( !$xml ) {
			return $result;
		}
		$dom = new DOMDocument("1.0", "UTF-8");
		@$dom->loadXML($xml); 
		if ( !$dom || !($root = $dom->documentElement)) {
			return $result;
		}
		
		$result["FILENAME"] = base64_decode(@$root->getAttribute("FILENAME") );
		$result["SIZE"] = @$root->getAttribute("SIZE");
		$result["DISKFILENAME"] = @$root->getAttribute("DISKFILENAME");
		$result["TYPE"] = @$root->getAttribute("TYPE");
		$result["DATETIME"] = @$root->getAttribute("DATETIME");
		$result["MIMETYPE"] = @$root->getAttribute("MIMETYPE");
		$result["PREVFILENAME"] = @$root->getAttribute("DISKFILENAME");

		return $result;
	}        
               
    
    public function countAllByFolders($contact_id = false)
    {
    	$result = array();
    	$sql = "SELECT CF_ID, count(*) C 
    			FROM ".$this->table." GROUP BY CF_ID";
    	$data = $this->prepare($sql)->query(array('contact_id' => $contact_id));
    	foreach ($data as $row) {
    		$result[$row['CF_ID']] = $row['C'];
    	}
    	return $result;
    }
    
    public function countByContact($contact_id, $other = false)
    {
        $sql = "SELECT count(*) c FROM ".$this->table." 
        		WHERE CF_ID IS NULL AND C_CREATECID ".($other ? "!=" : "=")." i:contact_id";
        return $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchField('c');
    }   
    
    public function getBySQL($where, $sort, $limit) 
    {
        $sql = "SELECT U.U_ID, U.U_PASSWORD, U.U_STATUS, C.* 
        		FROM WBS_USER U RIGHT JOIN
           			 CONTACT C ON U.C_ID = C.C_ID
           		WHERE ".$where;
        if ($sort) {
        	$sql .= " ORDER BY ".$sort;
        }
        if ($limit) {
        	$sql .= " LIMIT ".$limit;
        }
               
        return $this->query($sql)->fetchAll();  
    }
    
    public function countBySQL($where)
    {
		$sql = "SELECT COUNT(*) C FROM CONTACT WHERE ".$where;
		return $this->query($sql)->fetchField('C');    	
    }
    
    public function getSubscribeByEmail($email, $confirm_only = false)
    {
    	$sql = "SELECT C_ID FROM ".$this->table." 
    			WHERE C_EMAILADDRESS = s:email AND 
    				  C_SUBSCRIBER ". ($confirm_only ? "= 1" : "IS NOT NULL");
    	return $this->prepare($sql)->query(array('email' => $email))->fetchField('C_ID');
    }
    
    public function getEmailByContactId($contact_ids)
    {
    	$sql = "SELECT * FROM  EMAIL_CONTACT WHERE EC_ID IN (".implode(", ", $contact_ids).") AND EC_EMAIL != ''";
    	$data = $this->query($sql);
    	$result = array();
    	foreach ($data as $row) {
    		if (isset($result[$row['EC_ID']])) {
    			$result[$row['EC_ID']][] = $row['EC_EMAIL'];
    		} else {
    			$result[$row['EC_ID']] = array($row['EC_EMAIL']);
    		}
    	}
    	return $result;
    }
    
    public function getAllEmails()
    {
        $sql = "SELECT EC_ID, EC_EMAIL FROM EMAIL_CONTACT WHERE EC_EMAIL != ''";
        $data = $this->query($sql);
        $result[] = array();
        foreach ($data as $row) {
    		if (isset($result[$row['EC_ID']])) {
    			$result[$row['EC_ID']][] = $row['EC_EMAIL'];
    		} else {
    			$result[$row['EC_ID']] = array($row['EC_EMAIL']);
    		}
        }
        return $result;
    }
	
    /**
     * Change folder ids
     * 
     * @param $old
     * @param $new
     */
	public function moveFolder($old, $new)
	{
		$sql = "UPDATE ".$this->table." 
				SET CF_ID = CONCAT('".$this->escape($new)."', SUBSTR(CF_ID, ".(strlen($old) + 1).")) 
				WHERE CF_ID LIKE '".$this->escape($old)."%'";
		$this->exec($sql);
	}
	
	
	/**
     * Delete all contacts in folder and subfolders
     * 
     * @param string $folder_id
     * @return boold
	 */
	public function deleteByFolderId($folder_id)
	{
        //@todo: Delete avatars and recalculate disk usage size for account
		$sql = "DELETE C, EC, CL
		        FROM CONTACT C LEFT JOIN 
		             EMAIL_CONTACT EC ON C.C_ID = EC.EC_ID LEFT JOIN
		             CLIST_CONTACT CL ON C.C_ID = CL.C_ID
		        WHERE C.CF_ID LIKE '".$this->escape($folder_id)."%'";
		return $this->exec($sql);
	}

    /**
     * Delete contact
     *  
     * @param int|array $id
     * @return bool
     */
    public function delete($ids)
    {
    	// Foolproof
    	if (!$ids) return false;
   	    $condition = is_array($ids) ? " IN ('".implode("', '", $ids)."')" : " = ".(int)$ids;
   	    if (Wbs::getDbkeyObj()->appExists('CM')) {
	   	    // Delete from lists
	        $sql = "DELETE FROM CLIST_CONTACT WHERE C_ID ".$condition;
	    	$this->exec($sql);
   	    }
        // Delete emails
    	$sql = "DELETE FROM EMAIL_CONTACT WHERE EC_ID ".$condition;
    	$this->exec($sql);
    	// Delete contact
	    $sql = "DELETE FROM ".$this->table." WHERE C_ID ".$condition;
	    return $this->exec($sql);
    }
	
    public function emptyField($type_id, $dbname) 
    {
        $sql = "UPDATE ".$this->table." 
        		SET ".$dbname." = NULL 
        		WHERE CT_ID = i:type_id";
        return $this->prepare($sql)->exec(array('type_id' => $type_id));    
    }
}

?>