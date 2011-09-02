<?php

	//
	// Quotas classes
	//

	class DiskQuotaManager
	//
	// Manages the disk usage quotas
	//
	{
		var $_userQuota = null;
		var $_userQuotaLoaded = false;
		var $_spaceAdded = null;

		var $_diskUsageCached = null;
		var $_databaseSizeCached = null;

		var $_sizeLimit = false;

		function DiskQuotaManager()
		{
			$this->_spaceAdded = 0;
			$this->_diskUsageCached = array();
		}

		function AddDiskUsageRecord( $U_ID, $APP_ID, $Size )
		//
		// Adds disk usage record to the object cache
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$APP_ID - application identifier
		//			$Size - file size in bytes
		//
		//		Returns null
		//
		{
			if ( !array_key_exists($APP_ID, $this->_diskUsageCached) )
				$this->_diskUsageCached[$APP_ID] = array( $U_ID=>0 );

			if ( !array_key_exists($U_ID, $this->_diskUsageCached[$APP_ID]) )
				$this->_diskUsageCached[$APP_ID][$U_ID] = 0;

			$this->_spaceAdded += $Size;
			$this->_diskUsageCached[$APP_ID][$U_ID] += $Size;
		}

		function GetSpaceUsageAdded()
		//
		// Returns a space usage added from last flush operation
		//
		{
			return $this->_spaceAdded;
		}

		function Flush( &$kernelStrings )
		//
		// Flushes disk usage changes to database
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null
		//
		{
			// Update the application/user record
			//
			if ( count($this->_diskUsageCached) > 0 ) {
				foreach ( $this->_diskUsageCached as $APP_ID=>$userRecords ) {
					foreach ( $userRecords as $U_ID=>$size ) {

						if ( $size != 0 ) {
							$this->_ensureAppUserRecord( $APP_ID, $U_ID, $kernelStrings );
							$this->_updateAppUserRecord( $APP_ID, $U_ID, $size, $kernelStrings );
						}
					}
				}
			}

			$this->_diskUsageCached = array();
			$this->_spaceAdded = 0;
		}

		function _ensureAppUserRecord( $APP_ID, $U_ID, $kernelStrings )
		//
		// Creates the application/user record in the disk usage table, if it is not exists
		//
		{
			global $qr_select_disk_usage_record_count;
			global $qr_insert_disk_usage_record;

			$params = array();
			$params['U_ID'] = $U_ID;
			$params['APP_ID'] = $APP_ID;

			$count = db_query_result( $qr_select_disk_usage_record_count, DB_FIRST, $params );
			if ( PEAR::isError($count) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !$count )
				db_query( $qr_insert_disk_usage_record, $params );
		}

		function _updateAppUserRecord( $APP_ID, $U_ID, $size, $kernelStrings )
		//
		// Updates the application/user record in the disk usage table
		//
		{
			global $qr_update_disk_usage_record;

			$params = array();
			$params['U_ID'] = $U_ID;
			$params['APP_ID'] = $APP_ID;
			$params['SIZE'] = $size;

			db_query( $qr_update_disk_usage_record, $params );
		}

		function ListQuotableApplications()
		//
		// Returns a list of quotable applications registration data
		//
		//		Returns array
		//
		{
			global $global_applications;

			$result = array();

			foreach ( $global_applications as $APP_ID=>$appData )
				if ( $appData[APP_QUOTABLE] )
					$result[$APP_ID] = $appData;

			$result = sortApplicationList( $result );

			return $result;
		}

		function ListUserApplicationsQuotes( $U_ID, &$KernelStrings )
		//
		// Returns a list of applications quotes
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns array or PEAR_Error
		//
		{
			global $qr_list_user_disk_quotas;

			$qr = db_query( $qr_list_user_disk_quotas, array('U_ID'=>$U_ID) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr) )
				$result[$row['UDQ_APP_ID']] = $row['UDQ_SIZE'];

			db_free_result($qr);

			return $result;
		}

		function ValidateUserApplicationQuotes( $U_ID, $quotas, &$kernelStrings )
		//
		// Validates user applicatoin quotes
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$quotas - user quotas as array
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;

			foreach ( $quotas as $APP_ID=>$Quota ) {
				$QuotaObj = new UserAppDiskQuota();

				$QuotaParams = array();
				$QuotaParams['UDQ_USER_ID'] = $U_ID;
				$QuotaParams['UDQ_APP_ID'] = $APP_ID;
				$QuotaParams['UDQ_SIZE'] = $Quota;

				$res = $QuotaObj->loadFromArray( $QuotaParams, $kernelStrings, true, array(s_datasource=>s_form) );
				if ( PEAR::isError($res) ) {
					return PEAR::raiseError(
							$res->getMessage(),
							ERRCODE_APPLICATION_ERR,
							$_PEAR_default_error_mode,
							$_PEAR_default_error_options,
							"diskquotas[$APP_ID]"
						);
				}
			}
		}

		function SetUserApplicationsQuotes( $U_ID, $quotas, &$kernelStrings )
		//
		// Sets user applications quotas
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$quotas - user quotas as array
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_insert_user_app_disk_quota;
			global $qr_delete_user_app_disk_quota;

			foreach ( $quotas as $APP_ID=>$Quota ) {
				$params = array();
				$params['UDQ_USER_ID'] = $U_ID;
				$params['UDQ_APP_ID'] = $APP_ID;
				$params['UDQ_SIZE'] = $Quota;

				if ( strlen($Quota) )
					db_query( $qr_insert_user_app_disk_quota, $params );
				else
					db_query( $qr_delete_user_app_disk_quota, $params );
			}
		}

		function GetUserApplicationQuota( $APP_ID, $U_ID, &$kernelStrings )
		//
		// Returns the application/user disk usage quota value in bytes
		//
		//		Parameters:
		//			$APP_ID - application identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_select_user_app_quota;

			$params = array();
			$params['UDQ_USER_ID'] = $U_ID;
			$params['UDQ_APP_ID'] = $APP_ID;

			$quota = db_query_result( $qr_select_user_app_quota, DB_FIRST, $params );
			if ( PEAR::isError($quota) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( strlen($quota) )
				$quota *= 1024;

			return $quota;
		}

		function UserApplicationQuotaExceeded( $usedSpace, $U_ID, $APP_ID, &$kernelStrings )
		//
		// Cheks if user application disk space quota is exceeded
		//
		//		Parameters:
		//			$usedSpace - amount of currently used space
		//			$U_ID - user identifier
		//			$APP_ID - application identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns boolean or PEAR_Error
		//
		{
			global $qr_select_user_app_quota;

			if ( !$this->_userQuotaLoaded ) {
				$this->_userQuota = $this->GetUserApplicationQuota( $APP_ID, $U_ID, $kernelStrings );
				$this->_userQuotaLoaded = true;
			}

			if ( !strlen($this->_userQuota) )
				return false;

			return $usedSpace > $this->_userQuota;
		}

		function SystemQuotaExceeded( $usedSpace )
		//
		// Cheks if system disk space quota is exceeded
		//
		//		Parameters:
		//			$usedSpace - amount of currently used space
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns boolean or PEAR_Error
		//
		{
			if ( $this->_sizeLimit === false )
				$this->_sizeLimit = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );

			if ( $this->_sizeLimit !== null )
				return $usedSpace/MEGABYTE_SIZE > $this->_sizeLimit;

			if ( DATABASE_SIZE_LIMIT == 0 )
				return false;

			return $usedSpace/MEGABYTE_SIZE > DATABASE_SIZE_LIMIT;
		}

		function ThrowNoSpaceError( &$kernelStrings )
		//
		// Returns the PEAR_Error object, informing about the exceeded quota.
		//
		//		Parameters:
		//			$usedSpace - amount of currently used space
		//			$kernelStrings - Kernel localization strings
		//
		//		Return PEAR_Error
		//
		{
			global $databaseInfo;
			global $currentUser;

			$settingValue = $databaseInfo[HOST_DBSETTINGS][HOST_DBSIZE_LIMIT];

			if ( $settingValue == 'FREE' || onWebAsystServer() )
			{
				if ( $this->_sizeLimit === false )
					$this->_sizeLimit = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );

				if ( hasAccountInfoAccess($currentUser) )
					return PEAR::raiseError( sprintf($kernelStrings['app_spacelimit_message'], $this->_sizeLimit)." ".getUpgradeLink($kernelStrings), ERRCODE_APPLICATION_ERR );
				else
					return PEAR::raiseError( sprintf($kernelStrings['app_spacelimit_message'], $this->_sizeLimit)." ".$kernelStrings['app_referadmin_message'], ERRCODE_APPLICATION_ERR );
			}
				else
					return PEAR::raiseError( $kernelStrings['app_dbsizelimit_message'], ERRCODE_APPLICATION_ERR );
		}

		function GetUserApplicationUsedSpace( $U_ID, $APP_ID, &$kernelStrings )
		//
		// Returns amount of disk space used by user in a specified application
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$APP_ID - application identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_select_disk_usage_record;

			$params = array();
			$params['U_ID'] = $U_ID;
			$params['APP_ID'] = $APP_ID;

			$size = db_query_result( $qr_select_disk_usage_record, DB_FIRST, $params );
			if ( PEAR::isError($size) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !strlen($size) )
				$size = 0;

			return $size;
		}

		function GetUsedSpaceTotal( &$kernelStrings, $addDatabaseSize = false )
		//
		// Returns a total amount of used space
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//			$addDatabaseSize - add the database size to the files size
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_select_disk_usage_total;

			$size = db_query_result( $qr_select_disk_usage_total, DB_FIRST, array() );
			if ( PEAR::isError($size) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !strlen($size) )
				$size = 0;

			if ( $addDatabaseSize ) {
				if ( is_null($this->_databaseSizeCached) )
					$this->_databaseSizeCached = getDatabaseSize();

				$size += $this->_databaseSizeCached;
			}

			return $size;
		}

		function GetAvailableSystemSpace( &$kernelStrings )
		//
		// Returns the available system disk space. If the account has no disk space limit, returns null
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			if ( $this->_sizeLimit === false )
				$this->_sizeLimit = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );

			$result = null;

			if ( $this->_sizeLimit !== null )
			{
				$result = $this->_sizeLimit*MEGABYTE_SIZE - $this->GetUsedSpaceTotal( $kernelStrings );
			} else
			{
				if ( DATABASE_SIZE_LIMIT == 0 )
					return null;

				$result = DATABASE_SIZE_LIMIT*MEGABYTE_SIZE - $this->GetUsedSpaceTotal( $kernelStrings );
			}

			if ( $result < 0 )
				$result = 0;

			return $result;
		}

		function GetAvailableUserSpace( $U_ID, $APP_ID, &$kernelStrings )
		//
		// Returns the availbale user disk space for the specified application. Returns null if user has no quota.
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$APP_ID - application identifer
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			$userQuota = $this->GetUserApplicationQuota( $APP_ID, $U_ID, $kernelStrings );

			if ( !strlen($userQuota) )
				return null;

			$result = $userQuota - $this->GetUserApplicationUsedSpace( $U_ID, $APP_ID, $kernelStrings );

			if ( $result < 0 )
				$result = 0;

			return $result;
		}

		function GetAvailableSpace( $U_ID, $APP_ID, &$kernelStrings )
		//
		// Returns the disk space available for storing files,
		// taking into account the system quota and the user/application quota.
		// Returns null if there are no system and user/application quotas.
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$APP_ID - application identifer
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			$SystemSpace = $this->GetAvailableSystemSpace( $kernelStrings );
			$UserSpace = $this->GetAvailableUserSpace( $U_ID, $APP_ID, $kernelStrings );

			if ( is_null($SystemSpace) && is_null($UserSpace) )
				return null;

			if ( !is_null($SystemSpace) && !is_null($UserSpace) )
				return min($SystemSpace, $UserSpace);

			if ( !is_null($SystemSpace) )
				return $SystemSpace;

			return $UserSpace;
		}
	}

	class UserAppDiskQuota extends arrayAdaptedClass
	//
	// Represents the per-application user disk quota
	//
	{
		var $UDQ_USER_ID;
		var $UDQ_APP_ID;
		var $UDQ_SIZE;

		function UserAppDiskQuota()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'UDQ_USER_ID', t_string, true, 20 );
			$this->dataDescrition->addFieldDescription( 'UDQ_APP_ID', t_string, true, 10 );
			$this->dataDescrition->addFieldDescription( 'UDQ_SIZE', t_integer, false );
		}
	}

?>