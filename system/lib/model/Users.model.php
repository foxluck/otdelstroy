<?php 

class UsersModel extends DbModel 
{
    protected $table = "WBS_USER";
    protected $id = "U_ID";
    
    /**
     * Returns user's info
     * 
     * @param int $user_id
     * @param bool $full_info - true, if you want get full info (with contacts etc.)
     * 
     * @return array
     */
    public function get($user_id, $full_info = true)
    {
        $this->setCacher(new DbCacher('USER_'.$user_id, 30, 'SYSTEM'));
        $sql = "SELECT U.U_ID, U.U_PASSWORD, U.U_STATUS, C.* FROM ".$this->table." U 
        		LEFT JOIN CONTACT C ON
        		U.C_ID = C.C_ID  
        		WHERE ".$this->id." = s:user_id";
        return $this->prepare($sql)->query(array("user_id" => $user_id))->fetch();   
    }
    
    public function getAllId()
    {
        $sql = "SELECT ".$this->id." FROM ".$this->table;
        $data = $this->query($sql);
        $result = array();
        foreach ($data as $row) {
            $result[] = $row[$this->id];
        }
        return $result;
    }
    
    /**
     * Returns number of the users for which status was changed
     * 
     * @param array $ids 
     * @param int $status
     * @return int
     */
    public function setStatusByContactId($ids, $status) 
    {
        $where_status = $status == User::STATUS_LOCKED ? 0 : User::STATUS_LOCKED;
        $sql = "UPDATE ".$this->table." 
        		SET U_STATUS = i:status 
        		WHERE C_ID IN ('".implode("','", $this->escape($ids))."') AND U_STATUS = i:where_status";
        return $this->prepare($sql)->query(array('status' => $status, 'where_status' => $where_status))->affectedRows();
    }
    
    public function getNames($key = 'C_ID')
    {
        $sql = "SELECT U.U_ID, U.U_STATUS, C.C_ID, C.C_FULLNAME, C.C_EMAILADDRESS 
        		FROM WBS_USER U JOIN CONTACT C ON U.C_ID = C.C_ID
        		ORDER BY C.C_FULLNAME";
        return $this->query($sql)->fetchAll($key);
    }
    
    public function set($user_id, $login, $password, $status = 0) 
    {
    	$this->addCacheCleaner(new DbCacher('USER_'.$user_id));
    	$login = mb_strtoupper($login);
        if (!preg_match("/^[A-Z0-9_]+$/i", $login)) {
        	throw new Exception(_("Latin letters and numbers only, no spaces."));
        }
            	
    	$password = md5($password);
    	
    	$sql = "UPDATE ".$this->table." 
    			SET U_ID = s:login, U_PASSWORD = s:password, U_STATUS = i:status
    			WHERE U_ID = s:user_id";
    	try {
	    	$result = $this->prepare($sql)->exec(array(
	    		'login' => $login,
	    		'password' => $password,
	    		'status' => $status,
	    		'user_id' => $user_id 
	    	));
    	} catch (MySQLException $e) {
    		throw new Exception(_("This login name is already in use. Please try another name."));
    	}	

    	// Replacing U_ID
    	$sql = "UPDATE U_ACCESSRIGHTS SET AR_ID = s:login WHERE AR_ID = s:user_id";
    	$this->prepare($sql)->query(array('login' => $login, 'user_id' => $user_id));
    	
    	$sql = "UPDATE USER_SETTINGS SET U_ID = s:login WHERE U_ID = s:user_id";
    	$this->prepare($sql)->query(array('login' => $login, 'user_id' => $user_id));
    	
    	$sql = "UPDATE UGROUP_USER SET U_ID = s:login WHERE U_ID = s:user_id";
    	$this->prepare($sql)->query(array('login' => $login, 'user_id' => $user_id));

    }
    
    public function setPassword($user_id, $password)
    {
        $this->addCacheCleaner(new DbCacher('USER_'.$user_id));
        $password = md5($password);
    	$sql = "UPDATE ".$this->table." 
    			SET U_PASSWORD = s:password 
    			WHERE U_ID = s:user_id";
    	return $this->prepare($sql)->exec(array('user_id' => $user_id, 'password' => $password)); 		
    }
    
