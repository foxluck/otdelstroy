<?php

	class cm_groupClass extends genericDocumentFolderTree
	{
		function cm_groupClass( &$descriptor )
		{
			$this->folderDescriptor = $descriptor->folderDescriptor;
			$this->documentDescriptor = $descriptor->documentDescriptor;

			$this->globalPrefix = "CM";
		}

		function addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback = null, $callbackParams = null, $propagateFolderRights = true, $suppressNotifications = false )
		//
		// Adds/modifies folder
		//
		//		Parameters:
		//			$action - form mode - new/edit
		//			$U_ID - user identidier
		//			$ID_PARENT - parent folder indentifier
		//			$folderData - array with folder information
		//			$kernelStrings - Kernel localization strings
		//			$admin - true if user is administrator
		//			$createCallback - callback function to execute after folder creation
		//			$callbackParams - parameters array to pass to create callback
		//			$propagateFolderRights - copy folder rights from parent folder
		//			$suppressNotifications - don't send any notifications
		//
		//		Returns folder identifier or PEAR_Error
		//
		{
			global $qr_setFolderType;

			$ID = parent::addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback, $callbackParams, $propagateFolderRights, $suppressNotifications );
			if ( PEAR::isError($ID) )
				return $ID;

			if ( $action == ACTION_NEW ) {
				// Set folder type for new folder
				//
				$params = array();

				$params['CT_ID'] = CONTACT_BASIC_TYPE;
				$params['CF_ID'] = $ID;

				$res = db_query( $qr_setFolderType, $params );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return $ID;
		}

		function listFolderDocuments( $ID, $U_ID, $sortStr, $kernelStrings, $entryProcessor = null, $ignoreUsers = false, $limitStart = null, $limitCount = null, $folderAccessLevel = null )
		//
		// Returns list of documents in specified folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$sortStr - sorting string
		//			$kernelStrings - Kernel localization strings
		//			$entryProcessor - callback function to process document entry. Can be null
		//			$ignoreUsers - list all documents in spite of users rights
		//			$limitStart, $limitCount - limit query result by this interval
		//			$folderAccessLevel - user access level for this folder. This value will be passed to document objects
		//
		//		Returns array of object representing document table rows
		//
		{
			global $qr_cm_selectFolderContacts;
			global $qr_limit_clause;

			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_document_id_field = $this->documentDescriptor->document_id_field;
			$_document_status_field = $this->documentDescriptor->document_status_field;
			$_user_id_field = $this->accessDescriptor->user_id_field;
			$_document_modifyuid_field = $this->documentDescriptor->document_modifyuid_field;
			$_access_right_field = $this->accessDescriptor->access_right_field;

			$limitStr = is_null($limitStart) ? '' : sprintf( $qr_limit_clause, $limitStart, $limitCount );

			$sql = $qr_cm_selectFolderContacts;

			$status = TREE_DLSTATUS_NORMAL;

			$sql = sprintf( $sql, $sortStr, $limitStr );

			$qr = db_query( $sql, array( $_folder_id_field=>$ID, $_document_status_field=>$status, $_user_id_field=>$U_ID, $_document_modifyuid_field=>$U_ID ) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				if ( !is_null($folderAccessLevel) )
					$row->$_access_right_field = $folderAccessLevel;

				if ( is_null( $entryProcessor ) )
					$result[$row->$_document_id_field] = $row;
				else
					$result[$row->$_document_id_field] = call_user_func( $entryProcessor, $row );
			}

			db_free_result( $qr );

			return $result;
		}

		function folderDocumentCount( $ID, $U_ID, $kernelStrings )
		//
		// Returns document count in folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_cm_selectfolderdocnum;

			$_document_status_field = $this->documentDescriptor->document_status_field;
			$_document_modifyuid_field = $this->documentDescriptor->document_modifyuid_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;

			if ( $ID != TREE_RECYCLED_FOLDER ) {
				$params = array( 'CF_ID'=>$ID );
				$result = db_query_result( $qr_cm_selectfolderdocnum, DB_FIRST, $params );

				if ( PEAR::isError($result) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			} else
				$result = 0;

			return $result;
		}

		function copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation = null, $callbackParams = null, $perFileCheck = true, $checkUserRights = true, $onFinishOperation = null, $suppressNotifications = false )
		{
			global $_cmQuotaManager;

			$_cmQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_cmQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation, $callbackParams, $perFileCheck, $checkUserRights, $onFinishOperation, $suppressNotifications );

			$_cmQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null,
			$onFolderDelete = null, $callbackParams = null, $onFinishMove = null, $checkUserRights = true,
			$topLevel = true, $accessInheritance = ACCESSINHERITANCE_COPY, $mostTopRightsSource = null,
			$folderStatus = TREE_FSTATUS_NORMAL, $plainMove = false, $checkFolderName = true )
		{
			global $_cmQuotaManager;

			$_cmQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_cmQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation , $onFolderCreate,
			$onFolderDelete, $callbackParams, $onFinishMove, $checkUserRights,
			$topLevel, $accessInheritance, $mostTopRightsSource,
			$folderStatus, $plainMove, $checkFolderName );

			$_cmQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null, $checkFolderName = true, $copyChilds = true )
		{
			global $_cmQuotaManager;

			$_cmQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_cmQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $onFininshCopy, $accessInheritance, $onBeforeFolderCreate, $checkFolderName, $copyChilds, !isAdministratorID($U_ID) );

			$_cmQuotaManager->Flush( $kernelStrings );

			return $res;
		}
	}
?>