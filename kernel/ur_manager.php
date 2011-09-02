<?php

	define( "UR_GROUP_ID", "GROUP" );
	define( "UR_USER_ID", "USER" );

	define( "UR_PATH", "AR_PATH");
	define( "UR_OBJECTID", "AR_OBJECT_ID");
	define( "UR_ID", "AR_ID");

	define( "UR_ROOT", "ROOT" );

	define( "UR_DELIMITER", "/" );
	define( "UR_ADMINISTRATOR", "ADMINISTRATOR");

	define( "UR_VALUE", "AR_VALUE" );
	define( "UR_AUX", "AR_AUX" );
	define( "UR_GROUPRIGHTS", "GROUPRIGHTS" );
	define( "UR_VIEWGROUPVALUE", "VIEW_GROUP_VALUE" );

	define( "UR_REVOKE", 0 );
	define( "UR_GRANT", 1 );

	define( "UR_ACTION", "ACTION" );
	define( "UR_EDITED", "EDITED" );
	
	define("UR_SPECIALSTATUS", "SPECIALSTATUS");

	define( "UR_ACTION_EDITUSER", "EDITUSER" );
	define( "UR_ACTION_EDITGROUP", "EDITGROUP" );

	define( "UR_ACTION_VIEWUSER", "VIEWUSERS" );
	define( "UR_ACTION_VIEWGROUP", "VIEWGROUPS" );

	define( "UR_INCLUDED_GROUPS", "UR_INCLUDED_GROUPS" );

	define( "UR_FIELD", "FIELD" );

	define( "UR_ITEMNAME", "ITEMNAME" );
	define( "UR_SCREENS", "SCREENS" );
	define( "UR_FUNCTIONS", "FUNCTIONS" );
	define( "UR_MESSAGES", "MESSAGES" );

	define( "UR_OBJECT", "AR_OBJECT");

	define( "UR_SYS_ID", "***SYS" );
	define( "UR_REAL_ID", "REAL_ID" );
	define( "UR_COPYFROM", "COPYFROM" );

	define( "WBS_UR_APPCLASS_FILE", "user_rights.php" );

	define( "UR_NO_RIGHTS", 0 );
	define( "UR_FULL_RIGHTS", intval( 0x7FFFFFFF ) );

	define( "UR_BOOL_TRUE", 1 );
	define( "UR_BOOL_FALSE", 0 );

	define( "UR_TREE_READ", 1 );
	define( "UR_TREE_WRITE", 2 );
	define( "UR_TREE_FOLDER", 4 );

	define( "UR_TRIGGER_ON", 1 );
	define( "UR_TRIGGER_OFF", 0 );

	define( "UR_CONTAINER_OFFSET", 10 );
	
	$ur_canLinkPaths = array ("/ROOT/DD/FOLDERS");

	$qr_urm_saveUserRightValue = "INSERT INTO U_ACCESSRIGHTS ( AR_ID, AR_PATH, AR_OBJECT_ID, AR_VALUE, AR_AUX ) VALUES( '!AR_ID!', '!AR_PATH!', '!AR_OBJECT_ID!', '!AR_VALUE!', '!AR_AUX!' ) ON DUPLICATE KEY UPDATE AR_VALUE='!AR_VALUE!', AR_AUX='!AR_AUX!'";

	$qr_urm_saveGroupRightValue = "INSERT INTO UG_ACCESSRIGHTS ( AR_ID, AR_PATH, AR_OBJECT_ID, AR_VALUE, AR_AUX ) VALUES( '!AR_ID!', '!AR_PATH!', '!AR_OBJECT_ID!', '!AR_VALUE!', '!AR_AUX!' ) ON DUPLICATE KEY UPDATE AR_VALUE='!AR_VALUE!', AR_AUX='!AR_AUX!'";

	$qr_ur_getRightsLinks = "SELECT * FROM ACCESSRIGHTS_LINK";
	
	$qr_ur_getUserRightsList = "(SELECT AR_ID, AR_PATH, AR_OBJECT_ID, AR_VALUE, NULL AS GROUPRIGHTS, AR_AUX FROM U_ACCESSRIGHTS AS UA WHERE UA. AR_ID = '!AR_ID!') UNION (SELECT AR_ID, AR_PATH, AR_OBJECT_ID, NULL AS AR_VALUE, BIT_OR(AR_VALUE) AS GROUPRIGHTS, AR_AUX FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID='!AR_ID!' GROUP BY AR_PATH, AR_OBJECT_ID)";

	$qr_ur_getUserPathPatternRightsList = "(SELECT AR_ID, AR_PATH, AR_OBJECT_ID, AR_VALUE, NULL AS GROUPRIGHTS, AR_AUX FROM U_ACCESSRIGHTS AS UA WHERE UA. AR_ID = '!AR_ID!' AND UA.AR_PATH LIKE '!AR_PATH!') UNION (SELECT AR_ID, AR_PATH, AR_OBJECT_ID, NULL AS AR_VALUE, BIT_OR(AR_VALUE) AS GROUPRIGHTS, AR_AUX FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID='!AR_ID!' AND UG.AR_PATH LIKE '!AR_PATH!' GROUP BY AR_PATH, AR_OBJECT_ID)";

	$qr_urm_getUserRightValue = "SELECT (IF ((SELECT UA.AR_VALUE FROM U_ACCESSRIGHTS AS UA WHERE AR_ID='!AR_ID!' AND AR_PATH='!AR_PATH!' AND AR_OBJECT_ID='!AR_OBJECT_ID!') IS NULL, 0, (SELECT UA.AR_VALUE FROM U_ACCESSRIGHTS AS UA WHERE AR_ID='!AR_ID!' AND AR_PATH='!AR_PATH!' AND AR_OBJECT_ID='!AR_OBJECT_ID!') ) | IF ( (SELECT BIT_OR(UG.AR_VALUE) FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID='!AR_ID!' AND UG.AR_OBJECT_ID='!AR_OBJECT_ID!' AND UG.AR_PATH='!AR_PATH!' GROUP BY UGU.U_ID) IS NULL, 0, (SELECT BIT_OR(UG.AR_VALUE) FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID='!AR_ID!' AND UG.AR_OBJECT_ID='!AR_OBJECT_ID!' AND UG.AR_PATH='!AR_PATH!' GROUP BY UGU.U_ID) )) AS AR_VALUE";

	$qr_urm_getGroupRightValue = "SELECT * FROM UG_ACCESSRIGHTS WHERE AR_PATH='!AR_PATH!' AND AR_OBJECT_ID='!AR_OBJECT_ID!' AND AR_ID = '!AR_ID!'";

	$qr_ur_getUserGroupsRightsList = "SELECT UG.*, UGU.U_ID FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID='!AR_ID!'";

	$qr_ur_selectUsersRightsList = "(SELECT AR_ID, AR_PATH, AR_OBJECT_ID, AR_VALUE, NULL AS GROUPRIGHTS, AR_AUX FROM U_ACCESSRIGHTS AS UA WHERE UA.AR_ID IN ( %s ) ) UNION (SELECT UGU.U_ID, AR_PATH, AR_OBJECT_ID, NULL AS AR_VALUE, BIT_OR(AR_VALUE) AS GROUPRIGHTS, AR_AUX FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID IN ( %s ) GROUP BY UGU.U_ID, AR_PATH, AR_OBJECT_ID)";

	$qr_ur_selectGroupsRightsList = "SELECT * FROM UG_ACCESSRIGHTS WHERE AR_ID IN ( %s )";

	$qr_ur_selectUserItems = "SELECT W.U_ID, W.U_STATUS, C.*, AR.AR_PATH, AR.AR_OBJECT_ID, AR.AR_VALUE, AR.AR_AUX   
							  FROM WBS_USER AS W 
 					  	      JOIN 
							  	   CONTACT AS C ON W.C_ID = C.C_ID 
							  LEFT JOIN 
							  	   U_ACCESSRIGHTS AS AR 
							  	   ON W.U_ID = AR.AR_ID AND AR.AR_PATH='!AR_PATH!' AND AR.AR_OBJECT_ID='!AR_OBJECT_ID!'  
 							  ORDER BY C.C_FULLNAME";

	$qr_ur_selectGroupItems = "( SELECT UG.UG_ID, UG.UG_NAME, NULL AS AR_PATH, NULL AS AR_OBJECT_ID, NULL AS AR_VALUE, NULL AS AR_AUX FROM UGROUP AS UG ) UNION ( SELECT UG.UG_ID, UG.UG_NAME, AR.AR_PATH, AR.AR_OBJECT_ID, AR.AR_VALUE, AR.AR_AUX FROM UGROUP AS UG LEFT JOIN UG_ACCESSRIGHTS AS AR ON UG.UG_ID = AR.AR_ID WHERE AR.AR_PATH='!AR_PATH!' AND AR.AR_OBJECT_ID='!AR_OBJECT_ID!' ) ORDER BY  UG_NAME";

	$qr_ur_getFoldersList = "SELECT FOLDER_ID_FIELD AS FOLDER_ID, FOLDER_NAME_FIELD AS FOLDER_NAME, FOLDER_PARENT_FIELD AS FOLDER_PARENT, FOLDER_STATUS_FIELD AS FOLDER_STATUS FOLDER_SPECIALSTATUS_STR FROM TREE_FOLDER_TABLE WHERE FOLDER_STATUS_FIELD<>'-1' ORDER BY FOLDER_SPECIALSTATUSORDER_STR FOLDER_PARENT_FIELD, FOLDER_NAME_FIELD";
	
	$qr_ur_getRightsLink = "SELECT * FROM ACCESSRIGHTS_LINK WHERE AR_PATH='!AR_PATH!' AND AR_OBJECT_ID='!AR_OBJECT_ID!'";
	
	$qr_ur_setRightsLink = "DELETE FROM U_ACCESSRIGHTS WHERE AR_PATH='!DEST_AR_PATH!' AND AR_OBJECT_ID='!DEST_AR_OBJECT_ID!'; 
		DELETE FROM UG_ACCESSRIGHTS WHERE AR_PATH='!DEST_AR_PATH!' AND AR_OBJECT_ID='!DEST_AR_OBJECT_ID!';
		REPLACE INTO ACCESSRIGHTS_LINK SET AR_PATH='!DEST_AR_PATH!', AR_OBJECT_ID='!DEST_AR_OBJECT_ID!', LINK_AR_PATH='!SRC_AR_PATH!', LINK_AR_OBJECT_ID='!SRC_AR_OBJECT_ID!';";
		
	$qr_ur_copyRightsLink = "DELETE FROM U_ACCESSRIGHTS WHERE AR_PATH='!DEST_AR_PATH!' AND AR_OBJECT_ID='!DEST_AR_OBJECT_ID!'; 
		DELETE FROM UG_ACCESSRIGHTS WHERE AR_PATH='!DEST_AR_PATH!' AND AR_OBJECT_ID='!DEST_AR_OBJECT_ID!';
		REPLACE INTO ACCESSRIGHTS_LINK (AR_PATH, AR_OBJECT_ID, LINK_AR_PATH, LINK_AR_OBJECT_ID) SELECT '!DEST_AR_PATH!' AS AR_PATH, '!DEST_AR_OBJECT_ID!' AS AR_OBJECT_ID, LINK_AR_PATH, LINK_AR_OBJECT_ID FROM ACCESSRIGHTS_LINK WHERE AR_PATH='!SRC_AR_PATH!' AND AR_OBJECT_ID='!SRC_AR_OBJECT_ID!'";
		
	$qr_ur_deleteRightsLinksTo = "DELETE FROM ACCESSRIGHTS_LINK WHERE LINK_AR_PATH='!SRC_AR_PATH!' AND LINK_AR_OBJECT_ID='!SRC_AR_OBJECT_ID!'";

	/**
	 * Class which handles localization puposes. Now only returns srings from preloaded arrays.
	 *
	 */
	class Localization
	{
		/**
		 * Gets a localization string from preloaded localization array
		 *
		 * @param string strID localization string id
		 * @param string $appID application id ( 'kernel' for AA strings )
		 * @param sting $langID language ( or null if current language is used )
		 * @return string localized string
		 */
		static function get( $strID, $appID="kernel", $langID=null )
		{
			$appID = strtolower( $appID );
			$appID = ( $appID == "kernel" || $appID == "aa" || $appID == "" ) ? "" : $appID."_";

			$var = $appID.'loc_str';

			global ${$var};
			global $language;

			$locArray = $$var;

			if ( is_null( $langID ) )
				$langID = strlen( $language ) ? $language : LANG_ENG;

			if ( isset( $locArray[$langID][$strID] ) )
				return $locArray[$langID][$strID];
			else {
				$keys = (is_array($locArray)) ? array_keys($locArray) : array ();
				if ( count($keys) )
					$langID = $keys[0];

				if ( isset( $locArray[$langID][$strID] ) )
					return $locArray[$langID][$strID];
			}

			return $strID;
		}

		/**
		 * Returns array of localization string with one id for all languages
		 *
		 * @param string $appID application id
		 * @param string $strID string id
		 * @return array array of localized strings
		 */
		static function sliceLocalizationArray( $appID, $strID )
		{
			$appID = strtolower( $appID );
			$appID = ( $appID == "kernel" || $appID == "aa" || $appID == "" ) ? "" : $appID."_";

			$var = $appID.'loc_str';

			global ${$var};

			$locArray = $$var;

			$result = array();
			if (is_array($locArray)) {
				$langList = array_keys( $locArray );

				foreach( $langList as $lang_id )
				{
					if ( isset( $locArray[$lang_id][$strID] ) )
						$result[$lang_id] = $locArray[$lang_id][$strID];
				}
			}

			return $result;
		}
	}

	/**
	 * Descripbes Tree Folders.
	 *
	 */
	class FoldersTreeDescriptor
	{
		/**
		 * Describes folders' tables
		 *
		 * @var treeFolderTableDescriptor
		 */
		var $folderDescriptor;

		/**
		 * Describes documents' tables
		 *
		 * @var treeDocumentsTableDescriptor
		 */
		var $documentDescriptor;

		/**
		 * Rights path of the Folder Tree
		 *
		 * @var string
		 */
		var $rightsPath;

		/**
		 * Constructor.
		 *
		 * @param treeFolderTableDescriptor $foldersDescr
		 * @param treeDocumentsTableDescriptor $docsDescr
		 * @param string $rightsPath
		 * @return FoldersTreeDescriptor
		 */
		function FoldersTreeDescriptor( $foldersDescr, $docsDescr, $rightsPath )
		{
			$this->folderDescriptor = $folderDescriptor;
			$this->documentDescriptor = $documentDescriptor;

			$this->rightsPath = $rightsPath;
		}

		/**
		 * Sets rights path parameter
		 *
		 * @param string $rightsPath Rights Path of UR_RO_FoldersTree
		 */
		function SetRightsPath( $rightsPath )
		{
			$this->rightsPath = $rightsPath;

			if ( is_object( $this->folderDescriptor ) && method_exists( $this->folderDescriptor, "SetRightsPath" ) )
				$this->folderDescriptor->SetRightsPath( $rightsPath );
		}
	}

	/**
	 * Parent class for all Rights Manager classes
	 *
	 */
	class UR_RightsObject
	{

		/**
		 * Root object
		 *
		 * @var UR_RightsManager
		 */
		var $__root = null;

		/**
		 * Parent object
		 *
		 * @var mixed
		 */
		var $__parent = null;

		/**
		 * Current object ID
		 *
		 * @var string
		 */
		var $__id       = null;

		/**
		 * Current object path
		 *
		 * @var string
		 */
		var $__path     = null;

		/**
		 * Array of childs objects
		 *
		 * @var array
		 */
		var $__childs   = array();

		/**
		 * Rights value
		 *
		 * @var int
		 */
		var $__value = null;

		/**
		 * Aux value
		 *
		 * @var mixed
		 */
		var $__aux = null;

		/**
		 * Cached rights array. Here loaded from db rights values would be stored.
		 *
		 * @var unknown_type
		 */
		var $__cachedRights = array();

		/**
		 * Name of rights object
		 *
		 * @var string
		 */
		var $__name = "";

		/**
		 * Application ID
		 *
		 * @var string
		 */
		var $__application = "";


		/**
		 * Comment
		 *
		 * @var string
		 */
		var $__comment = null;


		/**
		 * Abstract: Saves data of the object to the store (DB)
		 *
		 * @param array $data Data array
		 * @return mixed Bool or PEAR::Error
		 */
		function __SaveMe( &$data )
		{
			return true;
		}

		/**
		 * Sets parent of the object
		 *
		 * @param UR_RightsObject $parent
		 * @return bool
		 */
		function __SetParent( &$parent )
		{
			$this->__parent = &$parent;
			return true;
		}

		/**
		 * Sets root object
		 *
		 * @param UR_RightsObject $root
		 * @return bool
		 */
		function __SetRoot( &$root )
		{
			$this->__root = &$root;
			return true;
		}

		/**
		 * Sets path to the object
		 *
		 * @param string $parent_path Path to a parent of the object
		 * @param string $parent_id Parent id
		 * @return bool
		 */
		function __SetPath( $parent_path, $parent_id )
		{
			$this->__path = $parent_path.UR_DELIMITER.$parent_id;
			return true;
		}

		/**
		 * Abstract: Starts rendering
		 *
		 * @param array $data Data array
		 * @return string Rendered data or PEAR::Error
		 */
		function __RenderStart( &$data )
		{
			return "";
		}

		/**
		 * Abstract: Finish rendering
		 *
		 * @param array $data Data array
		 * @return string Rendered data or PEAR::Error
		 */
		function __RenderFinish( &$data )
		{
			return "";
		}

		/**
		 * Abstract: Action which executes when object will be connected to a parent
		 *
		 * @return mixed Bool or PEAR:Error
		 */
		function __ActionOnConnect( )
		{
			return true;
		}

		/**
		 * Checks trigger value of the object whether user or group permitted or not
		 *
		 * @param string $ID User or Group ID
		 * @param string $type UR_USER_ID or UR_GROUP_ID (Checks users' or groups' permissions)
		 * @return mixed Boolean or PEAR::Error
		 */
		function __Permitted( $ID, $type, $triggerON = UR_BOOL_TRUE )
		{
			$ret = null;

			if ( $type == UR_USER_ID )
				$ret = $this->__root->GetUserRightValue( $ID, $this->GetFullPath() );
			else
			if ( $type == UR_GROUP_ID )
				$ret = $this->__root->GetGroupRightValue( $ID, $this->GetFullPath() );

			if ( PEAR::isError( $ret ) )
				return $ret;

			if ( !is_null( $ret ) && ( ( $ret & $triggerON ) == $triggerON ) )
				return true;

			return false;
		}

		/**
		 * Grants or Revokes rights object privileges
		 *
		 * @param string $ID
		 * @param string $IDtype
		 * @param string $action
		 */
		function __SetGlobalRights( $ID, $IDtype, $action )
		{
			return true;
		}

		/**
		 * Checks if object has child object with sepcified id
		 *
		 * @param string $ID Object ID
		 * @return mixed Returns child object or null
		 */
		function &__hasChild( $ID )
		{
			if ( count( $this->__childs ) )
				foreach( $this->__childs as $child )
					if ( $child->GetID() == $ID )
						return $child;
			return null;
		}

		// public

		/**
		 * Constructor
		 *
		 * @param string $id Object ID
		 * @param string $name Object Name
		 * @param string $app Application ID ( Kernel if empty )
		 * @return UR_RightsObject
		 */
		function UR_RightsObject( $id, $name, $app="" )
		{
			$this->__id = $id;
			$this->__name = $name;
			$this->__application = $app;
		}

		/**
		 * Adds a child
		 *
		 * @param mixed $child Child object
		 * @return boolean
		 */
		function AddChild( &$child )
		{
			$child->__SetPath( $this->__path, $this->__id );
			$child->__SetParent( $this );

			if ( is_null( $this->__root ) )
				$child->__SetRoot( $this );
			else
				$child->__SetRoot( $this->__root );

			// ? непонятное место. по идее надо проверить на ошибку и вернуть ее, если ошиблись���� ��������
			$child->__ActionOnConnect();

			$this->__childs[] = &$child;

			return true;
		}

		/**
		 * Find object with specified path and returns its' reference
		 *
		 * @param sting $path
		 * @return UR_RightsObject|null
		 */
		function &SearchPath( $path )
		{
			if ( strtoupper( $path ) == $this->GetFullPath( ) )
				return $this;

			if ( count( $this->__childs ) )
			{
				foreach( $this->__childs as $child )
				{
					$ret = $child->SearchPath( $path );

					if ( !is_null( $ret ) )
						return $ret;
				}
			}

			$null_var = null;

			return $null_var;
		}


		/**
		 * Checks boolean value of the object whether user permitted or not
		 *
		 * @param string $U_ID User ID
		 * @return mixed Boolean or PEAR::Error
		 */
		function UserPermitted( $U_ID )
		{
			return $this->__root->__Permitted( $U_ID, UR_USER_ID );
		}

		/**
		 * Checks boolean value of the object whether group permitted or not
		 *
		 * @param string $ID Group ID
		 * @return mixed Boolean or PEAR::Error
		 */
		function GroupPermitted( $ID )
		{
			return $this->__root->__Permitted( $ID, UR_GROUP_ID );
		}

		/**
		 * Render function
		 *
		 * @param array $data Rendering data array
		 * @return mixed Rendered string or PEAR::Error
		 */
		function Render( &$data )
		{
			$render = "";

			if ( PEAR::isError( $ret = $this->__RenderStart( $data ) ) )
				return $ret;
			else
				$render .= $ret;
				
			if ( count( $this->__childs ) )
			{
				foreach( $this->__childs as $child )
				{
					if ( PEAR::isError( $ret = $child->Render( $data ) ) )
						return $ret;
					else
						$render .= $ret;
				}
			}

			if ( PEAR::isError( $ret = $this->__RenderFinish( $data ) ) )
				return $ret;
			else
				$render .= $ret;

			return $render;
		}

		/**
		 * Set global rights
		 *
		 * @param string $ID
		 * @param string $IDtype
		 * @param integer $action
		 */
		function SetGlobalRights( $ID, $IDtype, $action )
		{
			if ( count( $this->__childs ) )
				foreach( $this->__childs as $child )
				{
					if ( PEAR::isError( $ret = $child->SetGlobalRights( $ID, $IDtype, $action ) ) )
						return $ret;
				}

			if ( PEAR::isError( $ret = $this->__SetGlobalRights( $ID, $IDtype, $action ) ) )
				return $ret;

			return true;
		}

		/**
		 * Abstract: Validate entered data
		 *
		 * @param array $data Data array
		 * @return boolean or PEAR::Error
		 */
		function Validate( &$data )
		{
			return true;
		}

		/**
		 * Saves data to the database
		 *
		 * @param array $data Data array
		 * @return boolean or PEAR::Error
		 */
		function Save( &$data )
		{
			if ( PEAR::isError( $ret = $this->Validate( $data ) ) )
				return $ret;

			if ( count( $this->__childs ) )
			{
				foreach( $this->__childs as $child )
				{
					if ( PEAR::isError( $ret = $child->Save( $data ) ) )
						return $ret;
				}
			}

			return $this->__SaveMe( $data );
		}

		/**
		 * Renders only one item for all users.
		 *
		 * @param data $data
		 * @return string or PEAR::Error
		 */
		function RenderItem( &$data )
		{
			$render = "";

			$fullPath = $this->GetFullPath();

			if ( $fullPath == $this->__root->__renderOptions[UR_PATH] && !is_null( $ret = $this->__hasChild( $this->__root->__renderOptions[UR_OBJECTID] ) ) )
				$render.= $ret->RenderItem( $data );

			return $render;
		}

		/**
		 * Saves data for one item
		 *
		 * @param array $data
		 * @return boolean or PEAR::Error
		 */
		function SaveItem( &$data )
		{
			$fullPath = $this->GetFullPath();

			if ( $fullPath == $this->__root->__renderOptions[UR_PATH] && !is_null( $ret = $this->__hasChild( $this->__root->__renderOptions[UR_OBJECTID] ) ) )
				$ret= $ret->SaveItem( $data );

			return $ret;
		}

		/**
		 * Gets current object path
		 *
		 * @return string
		 */
		function GetPath( )
		{
			return strtoupper( $this->__path );
		}

		/**
		 * Gets current object ID
		 *
		 * @return string
		 */
		function GetID( )
		{
			return strtoupper( $this->__id );
		}

		/**
		 * Gets current object full path
		 *
		 * @return unknown
		 */
		function GetFullPath( )
		{
			return $this->GetPath() .UR_DELIMITER. $this->GetID();
		}

		/**
		 * Makes path string from $pathArray pieces
		 *
		 * @param array $pathArray
		 * @return string
		 */
		function MakePath( $pathArray )
		{
			return UR_DELIMITER . implode( UR_DELIMITER, $pathArray );
		}

		/**
		 * Checks whether the integer value fits the bit mask
		 *
		 * @param int $value
		 * @param mixed $mask integer value or array of integer values (array of masks)
		 * @return boolean
		 */
		static function CheckMask( $value, $mask )
		{
			if ( is_array($mask) ) {
				foreach ( $mask as $curMask )
				{
					if ( ( $value & $curMask ) == $curMask )
						return true;
				}

				return false;
			}

			return ( ( $value & $mask ) == $mask );
		}

		/**
		 * Sets comment string
		 *
		 * @param string $comment
		 */
		function SetComment( $comment )
		{
			$this->__comment = $comment;
		}
	}

	/**
	 * Root class for all users rights (has /ROOT path)
	 *
	 */
	class UR_RightsManager extends UR_RightsObject
	{
		// Private Data

		/**
		 * Render options. Would be loaded from $data array.
		 *
		 * @var array
		 */
		var $__renderOptions;

		/**
		 * Array for cached rights values
		 *
		 * @var array
		 */
		var $__cachedRights = array();

		/**
		 * Array for cached items rights values
		 *
		 * @var array
		 */
		var $__cachedItems = array();

		// Private Functions

		/**
		 * Interpret object's path. Returns path and object id array ( UR_PATH=>$path, UR_OBJECTID=>$id )
		 *
		 * @param string $string Full path
		 * @return array
		 */
		function __interpretPath( $string )
		{
			$parts = explode( UR_DELIMITER, $string );
			$parts_count = count($parts);

			$path = $id = "";
			$path_parts = array();

			if ( $parts_count > 0 )
			{
				$id = $parts[ $parts_count-1 ];
				unset( $parts[ $parts_count-1 ] );

				if ( count( $parts ) )
					$path = implode( UR_DELIMITER, $parts );
			}

			return array( UR_PATH=>$path, UR_OBJECTID=>$id );
		}

		// Database

		/**
		 * Starts database transaction
		 *
		 * @return boolean or PEAR::Error
		 */
		function __StartTransaction( )
		{
			return true;
		}

		/**
		 * Commit database transaction
		 *
		 * @return boolean or PEAR::Error
		 */
		function __CommitTransaction( )
		{
			return true;
		}

		/**
		 * Rollback database transaction
		 *
		 * @return boolean or PEAR::Error
		 */
		function __RollbackTransaction( )
		{
			return true;
		}

		/**
		 * Gets right value from cache
		 *
		 * @param string $id User or Group ID
		 * @param string $path Object path
		 * @param string $type Type of searched data. If data is searched for group or user.
		 * @return array or null if not found
		 */
		function __GetCachedRightValue( $id, $path, $type )
		{
			//$key = $id . $path . $type;
			//if (($cacheValue = getLocalCacheValue ("USERRIGHTS", $key)) !== null)
				//return $cacheValue;			
			
			$pathArr = $this->__interpretPath( $path );

			if ( !isset($this->__cachedRights[$type]) )
				return null;

			$searchArr = $this->__cachedRights[$type];
			
			if ( isset( $searchArr[$id][$pathArr[UR_PATH]][$pathArr[UR_OBJECTID]] ) )
				return $searchArr[$id][$pathArr[UR_PATH]][$pathArr[UR_OBJECTID]];
			else
				return null;
		}

		/**
		 * Gets right value
		 *
		 * @param string $id User or Group ID
		 * @param string $path Object path
		 * @param string $type Type of searched data. If data is searched for group or user.
		 * @param boolean $useCache Use cached values if possible
		 * @return array or null if not found or PEAR::Error
		 */
		function __GetRightValue( $id, $path, $type, $useCache = true, $updateCache = false )
		{
			global $qr_urm_getUserRightValue;
			global $qr_urm_getGroupRightValue;

			if ( $useCache && !$updateCache && !is_null( $ret = $this->__GetCachedRightValue( $id, $path, $type ) ) )
				return $ret;
			
			$searchArr = $this->__interpretPath( $path );
			if ($this->CanBeLinked($searchArr[UR_PATH])) {
				$rightsLink = $this->GetRightsLink ($searchArr);
				if (PEAR::isError($rightsLink))
					return $rightsLink;
				if ($rightsLink)
					$searchArr = $rightsLink;
			}
			
			$checkStr = ( $type == UR_USER_ID ) ? '/^[0-9A-Z\._\-@]+$/' : '/^[0-9]+$/';
			if ( !preg_match( $checkStr, $id ) )
				return null;

			$searchArr[UR_ID] = $id;

			$res = db_query( ( $type == UR_USER_ID ) ? $qr_urm_getUserRightValue : $qr_urm_getGroupRightValue, $searchArr );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

			$row = null;
			if ( db_result_num_rows( $res ) > 0 )
			{
				$row = db_fetch_array( $res );
				$row[UR_PATH] = $searchArr[UR_PATH];
				$row[UR_OBJECTID] = $searchArr[UR_OBJECTID];
				$row[UR_VIEWGROUPVALUE] = 0;

				if ( $useCache ) {
					//$key = $id . $path . $type;
					//setLocalCacheValue ("USERRIGHTS", $key, $row);
					$this->__cachedRights[$type][ $id ][ $searchArr[UR_PATH] ][ $searchArr[ UR_OBJECTID ] ] = $row;
				}
			}

			db_free_result($res);

			return $row;
		}

		/**
		 * Load cached data for item rendering
		 *
		 * @param string $object_id Object Id
		 * @return boolean or PEAR::Error
		 */
		function __loadItemsCache( $object_id=null )
		{
			global $qr_ur_selectUserItems;
			global $qr_ur_selectGroupItems;

			$action = $this->__renderOptions[UR_ACTION];

			$this->__cachedItems=array();

			$sql = $qr_ur_selectUserItems;
			if ( $action == UR_ACTION_EDITGROUP )
				$sql = $qr_ur_selectGroupItems;

			$params = $this->__renderOptions;

			if ( !is_null( $object_id )  )
				$params[UR_OBJECTID] = $object_id;

			$res = db_query( $sql, $params );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

			$resarr = array();

			while ( $u = db_fetch_array( $res ) )
			{
			    $row = array(
			        UR_PATH => $u[UR_PATH],
			        UR_VALUE => $u[UR_VALUE],
			        UR_OBJECTID => $u[UR_OBJECTID] 
			    );
			    $row[ UR_ID ] = ( $action == UR_ACTION_EDITGROUP ) ? $u["UG_ID"] : $u['U_ID'];
			    $row['CODE'] = ( $action == UR_ACTION_EDITGROUP ) ? $u["UG_ID"] : ($u['U_STATUS'] == 3 ? '' : $u['U_ID']);
				$row[UR_ITEMNAME] = ($action == UR_ACTION_EDITGROUP) ? $u[ "UG_NAME" ] : $u['C_FULLNAME'];
				if ($u['U_STATUS'] == 3) {
				    $row[UR_ITEMNAME] = "<i>".$row[UR_ITEMNAME]." (".Localization::get('app_user_invited').")</i>";    
				}
				if ($u['U_STATUS'] == 2) {
				    $row[UR_ITEMNAME] = '<i style="color:#666">'.$row[UR_ITEMNAME]." (".Localization::get('app_user_disabled').")</i>"; 
				}				

				if ( !isset( $resarr[ $row[ UR_ID ] ] ) || !is_null( $row[UR_PATH] ) )
					$resarr[ $row[ UR_ID ] ] = $row;
			}

			$this->__cachedItems = $resarr;
			db_free_result($res);

			return true;
		}

		/**
		 * Loads access rights cache
		 *
		 * @return boolean or PEAR::Error
		 */
		function __loadAccessRightsCache( )
		{
			global $qr_ur_getUserRightsList;
			global $qr_ur_getUserGroupsRightsList;
			global $qr_ur_selectUsersRightsList;
			global $qr_ur_selectGroupsRightsList;
			global $qr_ur_getRightsLinks;
			
			$action = $this->__renderOptions[UR_ACTION];

			$this->__cachedRights=array();
			$this->__cachedRights[UR_USER_ID]=array();
			$this->__cachedRights[UR_GROUP_ID]=array();
			
			$res = db_query( $qr_ur_getRightsLinks, array( UR_ID => $this->__renderOptions[UR_ID] ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );
			$linksRows = array ();
			while ( $row = db_fetch_array( $res ) ) {
				$linksRows[] = $row;
			}
			
			if ( $action == UR_ACTION_EDITUSER )
			{
				if ( $this->__renderOptions[UR_ID] != UR_SYS_ID )
				{
					$res = db_query( $qr_ur_getUserRightsList, array( UR_ID => $this->__renderOptions[UR_ID] ) );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

					while ( $row = db_fetch_array( $res ) )
					{
						$row[UR_VIEWGROUPVALUE] = 0;
						if ( isset($this->__cachedRights[UR_USER_ID][ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ]) )
						{
							if ( strlen($row[UR_VALUE]) )
								$this->__cachedRights[UR_USER_ID][ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_VALUE] = $row[UR_VALUE];
							else
								$this->__cachedRights[UR_USER_ID][ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_GROUPRIGHTS] = $row[UR_GROUPRIGHTS];
						} else
							$this->__cachedRights[UR_USER_ID][ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ] = $row;
					}
					
					db_free_result($res);
				}
				
				if ( isset( $this->__renderOptions[UR_INCLUDED_GROUPS] ) && $this->__renderOptions[UR_INCLUDED_GROUPS] != "" )
				{
					$qr_ur_getGroupsRightsList = "SELECT AR_PATH, AR_OBJECT_ID, BIT_OR( AR_VALUE ) AS VIEW_GROUP_VALUE FROM UG_ACCESSRIGHTS WHERE AR_ID IN ( %s ) GROUP BY AR_PATH, AR_OBJECT_ID";

					$ur_getGroupsRightsList = sprintf( $qr_ur_getGroupsRightsList, $this->__renderOptions[UR_INCLUDED_GROUPS] );

					$res = db_query( $ur_getGroupsRightsList, array( ) );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

					while ( $row = db_fetch_array( $res ) )
					{
						if ( !isset($this->__cachedRights[UR_USER_ID][  $this->__renderOptions[UR_ID]  ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_VALUE]) )
							$this->__cachedRights[UR_USER_ID][  $this->__renderOptions[UR_ID]  ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_VALUE] = 0;

						$this->__cachedRights[UR_USER_ID][  $this->__renderOptions[UR_ID]  ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_VIEWGROUPVALUE] = $row[UR_VIEWGROUPVALUE];
					}

					db_free_result($res);
				}
				
				// Copy users rights from
				$keys = array_keys($this->__cachedRights[UR_USER_ID]);
				foreach ($keys as $cUrId) {
					foreach ($linksRows as $cLinkRow) {
						if (!isset($this->__cachedRights[UR_USER_ID][$cUrId][$cLinkRow["LINK_AR_PATH"]][$cLinkRow["LINK_AR_OBJECT_ID"]]))
							continue;
						$sourceRow = &$this->__cachedRights[UR_USER_ID][$cUrId][$cLinkRow["LINK_AR_PATH"]][$cLinkRow["LINK_AR_OBJECT_ID"]];
						$destRow = &$this->__cachedRights[UR_USER_ID][$cUrId][$cLinkRow[UR_PATH]][$cLinkRow[UR_OBJECTID]];
						$destRow[UR_VALUE] = $sourceRow[UR_VALUE];
						$destRow[UR_GROUPRIGHTS] = $sourceRow[UR_GROUPRIGHTS];
						if (isset($sourceRow[UR_VIEWGROUPVALUE]))
							$destRow[UR_VIEWGROUPVALUE] = $sourceRow[UR_VIEWGROUPVALUE];
					}
				}
			}
			else
			{
				if ( $this->__renderOptions[UR_ID] != UR_SYS_ID )
				{
					if ( $action == UR_ACTION_EDITGROUP || $action == UR_ACTION_VIEWGROUP )
						$sql = $qr_ur_selectGroupsRightsList;
					else
						$sql = $qr_ur_selectUsersRightsList;

					$ids = $this->__renderOptions[UR_ID];
					$ids = is_array( $ids ) ? sprintf( "'%s'", implode("','", $ids) ) : "'$ids'";

					$sql = sprintf( $sql,  $ids, $ids );

					$res = db_query( $sql, array( ) );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

					$resarr = array();
					while ( $row = db_fetch_array( $res ) )
					{
						if ( isset( $resarr[ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ] ) )
						{
							if ( strlen( $row[UR_VALUE] ) )
								$resarr[ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_VALUE] = $row[UR_VALUE];
							else
								$resarr[ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_GROUPRIGHTS] = $row[UR_GROUPRIGHTS];
						}
						else
							$resarr[ $row[ UR_ID ] ][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ] = $row;
					}
					
					// Copy links rights
					$keys = array_keys($resarr);
					foreach ($keys as $cUrId) {
						foreach ($linksRows as $cLinkRow) {
							if (!isset($resarr[$cUrId][$cLinkRow["LINK_AR_PATH"]][$cLinkRow["LINK_AR_OBJECT_ID"]]))
								continue;
							$sourceRow = &$resarr[$cUrId][$cLinkRow["LINK_AR_PATH"]][$cLinkRow["LINK_AR_OBJECT_ID"]];
							$destRow = &$resarr[$cUrId][$cLinkRow[UR_PATH]][$cLinkRow[UR_OBJECTID]];
							$destRow[UR_VALUE] = $sourceRow[UR_VALUE];
							$destRow[UR_GROUPRIGHTS] = $sourceRow[UR_GROUPRIGHTS];
						}
					}

					if ( $action == UR_ACTION_EDITGROUP || $action == UR_ACTION_VIEWGROUP )
						$this->__cachedRights[UR_GROUP_ID]=$resarr;
					else
						$this->__cachedRights[UR_USER_ID]=$resarr;

					db_free_result($res);
				}
			}

			return true;
		}

		/**
		 * Loads all $id users' rights into cache
		 *
		 * @param string $id User ID
		 * @param string $path Object Path if needed
		 * @return array or PEAR::Error
		 */
		function __loadUserRightsCache( $id, $path="" )
		{
			global $qr_ur_getUserPathPatternRightsList;
			global $qr_ur_getUserRightsList;

			$sql = ( $path == "" ) ? $qr_ur_getUserRightsList : $qr_ur_getUserPathPatternRightsList;

			$res = db_query( $sql, array( UR_ID => $id, UR_PATH => $path ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

			while ( $row = db_fetch_array( $res ) )
			{
				if ( isset($this->__cachedRights[UR_USER_ID][$id][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ]) )
				{
					if ( strlen($row[UR_VALUE]) )
						$this->__cachedRights[UR_USER_ID][$id][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_VALUE] = $row[UR_VALUE];
					else
						$this->__cachedRights[UR_USER_ID][$id][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ][UR_GROUPRIGHTS] = $row[UR_GROUPRIGHTS];
				} else
					$this->__cachedRights[UR_USER_ID][$id][ $row[ UR_PATH ] ][ $row[ UR_OBJECTID ] ] = $row;
			}

			db_free_result($res);
		}

		/**
		 * Saves right value into database
		 *
		 * @param string $path Object path
		 * @param string $id User or Group ID
		 * @param string $type IDT_USER or IDT_GROUP
		 * @param int $value Value
		 * @param array $aux Aux rights array
		 * @param boolean $updateCache update cache after saving of the right
		 * @return boolean or PEAR::Error
		 */
		function __SaveRightValue( $path, $id, $type, $value=0, $aux=null, $updateCache = true )
		{
		    
			global $qr_urm_saveUserRightValue;
			global $qr_urm_saveGroupRightValue;

			$saveArr = $this->__interpretPath( $path );


			$saveArr[UR_ID] = $id;
			$saveArr[UR_VALUE] = $value;
			
			if ($value > 0 && !preg_match('/SCREENS/', $saveArr[UR_PATH])) {
    		    $path_info = explode("/", $path);
    		    $app_id = $path_info[2];
    		    $screens = array_keys($this->__screens[$app_id]);
    		    $screen_id = $screens[0];
    		    if (!$this->__cachedRights[$type][$saveArr[UR_ID]]['/ROOT/'.$app_id."/SCREENS"][$screen_id]) {
    		        $this->__SaveRightValue('/ROOT/'.$app_id.'/SCREENS/'.$screen_id, $id, $type, 1, null, false);    
    		    }
			}			

			$saveArr[UR_AUX] = null;

			$sql = ( $type == UR_USER_ID ) ? $qr_urm_saveUserRightValue : $qr_urm_saveGroupRightValue;

			$res = db_query( $sql, $saveArr );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

			if ( $updateCache ) {
				$key = $id . $path . $type;
				setLocalCacheValue ("USERRIGHTS", $key, $saveArr);
				$this->__cachedRights[$type][ $saveArr[ UR_ID ] ][ $saveArr[ UR_PATH ] ][ $saveArr[ UR_OBJECTID ] ] = $saveArr;
			}

			return true;
		}

		/**
		 * Render options must be included into the form. It returns this rendered options.
		 *
		 * @return string
		 */
		function __RenderRenderOptions( )
		{
			$ret = "";

			foreach( $this->__renderOptions as $name=>$value )
				$ret .= "<input type=hidden name='".$this->__renderOptions[UR_FIELD]."[$name]' id='".$this->__renderOptions[UR_FIELD]."[$name]' value='".(($name==UR_EDITED) ? 1 : $value ). "'>";

			return $ret;
		}

		/**
		 * Parses $data array and checks render item options.
		 *
		 * @param array $data
		 * @return array of render options or null if error
		 */
		function __loadRenderItemOptions( &$data )
		{
			$result = array();

			if ( !isset( $data[UR_PATH] ) || !isset( $data[UR_ACTION] ) )
				return null;

			switch( $data[UR_ACTION])
			{
				case UR_ACTION_EDITUSER:
					$result[UR_ACTION] = UR_ACTION_EDITUSER;
					break;
				case UR_ACTION_EDITGROUP:
					$result[UR_ACTION] = UR_ACTION_EDITGROUP;
					break;
				default:
					return null;
			}

			$result[UR_FIELD] = "DATA";
			if ( isset( $data[UR_FIELD]) )
			{
				if ( !ereg( '^[A-Za-z0-9]+$', $data[UR_FIELD] ) )
					return null;

				$result[UR_FIELD] = $data[UR_FIELD];
			}

			if ( isset($data[UR_EDITED]) && $data[UR_EDITED] == "1" )
				$result[UR_EDITED] = true;
			else
				$result[UR_EDITED] = false;
			
			if (empty($data[UR_SPECIALSTATUS]))
				$result[UR_SPECIALSTATUS] = null;
			else
				$result[UR_SPECIALSTATUS] = $data[UR_SPECIALSTATUS];

			$result[UR_PATH] = strtoupper( $data[UR_PATH] );
			if ( !ereg( '^[A-Z/]+$', $result[UR_PATH] ) )
				return null;

			if ( !is_null( $data[UR_OBJECTID] ) )
				$data[UR_OBJECTID] = strtoupper( $data[UR_OBJECTID] );

			if ( $data[UR_OBJECTID] == UR_SYS_ID )
			{
				if ( !isset( $data[UR_REAL_ID] ) )
					return null;
				else
					$data[UR_OBJECTID] = null;
			}

			if ( !is_null( $data[UR_OBJECTID] ) )
			{
				if ( !preg_match( '/^[0-9A-Z\._\-@]+$/',$data[UR_OBJECTID] ) )
					return null;

				$result[UR_OBJECTID] = $data[UR_OBJECTID];
			}
			else
			{
				$result[UR_OBJECTID] = UR_SYS_ID;

				if ( isset( $data[UR_REAL_ID] ) )
				{
					if ( !preg_match( '/^[0-9A-Z\._\-@]+$/',$data[UR_REAL_ID] ) )
						return null;
					else
						$result[UR_REAL_ID] = $data[UR_REAL_ID];
				}

				if ( isset( $data[UR_COPYFROM] ) )
				{
					if ( !preg_match( '/^[0-9A-Z\._\-@]+$/',$data[UR_COPYFROM] ) )
						return null;
					else
						$result[UR_COPYFROM] = $data[UR_COPYFROM];
				}

			}

			return $result;
		}

		/**
		 * Parses $data array and checks render options.
		 *
		 * @param array $data
		 * @return array of render options or null if error
		 */
		function __loadRenderOptions( &$data )
		{
			$result = array();

			if ( !isset( $data[UR_PATH] ) || !isset( $data[UR_ACTION] ) )
				return null;

			$result[UR_FIELD] = "DATA";
			if ( isset( $data[UR_FIELD] ) )
			{
				if ( !ereg( '^[A-Za-z0-9]+$', $data[UR_FIELD] ) )
					return null;

				$result[UR_FIELD] = $data[UR_FIELD];
			}

			if ( isset($data[UR_EDITED]) && $data[UR_EDITED] == "1" )
				$result[UR_EDITED] = true;
			else
				$result[UR_EDITED] = false;

			$result[UR_PATH] = strtoupper( $data[UR_PATH] );
			if ( !ereg( '^[A-Z/]+$', $result[UR_PATH] ) )
				return null;

			$result[UR_ACTION] = strtoupper( $data[UR_ACTION] );

			if ( !is_null( $data[UR_ID] ) )
				$data[UR_ID] = strtoupper( $data[UR_ID] );

			if ( $data[UR_ID] == UR_SYS_ID )
			{
				if ( !isset( $data[UR_REAL_ID] ) )
					return null;
				else
					$data[UR_ID] = null;
			}

			switch( $result[UR_ACTION] )
			{
				case UR_ACTION_EDITGROUP:

					if ( !is_null( $data[UR_ID] ) && !ereg( '^[0-9]+$', $data[UR_ID] ) )
						return null;

					if ( !is_null( $data[UR_ID] ) )
						$result[UR_ID] = $data[UR_ID];
					else
					{
						$result[UR_ID] = UR_SYS_ID;

						if ( isset( $data[UR_REAL_ID] ) )
						{
							if ( !ereg( '^[0-9]+$', $data[UR_REAL_ID] ) )
								return null;
							else
								$result[UR_REAL_ID] = $data[UR_REAL_ID];
						}
					}

					$result['NUMCOLS'] = 2;

					break;

				case UR_ACTION_EDITUSER:

					if ( !is_null( $data[UR_ID] ) && !preg_match( '/^[0-9A-Z\._\-@]+$/', $data[UR_ID] ) )
						return null;

					if ( !is_null( $data[UR_ID] ) )
						$result[UR_ID] = $data[UR_ID];
					else
					{
						$result[UR_ID] = UR_SYS_ID;

						if ( isset( $data[UR_REAL_ID] ) )
						{
							if ( !preg_match( '/^[0-9A-Z\._\-@]+$/', $data[UR_REAL_ID] ) )
								return null;
							else
								$result[UR_REAL_ID] = $data[UR_REAL_ID];
						}
					}

					if ( isset( $data[UR_INCLUDED_GROUPS] )  && is_array( $data[UR_INCLUDED_GROUPS] ) && count( $data[UR_INCLUDED_GROUPS] )  )
					{
						$i=1;
						$result[UR_INCLUDED_GROUPS] = "";
						foreach( $data[UR_INCLUDED_GROUPS] as $value )
						{
							if ( !ereg( '^[0-9]+$', $value ) )
								return null;
							else
								$result[UR_INCLUDED_GROUPS] .= "'{$value}'";

							if  ( $i != count( $data[UR_INCLUDED_GROUPS] ) )
								$result[UR_INCLUDED_GROUPS] .= ',';

							++$i;
						}

					}
					$result['NUMCOLS'] = 3;

					break;

				case UR_ACTION_VIEWUSER:

					if ( !preg_match( '/^[0-9A-Z,\._\-@]+$/', $data[UR_ID] ) )
						return null;

					$ids = explode( ",", $data[UR_ID] );

					foreach ( $ids as $key=>$value )
						if ( $value=="" )
							unset( $ids[$key] );

					if ( count( $ids ) == 0 )
						return null;

					$result[UR_ID] = $ids;

					$result['NUMCOLS'] = is_array( $ids ) ? count( $ids ) + 1 : 2;

					break;

				case UR_ACTION_VIEWGROUP:

					if ( !ereg( '^[0-9,]+$', $data[UR_ID] ) )
						return null;

					$ids = explode( ",", $data[UR_ID] );

					foreach ( $ids as $key=>$value )
						if ( $value=="" )
							unset( $ids[$key] );

					if ( count( $ids ) == 0 )
						return null;

					$result[UR_ID] = $ids;

					$result['NUMCOLS'] = is_array( $ids ) ? count( $ids ) + 1 : 2;

					break;

				default:

					return null;
			}

			return $result;
		}

		/**
		 * Start /ROOT render function.
		 *
		 * @param array $data
		 * @return string
		 */
		function __RStart( &$data )
		{
			$ret = "<script language=JavaScript>toggleCbRightsControl{$this->__renderOptions[UR_FIELD]} = new toggleCBContainer(); </script>";

			switch( $this->__renderOptions[UR_ACTION] )
			{
				case UR_ACTION_EDITGROUP:
				case UR_ACTION_EDITUSER:

					$ret .= "<table width=100% class='RightsTable'>" ;
					$ret .= "<thead><th width=50%>&nbsp;</th>";

					if ( $this->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
						$ret .= "<th class=\"AlignCenter\">".Localization::get( 'usa_indqualifier_label' )."</th><th class=\"AlignCenter\">".Localization::get( 'usa_groupqualifier_label' )."</th>";
					else
						$ret .= "<th>&nbsp;</th>";

					$ret .= "</tr></thead>\n";

					break;

				case UR_ACTION_VIEWUSER:
				case UR_ACTION_VIEWGROUP:

					$ret .= "<table width=98% class='SimpleList RightsTable'>" ;
					$ret .= "<thead><tr>";

					$ids = $this->__renderOptions[UR_ID];
					if ( !is_array( $ids ) )
						$ids = array( 0=>$ids );

					$ret .= "<th width=50%>".Localization::get( 'usa_accessobj_title' )."</th>";

					foreach( $ids as $id )
					{
						if ( $this->__renderOptions[UR_ACTION] == UR_ACTION_VIEWUSER )
							$ret .= "<th class=\"AlignCenter\">".getUserName($id)."</th>";
						else
							$ret .= "<th class=\"AlignCenter\">".getGroupName($id)."</th>";
					}

					$ret .= "</tr></thead>\n";

					break;
			}

			return $ret;
		}

		/**
		 * Finish /ROOT render function.
		 *
		 * @param array $data
		 * @return string
		 */
		function __RFinish( &$data )
		{
			return "</table>";
		}

		// public functions

		/**
		 * Constrtuctor
		 *
		 * @return UR_RightsManager
		 */
		function UR_RightsManager( )
		{
			$this->__path = "";
			$this->__id = UR_ROOT;
		}

		/**
		 * Gets User right value
		 *
		 * @param string $id User ID
		 * @param string $path Object path
		 * @param boolean $useCache Use cached data
		 * @return int Value
		 */
		function GetGroupRightValue( $id, $path, $useCache = true )
		{
			if ( PEAR::isError( $value = $this->__GetRightValue( $id, $path, UR_GROUP_ID ) ) )
				return $value;

			if ( is_null( $value ) )
				return 0;

			return $value[UR_VALUE];
		}

		/**
		 * Gets Group right value
		 *
		 * @param string $id User ID
		 * @param string $path Object path
		 * @param boolean $useCache Use cached data
		 * @return int Value
		 */
		function GetUserRightValue( $id, $path, $useCache = true, $updateCache = false )
		{
			$emptyRights = array( UR_VALUE => 0, UR_GROUPRIGHTS => 0 );

			if ( PEAR::isError( $value = $this->__GetRightValue( $id, $path, UR_USER_ID, $useCache, $updateCache ) ) )
				return $value;

			if ( PEAR::isError( $admValue = $this->__GetRightValue( $id, $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ), UR_USER_ID, true, $updateCache ) ) )
				return $admValue;

			if ( is_null( $value ) )
				$value = $emptyRights;

			if ( is_null( $admValue ) )
				$admValue = $emptyRights;

			$res = $value[UR_VALUE]  |  ( isset( $value[UR_GROUPRIGHTS] ) ? $value[UR_GROUPRIGHTS] : 0 )  |  $admValue[UR_VALUE];
			
			return $res;
		}

		/**
		 * Checks whether user U_ID has global administrator rights or not
		 *
		 * @param string $U_ID User ID
		 */
		function IsGlobalAdministrator( $U_ID, $updateCache = false  )
		{
			if ( PEAR::isError( $value = $this->GetUserRightValue( $U_ID, $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ), true, $updateCache ) ) )
				return $value;

			return $this->CheckMask( $value, UR_FULL_RIGHTS );
		}

		/**
		 * Checks whether user U_ID has global administrator rights or not
		 *
		 * @param string $U_ID User ID
		 */
		function IsUserGlobalAdministrator( $U_ID, $updateCache = false  )
		{
			$emptyRights = array( UR_VALUE => 0, UR_GROUPRIGHTS => 0 );

			if ( PEAR::isError( $admValue = $this->__GetRightValue( $U_ID, $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ), UR_USER_ID, true, $updateCache ) ) )
				return $admValue;

			if ( is_null( $admValue ) || !isset( $admValue[UR_VALUE] ) )
				$admValue = $emptyRights;

			return $this->CheckMask( $admValue[UR_VALUE], UR_FULL_RIGHTS );
		}

		/**
		 * Registers application screen. Called only from UR_RO_Screen object
		 *
		 * @param string $APP_ID Application ID
		 * @param string $SCR_ID Screen ID
		 * @param string $SCR_DATA
		 * @return null
		 */
		function registerAppScreen( $APP_ID, $SCR_ID, $SCR_DATA )
		{
			global $global_screens;
			global $init_required;
			
			if ($init_required === false) {
				$global_screens[$APP_ID][$SCR_ID] = $SCR_DATA;
			}
			
			$this->__screens[$APP_ID][$SCR_ID] = $SCR_DATA;

			return null;
		}

		/**
		 * Returns users' screens list
		 *
		 * @param string $U_ID User ID
		 * @return array Menu Links
		 */
		function listUserScreens( $U_ID )
		{
			global $global_screens;
			
			$menuLinks = array();
			
			if ( isAdministratorID( $U_ID ) )
			{
				$menuLinks[AA_APP_ID] = array_keys($global_screens[AA_APP_ID]);
				$menuLinks["UG"] = array_keys($global_screens[UG]);

				$menuLinks[MYWEBASYST_APP_ID] = array();

				foreach( array_keys($global_screens[MYWEBASYST_APP_ID]) as $key )
					if ( !in_array($key, array(MYWEBASYST_LOOKANDFEEL, MYWEBASYST_PREFERENCES) ) )
						$menuLinks[MYWEBASYST_APP_ID][] = $key;

				return $menuLinks;
			}

			$this->__loadUserRightsCache( $U_ID, "%SCREENS" );

			$globalAdmin = $this->IsGlobalAdministrator($U_ID);
			
			foreach( $global_screens as $app_id=>$app_val )
			{
				if ( !is_array( $app_val ) )
					continue;
				
				foreach( $app_val as $scr_id=>$scr_arr )
				{
					if ( !$globalAdmin )
					{
						if (defined("CURRENT_APP") && CURRENT_APP == "SC" && $app_id=="SC") {
							$scrObject = $this->SearchPath( $scr_arr[UR_PATH] );
							if ( is_null( $scrObject ) )
								continue;

							if ( PEAR::isError( $ret = $scrObject->UserPermitted( $U_ID ) ) )
								return $ret;

							if ( $ret & UR_BOOL_TRUE )
								$menuLinks[$app_id][]=$scr_id;
						} else {
							//$val = $srcObject->GetUserRightValue( $U_ID, $scr_arr[UR_PATH] );
							$val = $this->__GetRightValue( $U_ID, $scr_arr[UR_PATH], UR_USER_ID );
							if ($val[UR_VALUE] | ( isset( $val[UR_GROUPRIGHTS] ) ? $val[UR_GROUPRIGHTS] : 0 ))
								$menuLinks[$app_id][]=$scr_id;
						}
					}
					else
						$menuLinks[$app_id][]=$scr_id;
				}
			}

			return $menuLinks;
		}

		// Render Path functions


		/**
		 * Render Rights Path
		 *
		 * @param array $data
		 * @return string or PEAR::Error
		 */
		function RenderPath( &$data )
		{
			$this->__renderOptions = $this->__loadRenderOptions( $data );
			if ( is_null( $this->__renderOptions ) )
				return PEAR::raiseError( "UR_MANAGER: Unable to load render options array." );

			$ret = $this->__RenderRenderOptions();

			$startObject = $this->SearchPath( $this->__renderOptions[UR_PATH] );
			if ( is_null( $startObject ) )
				return $ret;

			$loaded = $this->__loadAccessRightsCache( );
			if ( PEAR::isError( $loaded ) )
				return $loaded;

			$ret .= $this->__RStart( $data );
			
			$result = $startObject->Render( $data );
			if ( PEAR::isError( $result ) )
				return $result;

			$ret .= $result;

			$ret .= $this->__RFinish( $data );

			return $ret;
		}

		/**
		 * Grants or revokes global rights
		 *
		 * @param string $ID User or group id
		 * @param string $IDtype UR_USER_ID or UR_GROUP_ID
		 * @param string $path Start path
		 * @param integer $action UR_GRANT or UR_REVOKE
		 */
		function SetGlobalRightsPath( $ID, $IDtype, $path, $action )
		{
			$startObject = $this->SearchPath( $path );

			if ( is_null( $startObject ) )
				return $ret;

			if ( PEAR::isError( $ret = $this->__StartTransaction( ) ) )
				return $ret;

			if ( PEAR::isError( $ret = $startObject->SetGlobalRights( $ID, $IDtype, $action ) ) )
			{
				if ( PEAR::isError( $ret2 = $this->__RollbackTransaction( ) ) )
					return $ret2;

				return $ret;
			}

			if ( PEAR::isError( $ret = $this->__CommitTransaction( ) ) )
				return $ret;
		}

		/**
		 * Saves Rights Path data into the database
		 *
		 * @param array $data
		 * @return boolean or PEAR::Error
		 */
		function SavePath( &$data )
		{
			$this->__renderOptions = $this->__loadRenderOptions( $data );

			if ( is_null( $this->__renderOptions ) )
				return $ret;

			$startObject = $this->SearchPath( $this->__renderOptions[UR_PATH] );

			if ( is_null( $startObject ) )
				return $ret;

			if ( $this->__renderOptions[UR_ID] != UR_SYS_ID )
			{
				$loaded = $this->__loadAccessRightsCache( );
				if ( PEAR::isError( $loaded ) )
					return $loaded;
			}

			if ( PEAR::isError( $ret = $this->__StartTransaction( ) ) )
				return $ret;

			if ( PEAR::isError( $ret = $startObject->Save( $data ) ) )
			{
				if ( PEAR::isError( $ret2 = $this->__RollbackTransaction( ) ) )
					return $ret2;

				return $ret;
			}

			if ( PEAR::isError( $ret = $this->__CommitTransaction( ) ) )
				return $ret;
		}

		// Render Item functions

		/**
		 * Returns rendered form for item
		 *
		 * @param array $data
		 * @return string or PEAR::Error
		 */
		function RenderItem( &$data )
		{
			$this->__renderOptions = $this->__loadRenderItemOptions( $data );
			if ( is_null( $this->__renderOptions ) )
				return PEAR::raiseError( "UR_MANAGER: Unable to load render options array." );

			$ret = $this->__RenderRenderOptions();

			$startObject = $this->SearchPath( $this->__renderOptions[UR_PATH] );
			if ( is_null( $startObject ) )
				return $ret;

			$loaded = $this->__LoadItemsCache( isset( $this->__renderOptions[UR_COPYFROM] ) ? $this->__renderOptions[UR_COPYFROM] : null );
			if ( PEAR::isError( $loaded ) )
				return $loaded;

			$ret .= $startObject->RenderItem( $data );

			return $ret;
		}

		/**
		 * Saves Item data into the database
		 *
		 * @param array $data
		 * @return boolean or PEAR::Error
		 */
		function SaveItem( &$data )
		{
			$this->__renderOptions = $this->__loadRenderItemOptions( $data );
			if ( is_null( $this->__renderOptions ) )
				return $ret;

			$ret = $this->__RenderRenderOptions();

			$startObject = $this->SearchPath( $this->__renderOptions[UR_PATH] );
			if ( is_null( $startObject ) )
				return $ret;

			$loaded = $this->__LoadItemsCache( );
			if ( PEAR::isError( $loaded ) )
				return $loaded;

			if ( PEAR::isError( $ret = $this->__StartTransaction( ) ) )
				return $ret;

			if ( PEAR::isError( $ret = $startObject->SaveItem( $data ) ) )
			{
				if ( PEAR::isError( $ret2 = $this->__RollbackTransaction( ) ) )
					return $ret2;

				return $ret;
			}

			if ( PEAR::isError( $ret = $this->__CommitTransaction( ) ) )
				return $ret;
		}
		
		
		/**
		 * Set the hard link for right2right (ACCESSRIGHTS_LINK)
		 * DELETE ALL setted rights for srcPath srcObjectId
		 * if link for this source already exists it will be replaced
		 */
		function SetRightsLink ($srcPath, $srcObjectId, $destPath, $destObjectId, $justCopy = false) {
			global $ur_canLinkPaths;
			global $qr_ur_setRightsLink;
			global $qr_ur_copyRightsLink;
			
			if (!in_array ($destPath, $ur_canLinkPaths))
				return PEAR::raiseError ("Path $srcPath cannot be linked to other path");
			
			$params = array (
				"SRC_AR_PATH" => $srcPath,
				"SRC_AR_OBJECT_ID" => $srcObjectId,
				"DEST_AR_PATH" => $destPath, 
				"DEST_AR_OBJECT_ID" => $destObjectId
			);
			
			$sql = $justCopy ? $qr_ur_copyRightsLink : $qr_ur_setRightsLink;
			
			$sqlParts = split(";", $sql);
			
			foreach ($sqlParts as $cSql) {			
				$cSql = trim($cSql);
				if (empty($cSql))
					continue;
				$res = db_query( $cSql, $params );
				if ( PEAR::isError($res) ) {
					return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );
				}
			}
			
			return null;
		}
		
		function CopyRightsLink ($srcPath, $srcObjectId, $destPath, $destObjectId) {
			return $this->SetRightsLink ($srcPath, $srcObjectId, $destPath, $destObjectId, true);
		}
		
		function DeleteRightsLinksTo($srcPath, $srcObjectId) {
			global $qr_ur_deleteRightsLinksTo;
			
			$sqlParams = array ("SRC_AR_PATH" => $srcPath, "SRC_AR_OBJECT_ID" => $srcObjectId);
			$res = db_query( $qr_ur_deleteRightsLinksTo, $sqlParams );
			 if ( PEAR::isError($res) )
			 	 return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );
			 return true;
		}
		
		
		/**
		 * Return true if right can be linked with other right
		 * use for perfomance (select's ACCESSRIGHTS_LINK only when it's needed) 
		 */
		function CanBeLinked ($path) {
			global $ur_canLinkPaths;
			return in_array ($path, $ur_canLinkPaths);
		}
		
		/**
		 * Return true if right can be linked with other right
		 * use for perfomance (select's ACCESSRIGHTS_LINK only when it's needed) 
		 */
		 function GetRightsLink ($searchArr) {
		 	 global $qr_ur_getRightsLink;
		 	 
		 	 $sqlParams = $searchArr;
		 	 
		 	 $res = db_query_result( $qr_ur_getRightsLink, DB_ARRAY, $sqlParams );
			 if ( PEAR::isError($res) )
			 	 return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );
			 
			 if (!empty($res)) {
			 	 $newRes = array ();
			 	 $newRes[UR_PATH] = $res["LINK_AR_PATH"];
			 	 $newRes[UR_OBJECTID] = $res["LINK_AR_OBJECT_ID"];
			 	 return $newRes;
			 }
			 return null;
		 }
	}

	/**
	 * Container class. Special for contain many child rightss.
	 *
	 */
	class UR_RO_Container extends UR_RightsObject
	{
		/**
		 * Show container name in rendered form or not
		 *
		 * @var boolean
		 */
		var $__showname = true;

		/**
		 * Starts render
		 *
		 * @param array $data
		 * @return string
		 */
		function __RenderStart( &$data )
		{
			if ($this->__name == "app_available_pages_name")
				return "";
			//if ($this->__name == "app_name_long") {
			
			$fullPath = $this->GetFullPath( );
			
			$numcols = $this->__root->__renderOptions["NUMCOLS"];
			$ret = "";
			
			
			
			if ( $this->__showname && strlen( $this->__name ) ) {
				$offset = (substr_count($fullPath,UR_DELIMITER) - 2)*UR_CONTAINER_OFFSET;
				if ($this->__name == "app_name_long") {
					$appScreens = $this->__root->__screens[$this->__application];
					$firstScreen = current($appScreens);
					$screenFullPath = $firstScreen["AR_PATH"];	
						
					$ids = $this->__root->__renderOptions[UR_ID];
					$id = $ids;
					
					$edited = $this->__root->__renderOptions[UR_EDITED];
					$admin = $this->__root->CheckMask( $this->__root->GetGroupRightValue( $id, $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ) ), UR_FULL_RIGHTS ) && $this->__mayBeDisabled;
					
					//$input = "<script language=JavaScript>toggleCbRightsControluserAccessRights.addCheckbox( 'userAccessRights[$ids][$screenFullPath]') </script>" . $input;
					
					$ret .= "<tr id='AppTitle_" . $this->__application . "' class='AppTitleTr'>";
					$ret .= "<th class='InTitle' scope='col'><div class=\"RightsCellContent\" style=\"padding-left: {$offset}px!important\">".Localization::get( $this->__name, $this->__application ) ."</th>";
					
					$isUser = false;
					switch( $this->__root->__renderOptions[UR_ACTION] )
					{
						case UR_ACTION_EDITGROUP:
						case UR_ACTION_EDITUSER:
							$field = $data["FIELD"];
							$type = ($data["ACTION"] == UR_ACTION_EDITGROUP || $data["ACTION"] == UR_ACTION_VIEWGROUP) ? IDT_GROUP : IDT_USER;
							
							$valueRow = $this->__root->__GetRightValue( $ids, $screenFullPath, $type );
							if ( PEAR::isError( $valueRow ) )
								return $valueRow;
							
							if ( !$edited || $admin )
								$value = $valueRow[UR_VALUE];
							else
								$value = isset( $data[$ids][$screenFullPath] ) ? $data[$ids][$screenFullPath] : null;
							
							$checked = ( $value) ? " checked" : "";
							$disabledStr = $admin ? "disabled" : "";
							
							$groupValue = (( $valueRow[UR_VIEWGROUPVALUE]*1 ) & (empty($this->__triggerValues) || $this->__triggerValues[UR_TRIGGER_ON]));
							$inputStyle = ($type == UR_USER_ID && $groupValue) ? "class='GroupValue'" : "";
							
							$groupSpan = ($type == UR_USER_ID) ?
								"<span class='". (($groupValue) ? "CheckedImg" : "UnCheckedImg" ) ." AlignCenter'></span>"
								: "";
							
							$input = "<input $inputStyle id=\"${field}[$ids][$screenFullPath]\" name=\"${field}[$ids][$screenFullPath]\" value=1 onClick='showHideRightsBlock(\"" . $this->__application . "\", this)' $checked $disabledStr type=checkbox>";
							$ret .= "<td class='InTitle'><div class='RightCbColumn'>$input</div></td>";
							if ($groupSpan)
								$ret .= "<th class='InTitle'>" . $groupSpan . "</th>";
							break;
						case UR_ACTION_VIEWUSER:
							$isUser = true;
						case UR_ACTION_VIEWGROUP:
							foreach( $ids as $id )
							{
								$tp = ($isUser) ? UR_USER_ID : UR_GROUP_ID;
								$valueRow = $this->__root->__GetRightValue( $id, $screenFullPath, $tp );
								$needCheck = $valueRow[UR_VALUE];
								if ($isUser) {
									$groupValue = ($valueRow[UR_GROUPRIGHTS] & ($this->__triggerValues[UR_TRIGGER_ON] || empty($this->__triggerValues)));
									$needCheck = $needCheck || $groupValue;
								}
								$valueImg = "<span class='". ($needCheck ? "CheckedImg" : "UnCheckedImg" ) ." AlignCenter'></span>";
								
								$ret .= "<td class='InTitle' style='text-align: center'>" . $valueImg . "</td>";
							}
							break;
					}
					$ret .= "</tr>";
					
					$offset = (substr_count($fullPath,UR_DELIMITER))*UR_CONTAINER_OFFSET;
					if ( !is_null( $this->__comment ) )
					{
						$numcols = $this->__root->__renderOptions["NUMCOLS"];
						$ret .= "<tr><th scope='col'><div style='padding-top: 5px!important; padding-bottom:0px; padding-left: ".$offset."px!important;'><dl style='margin:0px; padding:0px' class=\"Note SmallFontNote\"><dd>";
						$ret .= Localization::get( $this->__comment, $this->__application );
						$ret .= "</dl></dd>";
						$ret .= "</th>";

						for ( $i = 1; $i < $numcols; $i++ )
							$ret .= "<th>&nbsp;</th>";

						$ret .= "</tr>\n";
					}
				} else {
					$ret .= "<tr class='SeparatedRow ". ( substr_count($fullPath,UR_DELIMITER) >= 3 ? "RightsSubContainer" : "RightsContainer"  ) ."'><th scope='col' colspan=$numcols><div class=\"RightsCellContent\" style=\"padding-left: {$offset}px!important\">".Localization::get( $this->__name, $this->__application ) ."</th></tr>\n";
				}
			}

			return $ret;
		}
		
		/**
		 * Finish render
		 *
		 * @param array $data
		 * @return string
		 */
		function __RenderFinish( &$data )
		{
			$fullPath = $this->GetFullPath( );
			$depth = substr_count($fullPath, UR_DELIMITER);

			$numcols = $this->__root->__renderOptions["NUMCOLS"];

			if ( $depth == 2 )
				return "<tr><td colspan=$numcols class=\"RightsSeparator\">&nbsp;</td></tr>";
		}

		/**
		 * Constructor
		 *
		 * @param string $id Object ID
		 * @param string $name Object Name
		 * @param string $app Application
		 * @param boolean $showname Show name or not
		 * @return UR_RO_Container
		 */
		function UR_RO_Container( $id, $name, $app="", $showname = true )
		{
			$this->__id = $id;
			$this->__name = $name;
			$this->__application = $app;
			$this->__showname = $showname;
		}

	}

	/**
	 * Screen container class which contains other rights objects. Users are permitted to access to the screen if they have access to one or more childs of this container
	 *
	 */
	class UR_RO_ScreenContainer extends UR_RO_Container
	{
		/**
		 * Short screen name
		 *
		 * @var string
		 */
		var $__ui_name;

		/**
		 * PHP file
		 *
		 * @var unknown_type
		 */
		var $__scr_page;

		/**
		 * Always visible in menu
		 *
		 * @var unknown_type
		 */
		var $__alwaysVisible = false;

		/**
		 * Register application screen in the /ROOT
		 *
		 * @return boolean
		 */
		function __ActionOnConnect( )
		{
			$this->__root->registerAppScreen( $this->__application, $this->__id, array( SCR_NAME=>Localization::sliceLocalizationArray( $this->__application, $this->__name ), SCR_UI_NAME=>Localization::sliceLocalizationArray( $this->__application, $this->__ui_name ), SCR_PAGE=>$this->__scr_page, SCR_TARGET=>null, UR_PATH=>$this->GetFullPath(), UR_OBJECT=>$this->GetFullPath() ) );
			return true;
		}

		/**
		 * Checks if user permitted to access to the screen
		 *
		 * @param unknown_type $U_ID
		 * @return unknown
		 */
		function UserPermitted( $U_ID )
		{
			$result = 0;

			if ( $this->__alwaysVisible )
				return 1;

			if ( count( $this->__childs ) )
			{
				foreach( $this->__childs as $child )
				{
					if ( PEAR::isError( $ret = $child->UserPermitted( $U_ID ) ) )
						return $ret;
					else
						$result |= $ret;
				}
			}

			return $result;
		}

		/**
		 * Constructor
		 *
		 * @param string $id Object ID
		 * @param string $name Long Name
		 * @param string $ui_name Short Name
		 * @param string $scr_page php file name
		 * @param string $app Application
		 * @param boolean $showname Show container name in the rendered form or not
		 * @return UR_RO_ScreenContainer
		 */
		function UR_RO_ScreenContainer( $id, $name, $ui_name, $scr_page, $app="", $showname = true )
		{
			$this->__id = $id;
			$this->__name = $name;
			$this->__ui_name = $ui_name;
			$this->__scr_page = $scr_page;
			$this->__application = $app;
			$this->__showname = $showname;
		}

		/**
		 * Sets screen is always visible in user menu or not
		 *
		 * @param boolean $value
		 */
		function SetVisibility( $value )
		{
			$this->__alwaysVisible = $value;
		}
	}

	/**
	 * Trigger rights: ( UR_FULL_RIGHTS / UR_NO_RIGHTS by default )
	 *
	 */
	class UR_RO_Trigger extends UR_RightsObject
	{
		// private functions

		var $__rightsData = false;
		var $__value = 0;

		var $__triggerValues = array( UR_TRIGGER_ON => UR_FULL_RIGHTS, UR_TRIGGER_OFF => UR_NO_RIGHTS );

		var $__mayBeDisabled = true;

		/**
		 * Saves object data to the database
		 *
		 * @param array $data
		 * @return boolean or PEAR::Error
		 */
		function __SaveMe( $data )
		{
			$fullPath = $this->GetFullPath( );

			$id = ( $this->__root->__renderOptions[UR_ID] != UR_SYS_ID ) ? $this->__root->__renderOptions[UR_ID] : $this->__root->__renderOptions[UR_REAL_ID];

			$admin = false;

			switch( $this->__root->__renderOptions[UR_ACTION] )
			{
					case UR_ACTION_EDITGROUP:
						$type = UR_GROUP_ID;
						break;

					case UR_ACTION_EDITUSER:

						$admin = $this->__root->IsUserGlobalAdministrator( $id ) && $this->__mayBeDisabled;

						$type = UR_USER_ID;
						break;
			}

			if ( $admin )
				return true;

			$ret = $this->__root->__SaveRightValue( $fullPath, $id, $type, $this->__value );

			if ( PEAR::isError( $ret ) )
				return $ret;

			return true;
		}

		/**
		 * Render a checkbox
		 *
		 * @param string $id User or Group ID
		 * @param array $data Data array
		 * @param string $type IDT_USER, IDT GROUP
		 * @param boolean $edited If form already edited
		 * @return string or PEAR::Error
		 */
		function __showCheckBox( $id, &$data, $type, $edited, $admin = false )
		{
			$fullPath = $this->GetFullPath( );

			$ret = "<td align=center><div class='RightCbColumn'>";

			$valueRow = $this->__root->__GetRightValue( $id, $fullPath, $type );
			if ( PEAR::isError( $valueRow ) )
				return $valueRow;

			if ( !$edited || $admin )
				$value = $valueRow[UR_VALUE];
			else
				$value = isset( $data[$id][$fullPath] ) ? $data[$id][$fullPath] : null;

			$checked = ( $value == $this->__triggerValues[UR_TRIGGER_ON] ) ? " checked" : "";

			$disabledStr = $admin ? "disabled" : "";

			if  ( $this->__mayBeDisabled )
				$ret .= "<script language=JavaScript>toggleCbRightsControl{$this->__root->__renderOptions[UR_FIELD]}.addCheckbox( '".$this->__root->__renderOptions[UR_FIELD]."[$id][$fullPath]') </script>";

			$ret .= "<input type=checkbox id='".$this->__root->__renderOptions[UR_FIELD]."[$id][$fullPath]' name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$fullPath]' value=". $this->__triggerValues[UR_TRIGGER_ON] . " $checked $disabledStr ";

			if  ( !$this->__mayBeDisabled )
				$ret .= " onClick=\"toggleCbRightsControl{$this->__root->__renderOptions[UR_FIELD]}.toggleStates( '".$this->__root->__renderOptions[UR_FIELD]."[$id][$fullPath]'  )\" ";

			$ret .= " >";

			$ret .= "</div></td>";

			if ( $type == UR_USER_ID )
				$ret .= "<td align=center><span class='". ( ( $valueRow[UR_VIEWGROUPVALUE]*1 ) & $this->__triggerValues[UR_TRIGGER_ON] ? "CheckedImg" : "UnCheckedImg" ) ." AlignCenter'></td>";

			return $ret;
		}

		/**
		 * Render option for view only
		 *
		 * @param string $id User or Group ID
		 * @param string $type IDT_USER, IDT GROUP
		 * @return string or PEAR::Error
		 */
		function __showViewOption( $id, $type )
		{
			$ret = "<td class=\"AlignCenter\">";

			$value = $this->__Permitted( $id, $type, $this->__triggerValues[UR_TRIGGER_ON] ) ? 1 : 0;
			if ( PEAR::isError( $value ) )
				return $value ;

			/*<span class='";
			$ret .= $value ? "CheckedImg" : "UnCheckedImg";
			$ret .= "'>"; */

			$src = $value ? "../../../common/html/cssbased/res/images/checked.gif" : "../../../common/html/cssbased/res/images/unchecked.gif";
			$ret .= "<img src=\"$src\" />";
			$ret .= "</td>";

			return $ret;
		}

		/**
		 * Starts rendering
		 *
		 * @param array $data
		 * @return string or PEAR::Error
		 */
		function __RenderStart( &$data )
		{
			$fullPath = $this->GetFullPath( );

			$offset = (substr_count($fullPath,UR_DELIMITER) - 2)*UR_CONTAINER_OFFSET;
			$ret = "<tr><th scope='col'><div class=noPadding style='padding-left: ".$offset."px!important;'>". Localization::get( $this->__name, $this->__application ) ."</div></th>";

			$ids = $this->__root->__renderOptions[UR_ID];
			if ( !is_array( $ids ) )
				$ids = array( 0=>$ids );

			$edited = $this->__root->__renderOptions[UR_EDITED];

			foreach( $ids as $id )
			{
				switch( $this->__root->__renderOptions[UR_ACTION] )
				{
					case UR_ACTION_EDITGROUP:

						$admin = $this->__root->CheckMask( $this->__root->GetGroupRightValue( $id, $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ) ), UR_FULL_RIGHTS ) && $this->__mayBeDisabled;

						$ret .= $this->__showCheckBox( $id, $data, UR_GROUP_ID, $edited, $admin );

						break;

					case UR_ACTION_EDITUSER:

						$admin = $this->__root->IsUserGlobalAdministrator( $id ) && $this->__mayBeDisabled;

						$ret .= $this->__showCheckBox( $id, $data, UR_USER_ID, $edited, $admin );

						break;

					case UR_ACTION_VIEWUSER:

						$admin = $this->__root->IsUserGlobalAdministrator( $id ) && $this->__mayBeDisabled;

						$ret .= $this->__showViewOption( $id, UR_USER_ID );

						break;

					case UR_ACTION_VIEWGROUP:

						$ret .= $this->__showViewOption( $id, UR_GROUP_ID );

						break;
				}
			}

			$ret .= "</tr>\n";

			if ( !is_null( $this->__comment ) )
			{
				$numcols = $this->__root->__renderOptions["NUMCOLS"];
				$ret .= "<tr><th scope='col'><div class=noPadding style='padding-left: ".$offset."px!important;'><dl class=\"Note SmallFontNote\"><dd>";
				$ret .= Localization::get( $this->__comment, $this->__application );
				$ret .= "</dl></dd>";
				$ret .= "</th>";

				for ( $i = 1; $i < $numcols; $i++ )
					$ret .= "<th>&nbsp;</th>";

				$ret .= "</tr>\n";
			}

			return $ret;
		}

		/**
		 * Render Finish
		 *
		 * @param string $data
		 * @return string or PEAR::Error
		 */
		function __RenderFinish( &$data )
		{
			return "";
		}

		/**
		 * Grants or Revokes Trigger Permissions
		 *
		 * @param string $ID
		 * @param string $IDtype
		 * @param string $action
		 */
		function __SetGlobalRights( $ID, $IDtype, $action )
		{
			$fullPath = $this->GetFullPath( );

			$ret = $this->__root->__SaveRightValue( $fullPath, $ID, $IDtype, $action == UR_GRANT ? $this->__triggerValues[UR_TRIGGER_ON] : $this->__triggerValues[UR_TRIGGER_OFF] );

			if ( PEAR::isError( $ret ) )
				return $ret;

			return true;
		}

		// public

		/**
		 * Constructor
		 *
		 * @param string $id Object ID
		 * @param string $name Object Name
		 * @param string $app Application
		 * @return UR_RO_Trigger
		 */
		function UR_RO_Trigger( $id, $name, $app="", $triggerON = null, $triggerOFF = null, $mayBeDisabled = null )
		{
			$this->__id = $id;
			$this->__name = $name;
			$this->__application = $app;

			if ( !is_null( $triggerON ) )
				$this->__triggerValues[UR_TRIGGER_ON] = $triggerON;

			if ( !is_null( $triggerOFF ) )
				$this->__triggerValues[UR_TRIGGER_OFF] = $triggerOFF;

			if ( !is_null( $mayBeDisabled ) )
				$this->__mayBeDisabled = $mayBeDisabled;
		}

		/**
		 * Check user's permissions
		 *
		 * @param stirng $U_ID
		 * @return boolean or PEAR::Error
		 */
		function UserPermitted( $U_ID )
		{
			$ret = $this->__root->GetUserRightValue( $U_ID, $this->GetFullPath() );

			if ( PEAR::isError( $ret ) )
				return $ret;

			if ( $this->CheckMask( $ret,  $this->__triggerValues[UR_TRIGGER_ON] ) )
				return true;

			return false;
		}

		/**
		 * Check group's permissions
		 *
		 * @param stirng $U_ID
		 * @return boolean or PEAR::Error
		 */
		function GroupPermitted( $ID )
		{
			$ret = $this->__root->GetGroupRightValue( $ID, $this->GetFullPath() );

			if ( PEAR::isError( $ret ) )
				return $ret;

			if ( $this->CheckMask( $ret,  $this->__triggerValues[UR_TRIGGER_ON] ) )
				return true;

			return false;
		}

		/**
		 * Validates entered data
		 *
		 * @param array $data
		 * @return boolean
		 */
		function Validate( &$data )
		{
			$fullPath = $this->GetFullPath( );

			$id = $this->__root->__renderOptions[UR_ID];

			$value = isset( $data[$id][$fullPath] ) ? $data[$id][$fullPath] : null;

			$this->__value = ( $value == $this->__triggerValues[UR_TRIGGER_ON] ) ? $this->__triggerValues[UR_TRIGGER_ON] : $this->__triggerValues[UR_TRIGGER_OFF];

			return true;
		}

		// Функции визуализации

		/**
		 * Renders item
		 *
		 * @param array $data
		 * @return string or PEAR::Error
		 */
		function RenderItem( &$data )
		{
			$fullPath = $this->GetFullPath();

			$items = $this->__root->__cachedItems;
			$edited = $this->__root->__renderOptions[UR_EDITED];

			$render = "<table width=100%>";

			$render .= "<tr class='RightsFolder'>";

			if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
				$render .= "<th align=left>".Localization::get('app_treename_title')."</th><th align=left>".Localization::get('app_treeuserid_title')."</th>";
			else
				$render .= "<th align=left>".Localization::get('app_treegroupname_label')."</th>";

			$render .= "<th align=left>".Localization::get( $this->__name, $this->__application )."</th>";

			$render .= "</tr>\n";

			foreach( $items as $id=>$row )
			{
				if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
					$render .= "<tr><td>".$row[UR_ITEMNAME]."</td><td>$row[UR_ID]</td>";
				else
					$render .= "<tr><td>".$row[UR_ITEMNAME]."</td>";

				if ( !$edited )
					$value = $row["AR_VALUE"];
				else
					$value = isset( $data[$id][$fullPath] ) ? $data[$id][$fullPath] : null;

				$checked = ( $value == $this->__triggerValues[UR_TRIGGER_ON] ) ? " checked" : "";

				$render .= "<td><input type=checkbox name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$fullPath]' value=". $this->__triggerValues[UR_TRIGGER_ON] ." $checked></td>";

				$render .= "</tr>";
			}

			$render .= "</table>";

			return $render;
		}

		// сохранение данных только этого класса

		/**
		 * Saves item's permissions
		 *
		 * @param array $data
		 * @return boolean or PEAR::Error
		 */
		function SaveItem( &$data )
		{
			$fullPath = $this->GetFullPath( );

			$items = $this->__root->__cachedItems;
			$edited = $this->__root->__renderOptions[UR_EDITED];

			if ( !$edited )
				return false;

			foreach( $items as $id=>$row )
			{
				$value = isset( $data[$id][$fullPath] ) ? $data[$id][$fullPath] : null;
				$value = ( $value == $this->__triggerValues[UR_TRIGGER_ON] ) ? $this->__triggerValues[UR_TRIGGER_ON] : $this->__triggerValues[UR_TRIGGER_OFF];

				switch( $this->__root->__renderOptions[UR_ACTION] )
				{
						case UR_ACTION_EDITGROUP:
							$type = UR_GROUP_ID;
							break;

						case UR_ACTION_EDITUSER:
							$type = UR_USER_ID;
							break;
				}


				$savePath  = ( $this->__root->__renderOptions[UR_OBJECTID] != UR_SYS_ID ) ? $fullPath : $this->GetPath( ).UR_DELIMITER.$this->__root->__renderOptions[UR_REAL_ID];
				$ret = $this->__root->__SaveRightValue( $savePath, $id, $type, $value );

				if ( PEAR::isError( $ret ) )
					return $ret;
			}

			return true;
		}

	}

	/**
	 * Boolean rights
	 *
	 */
	class UR_RO_Bool extends UR_RO_Trigger
	{
		/**
		 * Constructor
		 *
		 * @param string $id Object ID
		 * @param string $name Object Name
		 * @param string $app Application
		 * @return UR_RO_Bool
		 */
		function UR_RO_Bool( $id, $name, $app="" )
		{
			parent::UR_RO_Trigger( $id, $name, $app, UR_BOOL_TRUE, UR_BOOL_FALSE );
		}
	}

	/**
	 * Screen
	 *
	 */
	class UR_RO_Screen extends UR_RO_Bool
	{
		/**
		 * Short screen name
		 *
		 * @var string
		 */
		var $__ui_name;

		/**
		 * PHP file
		 *
		 * @var unknown_type
		 */
		var $__scr_page;

		/**
		 * Register application screen in the /ROOT
		 *
		 * @return boolean
		 */
		function __ActionOnConnect( )
		{
			$this->__root->registerAppScreen( $this->__application, $this->__id, array( SCR_NAME=>Localization::sliceLocalizationArray( $this->__application, $this->__name ), SCR_UI_NAME=>Localization::sliceLocalizationArray( $this->__application, $this->__ui_name ), SCR_PAGE=>$this->__scr_page, SCR_TARGET=>null, UR_PATH=>$this->GetFullPath(), UR_OBJECT=>$this->GetFullPath() ) );
			return true;
		}
		
		function __RenderStart (&$data) {
			return "";
		}

		/**
		 * Constructor
		 *
		 * @param string $id Object ID
		 * @param string $name Long Name
		 * @param string $ui_name Short Name
		 * @param string $scr_page php file name
		 * @param string $app Application
		 * @param boolean $showname Show container name in the rendered form or not
		 * @return UR_RO_ScreenContainer
		 */
		function UR_RO_Screen( $id, $name, $ui_name, $scr_page, $app="", $showname = true )
		{
			parent::UR_RO_Bool( $id, $name, $app );

			$this->__id = $id;
			$this->__name = $name;
			$this->__ui_name = $ui_name;
			$this->__scr_page = $scr_page;
			$this->__application = $app;
			$this->__showname = $showname;
		}
	}

	/**
	 * Can create root folders right
	 *
	 */
	class UR_RO_RootFolder extends UR_RO_Trigger
	{
		/**
		 * Constructor
		 *
		 * @param string $id Object ID
		 * @param string $name Object Name
		 * @param string $app Application
		 * @return UR_RO_RootFolder
		 */
		function UR_RO_RootFolder( $id, $name, $app="" )
		{
			parent::UR_RO_Trigger( $id, $name, $app, UR_TREE_FOLDER | UR_TREE_WRITE | UR_TREE_READ , UR_NO_RIGHTS );
		}
	}

	/**
	 * Folders Tree
	 *
	 */
	class UR_RO_FoldersTree extends UR_RightsObject
	{
		/**
		 * Folder tree descriptor
		 *
		 * @var FoldersTreeDescriptor
		 */
		var $__descriptor;

		/**
		 * Rights Array
		 *
		 * @var array
		 */
		var $__rightsArr = array( UR_TREE_READ=>0, UR_TREE_WRITE=>0, UR_TREE_FOLDER=>0 );

		/**
		 * Rights Names array
		 *
		 * @var array
		 */
		var $__rightsNames = array( UR_TREE_READ=>'app_readaccess_name', UR_TREE_WRITE=>'app_writeaccess_name', UR_TREE_FOLDER=>'app_folderaccess_name' );

		/**
		 * PRIVATE: Values for saving
		 *
		 * @var array
		 */
		var $__values = array();

		/**
		 * Sets Rights path to the descriptor
		 *
		 * @return boolean
		 */
		function __ActionOnConnect( )
		{
			$this->__descriptor->SetRightsPath( $this->GetFullPath( ) );
			return true;
		}

		/**
		 * Saves object data to the database
		 *
		 * @param array $data
		 * @return boolean or PEAR::Error
		 */
		function __SaveMe( $data )
		{
			$id = $this->__root->__renderOptions[UR_ID] != UR_SYS_ID ? $this->__root->__renderOptions[UR_ID] : $this->__root->__renderOptions[UR_REAL_ID];

			$admin = false;

			switch( $this->__root->__renderOptions[UR_ACTION] )
			{
					case UR_ACTION_EDITGROUP:
						$type = UR_GROUP_ID;
						break;

					case UR_ACTION_EDITUSER:
						$admin = $this->__root->IsUserGlobalAdministrator( $id );
						$type = UR_USER_ID;
						break;
			}

			if ( $admin )
				return true;

			foreach( $this->__values as $path=>$value )
			{
				$ret = $this->__root->__SaveRightValue( $path, $id, $type, $value );

				if ( PEAR::isError( $ret ) )
					return $ret;
			}

			return true;
		}

		/**
		 * Replaces SQL pseudo-names with real object names
		 *
		 * @param string $query
		 * @return string Prepared query
		 */
		function __applySQLObjectNames( $query )
		{
			if ($this->__descriptor->folderDescriptor->folder_specialstatus_field) {
				$query = str_replace( 'FOLDER_SPECIALSTATUS_STR', ", " . $this->__descriptor->folderDescriptor->folder_specialstatus_field . ' AS SPECIALSTATUS', $query);
				$query = str_replace( 'FOLDER_SPECIALSTATUSORDER_STR', $this->__descriptor->folderDescriptor->folder_specialstatus_field . ',', $query);
			} else {
				$query = str_replace( 'FOLDER_SPECIALSTATUS_STR', '', $query);
				$query = str_replace( 'FOLDER_SPECIALSTATUSORDER_STR', '', $query);
			}
			
			
			$query = str_replace( 'TREE_FOLDER_TABLE', $this->__descriptor->folderDescriptor->tableName, $query );
			$query = str_replace( 'FOLDER_ID_FIELD', $this->__descriptor->folderDescriptor->folder_id_field, $query );
			$query = str_replace( 'FOLDER_NAME_FIELD', $this->__descriptor->folderDescriptor->folder_name_field, $query );
			$query = str_replace( 'FOLDER_PARENT_FIELD', $this->__descriptor->folderDescriptor->folder_parent_field, $query );
			$query = str_replace( 'FOLDER_STATUS_FIELD', $this->__descriptor->folderDescriptor->folder_status_field, $query );

			return $query;
		}

		/**
		 * Gets folder list from database
		 *
		 * @return array Folders list
		 */
		function __GetFoldersList( )
		{
			global $qr_ur_getFoldersList;
			
			$query = $this->__applySQLObjectNames( $qr_ur_getFoldersList );

			$res = db_query( $query, array( ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

			$foldersList = array();

			while ( $row = db_fetch_array( $res ) )
				$foldersList[ $row["FOLDER_PARENT"] ][ $row["FOLDER_ID"] ] = $row;
			
			db_free_result($res);

			return $foldersList;
		}

		/**
		 * Render checkboxes for folder
		 *
		 * @param string $name Object Name
		 * @param string $id User or Group ID
		 * @param string $folder_id Folder ID
		 * @param array $data Data array
		 * @param string $type IDT_USER, IDT_GROUP
		 * @param string $edited Edited flag
		 * @param string $admin Admin flag
		 * @param string $specialStatus special status flag
		 * @return string or PEAR::Error
		 */
		function __showCheckBoxes( $name, $id, $folder_id, &$data, $type, $edited, $admin = false, $specialStatus = false )
		{
			$folderPath = $this->GetFullPath( ).UR_DELIMITER.$folder_id;

			$valueRow = $this->__root->__GetRightValue( $id, $folderPath, $type );
			if ( PEAR::isError( $valueRow ) )
				return $valueRow;

			$rVal = $this->__rightsArr;

			if ( !$edited || $admin )
			{
				$value = is_null( $valueRow ) ? 0 : $valueRow[UR_VALUE];

				if ( $this->CheckMask( $value, UR_TREE_READ ) )
					$rVal[UR_TREE_READ] = 1;

				if ( $this->CheckMask( $value, UR_TREE_WRITE ) )
					$rVal[UR_TREE_WRITE] = 1;

				if ( $this->CheckMask( $value, UR_TREE_FOLDER ) )
					$rVal[UR_TREE_FOLDER] = 1;
			}
			else
			{
				if ( isset( $data[$id][$folderPath] ) )
					foreach( $data[$id][$folderPath] as $key=>$value )
						$rVal[intval($key)] = $value;
			}

			$ret = "<td>";
			$ret .= "<table width=100% cellsapcing=0 cellpadding=0 class='RightsTable'><tr>";

			$disabledStr = ($admin) ? " disabled" : "";
				 
			if ($specialStatus == FOLDER_SPECIALSTATUS_PM_ROOT) {
				$ret .= "<td colspan=3 align=center>" . Localization::get('app_inheritedprojects_label', AA_APP_ID) . " </td>";
			} else {
				foreach( $rVal as $key=>$value )
				{
					$checked = ( $value == 1 ) ? " checked" : "";
					if ($specialStatus)
						$ret .= "<td align=center><div class='RightCbColumn'><input disabled=true type=checkbox value='1' $checked></div></td>";
					else
						$ret .= "<td align=center><div class='RightCbColumn'><script language=JavaScript> appRightsControl".$this->__application.".addCheckbox( $key, '".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]'); toggleCbRightsControl{$this->__root->__renderOptions[UR_FIELD]}.addCheckbox( '".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]') </script><input type=checkbox name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' id='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' value='1' $checked onClick=\"new_updateMultiAppFolderCb( this, '".$this->__root->__renderOptions[UR_FIELD]."', '$id', '$folderPath', $key )\" $disabledStr></div></td>";
				}
			}

			$ret .= "</tr>\n</table></td>";

			if ( $type == UR_USER_ID )
			{
				$ret .= "<td align=center>";

				$value = is_null( $valueRow ) ? 0 : $valueRow[UR_VIEWGROUPVALUE];

				$res = "";
				foreach( $this->__rightsNames as $rKey=>$rValue )
					$res .= $this->CheckMask( $value, $rKey ) ? substr( Localization::get($rValue, $this->__application), 0, 1 ) : "";

				$ret .= $res =="" ? "-" : $res;

				$ret .= "</td>";
			}


			return $ret;
		}

		/**
		 * Render option for view only
		 *
		 * @param string $name Object Name
		 * @param string $id User or Group ID
		 * @param string $type IDT_USER, IDT GROUP
		 * @return string or PEAR::Error
		 */
		function __showViewOption( $name, $id, $folder_id, $type )
		{
			$folderPath = $this->GetFullPath( ).UR_DELIMITER.$folder_id;

			$ret = "<td class=\"AlignCenter\">";

			$result = null;

			if ( $type == UR_USER_ID )
				$result = $this->__root->GetUserRightValue( $id, $folderPath );
			else
			if ( $type == UR_GROUP_ID )
				$result = $this->__root->GetGroupRightValue( $id, $folderPath );

			if ( PEAR::isError( $result ) )
				return $result;

			$tret = "";

			foreach( $this->__rightsNames as $key=>$value )
				$tret .= $this->CheckMask( $result, $key ) ? substr( Localization::get($value, $this->__application), 0, 1 ) : "";

			$ret .= strlen( $tret ) ? $tret : "-";

			$ret .= "</td>";

			return $ret;
		}

		function __RenderHeader( )
		{
			$ret = "";
			$numcols = $this->__root->__renderOptions["NUMCOLS"];
			$fullPath = $this->GetFullPath();
			$offset = (substr_count($fullPath,UR_DELIMITER) - 1)*UR_CONTAINER_OFFSET;

			$rVal = $this->__rightsArr;

			if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITGROUP || $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
			{
				$id = $this->__root->__renderOptions[UR_ID];

				$ret .= "<tr class='RightsSubContainer'><th><div class=noPadding style='margin-left: ".UR_CONTAINER_OFFSET."px!important;'>".Localization::get('app_treeavailflds_title', $this->__application)."</div></th><th>";

				if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER)
					$disabled = $this->__root->IsUserGlobalAdministrator( $this->__root->__renderOptions[UR_ID] ) ? " disabled " : "";
				else
					if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITGROUP )
					{
						$disabled = $this->__root->CheckMask( $this->__root->GetGroupRightValue( $this->__root->__renderOptions[UR_ID], $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ) ), UR_FULL_RIGHTS ) ?  " disabled " : "";
					}

				$ret .= "<table width=100% cellsapcing=0 cellpadding=0 class='RightsTable'><tr>";

				foreach( $rVal as $key=>$value )
					$ret .= "<td  width=33% align=center><div class='RightCbColumn'><script language=JavaScript>toggleCbRightsControl{$this->__root->__renderOptions[UR_FIELD]}.addCheckbox( 'userRightsCBAll$key{$this->__application}') </script><input onClick='appRightsControl".$this->__application.".setAppRights(this, $key)' type=checkbox id='userRightsCBAll$key{$this->__application}' name='userRightsCBAll[$key]' value='1' $disabled><br><span>".Localization::get($this->__rightsNames[$key], $this->__application)."</span></div></td>";

				$ret .= "</tr>\n</table>";

				if ( $numcols > 2 )
					$ret .= "<td></td>";

				$ret .= "</tr>\n";

			} else
			{
				$ret .= "<tr class='RightsSubContainer'><th colspan='$numcols'><div class=noPadding style='margin-left: ".$offset."px!important;'>".Localization::get('app_treeavailflds_title', $this->__application)."</div></th></tr>";
			}

			return $ret;
		}

		function __RenderFolders( &$foldersList, $ids, &$data, $parent, $level )
		{
			$ret = "";

			if ( !isset( $foldersList[$parent] ) )
				return $ret;

			$edited = $this->__root->__renderOptions[UR_EDITED];

			foreach( $foldersList[$parent] as $folder_id=>$row )
			{
				$fullPath = $this->GetFullPath();
				$offset = (substr_count($fullPath,UR_DELIMITER) - 1)*UR_CONTAINER_OFFSET+5;

				$offset_str = str_repeat( "&nbsp;&nbsp;", $level );

				$name = $offset_str.$row["FOLDER_NAME"];
                if (array_key_exists("SPECIALSTATUS",$row)){
                	$specialStatus = $row["SPECIALSTATUS"];
                }else{
                	$specialStatus = null;
                }

				$ret .= "<tr>";
				$ret .= "<th scope='col' class='RightsFolder' style=\"background-position: {$offset}px 5px !important;\"><div class=noPadding style='margin-left: ". ($offset-5) ."px!important;'>$name</div></th>";

				foreach( $ids as $id )
				{
					switch( $this->__root->__renderOptions[UR_ACTION] )
					{
						case UR_ACTION_EDITGROUP:
							$admin = $this->__root->CheckMask( $this->__root->GetGroupRightValue( $id, $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ) ), UR_FULL_RIGHTS );

							$ret .= $this->__showCheckBoxes( $name, $id, $folder_id, $data, UR_GROUP_ID, $edited, $admin, $specialStatus );
							break;

						case UR_ACTION_EDITUSER:

							$admin = $this->__root->IsUserGlobalAdministrator( $id );

							$ret .= $this->__showCheckBoxes( $name, $id, $folder_id, $data, UR_USER_ID, $edited, $admin, $specialStatus );
							break;

						case UR_ACTION_VIEWUSER:

							$admin = $this->__root->IsUserGlobalAdministrator( $id );

							$ret .= $this->__showViewOption( $name, $id, $folder_id, UR_USER_ID );

							break;

						case UR_ACTION_VIEWGROUP:

							$ret .= $this->__showViewOption( $name, $id, $folder_id, UR_GROUP_ID );

							break;
					}
				}

				$ret .= "</tr>\n";

				if ( isset( $foldersList[ $row[ "FOLDER_ID" ] ] ) )
					$ret .= $this->__RenderFolders( $foldersList, $ids, $data, $row[ "FOLDER_ID" ], $level+1 );
			}

			return $ret;
		}

		function __RenderStart( &$data )
		{
			$numcols = $this->__root->__renderOptions["NUMCOLS"];
			$fullPath = $this->GetFullPath();

			$ret = "<tr class='RightsSubContainer'><th scope='col' colspan=$numcols><div class=noPadding style='margin-left: ". ( substr_count($fullPath,UR_DELIMITER) >= 3 ? UR_CONTAINER_OFFSET : 0  ) ."px!important;'>". Localization::get( $this->__name, $this->__application );
			$ret .= "<script language=JavaScript> var appRightsControl". $this->__application." = new new_appRightsContrainer(); </script>";
			$ret .= "</div></th></tr>\n";

			return $ret;
		}

		function __RenderFinish( &$data )
		{
			$foldersList = $this->__GetFoldersList( );
			if ( PEAR::isError( $foldersList ) )
				return $foldersList;

			$ids = $this->__root->__renderOptions[UR_ID];
			if ( !is_array( $ids ) )
				$ids = array( 0=>$ids );

			$ret = $this->__RenderHeader( );

			if ( PEAR::isError( $result = $this->__RenderFolders( $foldersList, $ids, $data, TREE_ROOT_FOLDER, 0 ) ) )
				return $result;

			$ret .= $result;

			return $ret;
		}

		/**
		 * Grants or Revokes Folders Permissions
		 *
		 * @param string $ID
		 * @param string $IDtype
		 * @param string $action
		 */
		function __SetGlobalRights( $ID, $IDtype, $action )
		{
			$foldersList = $this->__GetFoldersList( );
			if ( PEAR::isError( $foldersList ) )
				return $foldersList;

			foreach( $foldersList as $folders )
			{
				foreach( $folders as $folder_id=>$row )
				{
					$folderPath = $this->GetFullPath( ).UR_DELIMITER.$folder_id;

					$ret = $this->__root->__SaveRightValue( $folderPath, $ID, $IDtype, ( $action == UR_GRANT ) ? UR_TREE_FOLDER | UR_TREE_READ | UR_TREE_WRITE  : 0 );

					if ( PEAR::isError( $ret ) )
						return $ret;
				}
			}

			return true;
		}

		function Validate( &$data )
		{
			$foldersList = $this->__GetFoldersList( );
			if ( PEAR::isError( $foldersList ) )
				return $foldersList;

			$id = $this->__root->__renderOptions[UR_ID];

			foreach( $foldersList as $folders )
			{
				foreach( $folders as $folder_id=>$row )
				{
					$folderPath = $this->GetFullPath( ).UR_DELIMITER.$folder_id;

					$result = 0;
					foreach( array_keys( $this->__rightsArr ) as $key )
						$result |= isset( $data[$id][$folderPath]["$key"] ) ? intval( $key ): 0;

					$this->__values[$folderPath]= $result;
				}
			}

			return true;
		}

		/**
		 * Constructor
		 *
		 * @param FoldersTreeDescriptor $descriptor
		 * @param string $id
		 * @param string $name
		 * @param string $app
		 * @param boolean $showname
		 * @return UR_RO_FoldersTree
		 */
		function UR_RO_FoldersTree( &$descriptor, $id, $name, $app="", $showname = true )
		{
			$this->__id = $id;
			$this->__name = $name;
			$this->__application = $app;
			$this->__showname = $showname;
			$this->__descriptor = &$descriptor;
		}

		function __RenderItemHeader( )
		{
			$ret = "";
			$rVal = $this->__rightsArr;

			$ret = "<thead><tr>";

			if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
				$ret .= "<th>".Localization::get('app_treename_title')."</th><th>".Localization::get('app_treeuserid_title')."</th>";
			else
				$ret .= "<th>".Localization::get('app_treegroupname_label')."</th>";

			foreach( $rVal as $key=>$value )
				$ret .= "<th><input onClick='appRightsControl".$this->__application.$this->__root->__renderOptions[UR_ACTION].".setAppRights(this, $key)' type=checkbox name='userRightsCBAll[$key]' value='1'> ".Localization::get($this->__rightsNames[$key], $this->__application)."</th>";

			$ret .= "</tr></thead>\n";
			return $ret;
		}

		function RenderItem( &$data )
		{
			$items = $this->__root->__cachedItems;
			$edited = $this->__root->__renderOptions[UR_EDITED];

			$folderPath = $this->__root->__renderOptions[UR_PATH] .UR_DELIMITER. $this->__root->__renderOptions[UR_OBJECTID];

			$render = "<script language=JavaScript> var appRightsControl". $this->__application.$this->__root->__renderOptions[UR_ACTION]." = new new_appRightsContrainer(); </script>";

			$render .= "<table width=100% class='SimpleList RightsItemTable'>";

			$render .= $this->__RenderItemHeader();

			foreach( $items as $id=>$valueRow )
			{
				if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
					$render .= "<tr><td>".$valueRow[UR_ITEMNAME]."</td><td>".$valueRow['CODE']."</td>";
				else
					$render .= "<tr><td>".$valueRow[UR_ITEMNAME]."</td>";


				$rVal = $this->__rightsArr;

				if ( !$edited )
				{
					$value = is_null( $valueRow ) ? 0 : $valueRow[UR_VALUE];

					if ( ( $value & UR_TREE_READ ) == UR_TREE_READ)
						$rVal[UR_TREE_READ] = 1;

					if ( ( $value & UR_TREE_WRITE ) == UR_TREE_WRITE)
						$rVal[UR_TREE_WRITE] = 1;

					if ( ( $value & UR_TREE_FOLDER ) == UR_TREE_FOLDER )
						$rVal[UR_TREE_FOLDER] = 1;
				}
				else
				{
					if ( isset( $data[$id][$folderPath] ) )
						foreach( $data[$id][$folderPath] as $key=>$value )
							$rVal[intval($key)] = $value;

				}

				foreach( $rVal as $key=>$value )
				{
					$checked = ( $value == 1 ) ? " checked" : "";
					$disabledStr = ($this->__root->__renderOptions[UR_SPECIALSTATUS] > 0) ? "disabled=true" : "";;
					$render .= "<td><script language=JavaScript> appRightsControl".$this->__application.$this->__root->__renderOptions[UR_ACTION].".addCheckbox( $key, '".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]') </script><input $disabledStr type=checkbox name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' id='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' value='1' $checked onClick=\"new_updateMultiAppFolderCb( this, '".$this->__root->__renderOptions[UR_FIELD]."', '$id', '$folderPath', $key )\"></td>";
				}

				$render .= "</tr>";
			}

			$render .= "</table>";

			return $render;
		}

		function SaveItem( &$data )
		{
			$items = $this->__root->__cachedItems;
			$edited = $this->__root->__renderOptions[UR_EDITED];

			$folderPath = $this->__root->__renderOptions[UR_PATH] .UR_DELIMITER. $this->__root->__renderOptions[UR_OBJECTID];

			if ( !$edited )
				return false;

			foreach( $items as $id=>$row )
			{
				$value = isset( $data[$id][$folderPath] ) ? $data[$id][$folderPath] : null;

				$value = 0;
				foreach( array_keys( $this->__rightsArr ) as $key )
						$value |= isset( $data[$id][$folderPath]["$key"] ) ? intval( $key ): 0;

				switch( $this->__root->__renderOptions[UR_ACTION] )
				{
						case UR_ACTION_EDITGROUP:
							$type = UR_GROUP_ID;
							break;

						case UR_ACTION_EDITUSER:
							$type = UR_USER_ID;
							break;
				}

				$savePath  = ( $this->__root->__renderOptions[UR_OBJECTID] != UR_SYS_ID ) ? $folderPath : $this->__root->__renderOptions[UR_PATH] .UR_DELIMITER. $this->__root->__renderOptions[UR_REAL_ID];
				$ret = $this->__root->__SaveRightValue( $savePath, $id, $type, $value );

				if ( PEAR::isError( $ret ) )
					return $ret;
			}

			return true;
		}

	}

	/**
	 * Loading Webasyst Users' Rights for all hosted applications
	 */
	 
	$UR_Manager = new UR_RightsManager( );

//	$__adminAccess = &new UR_RO_Trigger( UR_ADMINISTRATOR, "app_administrator_rights_text",  AA_APP_ID, null, null, false );
//	$UR_Manager->AddChild( $__adminAccess );

	if ( isset( $host_applications ) )
	{
		$ur_applications = array();
		foreach ( $host_applications as $application )
			$ur_applications[$application] = $application;
		
		$sortList = sortApplicationList( $ur_applications ) ;
		$currentApps = (defined("CURRENT_APP")) ? split(",", CURRENT_APP) : array ();
		foreach ( $sortList as $application ) {
			if ($currentApps && !(in_array($application, $currentApps) || $application == "AA" || in_array("AA", $currentApps) || in_array("UG", $currentApps)))
				continue;
			
			if ( file_exists( WBS_PUBLISHED_DIR . UR_DELIMITER. strtoupper( $application ) . UR_DELIMITER .WBS_UR_APPCLASS_FILE ) )
				require_once( WBS_PUBLISHED_DIR . UR_DELIMITER. strtoupper( $application ) . UR_DELIMITER .WBS_UR_APPCLASS_FILE );
		}
	}

?>
