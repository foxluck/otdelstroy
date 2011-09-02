<?php
	if ($afterAction == "add") {
		$appsIds = split(",",base64_decode($apps));
		
		global $UR_Manager;
		foreach ($appsIds as $cId) {
			$UR_Manager->SetGlobalRightsPath( $currentUser, UR_USER_ID, "/ROOT/$cId", UR_GRANT );
		}
	}
	
?>