<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}
switch (true) {
	case (true) :
		$messageStack = array();

		header('Content-type: text/tab-separated-values' );
		header('Content-Disposition: inline; filename="' . $lang_id . '.tsv"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		wbs_exportLocalizationFile( $lang_id, $kernelStrings, $LocalizationStrings, $messageStack );
		exit();
}
?>