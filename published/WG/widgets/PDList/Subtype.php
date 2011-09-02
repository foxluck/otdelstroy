<?php
	include_once ("../../../PD/pd_functions.php");
	include_once ("../../../PD/pd_consts.php");
	
	class PDListSubtype extends WidgetSubtype {
		
		function PDListSubtype ($type) {
			$this->commonFields = array ("FILES", "FOLDERS", "VIEW_MODE");
			parent::WidgetSubtype ($type);
		}
		
		
		function prepare (&$preproc, &$widgetData) {
			
			global $kernelStrings;
			global $language;
			global $pd_loc_str;
			
			$filesList = $this->getWidgetFilesData($widgetData);	
			
			$mainRatio = 0;
			$r34 = round(768/1024,3);
			$r23 = round(170/256,3);
			foreach ($filesList as $cKey => $cFile) {
				if ($mainRatio)
					break;
				$wh = round($cFile["w"] / $cFile["h"], 3);
				$hw = round($cFile["h"] / $cFile["w"], 3);
				if ($wh == $r34 || $hw == $r34)
					$mainRatio = $r34;
					
				if ($wh == $rw23 || $hw == $r23)
					$mainRatio = $r23;
				
				$cFile["ENC_PL_ID"] = base64_encode($cFile["PL_ID"]);
			}
			if (!$mainRatio)
				$mainRatio = $r34;
			
			$preproc->assign ("mainRatio", $mainRatio);
			$preproc->assign ("filesList", $filesList);
			$preproc->assign ("pdStrings", $pd_loc_str[$language]);
			
			if ($this->pageState->params["viewmode"] == 3)
				$preproc->assign ("canBack", "true");
			if ($widgetData["params"]["VIEW_MODE"] == 3 || $this->pageState->params["viewmode"] == 3)
				$res["tplFilename"] = "pdlist_linkslideshow.htm";
			else
				$res["tplFilename"] = "pdlist_link.htm";
			
			return $res;
		}
		
		
		function prepareConstructorPage (&$preproc, &$pageState = null, $widgetData = array ()) {
			if ($this->embType == "inplace") {
				include_once("../../../PD/pd.php");
				$access = null;
				$hierarchy = null;
				$deletable = null;
				global $dd_treeClass;
				global $currentUser;
				
				parent::prepareConstructorPage ($preproc, $pageState, $widgetData);
				
				$folderID = $widgetData["params"]["FOLDERS"];
				if ($folderID) {
					$folderInfo = $pd_treeClass->getFolderInfo( $folderID, $kernelStrings );
					if (PEAR::isError($folderInfo))
						return $folderInfo;
				}
				
				$filesIds = $widgetData["params"]["FILES"];
				if ($filesIds) {
					$filesList = $this->getWidgetFilesData($widgetData);
					$preproc->assign ("filesList", $filesList);
				}
				
				$preproc->assign ("folderName", $folderInfo["PF_NAME"]);
			}
		}
		
		function getEmailBody ($widgetData, $sendData) {
			$filesData = $this->getWidgetFilesData($widgetData);
			if ($filesData)
				$fileData = $filesData[0];
			
			global $language;
			global $templateName;
			$path = sprintf( "%s/%s/widgets/%s/public/", WBS_PUBLISHED_DIR, WG_APP_ID, $widgetData["WT_ID"]);
			$preproc = new php_preprocessor( $templateName, $this->kernelStrings, $language, WG_APP_ID);
			$preproc->template_dir = $path;
			
			$widgetLink  = $this->getWidgetSrc($widgetData);
			
			$preproc->assign('sendData', $sendData);
			$preproc->assign('widgetData', $widgetData);
			$preproc->assign('widgetLink', $widgetLink);
			$preproc->assign("widgetStrings", $this->type->strings);
			if ($fileData)
				$preproc->assign('imageUrl', $fileData["url"]["256"]);
			
			$this->prepare($preproc, $widgetData);
			
			$res = $preproc->fetch("pdlist_email.htm");
			return $res;			
		}
		
		function getWidgetFilesData ($widgetData) {
			
			$widgetParams = $this->getRealParams($widgetData);
			
			if($this->pageState)
				$mode = $this->pageState->getParam("mode");
			
			$filesStr = $widgetData["params"]["FILES"];
			$folderId = $widgetData["params"]["FOLDERS"];
			$sql = new CSelectSqlQuery ("PIXLIST");
			if ($filesStr) {
				$sql->addConditions ("PL_ID in ($filesStr)");
			}
			if ($folderId)
				$sql->addConditions ("PF_ID='$folderId'");
			$sql->addConditions ("PL_STATUSINT<>" . TREE_DLSTATUS_DELETED);
			$sql->setOrderBy("PL_SORT", "ASC");
			
			if (!$filesStr && !$folderId)
				return;
			
			$filesList = $this->db_getListFromQuery ($sql->getQuery(), "DL_ID");
			if ( PEAR::isError($filesList))
				return $filesList;
			
			
			$middleSize = isset($widgetData["imgSize"]) ? $widgetData["imgSize"] : PD_LARGE_THUMB_SIZE;
			
			$maxWidth = @$widgetParams["GLIMGWIDTH"];
			$maxHeight = @$widgetParams["GLIMGHEIGHT"];
			
			$resultFileList = array ();
			$fileNum = 0;
			foreach ($filesList as $cKey => $data) {
				if( $data["PL_STATUSINT"] == TREE_DLSTATUS_NORMAL )
					$attachmentPath = pd_getFolderDir( $data["PF_ID"] )."/".$data["PL_DISKFILENAME"];
				elseif ( $data["PL_STATUSINT"] == TREE_DLSTATUS_DELETED )
					$attachmentPath = pd_recycledDir()."/".$data["PL_DISKFILENAME"];
				
				$imageParams = (file_exists($attachmentPath)) ?
		    	getimagesize($attachmentPath) :
		    	getimagesize(iconv("UTF-8", "WINDOWS-1251", $attachmentPath));
		    
    		    $width = $imageParams[0];
    		    $height = $imageParams[1];
    		    
    		    if ($maxWidth && $maxHeight) {
    		    	list ($width, $height) = $this->getImageSizes ($width, $height, $maxWidth, $maxHeight);
    		    } elseif ($middleSize) {
			    if ($middleSize == 96)
			    {
			    	$width = $middleSize; $height = $middleSize;
			    }
			    if ( $width > $height ) {
						if ( $width > $middleSize ) {
							$ratio = $width/$height;
							
							$height = $middleSize/$ratio;
							$width = $middleSize;
						}
					} else {
						if ( $height > $middleSize ) {
							$ratio = $width/$height;

							$width = $middleSize*$ratio;
							$height = $middleSize;
						}
					}
				}
				
				$thumbParams = array();
				$srcExt = null;
				$thumbParams['WG'] = $widgetData['WST_ID'];
				$thumbParams['D'] = 'PD';
				$thumbParams['DBKEY'] = base64_encode($GLOBALS['DB_KEY']);
				$thumbParams['nocache'] = getThumbnailModifyDate( $attachmentPath, 'win', $srcExt );
				$thumbParams['basefile'] = base64_encode(rawurlencode( $attachmentPath ));
				$thumbParams['ext'] = base64_encode( $data["PL_FILETYPE"] );
		        
		    //$fileURL = prepareURLStr( getLinkPrefix(3)."/PD/html/scripts/".PAGE_PD_GETFILETHUMB, $thumbParams);
		    
		    $params = explode("/", $attachmentPath);
		    $img = $params[count($params)-1];
		    $albumId = $params[count($params)-2];
		    
			$prep = '&client='.$widgetData["WST_ID"];
		    
		    $fileURL = getLinkPrefix(3)."/PD/image.php?DBKEY=".base64_encode($GLOBALS['DB_KEY'])
		                                ."&filename=". rawurlencode(base64_encode( $img))
		                                ."&albumId=".$albumId 
		                                ."&mode=orig".$prep;
		                                
		    
				$fileData = $data;
				
				//$fileData["fileURL"] = prepareURLStr( "../../../PD/html/scripts/".PAGE_PD_GETFILETHUMB, $thumbParams);
				$fileData["w"] = round($width);
				$fileData["h"] = round($height);
				$fileData["ratio"] = $width/$height;
				
				
				$fileData["url"][PD_SMALL_THUMB_SIZE] = $fileURL."&size=".PD_SMALL_THUMB_SIZE;
			  $fileData["url"][PD_MEDIUM_THUMB_SIZE] = $fileURL."&size=".PD_MEDIUM_THUMB_SIZE;
			  $fileData["url"][PD_LARGE_THUMB_SIZE] = $fileURL."&size=970";
			  $fileData["url"][PD_144_SIZE] = $fileURL."&size=".PD_144_SIZE;
			  $fileData["url"][970] = $fileURL."&size=970";
			  
			  // thumbs
			  $fileData["url"][PD_DEFAULT_THUMB_SIZE] = $fileURL."&size=".PD_DEFAULT_THUMB_SIZE;
			  $fileData["url"][PD_ULTRA_SMALL_THUMB_SIZE] = $fileURL."&size=".PD_ULTRA_SMALL_THUMB_SIZE;
			  
			  $fileData["ENC_PL_ID"] = base64_encode($fileData["PL_ID"]);
			  
			  $fileData["DELETE_URL"] = $this->getWidgetSrc ($widgetData, "&action=delete&fileId=".$fileData["ENC_PL_ID"] . "&currentImageNum=" . $fileNum . "&mode=" . $mode);
			  $fileData["ZOOM_URL"] = $this->getWidgetSrc ($widgetData, "currentImageNum=" . $fileNum . "&mode=big");
			  $fileNum++;
			    
				$resultFileList[] = $fileData;
			}
			return $resultFileList;
		}
		
		function getImageSizes ($width, $height, $maxWidth, $maxHeight) {
			
			$ratio = $height / $width;
			
			if ($width > $maxWidth) {
				$newWidth = $maxWidth;
				$newHeight = $newWidth * $ratio;
			} else {
				$newHeight = $height;
			}
			
			if ($newHeight > $maxHeight) {
				$newHeight = $maxHeight;
				$newWidth = $newHeight/$ratio;
			}
			
			if ($width <= $maxWidth && $height <= $maxHeight) {
				$newWidth = $width;
				$newHeight = $height;
			}
			
			return array($newWidth, $newHeight);
		}
	}
?>