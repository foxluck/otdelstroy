<?php

class DiskQuotaManager extends DbModel 
{
	protected $table = 'DISK_USAGE';
	
	public function getSpaceByUsers($app_id)
	{
		$sql = "SELECT U.U_ID, U.C_ID, DU.DU_SIZE USED, UDQ.UDQ_SIZE QUOTA
				FROM WBS_USER U JOIN
					 DISK_USAGE DU ON U.U_ID = D.DU_USER_ID JOIN
					 USER_DISK_QUOTA UDQ U.U_ID = UDQ_USER_ID
				WHERE DU.DU_APP_ID = s:app_id AND UDQ.UDQ_APP_ID = s:app_id";
		$spaces = $this->prepare($sql)->query(array('app_id' => $app_id))->fetchAll('C_ID');
		$contacts_model = new ContactsModel();
		$contacts = $contacts_model->getByIds(array_keys($spaces));
		Contact::useStore(false);
		foreach ($spaces as $contact_id => &$user_stat) {
			$user_stat['NAME'] = Contact::getName($contact_id, false, $contacts[$contact_id]);
			$user_stat['PERCENT'] = $user_stat['USED'] / ($user_stat['QUOTA'] * 1024) * 100; 
			$user_stat['USED'] = FilesFunctions::getSizeStr($user_stat['USED']);
			$user_stat['QUOTA'] = FilesFunctions::getSizeStr($user_stat['QUOTA'] * 1024);
		}
		Contact::useStore(true);
		return $spaces;
	}
	
	/**
	 * Adds disk usage record to the object cache
	 *
	 * @param string $U_ID - user identifier
	 * @param string $APP_ID - application identifier
	 * @param int $Size - file size in bytes
	 */
	function addDiskUsageRecord( $U_ID, $APP_ID, $size )
	{
		$sql = "INSERT DISK_USAGE 
				SET DU_SIZE = i:size, DU_USER_ID = s:u_id, DU_APP_ID=s:app_id 
				ON DUPLICATE KEY UPDATE DU_SIZE = DU_SIZE + i:size";
		return $this->prepare($sql)->exec(array(
			 	'size' => $size,
			 	'u_id' => $U_ID,
			 	'app_id' => $APP_ID
		));
	}
	/**
	 * Delets disk usage record to the object cache
	 *
	 * @param string $U_ID - user identifier
	 * @param string $APP_ID - application identifier
	 * @param int $Size - file size in bytes
	 */
	function deleteDiskUsageRecord( $U_ID, $APP_ID, $size )
	{
		$sql = "INSERT DISK_USAGE 
				SET DU_SIZE = i:size, DU_USER_ID = s:u_id, DU_APP_ID=s:app_id 
				ON DUPLICATE KEY UPDATE DU_SIZE = DU_SIZE - i:size";
		$this->prepare($sql)->exec(array(
			 	'size' => $size,
			 	'u_id' => $U_ID,
			 	'app_id' => $APP_ID
			 ));
	}
		
	/**
	 * Returns a list of applications quotes
	 *
	 * @param string $U_ID - user identifier
	 * @return array
	 */
	public function listUserApplicationsQuotes($U_ID)
	{
		$sql = "SELECT * FROM USER_DISK_QUOTA WHERE UDQ_USER_ID = s:u_id";
		$rows = $this->prepare($sql)->query(array('u_id' => $U_ID))->fetchAll();
		$result = array();
		foreach ( $rows as $row )
			$result[$row['UDQ_APP_ID']] = $row['UDQ_SIZE'];
			
		return $result;
	}
	
	/**
	 * Sets user applications quotas
	 *
	 * @param string $U_ID - user identifier
	 * @param int $quotas - user quotas as array
	 */
	public function setUserApplicationsQuotes( $U_ID, $quotas)
	{
		foreach ( $quotas as $APP_ID=>$quota ) {
			if ( strlen($quota) )
				$this->prepare('REPLACE INTO USER_DISK_QUOTA SET UDQ_USER_ID = s:u_u_id, UDQ_APP_ID = s:u_ap_id, UDQ_SIZE = i:u_s')
					 ->exec(array( 
					 	'u_u_id' => $U_ID,
					 	'u_ap_id' => $APP_ID,
					 	'u_s' => $quota
					 ));
			else
				$this->prepare('DELETE FROM USER_DISK_QUOTA WHERE UDQ_USER_ID = s:udq_user_id AND UDQ_APP_ID = s:udq_app_id')
					 ->exec(array( 'udq_user_id' => $U_ID, 'udq_app_id' => $APP_ID ));
		}
	}
	
