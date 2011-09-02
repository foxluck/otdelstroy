<?php
	require_once ("SBSCWidgetType.php");
	require_once ("subtypes.php");

	$type = new SBSCWidgetType ();
	$factory = WidgetTypeFactory::getInstance ();
	$factory->registerWidgetType("SBSC", $type);
?>