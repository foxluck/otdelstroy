<?php

class Limits
{
	/**
	 * Hosted Apps Data
	 */
	public static $info = array();
	
	/**
	 * Returns an application resource limitations in accordancewith a billing plan.
	 * 
	 * @param string $APP_ID application identifier
	 * @param string [optional]$resource specifies the resource name
	 * @param string [optional]$plan specifies the resource name
	 * 
	 * @return int
	 */
	public static function get($APP_ID, $resource = false , $plan = false)
	{		
		if (!Wbs::isHosted()) {
			return 0;
		}
		if (!self::$info) {
			self::$info = Wbs::getDbkeyObj()->loadHostedAppsData();
		}
		include(WBS_ROOT_PATH . "/kernel/hosting_plans.php");
		
		$value = 0;
		 
		// Get setting for CUSTOM plan from DB XML
		if ( isset(self::$info[$APP_ID]['settings'][$APP_ID])) {	
			$value = self::$info[$APP_ID]['settings'][$APP_ID];
		} elseif (isset(self::$info[$APP_ID]['settings'][$resource]) ) {
			$value = self::$info[$APP_ID]['settings'][$resource];
		}		
		if (!empty($value) && ($plan == HOST_CUSTOM_PLAN || !$plan)) {
			$value = ($value == -1) ? 0 : $value;
			return $value;
		}		
		if(!$plan && !($plan = self::$info[$APP_ID]['plan'])) {
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
			if ( isset(self::$info[$APP_ID]['type']) &&  self::$info[$APP_ID]['type'] = 'APP') {
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