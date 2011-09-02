<?php

/**
 * DU_SIZE in bytes
 * 
 * @author WebAsyst Team
 *
 */
class DiskUsageModel extends DbModel
{
	protected $table = 'DISK_USAGE';
	
	/**
	 * Adds disk usage record to the object cache
	 *
	 * @param string $user_id - user identifier
	 * @param string $app_id - application identifier
	 * @param int $size - file size in bytes
	 */
	public function add( $user_id, $app_id, $size )
	{
		$sql = "INSERT DISK_USAGE 
				SET DU_SIZE = i:size, DU_USER_ID = s:user_id, DU_APP_ID=s:app_id 
				ON DUPLICATE KEY UPDATE DU_SIZE = DU_SIZE + i:size";
		return $this->prepare($sql)->exec(array(
			 	'size' => $size,
			 	'user_id' => $user_id,
			 	'app_id' => $app_id
		));
	}	
	
	
	/**
	 * Delets disk usage record to the object cache
	 *
	 * @param string $U_ID - user identifier
	 * @param string $APP_ID - application identifier
	 * @param int $Size - file size in bytes
	 */
	public function delete( $user_id, $app_id, $size )
	{
		$sql = "UPDATE DISK_USAGE 
				SET DU_SIZE = DU_SIZE - i:size 
				WHERE DU_USER_ID = s:user_id AND DU_APP_ID=s:app_id";
		$this->prepare($sql)->exec(array(
			 	'size' => $size,
			 	'user_id' => $user_id,
			 	'app_id' => $app_id
			 ));
	}	
	

	/**
	 * Returns amount of disk space used by user in a specified application
	 *
	 * @param string $user_id - user identifier
	 * @param string $app_id - application identifier
	 * @return int - summary size in bytes
	 */	
	public function get($user_id, $app_id)
	{
		$sql = "SELECT DU_SIZE FROM DISK_USAGE WHERE DU_USER_ID = s:user_id AND DU_APP_ID = s:app_id";
		return $this->prepare($sql)->query(array('user_id' => $user_id, 'app_id' => $app_id))->fetchField('DU_SIZE');
	}
	
	/**
	 * Return amount of the disk used by all users in whole account or in a specified application
	 * 
	 * @param $app_id
	 * @return int - summary size in bytes
	 */
	public function getAll($app_id = false)
	{
		$sql = "SELECT SUM(DU_SIZE) size 
				FROM ".$this->table;
		if ($app_id) {
			$sql .= " WHERE DU_APP_ID = s:app_id";
		}	
		return $this->prepare($sql)->query(array('app_id' => $app_id))->fetchField('size');
	}
}
?>