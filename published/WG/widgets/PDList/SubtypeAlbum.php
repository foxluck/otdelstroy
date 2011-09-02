<?php
	class PDListAlbumSubtype extends PDListSubtype {
		var $shortLink = "/photos";
		
		function PDListAlbumSubtype (&$type) {
			$this->id = "Album";
			$this->embType = "inplace";
			$this->minFolderRights = 4;
			parent::PDListSubtype ($type);
			$this->hidePlaces = array ();
			$this->commonFields = array_merge ($this->commonFields, array ("FILES", "FOLDERS", "SLBODYBGCOLOR", "SLWIDTH", "SLHEIGHT", "MAXFILESCOUNT", "MAXFILESIZE", "SLCPANEL", "SLSECONDS", "SLAUTOPLAY", "SLONCLICK"));
		}
		
		function prepare (&$preproc, &$widgetData) {
			
			include_once("../../../PD/pd.php");
			
			//$res = parent::prepare($preproc, $widgetData);
			
			$widgetParams = $this->getRealParams($widgetData);
			
			
			global $kernelStrings;
			global $language;
			global $pd_loc_str;
			global $pd_treeClass;
			global $currentUser;
			$pdStrings = $pd_loc_str[$language];
			$fromWidget = "widget";
			$currentImageNum = $this->pageState->getParam("currentImageNum");
			$maxFileSize = $widgetParams["MAXFILESIZE"] * 1024 * 1024;
			
			do {
				$uid = $widgetData["WG_USER"];
				$folderID = $widgetParams["FOLDERS"];
				
				if ($this->pageState->getParam("action") == "delete" && $widgetParams["SLCPANEL"] == "manage") {
					$fileId = base64_decode($this->pageState->getParam("fileId"));
					if (!$fileId) {
						$error = PEAR::raiseError("Empty file id");
						break;
					}
					$documentList = array($fileId);
					
					$delRes = pd_removeDocuments( $documentList, $uid, $kernelStrings, $pdStrings, true);
					if (PEAR::isError($error = $delRes))
						break;
				}
				
				if ($this->pageState->getParam("action") == "add" && $widgetParams["SLCPANEL"] == "manage") {
				
					$showPageSelector = false;
					$pages = null;
					$pageCount = 0;
					$startIndex = 0;
					$count = 100;
					$sqlSorting = "PL_FILENAME ASC";
					$files = $pd_treeClass->listFolderDocuments( $folderID, $widgetData["WG_USER"], $sqlSorting, $kernelStrings, null, false, $startIndex, $count);
					if ($widgetParams["MAXFILESCOUNT"] && $widgetParams["MAXFILESCOUNT"] <= sizeof($files)) {
						$error = PEAR::raiseError (sprintf($this->type->strings["up_maxfilescount_error"], $widgetParams["MAXFILESCOUNT"]) );
						break;
					}
				
					$name = $_FILES['file']['name'];
					
					if ( strlen($name) ) {
						if ($_FILES['file']['size'] > $maxFileSize) {
							$error = PEAR::raiseError (sprintf($this->type->strings["up_maxfilesize_error"], $widgetParams["MAXFILESIZE"]));
							break;
						}
						
						if ( $_FILES['file']['size'] != 0) {

							$tmpFileName = uniqid( TMP_FILES_PREFIX );
							$destPath = WBS_TEMP_DIR."/".$tmpFileName;
							$srcName =  $_FILES['file']['tmp_name'];
							if ( !@move_uploaded_file( $srcName, $destPath ) )
							{
								$error = PEAR::raiseError ($pdStrings['add_screen_upload_error'], $_FILES['file']['name'], $pdStrings['app_copyerr_message'] );
							} else {

								$fileObj = new pd_fileDescription();
								$fileObj->PL_FILENAME = getFileBaseName($name);
								$fileObj->PL_FILESIZE = $_FILES['file']['size'];
								$fileObj->PL_DESC = "";
								$fileObj->PL_MIMETYPE = $_FILES['file']['type'];
								$fileObj->sourcePath = $destPath;
								$fileList[] = $fileObj;
								$metric = metric::getInstance();
								$metric->addAction($_REQUEST['wbs_login_host'], '', 'PD', 'UPLOAD-STD', 'WG-SLIDESHOW', $_FILES['file']['size']);

							}
						}
						else
						{
							//$error = PEAR::raiseError ($pdStrings['add_screen_zero_size'], $_FILES['file']['name'], $pdStrings[$dd_uploadErrors[$_FILES['file']['error']]] );
						}
					}
					
					if ($fileList) {
						$curPF_ID = $folderID;
						$lastFile = null;
						$resultStatistics = null;
						$existingFileOperation = PD_REPLACE_FILES;
						$removeFilesAfterCopy = true;
						
						//$fromWidget = ($widgetData["WG_DESC"]) ? $widgetData["WG_DESC"] : $this->type->name . " " . $this->name;
						$fromWidget = "widget";
						$addRes = pd_addFiles( $fileList, $curPF_ID, $uid, $kernelStrings, $pdStrings, $messageStack, $lastFile, $resultStatistics, true, $existingFileOperation = PD_REPLACE_FILES, $isFromArchive = false, $fromWidget);
						
						$selectFile = null;
						if (PEAR::isError($error = $addRes))
							break;
						else {
							$selectFile = @$addRes[0];
							
							$filesList = $this->getWidgetFilesData($widgetData);
							$newIds = array ();
							$oldIds = array ();
							$i = 0;
							foreach ($filesList as $cFile) {
								$oldIds[] = $cFile["PL_ID"];
								$newIds[] = $cFile["PL_ID"];
								if ($currentImageNum == $i)
									$newIds[] = $selectFile;							
								$i++;
							}
							$oldIds[] = $selectFile;
							$sortRes = pd_saveSortOrder( $uid, $curPF_ID, $oldIds, $newIds, $pdStrings, $kernelStrings );
							if (PEAR::isError($error = $sortRes))
								break;
						}
						$preproc->assign("selectFile", $selectFile);
						$currentImageNum++;
					}
				}	
			} while (false);
			
			$imgSize = PD_LARGE_THUMB_SIZE;
			$slwidth = $widgetParams["SLWIDTH"];
			if ($slwidth <= 96 )
				$imgSize = 96;
			elseif ($slwidth <= 256 )
				$imgSize = 256;
			elseif ($slwidth <= 512 )
				$imgSize = 512;
			
			if ($this->pageState->getParam("mode") != "big")
				$widgetData["imgSize"] = $imgSize;
			
			$res = parent::prepare($preproc, $widgetData);
			
			if (PEAR::isError($error)) {
				$preproc->assign("errorStr", $error->getMessage());				
			}
			
			$manageUrl = $this->getWidgetSrc ($widgetData, "mode=control");
			if ($this->pageState->getParam("mode") == "big")
				$preproc->assign('widgetFilename', "pdlist_linkslideshow.htm");
			else
				$preproc->assign('widgetFilename', "pdlist_album.htm");
			if ($this->pageState->getParam("action")) {
				$preproc->assign("NO_SLIDESHOW_START", true);
			}
			
			$preproc->assign("IMG_SIZE", $imgSize);
			$preproc->assign("MANAGE_URL", $manageUrl);
			$preproc->assign("MAXFILESIZEBYTES", $maxFileSize);
			$preproc->assign("currentImageNum", $currentImageNum);
			$res["tplFilename"] = PATH_WG_WIDGETS . "_common/public/html/empty_wrapper.htm";
			return $res;			
		}
		
		function getEmbInfo ($widgetData) {
			$widgetParams = $this->getRealParams($widgetData);
			return array("short_link" => $this->shortLink, "width" => $widgetParams["SLWIDTH"], "height" => $widgetParams["SLHEIGHT"], "widthAdd" => 0, "heightAdd" => 0, "style" => "border: 1px solid #999999");
		}
		
		
		
		function createNewWidget ($user, $params) {
			require_once( WBS_DIR."/published/PD/pd.php" );
			global $pd_loc_str;
			global $loc_str;
			global $language;
			global $UR_Manager;
			$kernelStrings = $loc_str[$language];
			$pdStrings = $dd_loc_str[$language];
			
			$params["FOLDER_NAME"] = "online-slideshow";
	
			$error = null;
			do {
				if (empty($language))
					$language = "eng";
				$kernelStrings = &$loc_str[$language];
				$currentUser = $user;
				$parentFolderID = "ROOT";
				$folderData["PF_NAME"] = $params["FOLDER_NAME"];
				$admin = false;
				$action = ACTION_NEW;
				
				$callbackParams = array( 'pdStrings'=>$pdStrings, 'kernelStrings'=>$kernelStrings );

				$folderID = $pd_treeClass->addmodFolder( $action, $user, $parentFolderID, prepareArrayToStore($folderData),
													$kernelStrings, $admin, 'pd_onCreateFolder', $callbackParams, true, true );
				
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
				
				$wgName = "Online Slideshow";
				$widgetData = array ("WT_ID" => "PDList", "WST_ID" => "Album", "WG_USER" => $user, "WG_DESC" => $wgName, "WG_LANG" => $language);
				$wgId = $widgetManager->add ($widgetData);
				
				if (PEAR::isError ($error = $wgId))
					break;
				
				$params = array ("FOLDERS" => $folderID, "TITLE" => $folderData["PF_NAME"]);
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
				"title" => $this->type->strings["subtype_album_igoogle_title"],
				"description" => $this->type->strings["subtype_album_igoogle_description"],
				"screenshot" => "http://www.webasyst.net/images/files/widget-online-folder.gif",
				"thumbnail" => "http://www.webasyst.net/images/files/widget-online-folder.gif",
				"title_url" => "http://www.webasyst.net/photos/",
				"height" => 192
			);
			return $info;
		}
	}
?>