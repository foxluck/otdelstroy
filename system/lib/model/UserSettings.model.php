<?php
class UserSettingsModel extends DbModel
{
	protected $table = 'USER_SETTINGS';
	
	public function set($user_id, $app_id, $name, $value)
	{
		$sql = "REPLACE INTO USER_SETTINGS 
				SET U_ID = s:user_id, APP_ID = s:app_id, name = s:name, value = s:value";
		return $this->prepare($sql)->exec(array(
			'user_id' => $user_id,
			'app_id' => $app_id,
			'name' => $name,
			'value' => $value
		));
	}
	
	public function get($user_id, $app_id, $name)
	{
		$sql = "SELECT VALUE FROM ".$this->table." WHERE U_ID = s:user_id AND APP_ID = s:app_id AND name = s:name";
		return $this->prepare($sql)->query(array(
			'user_id' => $user_id,
			'app_id' => $app_id,
			'name' => $name
		))->fetchField('VALUE');
	}
	
	public function getMax($user_id, $name) 
	{
		$sql = "SELECT MAX(VALUE) m FROM ".$this->table." WHERE U_ID = s:user_id AND NAME = s:name";
		return $this->prepare($sql)->query(array('user_id' => $user_id, 'name' => $name))->fetchField('m');	
	}
	
	public function getAll($user_id, $app_id)
	{
		$sql = "SELECT NAME, VALUE FROM ".$this->table." WHERE (U_ID = s:user_id OR U_ID = '') AND APP_ID = s:app_id ORDER BY U_ID";
		$data = $this->prepare($sql)->query(array('user_id' => $user_id, 'app_id' => $app_id));
		$settings = array();
		foreach ($data as $row) {
			$settings[$row['NAME']] = $row['VALUE'];
		}
		return $settings;
	}
	
	/** 
	 * Delete records
	 * 
	 * @param $user_id 
	 * @param $app_id
	 * @param $name
	 * @return bool
	 */
	public function delete($user_id, $app_id = false, $name = false)
	{
		$sql = "DELETE FROM ".$this->table." 
				WHERE U_ID = s:user_id";
		if ($app_id) {
			$sql .= " AND APP_ID = s:app_id";
		} 	
		if ($name) {
			$sql .= " AND NAME = s:name";
		}
		
		return $this->prepare($sql)->exec(array('user_id' => $user_id, 'app_id' => $app_id, 'name' => $name));
	}
	
	public function deleteAll($user_id, $app_id, $name) 
	{
	    $name .= "%";
	    $sql = "DELETE FROM ".$this->table." WHERE U_ID = s:user_id AND APP_ID = s:app_id AND NAME LIKE s:name";
	    return $this->prepare($sql)->exec(array('user_id' => $user_id, 'app_id' => $app_id, 'name' => $name));    
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