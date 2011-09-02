<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );
	
	//	
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";
	
	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	// 
	// Page variables setup
	//
	
	function ResizeImage ($filename, $destFilename, $nwidth, $nheight) {
		
		if (!function_exists('imagecreatefromjpeg'))
			return move_uploaded_file($filename, $destFilename);
			
		// Get new sizes
		list($width, $height) = getimagesize($filename);
		$source = imagecreatefromgif($filename);
		
		$nratio = $nwidth / $nheight;
		$ratio = $width / $height;
		if ($nratio < $ratio) {
			$resHeight = ($height/($width/$nwidth));
			$resWidth = $nwidth;
		}	else {
			$resHeight = $nheight;
			$resWidth = ($width/($height/$nheight));
		}
		
		if ($resHeight > $height && $resWidth > $width)
			return move_uploaded_file($filename, $destFilename);
		
		$thumb = imagecreatetruecolor($resWidth, $resHeight);
		
		// sets background to white
		$bg = imagecolorallocate($thumb, 255, 255, 255);
		
		// Resize
		imagecopyresized($thumb, $source, 0, 0, 0, 0, $resWidth, $resHeight, $width, $height);
		//imagecopyresized($thumb, $source, ($nwidth-$resWidth)/2, ($nheight-$resHeight)/2, 0, 0, $resWidth, $resHeight, $width, $height);
		
		return imagegif($thumb, $destFilename, 100);
	}
	
	

	
	$kernelStrings = $loc_str[$language];
	$invalidField = null;
	
	$current_plan = getApplicationHostingPlan();
	
	if ( !isset($edited) || !$edited ) {
		$companyData = db_query_result( $qr_selectCompanyInfo, DB_ARRAY );

		if ( PEAR::isError( $companyData ) ) {
			$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

			$fatalError = true;
		}

		$deleteLogo = 0;
		$prevName = base64_encode($companyData["COM_NAME"]);
	}

	$logoPath = getKernelAttachmentsDir();
	$logoPath .= "/logo.gif";
	$showLogo = file_exists($logoPath);

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, "deletebtn"), $_POST );
	

	ClassManager::includeClass('AccAdvSettings', 'kernel');
	$advSettings = new AccAdvSettings ($DB_KEY);
	
	switch ( $btnIndex ) {
		case 0 : {
			//redirectBrowser( PAGE_SIMPLEREPORT, array( INFO_STR=>urlencode(base64_encode($kernelStrings['ci_nochanges_message'])), "reportType"=>2 ) );
			redirectBrowser( PAGE_AADMIN, array () );

			break;
		}
		case 1 : {
			
			$imageUploaded = false;

			if ( $_FILES['logo']['size'] ) {
				$attachmentsPath = getKernelAttachmentsDir();

				if ( PEAR::isError( $res = checkLogoFile($_FILES['logo']['name'], $kernelStrings) ) ) {
					$errorStr = $res->getMessage();

					break;
				}

				$fdError = 0;
				$res = @forceDirPath( $attachmentsPath, $fdError ); 
				if ( !$res ) {
					$errorStr = $kernelStrings[ERR_CREATEDIRECTORY];

					break;
				}

				$filePath = $attachmentsPath."/logo.gif";

				if (!@ResizeImage($_FILES['logo']['tmp_name'], $filePath , 250, 100)) {
					$errorStr = $kernelStrings[ERR_ATTACHFILE];
					break;
				}
				$imageUploaded = true;
				$showLogo = true;
			}

			if (isset($showCompanyTop) || isset($showCompanyNameTop)) {
				$advSettings->SetParam(SHOW_COMPANYTOP, $showCompanyTop);
				$advSettings->SetParam(SHOW_COMPANYNAMETOP, $showCompanyNameTop);
				$advSettings->SetParam(SCREEN_THEME, $curTheme);
				if (!empty($companyData["COM_NAME"]))
					$advSettings->SetParam("company_name", $companyData["COM_NAME"]);
				$databaseInfo[HOST_ADVSETTINGS][SHOW_COMPANYTOP] = $showCompanyTop;
				$databaseInfo[HOST_ADVSETTINGS][SHOW_COMPANYNAMETOP] = $showCompanyNameTop;
				//writeUserCommonSetting( $currentUser, SHOW_COMPANYTOP, $showCompanyTop, $kernelStrings );
			}
			$companyData = trimArrayData( $companyData );
			$res = updateCompanyInfo( prepareArrayToStore($companyData), $kernelStrings );
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				if ( $res->getCode() == ERRCODE_INVALIDFIELD )
					$invalidField = $res->getUserInfo();

				break;
			}
			
			//redirectBrowser( PAGE_SIMPLEREPORT, $params );
			break;
		}
		case 2 : {
			if ( $showLogo ) {
				@unlink( $logoPath );
			}
			
			$showLogo = false;
			$deleteLogo = 1;
		}
	}
	
	$themes = aa_listThemes();
	
	$showCompanyTop = $advSettings->GetParam(SHOW_COMPANYTOP);
	$showCompanyNameTop = $advSettings->GetParam(SHOW_COMPANYNAMETOP);
	$curTheme = $advSettings->GetParam(SCREEN_THEME);
	if (!$curTheme)
		$curTheme = ($showLogo && $showCompanyTop) ? "1albino" : "darkblue";
	
	//$showCompanyTop = readUserCommonSetting( $currentUser, SHOW_COMPANYTOP);
	//$showCompanyNameTop = readUserCommonSetting( $currentUser, SHOW_COMPANYNAMETOP);
	
	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['ci_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_COMPANY );	
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "companyinfo.htm");

	if ( !$fatalError ) {
		$preproc->assign( "companyData", prepareArrayToDisplay( $companyData, null, isset($edited) && $edited ) );
		$preproc->assign( "prevName", $prevName );
		$preproc->assign( "showLogo", $showLogo );
		$preproc->assign( "deleteLogo", $deleteLogo );
		$preproc->assign( "current_plan", $current_plan );
		$preproc->assign ("edited", !empty($edited));
		
		$preproc->assign( "showCompanyTop", ($showCompanyTop != "no") );
		$preproc->assign( "showCompanyNameTop", ($showCompanyNameTop != "no"));
		$preproc->assign( "themes", $themes);
		$preproc->assign ("curTheme", $curTheme);
	}

	$preproc->display("company.htm");
?>