    public function setStatus($user_id, $status) 
    {
        $this->addCacheCleaner(new DbCacher('USER_'.$user_id));
    	$sql = "UPDATE ".$this->table." SET U_STATUS = i:status WHERE U_ID = s:user_id";
    	return $this->prepare($sql)->exec(array('user_id' => $user_id, 'status' => $status)); 		
    }
    
    
    /**
     * Returns user's info by contact id
     * 
     * @param int $contact_id
     * @return array
     */
    public function getByContact($contact_id)
    {
        $sql = "SELECT U.U_ID, U.U_PASSWORD, U.U_STATUS, C.* FROM ".$this->table." U 
        		LEFT JOIN CONTACT C ON
        		U.C_ID = C.C_ID  
        		WHERE U.C_ID = i:contact_id";
        return $this->prepare($sql)->query(array("contact_id" => $contact_id))->fetch();    	
    }
    
    /**
     * Returns user's info by contact id
     * 
     * @param int $contact_id
     * @return array
     */
    public function getByContactId($contact_id)
    {
        $sql = "SELECT U.U_ID, U.U_PASSWORD, U.U_STATUS, C.* FROM ".$this->table." U 
        		RIGHT JOIN CONTACT C ON U.C_ID = C.C_ID  
        		WHERE C.C_ID = i:contact_id";
        return $this->prepare($sql)->query(array("contact_id" => $contact_id))->fetch();    	
    }    
        
    public function getIdByContactId($contact_id)
    {
        $sql = "SELECT U_ID FROM ".$this->table." U 
        		WHERE U.C_ID = i:contact_id";
        return $this->prepare($sql)->query(array("contact_id" => $contact_id))->fetchField('U_ID');    	
    }

    public function getn($user_ids)
    {
    	$users = array();
    	foreach ($user_ids as $uid) {
    		$users[] = $this->escape($uid);
    	}
        $sql = "SELECT * FROM ".$this->table." U 
        		LEFT JOIN CONTACT C ON
        		U.C_ID = C.C_ID  
        		WHERE ".$this->id." IN ('".implode("', '", $users)."')";
        return $this->query($sql)->fetchAll('U_ID');    	
    }
    
    /**
     * Create new user
     * 
     * @param array $data - array('login' => '..', 'password' => '', 'contact_id' => '..') 
     */
    public function add($login, $password, $contact_id, $status = 0)
    {
		$login = mb_strtoupper($login);
        if (!preg_match("/^[a-z0-9_@\.-]+$/i", $login) && !preg_match("/\\\$INVITED[0-9]+/", $login)) {
        	throw new Exception(_("Latin letters and numbers only, no spaces."));
        }
        if ($password !== false) {
        	$password = md5($password);
        }
        $sql = "INSERT INTO ".$this->table." 
        		SET U_ID = s:login, 
        			U_PASSWORD = s:password, 
        			C_ID = i:contact_id,
        			U_STATUS = i:status";
        try {
	        $result = $this->prepare($sql)->exec(array(
	        	'login' => $login, 
	        	'password' => $password, 
	        	'contact_id' => $contact_id,
	        	'status' => $status
	        ));
	        if ($result) {
	        	return $login;
	        } else {
	        	//throw new Exception('Database error');
	        }
        } catch (MySQLException $e) {
        	throw new Exception(_("This login name is already in use. Please try another name."));
        }
    }

    /**
     * Change login of the user
     * 
     * @param $user_id - id of the user 
     * @param $login - new login
     */
    public function changeLogin($user_id, $login)
    {
        $this->addCacheCleaner(new DbCacher('USER_'.$user_id));
    	$sql = "UPDATE ".$this->table. " SET U_ID = s:login WHERE U_ID = s:user_id";
    	try {
    		$this->prepare($sql)->exec(array('user_id' => $user_id, 'login' => $login));	
    	} catch (MySQLException $e) {
        	throw new Exception(_("This login name is already in use. Please try another name."));
        }
    }
      
    
    public function getDependences($user_id)
    {
    	
    }
    
    /** 
     * Returns groups of the user
     * array(
     * 		UG_ID => UG_NAME,
     * 	...
     * ) 
     * 
     * @param $user_id
     * @return array
     */
    public function getGroups($user_id)
    {
       	$sql = "SELECT UGROUP.UG_ID, UG_NAME FROM UGROUP_USER UG JOIN 
        			   UGROUP ON UG.UG_ID = UGROUP.UG_ID
        		WHERE UG.U_ID = s:user_id
        		ORDER BY UG_NAME";
        $data = $this->prepare($sql)->query(array("user_id" => $user_id));
        $result = array();
        foreach ($data as $row) {
        	$result[$row['UG_ID']] = $row['UG_NAME'];
        }
        return $result;
    }
    
