<?
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	set_time_limit(20);
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$maxDocsCount = 100;
	
	$SRC_ID = "";
	
	$appId = (!empty($fapp)) ? $fapp : "DD";
		
	pageUserAuthorization( $SCR_ID, $appId, true);
	$kernelStrings = $loc_str[$language];
	$cmStrings = $cm_loc_str[$language];
	$ugStrings  = $ug_loc_str[$language];
	$nodes = array ();
	
	if (!isset($node))
		$node = "";
	
	$sorting = "NAME asc";
	$sortStr = getColumnSortString( $sorting, $fieldsPlainDesc );
	$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
	$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true, true );
	
	if ($node == "ROOT") {
		if (checkUserAccessRights( $currentUser, "UNG", "UG", false) )
			$nodes[] = array ("text" => $ugStrings["app_name_long"], "id" => "UNG", "iconCls" => "users-folder");		
		if (checkUserAccessRights( $currentUser, "UC", "CM", false) ) {
			$nodes[] = array("text" => $cmStrings["app_name_long"], "id" => "CM");
			$nodes[] = array("text" => $cmStrings["app_cmlists_label"], "id" => "CML", "iconCls" => "lists-folder");
		}
	}
	
	if ($node == "UNG") {
		$fullObjectList = array();

		$groups = listUserGroups( $kernelStrings );
		
		foreach ( $groups as $UG_ID=>$groupData ) {
			$nodes[] = array ("id" => "UG" . $groupData["UG_ID"], "text" => $groupData["UG_NAME"], "iconCls" => "users-folder");
		}
	}
	
	if (substr($node,0,2) == "UG") {
		$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );
		
		$callbackParams = array();
		$callbackParams['fieldsPlainDesc'] = $fieldsPlainDesc;
		$callbackParams['typeDescription'] = $typeDescription;
		$callbackParams['kernelStrings'] = $kernelStrings;
		$callbackParams['language'] = $language;
		
		$selectedGroup = substr($node, 2);
		$docCount = $ContactCollection->getGroupContactNum( $selectedGroup, $kernelStrings );
		
		if ($docCount > $maxDocsCount) {
			tooManyNodes($nodes);
		} else {
			//getQueryLimitValues( $docCount, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount, $startIndex, $count );
			$ContactCollection->loadFromUserGroup( $selectedGroup, $sortStr, $startIndex = 0, $docCount, $callbackParams, 'prepareContactEntry', $kernelStrings );
			
			foreach ($ContactCollection->items as $cItem) {
				if (!$cItem["C_EMAILADDRESS"])
					continue;
				$nodes[] = array("id" => "U" . $cItem["U_ID"], "leaf" => true, "contact" => $cItem["CONTACT"], "text" => $cItem["HCONTACT"], "email" => $email, "iconCls" => "user");
			}		
		}
		
		if (!$nodes)
			noContacts ($nodes);
	}
	
	if ($node == "SEARCH" && $searchStr) {
		if (strlen($searchStr) < 3) {
			$nodes[] = array("id" => "", "leaf"=> true, "text"=> "<i>" . $kernelStrings["selcontacts_searchsmalllength_text"] . "</i>", "iconCls" => "user");
		} else {	
			$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );
			
			$callbackParams['fieldsPlainDesc'] = $fieldsPlainDesc;
			$callbackParams['typeDescription'] = $typeDescription;
			$callbackParams['kernelStrings'] = $kernelStrings;
			$callbackParams['language'] = $language;
			$callbackParams['viewMode'] = $viewMode;
			$callbackParams['searchString'] = $searchStr;
			
			$res = $ContactCollection->findContacts( $searchStr, $currentUser, $sortStr, $callbackParams, 'prepareContactEntry', $kernelStrings );
			
			$docCount = count($ContactCollection->items);
			if ($docCount > $maxDocsCount) {
				tooManyNodes($nodes, true);
			} else {
				foreach ($ContactCollection->items as $cItem) {
					if (!$cItem["C_EMAILADDRESS"])
						continue;
					$fullName = $cItem["NAME"];//trim($cItem["C_FIRSTNAME"] . " " . $cItem["C_LASTNAME"]);
					//if (!$fullName)
						//$fullName = $cItem["C_ID"];
					$email = $cItem["C_EMAILADDRESS"];
					$nodes[] = array("id" => "C" . $cItem["C_ID"], "leaf" => true, "currentUser" => $currentUser, "parent" => $parent, "right" => $right, "contact" => $cItem["CONTACT"], "text" => $cItem["HCONTACT"], "email" => $email, "iconCls" => "user");
				}
			}
			
			if (!$nodes)
				noFoundedContacts ($nodes);
		}
	}
	
	if ($node == "CM" || substr($node,0,2) == "CF") {
		$access = null;
		$hierarchy = null;
		$deletable = null;
		
		$parent = ($node == "CM") ? TREE_ROOT_FOLDER : substr($node,2);
		$parentRight = $cm_groupClass->getIdentityFolderRights( $currentUser, $parent, $kernelStrings );
				
		
		$folders = $cm_groupClass->listFolders( $currentUser, $parent, $kernelStrings, 0,
												false, $access, $hierarchy,
												$deletable, null, null, false, null, true, null,
												true);
		
		foreach ($folders as $cFolder) {
			if ($cFolder->CF_ID == TREE_AVAILABLE_FOLDERS)
				continue;
			if ($cFolder->CF_ID_PARENT != $parent	) 
				continue;
			
			$iconCls = ($cFolder->RIGHT != TREE_NOACCESS) ? "my-folder" : "gray-folder";
			$nodes[] = array("id" => "CF" . $cFolder->CF_ID, "right" => $cFolder->RIGHT, "text" => $cFolder->CF_NAME, "iconCls" => $iconCls);
		}
		
		if ($parent != TREE_ROOT_FOLDER && $parentRight != TREE_NOACCESS) {
			
			$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );
			
			$callbackParams = array();
			$callbackParams['fieldsPlainDesc'] = $fieldsPlainDesc;
			$callbackParams['typeDescription'] = $typeDescription;
			$callbackParams['kernelStrings'] = $kernelStrings;
			$callbackParams['language'] = $language;
			
			$docCount = $ContactCollection->getFolderContactNum( $parent, $kernelStrings );
			
			if ($docCount > $maxDocsCount) {
				tooManyNodes($nodes);
			} else {
				$ContactCollection->loadFromContactFolder( $parent, $sortStr, 0, $docCount, $callbackParams, 'prepareContactEntry', $kernelStrings );
				foreach ($ContactCollection->items as $cItem) {
					if (!$cItem["C_EMAILADDRESS"])
						continue;
					$fullName = $cItem["NAME"];//trim($cItem["C_FIRSTNAME"] . " " . $cItem["C_LASTNAME"]);
					//if (!$fullName)
						//$fullName = $cItem["C_ID"];
					$email = $cItem["C_EMAILADDRESS"];
					$nodes[] = array("id" => "C" . $cItem["C_ID"], "leaf" => true, "currentUser" => $currentUser, "parent" => $parent, "right" => $right, "contact" => $cItem["CONTACT"], "text" => $cItem["HCONTACT"], "email" => $email, "iconCls" => "user");
				}
			}
			
			if (!$nodes)
				noContacts ($nodes);
		}
	}
	
	if ($node == "CML") {
		$ContactListCollection = new ContactListCollection();
		$listCallbackParams = array();
		$userId = isAdministratorID($currentUser) ? null : $currentUser;
		$res = $ContactListCollection->loadContactLists( 'CL_NAME ASC', null, null, $userId, $listCallbackParams, null, $kernelStrings );
		$statisticsMode = false;
		
		$totalContactNum = 0;
		foreach( $ContactListCollection->items as $key=>$data ) {
			$cname = $data->CL_NAME;
			$nodes[] = array("id" => "CL-" . $data->CL_ID, "text" => $cname, "leaf" => true, "contact" => $cname . "[ID:" . $data->CL_ID . "]", "iconCls" => "list", "isList" => true);
		}
	}
	
	
	
	
	
	function prepareContactEntry( &$params, &$data )
	{
		global $thumbPerms;
		global $language;
		global $userRecordsFound;
		global $fieldsPlainDesc;

		extract($params);

		$data = (array)$data;
		$visibleColumns = array ("NAME", "C_EMAILADDRESS");
		$data = applyContactTypeDescription( $data, $visibleColumns, $fieldsPlainDesc, $kernelStrings, $viewMode );
		
		$email = "<" . $data["C_EMAILADDRESS"] . ">";
		$hemail = "&lt;" . $data["C_EMAILADDRESS"] . "&gt;";
		$data["CONTACT"] = ($data["NAME"] != $data["C_EMAILADDRESS"]) ? $data["NAME"] . " " . $email : $email;
		$data["HCONTACT"] = ($data["NAME"] != $data["C_EMAILADDRESS"]) ? $data["NAME"] . " " . $hemail : $hemail;
		
		foreach ( $fieldsPlainDesc as $fieldId=>$fieldData ) {
			$contactFieldData = $data[$fieldId];
			$data[$fieldId] = $contactFieldData;
		}
		return $data;
	}
	
	function tooManyNodes(&$nodes, $isSearch = false) {
		global $kernelStrings;
		if ($isSearch) {
			$nodes[] = array("id" => "", "leaf"=> true, "text"=> "<i>" . $kernelStrings["selcontacts_searchmanycontacts_text"] . "</i>", "iconCls" => "user");
			$nodes[] = array("id" => "", "leaf"=> true, "text"=> "<i>" . $kernelStrings["selcontacts_searchmanycontacts_text2"] . "</i>", "iconCls" => "user");
		} else {
			$nodes[] = array("id" => "", "leaf"=> true, "text"=> "<i>" . $kernelStrings["selcontacts_foldermanycontacts_text"] . "</i>", "iconCls" => "user");
			$nodes[] = array("id" => "", "leaf"=> true, "text"=> "<i>" . $kernelStrings["selcontacts_foldermanycontacts_text2"]. "</i>", "iconCls" => "user");
		}
	}
	
	function noFoundedContacts(&$nodes) {
		global $kernelStrings;
		if (!$nodes)
			$nodes[] = array("id" => "", "leaf"=> true, "text"=> "<i>" . $kernelStrings["selcontacts_searchnofound_text"] ."</i>", "iconCls" => "user");
	}
	
	function noContacts(&$nodes) {
		global $kernelStrings;
		if (!$nodes)
			$nodes[] = array("id" => "", "leaf"=> true, "text"=> "<i>" . $kernelStrings["selcontacts_folderempty_text"] . "</i>", "iconCls" => "user");
	}
	
	
	print $json->encode($nodes);	
?>