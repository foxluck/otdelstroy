<?
	include_once("kernel.php");

	// Include common packages
	Kernel::incPackage("exceptions");
	Kernel::incPackage("files");
	Kernel::incPackage("localization");
	Kernel::incPackage("wbs");
	Kernel::incPackage("webquery");

	try {
		session_start();
		
		if (!Wbs::loadCurrentDBKey()) {
			Wbs::logout();
		}
	} catch (Exception $ex) {
		trigger_error($ex->getMessage (), E_USER_ERROR);
	}
?>