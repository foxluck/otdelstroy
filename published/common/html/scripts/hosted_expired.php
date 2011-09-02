<?php
$isSSL = isset($_SERVER['HTTPS']); 
$data_url = 'http'.($isSSL?'s':'').'://www.webasyst.net';

$init_required = false;
$AA_APP_ID = "AA";


require_once( "../../../common/html/includes/httpinit.php" );
$localizationPath = "../../../AA/localization";

$appStrings = loadLocalizationStrings( $localizationPath, strtolower($AA_APP_ID) );

if (ClassManager::includeClass('accountname', 'kernel')!==false) {
	// Dbkey aliases mechanism

	$account_name = AccountName::getDomainName();
	$dbkey = AccountName::getHostDBKEY();
	if(!$dbkey)$account_name = '';
} else {
	$dbkey='';
	$account_name = '';
}

$language = isset($_GET['lang'])&&$_GET['lang']==LANG_RUS?LANG_RUS:'';
if(!$language && $dbkey){
	
	$__host_data = loadHostDataFile($dbkey, $appStrings[LANG_ENG]);
	if(!PEAR::isError($__host_data) && isset($__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE]) && $__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE]){
		
		$language = $__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE];
	}
}

if(!$language)$language = LANG_ENG;

$charset = $language == LANG_RUS?'windows-1251':'iso-8859-1';
$locStrings = $appStrings[$language];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!-- saved from url=(0041)http://www.shop-script.com/wa2/login.html -->
<HTML>
	<HEAD>
		<title>Session is expired</title>
		<LINK href="<?=$data_url?>/css/old_main.css" type=text/css rel=stylesheet>
		<script language="JavaScript">
		<!--
			<?php
				if ( isset($_GET['redirect']) ) {
			?>
				if (top) top.document.location.href = "expired.php";
			<?php 
				} 
			?>
		//-->
		</script>
	</HEAD>
<BODY bgColor=white leftMargin=0 topMargin=0 marginwidth="0" marginheight="0">

<CENTER>
  <DIV style="WIDTH: 750px; TEXT-ALIGN: right"></DIV>
<DIV style="PADDING-BOTTOM: 30px; PADDING-TOP: 40px"><A href="<?=$data_url?>/"><IMG src="<?=$data_url?>/images/wa-logo-homepage-<?=$language?>.gif" border=0></A> </DIV>
<DIV 
style="BORDER-RIGHT: #bad4ff 0px solid; BORDER-TOP: #bad4ff 1px solid; BACKGROUND: url(<?=$data_url?>/images/middle-stripe-background-top-bottom.gif) repeat-x 50% top; BORDER-LEFT: #bad4ff 0px solid; PADDING-TOP: 20px; BORDER-BOTTOM: #bad4ff 0px solid">
<DIV style="WIDTH: 750px; TEXT-ALIGN: left">
<CENTER>
<H2 style="MARGIN-TOP: 0px; MARGIN-BOTTOM: 15px; PADDING-BOTTOM: 0px"><?=$locStrings['app_session_expired']?></H2>

<?=$locStrings['app_system_enter']?>
<br />
<br />
<br />

</CENTER></DIV></DIV>
<br />
<P class=small><I><?=$locStrings['app_copyright']?></I></P></CENTER><br />

</BODY></HTML>