<?php
	include_once ("../../../DD/dd_functions.php");
	include_once ("../../../DD/dd_consts.php");
	
	require_once ("Type.php");
	require_once ("Subtype.php");
	require_once ("SubtypeInplace.php");
	
	$type = new DDUploaderWidgetType ();
	$factory = WidgetTypeFactory::getInstance ();
	$factory->registerWidgetType("DDUploader", $type);
?>
