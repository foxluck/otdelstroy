<?php
	switch($_SERVER["HTTP_HOST"]){
		case 'dev.webasyst.net':
		case 'public.webasyst.net':
			$DB_KEY = "WK1245";
			$U_ID = "PUBLIC";
			break;
		default:
			$DB_KEY = "AJAX";
			$U_ID = "TIMUR";
			break;
	}
	
	$result = false;
	$get_key_from_url = true;
	$_GET["DB_KEY"] = base64_encode($DB_KEY);
	require_once("../../../common/html/includes/httpinit.php");
	$DB_KEY = base64_decode($_GET["DB_KEY"]);
	$error = null;
	if (empty($language))
		$language = "eng";
	$kernelStrings = $loc_str[$language];
	$wgStrings = $wg_loc_str[$language];
	
	if (empty($type) || empty($subtype))
		die ("Code error");
	
	if (!empty($doPost)) {
		do {
			session_start ();
			header('P3P: CP="CAO PSA OUR"');
			
			$capWord = @$_POST["captcha"];
			$realCaptcha = @$_SESSION["captcha_word"];
			if ($capWord != $realCaptcha) {
				$error = PEAR::raiseError($wgStrings["createwg_captchaerror_message"]);
				break;
			}		
			$_SESSION["captcha_word"] = "";
			
			require_once( WBS_DIR."/published/WG/wg.php" );
			require_once( WBS_DIR."/published/WG/wg_widgets.php" );
			
			$factory = WidgetTypeFactory::getInstance ();
			
			$WT_ID = $type;
			$WST_ID = $subtype;
			
			$typeObj = $factory->getWidgetType($WT_ID);	
			if (!$typeObj || PEAR::isError($typeObj))
				die("Error widget type");
			$subtypeObj = $typeObj->subtypes[$WST_ID];
			
			if (!$subtypeObj)
				die ("Error widget subtype");
			
			$params = array ();
			$widgetData = $subtypeObj->createNewWidget ($U_ID, $params);
			//$widgetManager = getWidgetManager ();
			//$widgetData = $widgetManager->get(5);
			if (PEAR::isError($error = $widgetData))
				break;
			
			if (!$widgetData)
				die("Error widget creating");
			
			$editSrc = $subtypeObj->getWidgetSrc($widgetData, "mode=edit");
			$googleSrc = $subtypeObj->getWidgetSrc($widgetData, "mode=igoogle");
			$embInfo = $typeObj->getWidgetEmbInfo($widgetData, $subtypeObj->id);
			$result = true;
		} while (false);
		
		if ($result)
			header("Location: $editSrc");
	}
	
	$errorStr = (PEAR::isError($error)) ? $error->getMessage () : null;
?>
<link rel="stylesheet" href="http://www.webasyst.net/main.css" type="text/css">

<body style='padding: 0px; margin: 0px; padding-top: 0px'>
	<form method='POST'>
		<CENTER>
		<input type='hidden' name='doPost' value='1'>
		<input type='hidden' name='language' value='<?php print $language ?>'>
		<div style='text-align: center'>
			<? print $wgStrings["createwg_captcha_label"] ?>:
			<BR>
			<input type='text' name='captcha' size='20'>
			<BR>
			<img src='captcha.php?nocache=<?php print rand(0,100000); ?>'>
		</div>
		<input type='submit' value='<? print $wgStrings["createwg_submit_label"] ?>'>
		
		<? if ($errorStr) print "<BR><BR><font color='red'>$errorStr</font>"; ?>
		</center>

	</form>
</body>