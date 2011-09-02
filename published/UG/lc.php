<?php

include dirname(__FILE__)."/../../system/lib/localization/init.php";

$app_id = 'UG'; 

$lc = new LocalizationCompiler();
$lc->domains = array(
	'ru' => 'webasyst'.$app_id,
	'en' => 'webasyst'.$app_id,
	'de' => 'webasyst'.$app_id
);

$dir = dirname(__FILE__);
$lc->source_path = $dir;
$lc->backup_path = false;
$lc->compile_path = $dir;
		
$lc->files_include = ".+\.(php|js|html)";
$lc->files_compile = ".+\.(js)";
$lc->files_words = ".+\.(js|html)";

$lc->dirs_exclude = "(\.svn|\.xml|locale)";

$lc->locale_path = $dir.DIRECTORY_SEPARATOR."locale";

$lc->split_on_subfolder = false;
$lc->update_files = false;
$lc->update_locale = true;

$lc->update_complile = true;

$lc->recursive = 5;

$lc->exec();

$lc->update_complile = false;
$lc->source_path = realpath($dir."/../CM/lib");

$lc->exec();
?>