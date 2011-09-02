<?php
@ini_set('zend.ze1_compatibility_mode',0);

if(file_exists("kernel/wbs.xml")){
	$xml= simplexml_load_file("kernel/wbs.xml");
	$type=(string)$xml->FRONTEND['type'];
	$type=isset($type)?$type:'$type';
	$__WBS_INSTALL_PATH = (string)@$xml->DIRECTORIES->WEB_DIRECTORY['PATH'];
}else{
	$type='$type';
	$__WBS_INSTALL_PATH = '';
}
switch ($type){
	case 'PD':{
		chdir('published/PD/');include "index.php";
		break;
		break;
	}
	case 'SC':{
		$_GET['frontend']=1;
		chdir('published/SC/html/scripts/');include "index.php";
		break;
	}
	case 'login':{
		header('Location: login/');
		break;
	}
	case 'none':{
		print '<html><head><title></title></head><body><!-- WebAsyst blank page --></body></html>';
		break;
	}
	default:{
		print '<html><head><title></title></head><body><!-- Error read wbs.xml file --></body></html>';
		break;
	}
}
?>