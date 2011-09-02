<?php 
/**
 * Class for working with Users' Rights on database layer
 * 
 * @author WebAsyst Team
 *
 */
class GroupsRightsModel extends DbModel
{
    protected $table = "UG_ACCESSRIGHTS";
    
    /**
     * Delete all exists rights of the user [to the application]
     * 
     * @param $user_id
     * @param $app_id
     */
    public function delete($group_id, $app_id = false) 
    {
    	$sql = "DELETE FROM {$this->table} WHERE AR_ID = s:group_id" . ($app_id ? "  AND AR_PATH LIKE s:path" : "");
    	$this->prepare($sql)->exec(array('group_id' => $group_id, 'path' => "/ROOT/".$app_id."/%"));
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
    public function save($group_id, $path, $object_id, $value, $save_max = false)
    {
    	if ($value) {
    		if (!$save_max) {
    			$sql = "INSERT INTO {$this->table} 
    					SET AR_ID = s:group_id, AR_PATH = s:path, AR_OBJECT_ID = s:object_id, AR_VALUE = i:value 
    					ON DUPLICATE KEY UPDATE AR_VALUE = VALUES(AR_VALUE)";
    		} else {
    			$sql = "INSERT INTO {$this->table} 
    					SET AR_ID = s:group_id, AR_PATH = s:path, AR_OBJECT_ID = s:object_id, AR_VALUE = i:value 
    					ON DUPLICATE KEY UPDATE AR_VALUE = GREATEST(AR_VALUE, VALUES(AR_VALUE))";    			
    		}
    	} else {
    		$sql = "DELETE FROM {$this->table} WHERE AR_ID = s:group_id AND AR_PATH = s:path AND AR_OBJECT_ID = s:object_id";
    	}
    	$this->prepare($sql)->exec(array('group_id' => $group_id, 'path' => $path, 'object_id' => $object_id, 'value' => $value));
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
     * @return unknown_type
     */
    public function getRights($group_id, $app_id = false) 
    {
    	$rights = array();
    	// Get groups rights 
    	$sql = "SELECT * FROM UG_ACCESSRIGHTS 
    			WHERE AR_ID = i:group_id AND
    				  AR_VALUE > 0" . ($app_id ? " AND AR_PATH LIKE '/ROOT/" . $app_id . "/%'" : "");
    	$data = $this->prepare($sql)->query(array('group_id' => $group_id));
        foreach ($data as $row) {
        	// "/ROOT/APP_ID/SECTION_ID"
    		$path = explode("/", $row['AR_PATH']);
   			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][0] = (int) $row['AR_VALUE'];
   			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][1] = (int) $row['AR_VALUE'];
   			$rights[$path[2]][$path[3]][$row['AR_OBJECT_ID']][2] = 0;    		
    	}
    	if ($app_id) {    	
    		return isset($rights[$app_id]) ? $rights[$app_id] : array(); 
    	} else {
    		return $rights;
    	}
    }
    
    
    public function getGroups($app_id, $section, $object_id)
    {
        $path = '/ROOT/'.$app_id.'/'.$section;
        $sql = "SELECT AR_ID, AR_VALUE FROM ".$this->table."
        		WHERE AR_PATH = s:path AND AR_OBJECT_ID = s:object_id";
        return $this->prepare($sql)->query(array('path' => $path, 'object_id' => $object_id))->fetchAll('AR_ID', true);
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
}
?>