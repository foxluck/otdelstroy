<?php
	class DDUploaderInplaceSubtype extends DDUploaderSubtype {
		var $shortLink = "/files";
		
		function DDUploaderInplaceSubtype (&$type) {
			$this->id = "Inplace";
			$this->embType = "inplace";
			$this->fields = array ();
			$this->onlyForFolders = true;
			parent::DDUploaderSubtype ($type);
		}
		
		function prepareViewPage(&$preproc) {
			parent::prepareViewPage($preproc);
			
			global $dd_loc_str;
			global $language;
			
			$preproc->assign("ddStrings", $dd_loc_str[$language]);
			$preproc->assign ("formFilename", PATH_WG_WIDGETS . "DDUploader/html/link_form.htm");
		}
		
		function prepare (&$preproc, &$widgetData) {
			$res = parent::prepare($preproc, $widgetData);
			$preproc->assign('widgetFilename', "dduploader_inplace.htm");
			return $res;			
		}
		
		function getEmbInfo ($widgetData) {
			return array("short_link" => $this->shortLink);
		}
		
		function createNewWidget ($user, $params) {
			require_once( WBS_DIR."/published/DD/dd.php" );
			global $dd_loc_str;
			global $loc_str;
			global $language;
			global $UR_Manager;
			$kernelStrings = $loc_str[$language];
			$ddStrings = $dd_loc_str[$language];
			
			$params["FOLDER_NAME"] = "online-folder";
	
			$error = null;
			do {
				if (empty($language))
					$language = "eng";
				$kernelStrings = &$loc_str[$language];
				$currentUser = $user;
				$parentFolderID = "ROOT";
				$folderData["DF_NAME"] = $params["FOLDER_NAME"];
				$admin = false;
				$action = ACTION_NEW;
				
				$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings );

				$folderID = $dd_treeClass->addmodFolder( $action, $user, $parentFolderID, prepareArrayToStore($folderData),
													$kernelStrings, $admin, 'dd_onCreateFolder', $callbackParams, true, true );
				
				if ( PEAR::isError($error = $folderID ))
					break;

				$userAccessRights[UR_REAL_ID] = $folderID;
				$saveResult =  $UR_Manager->SaveItem( $userAccessRights );
				if ( PEAR::isError( $error = $saveResult ) )
					break;
				
				$groupAccessRights[UR_REAL_ID] = $folderID;
				$saveResult =  $UR_Manager->SaveItem( $groupAccessRights );
				if ( PEAR::isError( $error = $saveResult ) )
					break;
				
				$widgetManager = getWidgetManager ();
				if (PEAR::isError ($error = $widgetManager))
					break;
				
				$wgName = "Online Folder";
				$widgetData = array ("WT_ID" => "DDUploader", "WST_ID" => "Inplace", "WG_DESC" => $wgName, "WG_LANG" => $language);
				$wgId = $widgetManager->add ($widgetData);
				
				if (PEAR::isError ($error = $wgId))
					break;
				
				$params = array ("FOLDER" => $folderID, "TITLE" => $folderData["DF_NAME"]);
				$res = $widgetManager->setWidgetParams ($wgId, $params);
				if (PEAR::isError ($error = $res))
					break;
			
			} while (false);
			
			if (PEAR::isError($error))
				return $error;
			
			$widgetData = $widgetManager->get ($wgId);
			return $widgetData;			
		}
		
		function getIGoogleInfo () {
			$info = array (
				"title" => $this->type->strings["subtype_inplace_igoogle_title"],
				"description" => $this->type->strings["subtype_inplace_igoogle_description"],
				"screenshot" => "http://www.webasyst.net/images/files/widget-online-folder.gif",
				"thumbnail" => "http://www.webasyst.net/images/files/widget-online-folder.gif",
				"title_url" => "http://www.webasyst.net/files/",
				"height" => 305
			);
			return $info;
		}
	}
?>