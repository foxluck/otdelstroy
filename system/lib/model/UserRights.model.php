<?php

/**
 * Class for working with Users' Rights on database layer
 * 
 * @author WebAsyst Team
 *
 */
class UserRightsModel extends DbModel
{
    protected $table = "U_ACCESSRIGHTS";
    
    protected static $objects = array();
    
    /**
     * Delete all exists rights of the user [to the application]
     * 
     * @param $user_id
     * @param $app_id
     */
    public function delete($user_id, $app_id = false) 
    {
        $this->addCacheCleaner(new DbCacher('APPS_'.$user_id));
    	$sql = "DELETE FROM {$this->table} WHERE AR_ID = s:user_id" . ($app_id ? "  AND AR_PATH LIKE s:path" : "");
    	$this->prepare($sql)->exec(array('user_id' => $user_id, 'path' => "/ROOT/".$app_id."/%"));
    }
    
    
    public function deleteByUserId($user_ids)
    {
		if (!is_array($user_ids)) {
			$user_ids = array($user_ids);
		}
		foreach ($user_ids as &$user_id) {
			$user_id = $this->escape($user_id);
		}
		$sql = "DELETE FROM ".$this->table." WHERE AR_ID IN ('".implode("', '", $user_ids)."')";
		return $this->exec($sql);    	
    }
    
    /**
     * Save user rights to the object.
     * If value is 0 then removes record. 
     * 
     * @param $user_id
     * @param $path
     * @param $object_id
     * @param $value
     * @param $save_max - Save max (old_value, new_value) - need for save all
     * @return 
     */
    public function save($user_id, $path, $object_id, $value, $save_max = false)
    {
        $this->addCacheCleaner(new DbCacher('APPS_'.$user_id));
    	if ($value) {
    		if (!$save_max) {
    			$sql = "INSERT INTO {$this->table} 
    					SET AR_ID = s:user_id, AR_PATH = s:path, AR_OBJECT_ID = s:object_id, AR_VALUE = i:value 
    					ON DUPLICATE KEY UPDATE AR_VALUE = VALUES(AR_VALUE)";
    		} else {
    			$sql = "INSERT INTO {$this->table} 
    					SET AR_ID = s:user_id, AR_PATH = s:path, AR_OBJECT_ID = s:object_id, AR_VALUE = i:value 
    					ON DUPLICATE KEY UPDATE AR_VALUE = GREATEST(AR_VALUE, VALUES(AR_VALUE))";    			
    		}
    	} else {
    		$sql = "DELETE FROM {$this->table} WHERE AR_ID = s:user_id AND AR_PATH = s:path AND AR_OBJECT_ID = s:object_id";
    	}
    	$this->prepare($sql)->exec(array('user_id' => $user_id, 'path' => $path, 'object_id' => $object_id, 'value' => $value));
    }
    
    /**
     * Return right of the user
     * 
     * @param $user_id
     * @param $app_id
     * @param $section
     * @param $object_id
     * @return array
     */
    public function get($user_id, $app_id, $section, $object_id, $return_array = false)
    {
    	$path = '/ROOT/'.$app_id.'/'.$section;
    	$sql = 	"SELECT AR_VALUE FROM U_ACCESSRIGHTS 
    			 WHERE AR_ID = s:user_id AND 
    			 	   AR_VALUE > 0 AND 
    			 	   AR_PATH = s:path AND
    			 	   AR_OBJECT_ID = s:object_id";
    	$right = $this->prepare($sql)->query(array('user_id' => $user_id, 'path' => $path, 'object_id' => $object_id))->fetchField('AR_VALUE');
    	
    	$sql = "SELECT UGA.AR_VALUE FROM UG_ACCESSRIGHTS UGA JOIN UGROUP_USER UGU ON UGA.AR_ID = UGU.UG_ID
    			WHERE UGU.U_ID = s:user_id AND 
    				  UGA.AR_VALUE > 0 AND 
    			 	  AR_PATH = s:path AND
    			 	  AR_OBJECT_ID = s:object_id";
    	$data = $this->prepare($sql)->query(array('user_id' => $user_id, 'path' => $path, 'object_id' => $object_id));
    	$right_group = 0;
        foreach ($data as $row) {
			$right_group = max($right_group, $row['AR_VALUE']);        	
        }
        if ($return_array) {
	        // Return array(summary right, only user right, groups right)
	       	return array(max($right, $right_group), $right, $right_group);
        } else {
        	return max($right, $right_group);
        }
    }
    
