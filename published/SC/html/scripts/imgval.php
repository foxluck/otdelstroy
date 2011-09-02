<?php
ob_start();
define('CAPTURE_IMG',true);

define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__))));
include_once(DIR_ROOT.'/includes/init.php');
include_once(DIR_ROOT.'/includes/constants.php');
include_once(DIR_CFG.'/connect.inc.wa.php');
if(!is_dir(DIR_PRODUCTS_PICTURES)){//Patch to dinamicaly create path if it not exists
		$dirPath=DIR_PRODUCTS_PICTURES;
		$currentDir=getcwd();
		$baseDir =WBS_DIR;
		
	

		$baseDir = trim(str_replace('\\','/',$baseDir));
		$dirPath = trim(str_replace('\\','/',$dirPath));
		$strlen = strlen( $baseDir );
		if ( $baseDir[$strlen-1] == "/")
			$baseDir=substr( $baseDir, 0, $strlen-1 );
		if ( strcmp(strtolower(substr($dirPath, 0, strlen($baseDir))),strtolower($baseDir))===0)
			$dirPath = substr( $dirPath, strlen($baseDir)+1 );
		$path=$dirPath;
		$path=str_replace('\\','/',$path);
		$path=str_replace('//','/',$path);
		$dirs = explode('/', $path);
    	$dir=$baseDir.(strlen($baseDir)?'/':'');
    	$oldMask = @umask(0);
    	foreach ($dirs as $part) {
       		$dir.=$part.'/';
        	if (!is_dir($dir) && strlen($dir)>0)
        	{
            	if(!@mkdir($dir, 0777))
					die(sprintf( "Unable to create directory %s", $dir ));
            	@umask($oldMask);
            }
        }
		chdir( $currentDir );
	}

$i = new IValidator();
$i->RndCodes = '0123456789';
$i->RndLength = 4;
ob_get_clean();
$i->generateImage();
//EOF