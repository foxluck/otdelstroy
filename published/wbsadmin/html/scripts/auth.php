<?php
	$init_required = false;
	define('WBS_AUTH_PAGE',true);
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	
	//$templateName = "classic";
//	$language = LANG_ENG;
	
	// Load application strings
	//
	///$kernelStrings = $loc_str[$language];
	//$db_locStrings = $db_loc_str[$language];
	
	
	$fatalError = false;
	$errorStr = null;
	
	if(isset($_POST['user'])){
		$user=$_POST['user'];
		if(strcmp(md5(WBS_DIR.base64_decode($user['CODE1'])),base64_decode($user['CODE2']))!=0){
			$errorStr=translate('auth_fake_data');
			$fatalError=true;
	
		}elseif(time()-base64_decode($user['CODE1'])<2){
			$errorStr=translate('auth_brute_force');
			$fatalError=true;
	
		}elseif(time()-base64_decode($user['CODE1'])>300){
			$errorStr=translate('auth_login_timeout');
			$fatalError=true;
	
		}else{
			$check=auth_checkPassword($user['LOGIN'],$user['PASSWORD']);
			if(!PEAR::isError($check)){
				if($check){
					
					//exit;
					//var_dump(PAGE_DB_WBSADMIN);exit;
					//header('location: ',PAGE_DB_WBSADMIN);exit();
					redirectBrowser(PAGE_DB_WBSADMIN,array());
					//print "GOOOd";exit;
				}else{
					$errorStr=translate('auth_invalid_login');
					$fatalError=true;
				}
			}else{
				$errorStr=$check->getMessage();
				$fatalError=true;
			}
		}
	}

	//extract(wbs_getSystemStatistics());
	//$systemConfiguration['link']=null;
//var_dump(array($templateName, $kernelStrings, $language));

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

	//$preproc->assign( 'systemConfiguration', $systemConfiguration );
	if(!is_array($user)){
		$user=array();
	}
	$time=time();
	$user['CODE1']=base64_encode($time);
	$user['CODE2']=base64_encode(md5(WBS_DIR.$time));
	$user['PASSWORD']='';

	$preproc->assign( 'user', $user);
	$preproc->assign( 'date', date('j.m.Y H:i:s'));

	$preproc->assign( PAGE_TITLE, translate(6) );
	$preproc->assign( FORM_LINK, PAGE_DB_AUTH );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

//	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
//	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );

//	$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );
//	$preproc->assign ( 'waStrings', $db_locStrings);
	//print ":F";exit;


	$preproc->assign( "mainTemplate","auth.htm" );
	$preproc->display( "main.htm" );

?>