<?php
//print function_exists('redirectBrowser')?'redirectBrowser':' - ';
//print PAGE_DB_AUTH.' '.PAGE_DB_WBSADMIN;
if(!function_exists('redirectBrowser')){
	function redirectBrowser()
	{
		exit('Access denied');
	}
}


if(!auth_isAuth()){
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	if(!defined('WBS_AUTH_PAGE')){
		defined('PAGE_DB_AUTH')?redirectBrowser(constant('PAGE_DB_AUTH'),array()):exit('Access denied');;
	}
}elseif(!isset($_GET['logout'])){
	if(defined('WBS_AUTH_PAGE')){
		defined('PAGE_DB_AUTH')?redirectBrowser( constant('PAGE_DB_WBSADMIN'),array()):exit('Access denied');
	}
}else{
	auth_logout();
	defined('PAGE_DB_AUTH')?redirectBrowser(constant('PAGE_DB_AUTH'),array()):exit('Access denied');
}


function auth_setPassword($user,$kernelStrings ){

	$requiredFields = array( 'LOGIN','PASSWORD1','PASSWORD2');
	if ( PEAR::isError( $invalidField = findEmptyField($user, $requiredFields) ) ) {
		$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

		return $invalidField;
	}
	if($user['PASSWORD1']!=$user['PASSWORD2'])
	return PEAR::raiseError('not equal passwords');
	$pwd=serialize(array(md5($user['LOGIN']),md5($user['PASSWORD1']),md5($user['LOGIN'].$user['PASSWORD1'])));
	$fh=fopen(WBS_DIR.'/temp/.wbs_protect','w');
	if($fh){
		fwrite($fh,$pwd);
		fclose($fh);
		auth_login();
		return true;
	}else{
		$path=str_replace(array('//','\\'),'/',WBS_DIR.'/temp/');
		return PEAR::raiseError('Couldn\'t write file at '.$path);
	}

}
function auth_isAuth()
{
	session_start();
	$result=session_is_registered('WBS_AUTH_USER');
	session_write_close();
	if(!$result){
		$result=!auth_setted();
	}

	return $result;
}
function auth_checkPassword($login,$password)
{
	if(file_exists(WBS_DIR.'/temp/.wbs_protect')){
		if($fh=fopen(WBS_DIR.'/temp/.wbs_protect','r')){
			$pwd=fread($fh,filesize(WBS_DIR.'/temp/.wbs_protect'));
			fclose($fh);
			$pwd=unserialize($pwd);
			if($pwd){
				if(md5($login)==$pwd[0]&&md5($password)==$pwd[1]&&md5($login.$password)==$pwd[2]){
					auth_login();
					return true;
				}else{
					return false;
				}
			}else{
				return PEAR::raiseError('File corrupted');
			}

		}else{
			return PEAR::raiseError('Couldn\'t read file');
		}
	}else{
		return PEAR::raiseError('File not exists');
	}
}
function auth_setted()
{
	if(file_exists(WBS_DIR.'/temp/.wbs_protect')&&!is_dir(WBS_DIR.'/temp/.wbs_protect')){
		if($fh=fopen(WBS_DIR.'/temp/.wbs_protect','r')){
			$pwd=fread($fh,filesize(WBS_DIR.'/temp/.wbs_protect'));
			fclose($fh);
			$pwd=unserialize($pwd);
			if($pwd){
				return true;
			}else{
				return PEAR::raiseError('File corrupted');
			}

		}else{
			return PEAR::raiseError('Couldn\'t read file');
		}
	}else{
		/*		if(PEAR::isError($res=auth_setPassword('admin','1234'))){
		return $res->getMessage;
		}*/
		return false;
	}
}
function auth_logout()
{
	session_start();
	if(session_is_registered('WBS_AUTH_USER'))
	session_unregister('WBS_AUTH_USER');
	session_write_close();
}
function auth_login()
{
	session_start();
	session_set_cookie_params(1800);
	session_register('WBS_AUTH_USER');
	//$_SESSION['WBS_AUTH_USER'] = array('time'=>time(),'ip'=>null);
	session_write_close();
}
?>