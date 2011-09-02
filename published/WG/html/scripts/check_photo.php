<?php
	return;
	// DIRTY HACK. must be corrected!
	
	if ($action == ACTION_NEW &&  base64_decode($WT_ID) == "SBSC" && base64_decode($WST_ID) == "PHOTO") {
		$fields = array ();
		$hasPhotoSection = false;
		$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
		foreach ($typeDescription as $cSectionId => $cSection) {
			$fields = array_merge($fields, array_keys($cSection["FIELDS"]));
			if (strtolower($cSection["ID"]) == "photo")
				$hasPhotoSection = true;
		}
		if (!in_array ("C_X_PHOTO", $fields)) {
			require_once( WBS_DIR."/published/CM/cm.php" );
			$kernelStrings = &$loc_str[$language];
			$cmStrings = &$cm_loc_str[$language];
				
			if (!$hasPhotoSection) {
				$res = cm_addModSection( CONTACT_BASIC_TYPE, ACTION_NEW, array ("LONG_NAME" => array (LANG_ENG => "Photo")), "CONTACT", $kernelStrings, $cmStrings);
				if (PEAR::isError($res))
					die ("Error by creating PHOTO section");
			}
			//$res = cm_addModField( CONTACT_BASIC_TYPE, ACTION_NEW, array ("LONG_NAME" => array (LANG_ENG => "photo"), "SHORT_NAME" => array (LANG_ENG => "photo"), "MAXLEN" => 255,"TYPE" => "IMAGE", "DECPLACES" => 0, "MENU" => "", "UNIQUE" => 0, "MANDATORY" => 0), "Photo", "", $kernelStrings, $cmStrings);
			//if (PEAR::isError($res))
			//	die ("Error by creating PHOTO field");
		}
	}
?>