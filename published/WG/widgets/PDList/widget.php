<?php
	include_once ("../../../PD/pd_functions.php");
	include_once ("../../../PD/pd_consts.php");
	
	require_once ("Type.php");
	require_once ("Subtype.php");
	require_once ("SubtypeLink.php");
	require_once ("SubtypeGallery.php");
	require_once ("SubtypeAlbum.php");
	
	$type = new PDListWidgetType ();
	$factory = WidgetTypeFactory::getInstance ();
	$factory->registerWidgetType("PDList", $type);
?>