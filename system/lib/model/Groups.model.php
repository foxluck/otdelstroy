<?php 

class GroupsModel extends DbModel 
{
    protected $table = "UGROUP";
    protected $id = "UG_ID";
    
    public function get($group_id)
    {
        return $this->getById($group_id);
    }
    
    public function add($name)
    {
        $sql = "INSERT INTO ".$this->table." SET UG_NAME = s:name";
        return $this->prepare($sql)->query(array('name' => $name))->lastInsertId();
    }
    
    public function delete($group_id)
    {
        // Delete binds with this group
        $sql = "DELETE FROM UGROUP_USER WHERE UG_ID = i:group_id";
        $this->prepare($sql)->exec(array('group_id' => $group_id));

        $sql = "DELETE FROM UG_ACCESSRIGHTS WHERE AR_ID = i:group_id";
        $this->prepare($sql)->exec(array('group_id' => $group_id));
        
        // Delete group
        $sql = "DELETE FROM {$this->table} WHERE UG_ID = i:group_id";
        $this->prepare($sql)->exec(array('group_id' => $group_id));
    }
    
    public function rename($group_id, $name)
    {
        $sql = "UPDATE ".$this->table." SET UG_NAME = s:name WHERE UG_ID = i:id";
        return $this->prepare($sql)->query(array('id' => $group_id, 'name' => $name))->affectedRows();
    }
   
    
    public function getAll($normalize = false)
    {
        $sql = "SELECT ".$this->id.", UG_NAME FROM ".$this->table." 
        		ORDER BY UG_NAME";
        return $this->query($sql)->fetchAll($this->id, $normalize);
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
     * Returns users of the group
     * 
     * @param $group_id
     * @param $sort
     * @param $limit
     * @return array
     */
    public function getUsers($group_id, $sort = false, $limit = false)
    {
        $sql = "SELECT U.*, C.* FROM UGROUP_USER UG JOIN
        		WBS_USER U ON UG.U_ID = U.U_ID LEFT JOIN
        		CONTACT C ON U.C_ID = C.C_ID
        		WHERE UG.UG_ID = i:group_id";
        if ($sort) {
        	$sql .= " ORDER BY ".$sort;
        }
        if ($limit) {
        	$sql .= " LIMIT ".$limit;
        }
        return $this->prepare($sql)->query(array("group_id" => $group_id))->fetchAll();
    }
    
    
    /**
     * @param $group_id
     * @return array
     */
    public function getUserIds($group_id)
    {
    	$sql = "SELECT U_ID FROM UGROUP_USER WHERE UG_ID = i:group_id";
    	$r = $this->prepare($sql)->query(array('group_id' => $group_id));
    	$user_ids = array();
    	foreach ($r as $row) {
    		$user_ids[$row['U_ID']] = $row['U_ID'];
    	}
    	return $user_ids;
    }
    
    public function countUsers($group_id)
    {
    	$sql = "SELECT count(*) NUM FROM UGROUP_USER UG WHERE UG.UG_ID = i:group_id";
    	return $this->prepare($sql)->query(array('group_id' => $group_id))->fetchField('NUM');
    }
    
    /**
     * Returns count of the users group by group
     * 
     * @return array
     */
    public function countAllUsers()
    {
        $sql = "SELECT UG_ID, count(*) NUM FROM UGROUP_USER GROUP BY UG_ID"; 
        return $this->query($sql)->fetchAll("UG_ID");
    }
    
    /**
     * Adds users to the group
     * 
     * @param $group_id
     * @param $user_ids
     * @return 
     */
    public function addUsers($group_id, $user_ids) 
    {
    	if (!$user_ids) {
    		return false;
    	}
    	if (!is_array($user_ids)) {
    		$user_ids = array($user_ids);
    	}
    	$sql = "INSERT INTO UGROUP_USER (U_ID, UG_ID) VALUES ('".implode("', i:group_id), ('", $user_ids)."', i:group_id)";
    	$this->prepare($sql)->exec(array('group_id' => $group_id));
    }
    
    
    /**
     * Deletes users from group
     * 
     * @param $user_id
     * @param $group_ids - array(UG_ID, UG_ID, ...
     * @return 
     */
    public function delUsers($group_id, $user_ids) 
    {
        if (!is_array($user_ids)) {
    		$user_ids = array($user_ids);
    	}    	
		$sql = "DELETE FROM UGROUP_USER WHERE UG_ID = i:group_id AND U_ID IN ('".implode("', '", $user_ids)."')";
		$this->prepare($sql)->exec(array('group_id' => $group_id));     	
    }
    
    
}

?>