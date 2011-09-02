<?php
include_once(dirname(__FILE__).'/sc.php');

class SC__UR_RO_Screen extends UR_RO_Screen {
	
	function UserPermitted($U_ID){
		
		$result = parent::UserPermitted($U_ID);
		
		$_SESSION['WBS_ACCESS_SC'] = $result;
		
		sc_setSessionData('U_ID', $U_ID);
		
		global $kernelStrings;
		$groups = findGroupsContaningUser($U_ID, $kernelStrings);
		sc_setSessionData('UG_IDs', $groups);

		return $result;
	}
}

class SC__UR_RO_Bool extends UR_RO_Bool{
	
	function Save(&$data){
		
		$result = parent::Save($data);

		$id_type = $data['FIELD'] == 'groupAccessRights'?1:0;
			
		$q_data = array('xDivisionID'=>str_replace('SC__', '', $this->GetID()), 'xU_ID' => $data['REAL_ID'], 'xID_TYPE' => $id_type);

		db_query("DELETE FROM SC_division_access WHERE xDivisionID='!xDivisionID!' AND xU_ID='!xU_ID!' AND xID_TYPE='!xID_TYPE!'", $q_data);
		if($this->__value){
			
			db_query("INSERT SC_division_access (xDivisionID, xU_ID, xID_TYPE) VALUES('!xDivisionID!', '!xU_ID!', '!xID_TYPE!')", $q_data);
		}

		return $result;
	}
}

$__ur_SCApp = new UR_RO_Container( "SC", "app_name_long", "SC" );
$__ur_SCApp = &$__ur_SCApp;
$UR_Manager->AddChild( $__ur_SCApp );

$__ur_SCScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name" );
$__ur_SCScreens = &$__ur_SCScreens;
$__ur_SCApp->AddChild( $__ur_SCScreens );

$__ur_SCScreens->AddChild( new SC__UR_RO_Screen( "FM", "sc_screen_long_name", "sc_screen_short_name", "frame.php", "SC" ));

global $wbs_database;
if(isset($wbs_database) && is_object($wbs_database)){
	sc_initURObjects($__ur_SCApp);
}
?>