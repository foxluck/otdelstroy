<?php

/**
 * Class for working with Users' Rights on database layer
 * 
 * @author WebAsyst Team
 *
 */
class UsersRightsModel extends DbModel
{
    protected $table = "U_ACCESSRIGHTS";
    
    /**
     * Delete all exists rights of the user [to the application]
     * 
     * @param $user_id
     * @param $app_id
     */
    public function delete($user_id, $app_id = false) 
    {
    	$sql = "DELETE FROM {$this->table} WHERE AR_ID = s:user_id" . ($app_id ? "  AND AR_PATH LIKE s:path" : "");
    	$this->prepare($sql)->exec(array('user_id' => $user_id, 'path' => "/ROOT/".$app_id."/%"));
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
     * Return single user right
     * 
     * @param $user_id
     * @param $app_id
     * @param $section
     * @param $object_id
     * @return int
     */
    public function get($user_id, $app_id, $section, $object_id)
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
        foreach ($data as $row) {
			$right = max($right, $row['AR_VALUE']);        	
        }
    	return new RightValue($right);
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
    	$sql = 	"SELECT * FROM U_ACCESSRIGHTS 
    			 WHERE AR_ID = s:user_id AND 
    			 	   AR_VALUE > 0" . ($app_id ? " AND AR_PATH LIKE '/ROOT/" . $app_id . "/%'" : "");
    	$data = $this->prepare($sql)->query(array('user_id' => $user_id));
    	foreach ($data as $row) {
    		$row['AR_VALUE'] = (int) $row['AR_VALUE'];
    		$path = explode("/", $row['AR_PATH']);
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
    		} else {
    			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][0] = (int) $row['AR_VALUE'];
    			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][1] = 0;
    		}
    		// Only Groups right
    		$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][2] = (int) $row['AR_VALUE'];
    		
    	}
    	if ($app_id) {    	
    		return isset($rights[$app_id]) ? $rights[$app_id] : array(); 
    	} else {
    		return $rights;
    	}
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
    
}

class RightValue
{
	protected $value;
	
	public function __construct($value)
	{
		$this->value = $value;	
	}

	public function isFull() 
	{
		return $this->value >= 7;	
	}
	
	public function isWrite()
	{
		return $this->value >= 3;
	}
	
	public function isRead()
	{
		return $this->value >= 1;
	}
	
	public function __toString()
	{
		return $this->value;	
	}
	
}


?>