    /**
     * Return all user rights in array
     * array(
     * 	APP_ID => array(
     * 		SECTION_ID => array( 
     * 			OBJECT_ID => RIGHT,
     * 			...
     * 		),
     * 		...
     * 	),
     * 	...
     * ),
     * 
     * @param $user_id
     * @param $app_id
     * @return array
     */
    public function getRights($user_id, $app_id = false) 
    {
    	$rights = array();
    	// Get personal user rights
    	$sql = 	"SELECT AR_PATH, AR_OBJECT_ID, AR_VALUE FROM U_ACCESSRIGHTS 
    			 WHERE AR_ID = s:user_id AND 
    			 	   AR_VALUE > 0" . ($app_id ? " AND AR_PATH LIKE '/ROOT/" . $app_id . "/%'" : "");
    	$data = $this->prepare($sql)->query(array('user_id' => $user_id));
    	foreach ($data as $row) {
    		$row['AR_VALUE'] = (int) $row['AR_VALUE'];
    		$path = explode("/", $row['AR_PATH']);
    		if (!isset($path[2])) {
    			continue;
    		}
    		// Max right
    		$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][0] = $row['AR_VALUE'];
    		// Personal right
    		$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][1] = $row['AR_VALUE'];
    		// Group right
    		$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][2] = 0;
    	}
    	// Get groups rights and overload
    	$sql = "SELECT UGA.* FROM UG_ACCESSRIGHTS UGA JOIN UGROUP_USER UGU ON UGA.AR_ID = UGU.UG_ID
    			WHERE UGU.U_ID = s:user_id AND 
    				  UGA.AR_VALUE > 0" . ($app_id ? " AND AR_PATH LIKE '/ROOT/" . $app_id . "/%'" : "");
    	$data = $this->prepare($sql)->query(array('user_id' => $user_id));
        foreach ($data as $row) {
        	// "/ROOT/APP_ID/SECTION_ID"
    		$path = explode("/", $row['AR_PATH']);
    		if (isset($rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']])) {
    			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][0] = max($rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][0], $row['AR_VALUE']);
    			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][2] = max($rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][2], $row['AR_VALUE']);
    		} else {
    			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][0] = (int) $row['AR_VALUE'];
    			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][1] = 0;
    			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][2] = (int) $row['AR_VALUE'];
    		}
    	}
    	if ($app_id) {    	
    		return isset($rights[$app_id]) ? $rights[$app_id] : array(); 
    	} else {
    		return $rights;
    	}
    }
    
    public function getUsers($app_id, $section, $object_id, $with_groups = true)
    {
        // Users rights
        $path = '/ROOT/'.$app_id.'/'.$section;
        $sql = "SELECT AR_ID, AR_VALUE FROM U_ACCESSRIGHTS 
        		WHERE AR_PATH = s:path AND AR_OBJECT_ID = s:object_id";
        $data = $this->prepare($sql)->query(array('path' => $path, 'object_id' => $object_id));
        $result = array();
        foreach ($data as $row) {
            if ($row['AR_VALUE']) {
	            $result[$row['AR_ID']] = array(
	                (int)$row['AR_VALUE'],
	                (int)$row['AR_VALUE'],
	                0
	            );
            }
        }
        if ($with_groups) {
	        // Group rights
	        $sql = "SELECT UG.U_ID, MAX(UGA.AR_VALUE) AR_VALUE 
	        		FROM `UG_ACCESSRIGHTS` UGA JOIN 
	        			  UGROUP_USER UG ON UGA.AR_ID = UG.UG_ID 
	        	    WHERE UGA.AR_PATH = s:path AND UGA.AR_OBJECT_ID = s:object_id 
	        	    GROUP BY UG.U_ID";
	        $data = $this->prepare($sql)->query(array('path' => $path, 'object_id' => $object_id));
	        foreach ($data as $row) {
	            if (isset($result[$row['U_ID']])) {
	                $result[$row['U_ID']][0] = max($result[$row['U_ID']][0], (int)$row['AR_VALUE']);
	                $result[$row['U_ID']][2] = (int)$row['AR_VALUE'];
	            } else {
	                $result[$row['U_ID']] = array(
	                    (int)$row['AR_VALUE'],
	                    0,
	                    (int)$row['AR_VALUE']
	                );
	            }
	        }
        }
        return $result;
    }
    
    public function getInheritRights($app_id = false) 
    {
    	$rights = array();
    	$sql = "SELECT * FROM ACCESSRIGHTS_LINK" . ($app_id ? " WHERE AR_PATH LIKE '/ROOT/" . $app_id . "/%'" : "");
    	$data = $this->query($sql);
    	foreach ($data as $row) {
    		$path = explode("/", $row['AR_PATH']);
    		$link_path = explode("/", $row['LINK_AR_PATH']);
    		$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']] = array(
    			'app_id' => $link_path[2],
    			'section_id' => $link_path[3],
    			'object_id' => $row['LINK_AR_OBJECT_ID']
    		);
    	}	
    	if ($app_id) {    	
    		return isset($rights[$app_id]) ? $rights[$app_id] : array(); 
    	} else {
    		return $rights;
    	}
    }
    
    /**
     * Returns array of the applications, available to the user 
     * 
     * @param $user_id
     * @return array
     */
    public function getAvailableApps($user_id) 
    {
        $this->setCacher(new DbCacher('APPS_'.$user_id, 60, 'SYSTEM'));
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
			$apps[] = array(
				'APP_ID' => preg_replace("#/ROOT/(.*)/SCREENS#", "$1", $row["AR_PATH"]),
				'SCREEN_ID' => $row['AR_OBJECT_ID']
			);
		}
		return $apps;		    	 	
    }
       
    public function getFolders($app_id, $user_id) 
    {
    	$sql = "SELECT UA.AR_OBJECT_ID 
    			FROM U_ACCESSRIGHTS UA 
    			WHERE UA.AR_ID = s:user_id AND UA.AR_VALUE > 0 AND UA.AR_PATH LIKE '/ROOT/".$app_id."/FOLDERS' 
    			UNION
    			SELECT UGA.AR_OBJECT_ID 
    			FROM UG_ACCESSRIGHTS UGA JOIN 
    				 UGROUP_USER UGU ON UGA.AR_ID=UGU.UG_ID
    			WHERE UGU.U_ID = s:user_id AND UGA.AR_VALUE > 0 AND UGA.AR_PATH LIKE '/ROOT/".$app_id."/FOLDERS'";
    	$folders = array();
		$data = $this->prepare($sql)->query(array('user_id' => $user_id));
		foreach ($data as $row) {
			$folders[] = $row['AR_OBJECT_ID'];
		}
		return $folders;		    	 	
    } 

    
    public function getObjects($app_id, $user_id, $key = 'FOLDERS')
    {
        if (isset(self::$objects[$user_id][$app_id][$key])) {
            return self::$objects[$user_id][$app_id][$key];
        }
    	$sql = "SELECT UA.AR_OBJECT_ID 
    			FROM U_ACCESSRIGHTS UA 
    			WHERE UA.AR_ID = s:user_id AND UA.AR_VALUE > 0 AND UA.AR_PATH LIKE '/ROOT/".$app_id."/".$key."' 
    			UNION
    			SELECT UGA.AR_OBJECT_ID 
    			FROM UG_ACCESSRIGHTS UGA JOIN 
    				 UGROUP_USER UGU ON UGA.AR_ID=UGU.UG_ID
    			WHERE UGU.U_ID = s:user_id AND UGA.AR_VALUE > 0 AND UGA.AR_PATH LIKE '/ROOT/".$app_id."/".$key."'";
    	$objects = array();
		$data = $this->prepare($sql)->query(array('user_id' => $user_id));
		foreach ($data as $row) {
			$objects[] = $row['AR_OBJECT_ID'];
		}
		self::$objects[$user_id][$app_id][$key] = $objects;
		return $objects;		    	 	
    }
    
	public function changeLogin($user_id, $login)
	{
		$sql = "UPDATE ".$this->table." SET AR_ID = s:login WHERE AR_ID = s:user_id";
		return $this->prepare($sql)->exec(array('user_id' => $user_id, 'login' => $login));
	}

    /**
     * Change AR_OBJECT_ID field
     * 
     * @param $app_id
     * @param $section
     * @param $old
     * @param $new
     * @param $with_child
     */
	public function updateObject($app_id, $section, $old, $new, $with_child = false)
	{
        if ($with_child) {
			$new = "CONCAT('".$this->escape($new)."', SUBSTR(AR_OBJECT_ID, ".(strlen($old) + 1)."))";
			$old = "'".$this->escape($old)."%'";
        } else {
			$new = "'".$this->escape($new)."'";
			$old = "'".$this->escape($old)."'";
        }
		$sql = "UPDATE {$this->table} SET AR_OBJECT_ID = $new
				WHERE AR_PATH = '/ROOT/$app_id/$section' AND AR_OBJECT_ID LIKE $old";
		$this->exec($sql);

		$sql = "UPDATE UG_ACCESSRIGHTS SET AR_OBJECT_ID = $new
				WHERE AR_PATH = '/ROOT/$app_id/$section' AND AR_OBJECT_ID LIKE $old";
		$this->exec($sql);
	}

}


?>