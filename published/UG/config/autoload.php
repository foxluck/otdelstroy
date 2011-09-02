<?php

$__autoload = array(
	"UGController" => "published/UG/lib/UGController.class.php",
    "UGFrontController" => "published/UG/lib/UGFrontController.class.php",
	"UGViewAction" => "published/UG/lib/UGViewAction.class.php",
	"UGAjaxAction" => "published/UG/lib/UGAjaxAction.class.php",
    "WidgetdsRTE" => "published/UG/lib/entity/WidgetdsRTE.class.php",
	'CMPlugins' => 'published/CM/lib/entity/CMPlugins.class.php',
	'CMPlugin' => 'published/CM/lib/entity/CMPlugin.class.php',
);

AutoLoad::load($__autoload);

// Add UG Rule for the UG Classes
//AutoLoad::addRule('substr($class, 0, 2) == "UG"', "published" . DIRECTORY_SEPARATOR . "UG/2.0/");

?>