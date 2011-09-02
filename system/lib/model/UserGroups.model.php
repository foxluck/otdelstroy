<?php

class UserGroupsModel extends DbModel
{
	protected $table = "UGROUP_USER";

	public function getGroups($user_id)
	{
       	$sql = "SELECT G.UG_ID, G.UG_NAME 
       			FROM UGROUP G JOIN 
        			 UGROUP_USER UG ON G.UG_ID = UG.UG_ID
        		WHERE UG.U_ID = s:user_id";
        $data = $this->prepare($sql)->query(array("user_id" => $user_id));
        $result = array();
        foreach ($data as $row) {
        	$result[$row['UG_ID']] = $row['UG_NAME'];
        }
        return $result;
	}
	
	
	public function getUsers($group_id, $sort = false, $limit = false)
	{
		$sql = "SELECT U.U_ID, U.U_STATUS, C.* 
				FROM ".$this->table." UG JOIN
					 WBS_USER U ON UG.U_ID = U.U_ID JOIN  
					 CONTACT C ON U.C_ID = C.C_ID
				WHERE UG.UG_ID = i:group_id";
	    if ($sort) {
	    	if (substr($sort, 0, 2) == 'U_') {
	    		$sort = "U.".$sort;
	    	}
        	$sql .= " ORDER BY ".$sort;
        }
        if ($limit) {
        	$sql .= " LIMIT ".$limit;
        }		
		return $this->prepare($sql)->query(array('group_id' => $group_id))->fetchAll();		
	}
	
	public function countByGroupId($group_id)
	{
	    $sql = "SELECT COUNT(*) N FROM ".$this->table." WHERE UG_ID = i:group_id";
	    return $this->prepare($sql)->query(array('group_id' => $group_id))->fetchField('N');
	}
	
	public function deleteByUserId($user_ids) 
	{		
		if (!is_array($user_ids)) {
			$user_ids = array($user_ids);
		}
		foreach ($user_ids as &$user_id) {
			$user_id = $this->escape($user_id);
		}
		$sql = "DELETE FROM ".$this->table." WHERE U_ID IN ('".implode("', '", $user_ids)."')";
		return $this->exec($sql);		
	}
	
	public function changeLogin($user_id, $login)
	{
		$sql = "UPDATE ".$this->table." SET U_ID = s:login WHERE U_ID = s:user_id";
		return $this->prepare($sql)->exec(array('user_id' => $user_id, 'login' => $login));
	}	
}

?>