	/**
	 * Returns the application/user disk usage quota value in bytes
	 *
	 * @param string $APP_ID - application identifier
	 * @param string $U_ID - user identifier
	 * @return int
	 */
	public function getUserApplicationQuota( $APP_ID, $U_ID )
	{
		$sql = "SELECT UDQ_SIZE FROM USER_DISK_QUOTA WHERE UDQ_USER_ID = s:upq_user_id AND UDQ_APP_ID = s:upq_app_id";
		$quota = $this->prepare($sql)->query(array( 'upq_user_id' => $U_ID, 'upq_app_id' => $APP_ID ))->fetchField('UDQ_SIZE');
		$quota = intval($quota)*1024;
		return $quota;		
	}
	
	/**
	 * Cheks if user application disk space quota is exceeded
	 *
	 * @param int $usedSpace - amount of currently used space
	 * @param string $U_ID - user identifier
	 * @param string $APP_ID - application identifier
	 * @return Boolean
	 */
	public function userApplicationQuotaExceeded( $usedSpace, $U_ID, $APP_ID )
	{
		$userQuota = $this->getUserApplicationQuota( $APP_ID, $U_ID );

		if ( !strlen($userQuota) )
			return false;

		return $usedSpace > $userQuota;		
	}
	
	//TODO: problem portable getApplicationResourceLimits
	public function systemQuotaExceeded( $usedSpace )
	{
		return true;
	}

	/**
	 * Returns amount of disk space used by user in a specified application
	 *
	 * @param string $U_ID - user identifier
	 * @param string $APP_ID - application identifier
	 * @return int
	 */
	public function getUserApplicationUsedSpace( $U_ID, $APP_ID )
	{
		$sql = "SELECT DU_SIZE FROM DISK_USAGE WHERE DU_USER_ID = s:du_user_id AND DU_APP_ID = s:du_app_id";
		$size = $this->prepare($sql)->query(array( 'du_user_id' => $U_ID, 'du_app_id' => $APP_ID ))->fetchField('DU_SIZE');
		
		if ( !strlen($size) )
			$size = 0;			

		return $size;		
	}	
	
	/**
	 * @param string $U_ID
	 * @param string $APP_ID
	 * @return int
	 */
	public function getUserApplicationFreeSpace( $U_ID, $APP_ID )
	{
		$quota = $this->getUserApplicationQuota( $APP_ID, $U_ID );
		$used = $this->getUserApplicationUsedSpace( $U_ID, $APP_ID );
		return ($quota>0)?($quota - $used):0;
	}
	
	/**
	 * Returns a total amount of used space
	 *
	 * @param Boolean $addDatabaseSize - add the database size to the files size
	 * @return int
	 */
	public function getUsedSpaceTotal( $addDatabaseSize = false )
	{
		$size = $this->query("SELECT SUM(DU_SIZE) AS SIZE FROM DISK_USAGE")->fetchField('SIZE');

		if ( !strlen($size) )
			$size = 0;

		if ( $addDatabaseSize ) {
			$databaseSize = getDatabaseSize();

			$size += $databaseSize;
		}

		return $size;
	}	
	
