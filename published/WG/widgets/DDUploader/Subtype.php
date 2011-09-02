<?php
	class DDUploaderSubtype extends WidgetSubtype {
		
		function DDUploaderSubtype($type) {
			$this->commonFields = array ("FOLDER", "SHOWBORDER", "BORDERCOLOR", "TITLE", "TITLECOLOR", "WIDTH", "BGCOLOR", "MAXFILESCOUNT", "MAXFILESIZE", "CANDELETE", "HEIGHT");
			$this->minFolderRights = 4;
			parent::WidgetSubtype ($type);
		}
		
		
		function prepare (&$preproc, &$widgetData) {
			include_once("../../../DD/dd.php");
			
			$res = parent::prepare($preproc, $widgetData);
			
			
			global $kernelStrings;
			global $language;
			global $dd_loc_str;
			global $dd_treeClass;
			global $currentUser;
			$ddStrings = $dd_loc_str[$language];
			
			$widgetData["params"] = $this->getRealParams($widgetData);
			
			$maxFileSize = $widgetData["params"]["MAXFILESIZE"] * 1024 * 1024;
			
			do {
				$uid = $widgetData["WG_USER"];
				if ($this->pageState->getParam("action") == "delete") {
					$fileId = base64_decode($this->pageState->getParam("fileId"));
					if (!$fileId) {
						$error = PEAR::raiseError("Empty file id");
						break;
					}
					$documentList = array($fileId);
					
					$delRes = dd_deleteRestoreDocuments( $documentList, DD_DELETEDOC, $uid, $kernelStrings, $ddStrings, null, false, "widget");
					if (PEAR::isError($error = $delRes))
						break;
				}
				
				$folderID = $widgetData["params"]["FOLDER"];
				if (!$folderID) {
					$error = PEAR::raiseError("Empty folder id");
					break;
				}
				
				$folderInfo = $dd_treeClass->getFolderInfo( $folderID, $kernelStrings );
				if (PEAR::isError($folderInfo)) {
					//$error = $folderInfo;
					$preproc->assign ("filesErrorStr", $folderInfo->getMessage());
					break;
				}
				
				$preproc->assign ("folderName", $folderInfo["DF_NAME"]);
				
				if ($this->pageState->getParam("action") == "add") {
					
					$showPageSelector = false;
					$pages = null;
					$pageCount = 0;
					$startIndex = 0;
					$count = 100;
					$sqlSorting = "DL_FILENAME ASC";
					$files = $dd_treeClass->listFolderDocuments( $folderID, $widgetData["WG_USER"], $sqlSorting, $kernelStrings, null, false, $startIndex, $count);
					if ($widgetData["params"]["MAXFILESCOUNT"] && $widgetData["params"]["MAXFILESCOUNT"] <= sizeof($files)) {
						$error = PEAR::raiseError (sprintf($this->type->strings["up_maxfilescount_error"], $widgetData["params"]["MAXFILESCOUNT"]) );
						break;
					}
				
					$name = $_FILES['file']['name'];
					if ( strlen($name) ) {
						if ($_FILES['file']['size'] > $maxFileSize) {
							$error = PEAR::raiseError (sprintf($this->type->strings["up_maxfilesize_error"], $widgetData["params"]["MAXFILESIZE"]));
							break;
						}
						
						if ( $_FILES['file']['size'] != 0) {
							$tmpFileName = uniqid( TMP_FILES_PREFIX );
							$destPath = WBS_TEMP_DIR."/".$tmpFileName;
							$srcName =  $_FILES['file']['tmp_name'];
							
							if ( !@move_uploaded_file( $srcName, $destPath ) )
							{
								$messageStack[] = sprintf ( $ddStrings['add_screen_upload_error'], $_FILES['file']['name'], $ddStrings['app_copyerr_message'] );
							} else {
								$fileObj = new dd_fileDescription();
								$fileObj->DL_FILENAME = getFileBaseName($name);
								$fileObj->DL_FILESIZE = $_FILES['file']['size'];
								$fileObj->DL_DESC = prepareStrToStore( trim( $descriptions ) );
								$fileObj->DL_MIMETYPE = $_FILES['file']['type'];
								$fileObj->sourcePath = $destPath;
								$fileList[] = $fileObj;
							}
							$metric = metric::getInstance();
							$metric->addAction($GLOBALS['DB_KEY'], '', 'DD', 'UPLOAD-STD', 'WG-ONLINEFOLDER', $_FILES['file']['size']);
						}
						else
						{
							$messageStack[] = sprintf ( $ddStrings['add_screen_zero_size'], $_FILES['file']['name'], $ddStrings[$dd_uploadErrors[$_FILES['file']['error']]] );
						}
					}
					
					$curDF_ID = $folderID;
					$lastFile = null;
					$resultStatistics = null;
					$existingFileOperation = DD_REPLACE_FILES;
					$removeFilesAfterCopy = true;
					
					//$fromWidget = ($widgetData["WG_DESC"]) ? $widgetData["WG_DESC"] : $this->type->name . " " . $this->name;
					$fromWidget = "widget";
					
					$addRes = dd_addFiles( $fileList, $curDF_ID, $uid, $kernelStrings, $ddStrings, $messageStack, $lastFile, $resultStatistics, true, $existingFileOperation = DD_REPLACE_FILES, $removeFilesAfterCopy, $fromWidget);
					if (PEAR::isError($error = $addRes))
						break;
					$preproc->assign("selectFile", $lastFile);
				}	
			} while (false);
			
			$listType = WidgetTypeFactory::getWidgetType("DDList");
			$listSubtype = $listType->subtypes["Inplace"];
			$widgetData["params"]["FOLDERS"] = $widgetData["params"]["FOLDER"];
			$widgetData["params"]["ORDER_BY"] = "DL_FILENAME ASC";
			
			$listSubtype->pageState = $this->pageState;
			$listSubtype->prepare($preproc, $widgetData);
			$preproc->assign("MAXFILESIZEBYTES", $maxFileSize);
			
			if (PEAR::isError($error))
				$preproc->assign("errorStr", $error->getMessage ());				
			
			return $res;
		}
		
		function prepareConstructorPage (&$preproc, &$pageState, $widgetData = array ()) {
			include_once("../../../DD/dd.php");
			$access = null;
			$hierarchy = null;
			$deletable = null;
			global $dd_treeClass;
			global $currentUser;
			
			/*$folders = $dd_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $pageState->kernelStrings, 0, false,
																	$access, $hierarchy, $deletable, TREE_WRITEREAD );
			if ( PEAR::isError($folders))
				return $folders;
			$visibleFolders = array();
			foreach ( $folders as $fCF_ID=>$folderData ) {
				//$encodedID = base64_encode($fCF_ID);
				$folderData->curID = $fCF_ID;
				$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

				$visibleFolders[$fCF_ID] = $folderData;
			}
			$folders = $visibleFolders;
			
			$preproc->assign( "folders", $folders );*/
			//$folderData = $dd_treeClass->getFolderInfo( $widgetData["params"]["FOLDER"], $kernelStrings );
			
			
			parent::prepareConstructorPage ($preproc, $pageState, $widgetData);
			$customFilename = $this->type->getHTMLPath() . "/folder.htm";
			//$preproc->assign ("subtypeBeforeFormFile", array ("files" => $customFilename));
			
			$folderID = $widgetData["params"]["FOLDER"];
			$folderInfo = $dd_treeClass->getFolderInfo( $folderID, $kernelStrings );
			if (PEAR::isError($folderInfo))
				return $folderInfo;
			
			$preproc->assign ("folderName", $folderInfo["DF_NAME"]);
			
		}

		
		function getEmailBody ($widgetData, $sendData) {

		}
	}
?>