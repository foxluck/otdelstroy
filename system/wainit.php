<?
	/****
		Initiazlize work with webasyst. Include common packages and configure main(!) paths	
		Load current dbkey.
	*****/

	header('Content-Type: text/html; charset=UTF-8;');

	include_once("kernel.php");

	// Include common packages
	Kernel::incPackage("exceptions");
	Kernel::incPackage("sql_query");
	Kernel::incPackage("files");
	Kernel::incPackage("wbs");
	Kernel::incPackage("db");
	Kernel::incPackage("date");	
	Kernel::incPackage("webquery");
	Kernel::incPackage("localization");
	
	// If cannot load dbkey settings
	try {
		session_start();
		
		if (!Wbs::loadCurrentDBKey()) {
			Wbs::logout();
		}
		
		Wbs::connectDb();			
	} catch (Exception $ex) {
		trigger_error($ex->getMessage (), E_USER_ERROR);
	}
?>
