<?php
header('Content-Type: text/html; charset=UTF-8;');
$wsdl_param = "wsdl";

	$loginScript = true;

	if ( isset($_GET[$wsdl_param]) || isset($_GET[strtoupper($wsdl_param)]) ) {
		require "common/soap/scripts/authorize.php";
		die();
	}

	$init_required = false;
	$language = "eng";
	$AA_APP_ID = "AA";

	require_once( "common/html/includes/httpinit.php" );
	
	if(onWebAsystServer()){
		
		include 'new_login.php';
		exit(1);
	}
	 
	if (file_exists(WBS_DIR."/kernel/wbs.xml") && !file_exists(WBS_DIR.'/published/wbsadmin/html/scripts/multidbkey.php')) {
		$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
		$dbkey=(string)$xml->FRONTEND['dbkey'];
	} else {
		$dbkey='';
	}
	
	if ($dbkey) {
	    define("GET_DBKEY_FROM_URL", true);
	    $_GET['DB_KEY'] = base64_decode($dbkey);
	    Wbs::loadCurrentDbKey();
	    $isRemember = Wbs::getDbkeyObj()->getAdvancedParam('show_remember');
	} else {
	    $isRemember = false;
	}
	
	// If user already authorized
	if ((Wbs::checkCurrentUserSession() || Wbs::cookieAuthorize()) && Env::Session("wbs_username")) {
		header('Location: index.php');
		exit();
	} else {
        session_unset();        
        session_destroy();
	}
	
	$language = isset($_GET['lang'])&&$_GET['lang']==LANG_RUS?LANG_RUS:'';
	if(!$language && $dbkey){
		
		$__host_data = loadHostDataFile($dbkey, $appStrings[LANG_ENG]);
		if(!PEAR::isError($__host_data) && isset($__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE]) && $__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE]){
			
			$language = $__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE];
		}
	}elseif (!$language){
		$language = 'eng';
	}
	$localizationPath = "../published/AA/localization";
	$appStrings = loadLocalizationStrings( $localizationPath, strtolower($AA_APP_ID) );
	$locStrings = $appStrings[$language];
	$isSSL = isset($_SERVER['HTTPS']);
	
	$warningStr=array();
	if(file_exists(WBS_DIR.'/install.php')){
		$warningStr[]=$locStrings['app_install_protect'];
		//'File install.php exists in your main WebAsyst installation folder. You should delete this file manually.';
		//$locStrings['app_'];
		//"В корневой директории WebAsyst существует файл install.php. Вам необходимо удалить этот файл вручную."';
	}
	if(file_exists(WBS_DIR.'/wbs.tgz')){
		$warningStr[]=$locStrings['app_install_wbstgz'];
		//'File install.php exists in your main WebAsyst installation folder. You should delete this file manually.';
		//$locStrings['app_'];
		//"В корневой директории WebAsyst существует файл install.php. Вам необходимо удалить этот файл вручную."';
	}
	if(!file_exists(WBS_DIR.'/temp/.wbs_protect')){
		$warningStr[]=sprintf($locStrings['app_installer_protect'],'admin.php');
//		'<strong>WebAsyst Installer is not protected</strong>. You should <a href="admin.php">enter login and password</a> to protect Installer.';
		//'<strong>WebAsyst Installer не защищен</strong>. Придумайте логин и пароль для доступа к Installer и <a ссылка на главную страницу инсталлера>сохраните его в настройках</a>'
	}

	if ( isset($_GET['error']) && !isset($edited) )
		$errorStr = base64_decode( $_GET['error'] );

	if ( isset($_GET['UID']) && isset($_GET['DBKEY']) && isset($_GET['PASSWORD']) && !isset($edited)) {
		$loginData = array();
		if (strlen($dbkey))
			$loginData['DB_KEY'] = $dbkey;
		else
			$loginData['DB_KEY'] = $_GET['DBKEY'];
		$loginData['U_ID'] = $_GET['UID'];
		$loginData['U_PASSWORD'] = $_GET['PASSWORD'];

		if ( isset($_GET['PAGE']) )
			$loginData['PAGE'] = $_GET['PAGE'];
		else
			$loginData['PAGE'] = null;

		//
		// System initialization start
		//

		$databaseInfo = loadHostDataFile($_GET['DBKEY']);

		if ( is_null($appListToLoad) )
			$host_applications = getHostApplications();
		else
			$host_applications = $appListToLoad;

		if ( PEAR::isError($host_applications) )
			die( "Unable load host application list" );

		$host_applications = array_merge( $host_applications, array( MYWEBASYST_APP_ID, WIDGETS_APP_ID, AA_APP_ID, "UG" ) );

		// System information registering
		//
		foreach ( $host_applications as $APP_ID )
			if ( !performAppRegistration( $APP_ID ) )
				die( "Error registering applications" );

		$ur_applications = array();
		foreach ( $host_applications as $application )
			$ur_applications[$application] = $application;

		foreach ( sortApplicationList( $ur_applications ) as $application )
			require_once( WBS_PUBLISHED_DIR . "/". strtoupper( $application ) . "/" .WBS_UR_APPCLASS_FILE );

		//
		// System initialization end
		//

		$res = process_htmllogin( $locStrings, $loginData, true );
		if ( PEAR::isError($res) )
			$errorStr = $res->getMessage();
	}

	if(isset($_GET['LOGIN']) && isset($_GET['HASH']) && ClassManager::includeClass('LoginHash', 'kernel')!==false){
	
		if (strlen($dbkey))
			$userdata['DB_KEY'] = $dbkey;
		elseif(isset($_GET['DB_KEY']))
			$userdata['DB_KEY'] = $_GET['DB_KEY'];
		
		$userdata['U_ID'] = $userdata['LOGIN'] = $_GET['LOGIN'];
		$userdata['HASH'] = $_GET['HASH'];
		
		$lhashEntry = new LoginHash();
		$res = $lhashEntry->loadByHash($userdata['DB_KEY'], $userdata['HASH']);
		
		if(isset($lhashEntry->redirect))$userdata['REDIRECT'] = $lhashEntry->redirect;
		
		$loginPageURL = null;
		if ( isset($thisPageURL) && strlen($thisPageURL) )
			$loginPageURL = $thisPageURL;

		$res = process_htmllogin( $locStrings, $userdata, false, $loginPageURL, isset($_POST['C']) ? $_POST['C'] : false );
		if ( PEAR::isError($res) ) {
			$errorStr = $res->getMessage();
		}
	}
	
	if ( isset( $edited ) && $edited ) {
    
		switch ( true ) {
			case true:
				$userdata = trimArrayData( $userdata );
				if (strlen($dbkey))
					$userdata['DB_KEY'] = $dbkey;

				$requiredFields = array( "U_ID", "DB_KEY" );
				if ( PEAR::isError( $res = findEmptyField($userdata, $requiredFields) ) ) {
					$errorStr = $locStrings['app_allfields_message'];

					break;
				}

				$DB_NAME = null;
				$HOST_ID = null;

				$loginData = $userdata;
				$loginData["U_PASSWORD"] = md5($loginData["U_PASSWORD"]);

				$loginPageURL = null;
				
				if (!isset($thisPageURL) || !$thisPageURL) {
				    $thisPageURL = isset($_POST['from']) ? $_POST['from'] : null;
				}
				if ( isset($thisPageURL) && strlen($thisPageURL) )
					$loginPageURL = $thisPageURL;

				$loginData['REDIRECT'] = $loginPageURL; 
					
				$res = process_htmllogin( $locStrings, $loginData, false, $loginPageURL, isset($_POST['C']) ? $_POST['C'] : false );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					break;
				} 
		}
	} else {
		$userdata = array();

		if (strlen($dbkey))
			$userdata['DB_KEY'] = $dbkey;
		else
			if ( isset( $_GET["DB_KEY"] ) )
				$userdata["DB_KEY"] = $_GET["DB_KEY"];
		if ( isset( $_GET["LOGIN"] ) )
			$userdata["U_ID"] = $_GET["LOGIN"];

		if ( !isset($userdata["DB_KEY"]) || !strlen($userdata["DB_KEY"]) )
			if ( isset($_COOKIE["wbs_login_host"]) )
				$userdata["DB_KEY"] = $_COOKIE["wbs_login_host"];

		if ( !isset($userdata["DB_KEY"]) || !strlen($userdata["DB_KEY"]) )
			$userdata["DB_KEY"] = null;

		if ( !isset($userdata["U_ID"]) || !strlen($userdata["U_ID"]) )
			$userdata["U_ID"] = null;
	}

	if ( isset($thisPageURL) && strlen($thisPageURL) ) {
		redirectBrowser( $thisPageURL, array('errorMessage'=>urlencode($errorStr)) );
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="login/res/styles.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
<!--
 function MM_findObj(n, d) { //v4.0
   var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
  d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
   if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
   for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
   if(!x && document.getElementById) x=document.getElementById(n); return x;
 }

 function focusControl(objName) {
   var obj = MM_findObj('userdata[DB_KEY]');
   if (obj && obj.value == "")
		obj.focus( );
	else {
		obj = MM_findObj('userdata[U_ID]');
		if (obj && obj.value == "" )
			obj.focus( );
		else
			obj = MM_findObj('userdata[U_PASSWORD]');
			if (obj )
				obj.focus( );
	}
 }

	function switchAction()
	{
         var access = document.forms[0].access;
         var actionURL;
         
         if (access&& access.checked )
                 actionURL = "https://"+location.host+"<?php echo (!empty($_SERVER['REQUEST_URI'])) ? htmlspecialchars($_SERVER['REQUEST_URI'],ENT_QUOTES) : $_SERVER['PHP_SELF'] ?>";
         else
                 actionURL = "http://"+location.host+"<?php echo (!empty($_SERVER['REQUEST_URI'])) ? htmlspecialchars($_SERVER['REQUEST_URI'],ENT_QUOTES) : $_SERVER['PHP_SELF'] ?>";

         document.forms[0].action = actionURL;
         
         return true;
	}

 if ( parent.frames.length > 1 )
	parent.location = 'login.php';

//-->
</script>
</head>
<body class="default" onLoad="focusControl('userdata[U_ID]')">

<?php
    if(count($warningStr)){
    foreach ($warningStr as $warning)
    echo '<div id="message-block" style="border: 0px solid #C75A5A;border-width: 0px 0px 1px 0px;background-color: #F7CFCF;padding:10px;margin: 0px;color: black;font-weight: bold;">
	'.$warning."\n</div>";
    }
?>
<div style="margin:150px auto; padding: 15px;width:350px;">
<form name="form" method="post" action="login.php" onSubmit="return switchAction()">
<img src="common/html/res/images/logo.png" alt="" style="margin-left:50px;" >
<br>
<?php if ( isset($errorStr) && strlen($errorStr) ) {
echo '<br><span class="invalidField">'.$errorStr.'</span><br><br>';
}
if (strlen($dbkey)) {?>
<input name="userdata[DB_KEY]" type="hidden" value="<?php echo htmlspecialchars($userdata["DB_KEY"], ENT_QUOTES);?>" style="width: 80%;height: 24px;font-size:18px;" maxlength="100">
<?php } else { ?>
<span style="white-space: nowrap;">Database Key:</span><br />
<input name="userdata[DB_KEY]" type="text" value="<?php echo htmlspecialchars($userdata["DB_KEY"], ENT_QUOTES);?>" style="width: 80%;height: 24px;font-size:18px;" maxlength="100">
<br /><br />
<?php } ?>
<span style="white-space: nowrap;"><?php echo $locStrings['app_loginname'] ?><input type="hidden" name="edited" value="1"></span><br />
<input name="userdata[U_ID]" type="text" value="<?php echo htmlspecialchars($userdata["U_ID"], ENT_QUOTES); ?>" style="width: 80%;height: 24px;font-size:18px;" maxlength="20"><br /><br />
<?php echo $locStrings['amu_password_label'] ?>
<br>
<input name="userdata[U_PASSWORD]" type="password" style="width: 80%;height: 24px;font-size:18px;">
<br />
<br />
<?php if ($isRemember) { ?>
<input name="remember" type="checkbox" value="1"><?php echo $locStrings['app_remember']?>
<br />
<?php } ?>
<input name="access" type="checkbox" value="ssl" <?php if ($isSSL) echo "checked"; ?>><?php echo $locStrings['app_use_ssl']?>
<br />
<a href="forgot.php" title="<?php echo  $locStrings['app_forgotpassword_link'];?>"><?php echo $locStrings['app_forgotpassword_link'];?></a>
<br>
<input name="enter" type="submit" value="<?php echo $locStrings['app_login_btn'];?>" style="font-size:150%;padding:0px auto;margin-left:50%;width:30%;">
<br>
<input type="hidden" name="C" value="<?php if(isset($_GET['C'])) echo htmlspecialchars($_GET['C'], ENT_QUOTES); ?>">
</form>
</div>
</body>
</html>