    public function getAll($full_info = true, $status = false, $sort = false, $limit = false)
    {
        if ($full_info) {
            $sql = "SELECT U.U_ID, U.U_PASSWORD, U.U_STATUS, C.* FROM ".$this->table." U JOIN
            		CONTACT C ON U.C_ID = C.C_ID";
        } else {
            $sql = "SELECT * FROM ".$this->table." U";
        }

        $params = array();
        if ($status !== false) {
            $sql .= " WHERE U.U_STATUS = i:status";
            $params['status'] = $status;
        }
        if ($sort) {
        	$sql .= " ORDER BY ".$sort;
        }
        if ($limit) {
        	$sql .= " LIMIT ".$limit;
        }
       
        return $this->prepare($sql)->query($params)->fetchAll();
    }
    
    
    public function countAll($status = false)
    {
       $sql = "SELECT COUNT(*) NUM FROM ".$this->table." U";
       if ($status !== false) {
           $sql .= " WHERE U.U_STATUS = i:status";
       }
       return $this->prepare($sql)->query(array('status' => $status))->fetchField('NUM');
    }    
    
    public function getAllLastTime()
    {
        $sql = "SELECT U_ID, MAX(VALUE) LAST_TIME FROM USER_SETTINGS WHERE NAME = 'LAST_TIME' GROUP BY U_ID";
        return $this->query($sql)->fetchAll('U_ID');     
    }
    
    /**
     * Returns online users since $time
     * 
     * @param int $time - unix timestamp
     * @param string $sort
     * @param string $limit
     * @return array
     */
    public function getLastUsers($time, $sort = false, $limit = false)
    {
        $sql = "SELECT DISTINCT U.U_ID, U.U_PASSWORD, U.U_STATUS, C.* FROM ".$this->table." U JOIN
                CONTACT C ON U.C_ID = C.C_ID JOIN 
                USER_SETTINGS US ON U.U_ID = US.U_ID 
                WHERE US.NAME = 'LAST_TIME' AND US.APP_ID = '' AND US.VALUE > i:time";
        if ($sort) {
        	$sql .= " ORDER BY ".$sort;
       	}
		if ($limit) {
			$sql .= " LIMIT ".$limit;
		}       	
        return $this->prepare($sql)->query(array('time' => $time))->fetchAll();
    }    

    public function countLastUsers($time)
    {
        $time = time() - $time;
        $sql = "SELECT count(DISTINCT U.U_ID) NUM FROM ".$this->table." U JOIN
                USER_SETTINGS US ON U.U_ID = US.U_ID 
                WHERE US.NAME = 'LAST_TIME' AND US.APP_ID = '' AND US.VALUE > i:time";
        return $this->prepare($sql)->query(array('time' => $time))->fetchField('NUM');
    }    
    
    
    public function getAvailableApps($user_id) 
    {
    	$sql = "SELECT UA.AR_PATH, UA.AR_OBJECT_ID 
    			FROM U_ACCESSRIGHTS UA 
    			WHERE UA.AR_ID = s:user_id AND UA.AR_VALUE > 0 AND UA.AR_PATH LIKE '/ROOT/%/SCREENS' 
    			UNION
    			SELECT UGA.AR_PATH, UGA.AR_OBJECT_ID 
    			FROM UG_ACCESSRIGHTS UGA JOIN 
    				 UGROUP_USER UGU ON UGA.AR_ID=UGU.UG_ID
    			WHERE UGU.U_ID = s:user_id AND UGA.AR_VALUE > 0 AND UGA.AR_PATH LIKE '/ROOT/%/SCREENS'";
    	$apps = array();
		$data = $this->prepare($sql)->query(array('user_id' => $user_id));
		foreach ($data as $row) {
			$apps[] = preg_replace("#/ROOT/(.*)/SCREENS#", "$1", $row["AR_PATH"]);
		}
		return $apps;		    	 	
    }
    
    /**
     * Adds user to groups
     * 
     * @param $user_id
     * @param $group_ids - array(UG_ID, UG_ID, ...
     * @return 
     */
    public function addToGroups($user_id, $group_ids) 
    {
    	if (!$group_ids) {
    		return false;
    	}
    	if (!is_array($group_ids)) {
    		$group_ids = array($group_ids);
    	}
    	$sql = "INSERT INTO UGROUP_USER (U_ID, UG_ID) VALUES (s:user_id, '".implode("'),(s:user_id, '", $group_ids)."')";
    	$this->prepare($sql)->exec(array('user_id' => $user_id));     	
    }