	/**
	 * Returns database size
	 *
	 * @return int - database size, in bytes
	 */
	private function getDatabaseSize()
	{
		$rows = $this->query('SHOW TABLE STATUS')->fetchAll();

		$result = 0;
		foreach ($rows as $row) {
			$result += $row['Data_length'] + $row['Index_length'] + $row['Data_free'] + 8900;
		}

		return $result;
	}	
	
	
	/**
	 * Returns an application resource limitations in accordancewith a billing plan.
	 * 
	 * @param string $APP_ID application identifier
	 * @param string [optional]$resource specifies the resource name
	 * @param string [optional]$plan specifies the resource name
	 * 
	 * @return int
	 */
	function getApplicationResourceLimits($APP_ID, $resource = false , $plan = false)
	{		
		if (!Wbs::isHosted()) {
			return 0;
		}
		$info = Wbs::getDbkeyObj()->loadHostedAppsData();
		include(WBS_ROOT_PATH . "/kernel/hosting_plans.php");
		
		$value = 0;
		 
		// Get setting for CUSTOM plan from DB XML
		if ( isset($info[$APP_ID]['settings'][$APP_ID])) {	
			$value = $info[$APP_ID]['settings'][$APP_ID];
		} elseif (isset($info[$APP_ID]['settings'][$resource]) ) {
			$value = $info[$APP_ID]['settings'][$resource];
		}		
		if (!empty($value) && ($plan == HOST_CUSTOM_PLAN || !$plan)) {
			$value = ($value == -1) ? 0 : $value;
			return $value;
		}		
		if(!$plan && !($plan = $info[$APP_ID]['plan'])) {
			return 0;
		}
			
		// Limit only for free plan	
		// 
   		if ($APP_ID != 'AA' && $plan == HOST_DEFAULT_PLAN && $plan != HOST_CUSTOM_PLAN)	{
			if (isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID])) {
				$limit = $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID];
				if (isset($mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource]) && $resource != null) {
					$value =  $mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource];
				} else {
					$value = $limit;
				}
			}
			$value = ($value == -1) ? null : $value;
			return $value;
		}
 
		// If may be this custom plan or old user
		if ($APP_ID != 'AA' && ($plan == HOST_CUSTOM_PLAN || $plan == HOST_OLD_CUSTOM_PLAN)) {
			if ( isset($info[$APP_ID]['type']) &&  $info[$APP_ID]['type'] = 'APP') {
				// for old user 
				$value = 0;
			} else {
				// for custom plan
		 		$planValue = $mt_hosting_plan_settings[HOST_CUSTOM_PLAN][$APP_ID];
		 		$planValue = $resource != null ? $planValue[$resource] : $planValue;
		 		// have not restriction
		 		
		 		if ($planValue == -1 && isset($mt_hosting_plan_settings[HOST_CUSTOM_PLAN][$APP_ID])) {
		 			$value = -1;
		 		} 
				if ( !is_array($planValue))	{					
					if (empty($planValue)) {
						if ( isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID]) ) {
							$value =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID];
						}
					} else {
						$value =  $planValue;
					}
					
				} elseif ( is_array($planValue)) {
					$value = $planValue[$APP_ID];
				}
			}
 
			if ( isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID]) ) {
				$value =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID];
			}
			$value = ($value == -1) ? 0 : $value;
			return $value;
		}
		
		// System settings 
		//
		if ($APP_ID == 'AA' && !is_null($resource)) {
 
 			if($resource == 'SPACE') {
 				$resource = HOST_DBSIZE_LIMIT;  
 			}	
 			if($resource == 'USERS') {
 				$resource = HOST_MAXUSERCOUNT; 
 			}
			$settingName = $resource;
			$paramValue = Wbs::getDbkeyObj()->getSetting($settingName);
			// for old billing 
			//
			if ( $plan == HOST_DEFAULT_PLAN ) {
 				$value =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName];	
			} elseif ( $plan == HOST_CUSTOM_PLAN && !empty($paramValue)) {
				$value =  $paramValue;	
			}
			elseif ($plan == HOST_CUSTOM_PLAN && empty($paramValue)) {
				if (isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName])) {
					$value =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName];
				} else {
					$value = 0;
				}
			} else {
				// for other plans
				if ( isset($mt_hosting_plan_settings[$plan][$APP_ID][$settingName])) {
					$value =  $mt_hosting_plan_settings[$plan][$APP_ID][$settingName];
				} else {
					$value =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName];
				}
			}

			$value = ($value == -1) ? 0 : $value;
			return $value;
		}
		
		if ($resource && isset($mt_hosting_plan_settings[$plan][$APP_ID])) {
			$limit = $mt_hosting_plan_settings[$plan][$APP_ID];
			if(isset($mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource])) {
				$value =  $mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource];
			}
		} elseif (isset($mt_hosting_plan_settings[$plan][$APP_ID])) {
			$value =  $mt_hosting_plan_settings[$plan][$APP_ID];
		}	
		$value = ($value == -1) ? 0 : $value;
		return $value;
	}

}
?>