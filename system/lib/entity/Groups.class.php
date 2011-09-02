<?php 

/**
 * Static class for work with the groups of users 
 *
 */
class Groups
{
    const ALL = 'all';
    const ONLINE = 'online';
    const INVITED = 'invited';
    const DISABLED = 'disabled';
    
    const USER_LOCKED = 2;
    const USER_INVITED = 3;
    
    /**
     * System groups
     * 
     * @var array 
     */
    public static $system_groups = array(
    	// _('All Users')
        self::ALL  => array(
               "UG_ID" => self::ALL,
               "UG_NAME" => "All users"
        ),
    	// _('Now Online')         
        self::ONLINE => array(
                "UG_ID" => self::ONLINE,
                "UG_NAME" => "Now online",
        ),
        // _('Invited')
        self::INVITED => array(
                "UG_ID" => self::INVITED,
                "UG_NAME" => "Invited"
        ),
        // _('Access Revoked')
        self::DISABLED => array(
                "UG_ID" => self::DISABLED,
                "UG_NAME" => "Temporarily disabled",
        ),
    );
    
    /**
     * Check group is system or not.
     * 
     * @param int $group_id
     * @return bool 
     */
    public static function isSystem($group_id)
    {
        return isset(self::$system_groups[$group_id]);
    }
    
    /**
     * Returns Group info 
     * 
     * @param int $group_id
     * @return array
     */
    public static function get($group_id)
    {
        if (self::isSystem($group_id)) {
            return array(
            	'UG_ID' => $group_id, 
            	'UG_NAME' => _(self::$system_groups[$group_id]['UG_NAME'])
            );
        }
        elseif ($group_id) {
            $groups_model = new GroupsModel();
            $group_info = $groups_model->get($group_id);
            if (!$group_info) {
                throw new Exception("Group not found [id: ".$group_id."]");
            }
            return $group_info;
        }
        else {
            throw new Exception("Unkown group [id: " . $group_id."]");
        }
    }
    
    
    public static function getSystemGroups($user_info)
    {
        $groups = array(self::ALL);
        if ($user_info['U_STATUS'] == self::USER_LOCKED) {
            $groups[] = self::DISABLED;
        }
        if ($user_info['U_STATUS'] == self::USER_INVITED) {
            $groups[] = self::INVITED;
        }
        if (time() - $user_info['LAST_TIME'] < User::ONLINE_TIMEOUT) {
            $groups[] = self::ONLINE; 
        }
        return $groups;
    }
    
    /**
     * Returns list of the groups
     *  
     * @return unknown_type
     */
    public static function getGroups($with_system_groups = true)
    {
    	if ($with_system_groups) {
	    	$groups = self::$system_groups; 
	    	foreach ($groups as &$group) {
	    		// Localization system group
	    		$group['UG_NAME'] = _($group['UG_NAME']); 
	    	}	
    	} else {
    		$groups = array();
    	}
        $groups_model = new GroupsModel();
        $groups = $groups + $groups_model->getAll();
        return $groups;
    }
    
	/**
	 * Returns users by group_id
	 * 
	 * @param $group_id
	 * @param $sort
	 * @param $limit
	 * @return array
	 */
    public static function getUsers($group_id, $sort = false, $limit = false)
    {
        if (self::isSystem($group_id)) {
            switch ($group_id) {
                case self::ALL: {
                    $users_model = new UsersModel();
                    return $users_model->getAll(true, false, $sort, $limit);
                }
                case self::INVITED: {
                    $users_model = new UsersModel();
                    return $users_model->getAll(true, self::USER_INVITED, $sort, $limit);
                }
                case self::ONLINE: {
                    $users_model = new UsersModel();
                    return $users_model->getLastUsers(time() - User::ONLINE_TIMEOUT, $sort, $limit);
                }
                case self::DISABLED: {
                    $users_model = new UsersModel();
                    return $users_model->getAll(true, self::USER_LOCKED, $sort, $limit);                    
                }
            }
        }
        else {
        	$user_groups_model = new UserGroupsModel();
        	return $user_groups_model->getUsers($group_id, $sort, $limit);
        }
    }

    /**
     * Returns number of users in the group
     * 
     * @param $group_id
     * @return int
     */
    public static function countUsers($group_id)
    {
        if (self::isSystem($group_id)) {
            switch ($group_id) {
                case self::ALL: {
                    $users_model = new UsersModel();
                    return $users_model->countAll(false);
                }
                case self::INVITED: {
                    $users_model = new UsersModel();
                    return $users_model->countAll(self::USER_INVITED);
                }
                case self::ONLINE: {
                    $users_model = new UsersModel();
                    return $users_model->countLastUsers(User::ONLINE_TIMEOUT);
                }
                case self::DISABLED: {
                    $users_model = new UsersModel();
                    return $users_model->countAll(self::USER_LOCKED);                    
                }
            }
        } else {
            $groups_model = new UserGroupsModel();
            return $groups_model->countByGroupId($group_id);
        }
    }    
    
    
}


?>