    /**
     * Deletes user from groups
     * 
     * @param $user_id
     * @param $group_ids - array(UG_ID, UG_ID, ...
     * @return 
     */
    public function delFromGroups($user_id, $group_ids) 
    {
        if (!is_array($group_ids)) {
    		$group_ids = array($group_ids);
    	}    	
		$sql = "DELETE FROM UGROUP_USER WHERE U_ID = s:user_id AND UG_ID IN ('".implode("', '", $group_ids)."')";
		$this->prepare($sql)->exec(array('user_id' => $user_id));     	
    }
    
    /**
     * Returns arrau of the user ids by contact ids
     * 
     * @param int|array $contact_ids
     * @return array
     */
    public function getUserIds($contact_ids)
    {
    	if (!is_array($contact_ids)) {
    		$contact_ids = array($contact_ids);
    	}
    	foreach ($contact_ids as &$contact_id) {
    		$contact_id = (int)$contact_id;
    	}
    	$sql = "SELECT U_ID,C_ID FROM WBS_USER WHERE C_ID IN (".implode(",", $contact_ids).")";
    	return $this->query($sql)->fetchAll('C_ID', true);
    }
    
    public function countByFolderId($folder_id)
    {
        $folder_id .= "%";
        $sql = "SELECT COUNT(*) N FROM WBS_USER U JOIN CONTACT C ON U.C_ID = C.C_ID 
        		WHERE C.CF_ID LIKE s:folder_id";
        return $this->prepare($sql)->query(array('folder_id' => $folder_id))->fetchField('N');
    }

    /**
     * Delete users
     * 
     * @param $user_ids
     * @param $full
     * @return bool
     */
	public function delete($user_ids) 
	{		
		if (!is_array($user_ids)) {
			$condition = " = '".$this->escape($user_ids)."'";
		} else {
		    $condition = " IN ('".implode("', '", $this->escape($user_ids))."')";
		}

        // Delete from group
        $sql = "DELETE FROM UGROUP_USER 
        		WHERE U_ID ".$condition;
		$this->exec($sql);
        // Delete settings
        $sql = "DELETE FROM USER_SETTINGS
        		WHERE U_ID ".$condition;
		$this->exec($sql);
        // Delete rights
        $sql = "DELETE FROM U_ACCESSRIGHTS
        		WHERE AR_ID ".$condition;
		$this->exec($sql);				
		// Delete user
		$sql = "DELETE FROM WBS_USER 
				WHERE U_ID ".$condition;
		return $this->exec($sql);		
	}
    
    
    public function deleteByFolderId($folder_id)
    {
        $folder_id .= "%";
        // Delete from groups
        $sql = "DELETE FROM UGROUP_USER 
        		WHERE U_ID IN (
        			SELECT U.U_ID 
        			FROM WBS_USER U JOIN 
        				 CONTACT C ON U.C_ID = C.C_ID 
        			WHERE C.CF_ID LIKE s:folder_id
        		)";    
        $this->prepare($sql)->exec(array('folder_id' => $folder_id));
        // Delete settings 
        $sql = "DELETE FROM USER_SETTINGS 
        		WHERE U_ID IN (
        			SELECT U.U_ID 
        			FROM WBS_USER U JOIN 
        				 CONTACT C ON U.C_ID = C.C_ID 
        			WHERE C.CF_ID LIKE s:folder_id
        		)";
        $this->prepare($sql)->exec(array('folder_id' => $folder_id));    
        // Delete rights
        $sql = "DELETE FROM U_ACCESSRIGHTS 
        		WHERE AR_ID IN (
        			SELECT U.U_ID 
        			FROM WBS_USER U JOIN 
        				 CONTACT C ON U.C_ID = C.C_ID 
        			WHERE C.CF_ID LIKE s:folder_id
        		)";
        $this->prepare($sql)->exec(array('folder_id' => $folder_id));
        // Delete users
        $sql = "DELETE U FROM WBS_USER U JOIN CONTACT C ON U.C_ID = C.C_ID 
        		WHERE C.CF_ID LIKE s:folder_id";    
        $this->prepare($sql)->exec(array('folder_id' => $folder_id));                
    }
}

?>