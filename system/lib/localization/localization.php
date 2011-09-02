<?php 
// Autoload for the Localization
function __autoload($class)
{
	include($class.".class.php");
}
// Init localization
$lc = new LocalizationCompiler();
// Find in sourse files unmarked words
$lc->update_files = false;
// Update dictionary, add new words in the file *.po
$lc->update_locale = true;
// Recompile templates and js
$lc->update_complile = true;
// Recursive, include subfolders
$lc->recursive = false;
// Run process
$lc->exec();
?>