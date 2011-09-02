<?php
	class DDListSubtype extends WidgetSubtype {
		
		function DDListSubtype ($type) {
			$this->commonFields = array ("FILES", "FOLDERS", "VIEW_MODE");
			parent::WidgetSubtype ($type);
		}
		
		
		function prepare (&$preproc, &$widgetData) {
			
			$res = parent::prepare($preproc, $widgetData);
			
			global $kernelStrings;
			global $language;
			global $dd_loc_str;
			
			if ($this->pageState) {
				$fn = $this->pageState->getParam("file");
				if ($fn)
					return $this->returnFile(base64_decode($fn), $this->pageState->getParam("download"));
			}
			
			$filesList = $this->getFilesList ($widgetData);
			
			$preproc->assign ("filesList", $filesList);
			$preproc->assign ("ddStrings", $dd_loc_str[$language]);
			
			return $res;
		}
		
		function getFilesList ($widgetData) {
			$filesStr = $widgetData["params"]["FILES"];
			$folderId = $widgetData["params"]["FOLDERS"];
			$sql = new CSelectSqlQuery ("DOCLIST");
			if ($filesStr) {
				$sql->addConditions ("DL_ID in ($filesStr)");
			}
			if ($folderId)
				$sql->addConditions ("DF_ID='$folderId'");
			$sql->addConditions ("DL_STATUSINT<>" . TREE_DLSTATUS_DELETED);
			
			if (!empty($widgetData["params"]["SORTING"]))
			{
				$sorting = "";
				if ($widgetData["params"]["SORTING"] == "filename") $sorting = "DL_FILENAME ASC";
				if ($widgetData["params"]["SORTING"] == "dateasc") $sorting = "DL_CHECKDATETIME ASC,DL_UPLOADDATETIME ASC";
				if ($widgetData["params"]["SORTING"] == "datedesc") $sorting = "DL_CHECKDATETIME DESC,DL_UPLOADDATETIME DESC";
				$widgetData["params"]["ORDER_BY"] = $sorting;
			}
			
			if ($widgetData["params"]["ORDER_BY"])
				$sql->setCustomOrderBy($widgetData["params"]["ORDER_BY"]);
			else
				$sql->setOrderBy("DL_FILENAME", "ASC");
			
			if (!$filesStr && !$folderId)
				return;
			
			$filesList = $this->db_getListFromQuery ($sql->getQuery(), "DL_ID");
			if ( PEAR::isError($filesList))
				return $filesList;
			
			$time = mktime();
			foreach ($filesList as $cKey => $cFile) {
				
				$cFile["DL_FILESIZE"] = formatFileSizeStr( $cFile["DL_FILESIZE"] );
				
				$attachmentPath = dd_getFolderDir( $cFile["DF_ID"] )."/".$cFile["DL_DISKFILENAME"];
				$thumbParams = array();
				$srcExt = null;
				$thumbParams['WG'] = $widgetData['WST_ID'];
				$thumbParams['D'] = 'DD';
				$thumbParams['U'] = base64_encode($widgetParams['WG_USER']);
				$thumbParams['nocache'] = getThumbnailModifyDate( $attachmentPath, 'win', $srcExt );
				$thumbParams['basefile'] = base64_encode( $attachmentPath );
				$thumbParams['ext'] = base64_encode( $cFile["DL_FILETYPE"] );
				
				$cFile["THUMB_URL"] = prepareURLStr( getLinkPrefix(3) . "/common/html/scripts/getfilethumb.php", $thumbParams );
				
				if (onWebasystServer() && $this->shortLink) {
					$cFile["ROW_URL"] = $this->getWidgetSrc($widgetData) . "/" . base64_encode("file=" . base64_encode($cFile["DL_ID"]));
					$cFile["DOWNLOAD_URL"] = $this->getWidgetSrc($widgetData) . "/" . base64_encode("file=" . base64_encode($cFile["DL_ID"]) . "&download=1");
				}
				else {
					$cFile["ROW_URL"] = $_SERVER["REQUEST_URI"] . "&file=" . base64_encode($cFile["DL_ID"]);
					$cFile["DOWNLOAD_URL"] = $_SERVER["REQUEST_URI"] . "&file=" . base64_encode($cFile["DL_ID"]) . "&download=1";
				}
				
				$cFile["DISPLAY_DATETIME"] = convertToDisplayDate($cFile["DL_UPLOADDATETIME"]);
				$ctime = sqlTimestamp($cFile["DL_UPLOADDATETIME"], true);
				
				$seconds = $time - $ctime;
				$minutes = floor($seconds / 60);
				$hours = floor($minutes / 60);
				$days = floor($seconds / (60 * 60 * 24));
				$minutes -= $hours * 60;
				$addedStr = $this->type->strings["wg_added_title"];
				$cFile["DISPLAY_DATETIME"] = $addedStr . " " . $cFile["DISPLAY_DATETIME"];
				if ($days == 0) {
					if ($minutes < 5) {
						$cFile["DISPLAY_DATETIME"] = $this->type->strings["wg_justadded_title"];
						$cFile["BOLD_DATETIME"] = true;
					}
					elseif ($hours  < 1)
						$cFile["DISPLAY_DATETIME"] = sprintf($addedStr . " %smin ago", $minutes);					
					elseif ($hours < 24)
						$cFile["DISPLAY_DATETIME"] = sprintf($addedStr . " %sh %sm ago", $hours, $minutes);
				}
				if ($cFile["DL_CHECKDATETIME"])
					$cFile["DISPLAY_DATE"] = convertToDisplayDate($cFile["DL_CHECKDATETIME"]);
				else
					$cFile["DISPLAY_DATE"] = convertToDisplayDate($cFile["DL_UPLOADDATETIME"]);
				
				$cFile["ENC_DL_ID"] = base64_encode($cFile["DL_ID"]);
				$filesList[$cKey] = $cFile;				
			}
			return $filesList;
		}
		
		function convertCreateParams($params) {
			if (isset($params["FOLDER"])) {
				$params["FOLDERS"] = $params["FOLDER"];
				unset($params["FOLDER"]);
			}
			return $params;
		}
			

		
		function getEmailBody ($widgetData, $sendData) {
			global $language;
			global $templateName;
			$path = sprintf( "%s/%s/widgets/%s/public/", WBS_PUBLISHED_DIR, WG_APP_ID, $widgetData["WT_ID"]);
			$preproc = new php_preprocessor( $templateName, $this->kernelStrings, $language, WG_APP_ID);
			$preproc->template_dir = $path;
			
			$widgetLink  = $this->getWidgetSrc($widgetData);
			
			$preproc->assign('sendData', $sendData);
			$preproc->assign('widgetData', $widgetData);
			$preproc->assign('widgetLink', $widgetLink);
			
			$this->prepare($preproc, $widgetData);
			
			$res = $preproc->fetch("email.htm");
			return $res;			
		}
		
		function returnFile ($DL_ID, $toDownload = false) {
			require_once( "../../../DD/dd_functions.php" );
			require_once( "../../../DD/dd_consts.php" );
			require_once( "../../../DD/dd_queries_cmn.php" );
			require_once( "../../../DD/dd_dbfunctions_cmn.php" );
			include_once ("../../../../kernel/classes/class.metric.php");
			global $widgetData;
			$fileData = dd_getDocumentData( $DL_ID, $kernelStrings );
			
			$fileName = $fileData->DL_FILENAME;
			
			$fileSize = $fileData->DL_FILESIZE;
			$fileType = $fileData->DL_MIMETYPE;
			$diskFileName = $fileData->DL_DISKFILENAME;
			
			if( $fileData->DL_STATUSINT == TREE_DLSTATUS_NORMAL )
				$attachmentPath = dd_getFolderDir( $fileData->DF_ID )."/".$diskFileName;
			elseif ( $fileData->DL_STATUSINT == TREE_DLSTATUS_DELETED )
				$attachmentPath = dd_recycledDir()."/".$diskFileName;
				
			$silentMode = 1;
			
			if ( !file_exists($attachmentPath) || is_dir($attachmentPath) )
				return PEAR::raiseError ("File not found");
			$metric = metric::getInstance();

			if ($fileData->DL_FILENAME) {
				/*switch ($widgetData['WST_ID']) {
					case 'Link' : $CLIENT = 'WG-FILELIST'; break;
					case 'Inplace' : $CLIENT = 'WG-ONLINEFOLDER'; break;
				}*/
				$CLIENT = ($_REQUEST['W'] == 'FL')
				? 'WG-FILELIST'
				: 'WG-ONLINEFOLDER';
				if ($this->embType == "link")
					$CLIENT = "LINK";
				$metric->addAction($GLOBALS['DB_KEY'], '', 'DD', 'DOWNLOAD', $CLIENT, $fileSize); 
			}

			if (preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])) {
				if (preg_match("/[à-ÿ]/ui", $fileName)) {
					$fileName = iconv("UTF-8", "Windows-1251", $fileName);
				} else {
					$fileName = rawurlencode($fileName);
				}
			}

			if (onWebasystServer()) {
				
				$path = "/data" . substr($attachmentPath, strlen(WBS_DATA_DIR));
				if (isset($_GET["debug"])) {
					die($path);
				}

				if ($toDownload) {
					
					header("Content-type: application/octet-stream"); 
					header('Content-Disposition: attachment; filename="' . $fileName . '"');
				}
				else {
					header("Content-type: $fileType");
					header('Content-Disposition: inline; filename="' . $fileName . '"');
				}			
				//header('Content-Disposition: inline; filename="' . $fileName . '"');
				header("Accept-Ranges: bytes");
				header("Content-Length: $fileSize");
				header("Expires: 0");
				header("Cache-Control: private");
				header("Pragma: public");
				header("Connection: close");
				
				header("X-Accel-Redirect: " . $path);
				exit;
			}
			
			
			@ini_set( 'async_send', 1 );

			if ($toDownload) {
				
//echo "$toDownload xxx <pre>"; print_r($fileName); exit;

				header("Content-type: application/octet-stream"); 
				header('Content-Disposition: attachment; filename="' . $fileName . '"');
			}
			else {
				header("Content-type: $fileType");
				header('Content-Disposition: inline; filename="' . $fileName . '"');
			}			
			header("Accept-Ranges: bytes");
			header("Content-Length: $fileSize");
			header("Expires: 0");
			header("Cache-Control: private");
			header("Pragma: public");
			header("Connection: close");

			$fp = @fopen($attachmentPath, 'rb');

			while (!feof($fp)) {
				print @fread($fp, 1048576 );
				@ob_flush();
			}

			@fclose($fp);
			exit;
		}
	}
?>