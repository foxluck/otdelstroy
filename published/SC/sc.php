<?php
function sc_getLanguageByISO3($iso3){
	
	$iso3 = strtolower($iso3);
	static $iso3_2_lang;
	if(!is_array($iso3_2_lang))$iso3_2_lang = array();
	
	if(isset($iso3_2_lang[$iso3]))return $iso3_2_lang[$iso3];
	
	$iso3_iso2 = array(
		'eng' => 'en',
		'rus' => 'ru'
	);
		
	$dbq_default_language = "
		SELECT * FROM SC_language LEFT JOIN SC_settings ON SC_settings.settings_value = SC_language.`id`
		WHERE SC_settings.settings_constant_name = 'CONF_DEFAULT_LANG'
	";
	
	$dbq_select_language = "
		SELECT * FROM SC_language WHERE `iso2`='!iso2!' 
	";
	
	$dbres = db_query(isset($iso3_iso2[$iso3])&&is_array($iso3_iso2[$iso3])?$dbq_select_language:$dbq_default_language, array('iso2' => isset($iso3_iso2[$iso3])?$iso3_iso2[$iso3]:''));
	$iso3_2_lang[$iso3] = db_fetch_array($dbres);
	return $iso3_2_lang[$iso3];
}

function sc_getDivisionByUKey($ukey){
	
	$dbres = db_query("SELECT * FROM SC_divisions WHERE xUnicKey='admin' LIMIT 1");
	return db_fetch_array($dbres);
}

function sc_translate($words, $lang_iso3 = ''){
	
	global $language;
	$iso3 = $lang_iso3?$lang_iso3:(isset($language)&&$language?$language:'');
	
	if(!$iso3){
		
		sc_translate($words, 'eng');
		sc_translate($words, 'rus');
		return;
	}
	$lang_data = sc_getLanguageByISO3($iso3);
	db_query ("set character_set_client='utf8'");
	db_query ("set character_set_results='utf8'");
	db_query ("set collation_connection='utf8_general_ci'");
	$dbres = db_query("SELECT `id`, `value` FROM SC_local WHERE lang_id=".intval($lang_data['id'])." AND `id` IN ('".implode("', '", xEscapeSQLstring($words))."') GROUP BY `id`");
	while ($row = db_fetch_array($dbres)) {
		sc_setLoc($row['id'], $row['value']);
	}
}

function sc_setLoc($loc, $str){
	
	global $sc_loc_str;
	global $language;

	$langID = strlen( $language ) ? $language : LANG_ENG;
	$sc_loc_str[$langID][$loc] = $str;
}

function sc_getAdminDivs(){
	
	$admin_division = sc_getDivisionByUKey('admin');
	if(!$admin_division)return;
	
	$screen_divisions = array();
	$locs = array();
	$dbres = db_query("SELECT * FROM SC_divisions WHERE xEnabled=1 AND xParentID=".xEscapeSQLstring($admin_division['xID']).' ORDER BY xPriority DESC');
	while($row = db_fetch_array($dbres)){

		$_db_res = db_query("SELECT * FROM SC_divisions WHERE xEnabled=1 AND xParentID=".xEscapeSQLstring($row['xID']).' ORDER BY xPriority DESC');
		if(db_result_num_rows($_db_res)){
			
			$row['sub_divs'] = array();
			while($_row = db_fetch_array($_db_res)){

				$locs[] = $_row['xName'];
				$row['sub_divs'][] = $_row;
			}
		}else{
			$row['sub_divs'] = null;
		}
		$locs[] = $row['xName'];
		$screen_divisions[] = $row;
	}
	
	sc_translate($locs);
	
	return $screen_divisions;
}

function sc_getDefaultDivisionID(){
	
	$dbres = db_query('SELECT xID FROM SC_divisions WHERE xUnicKey="admin_orders"');
	$row = db_fetch_array($dbres);
	return $row['xID'];
}

function sc_getParentDivision($division_id){
	
	$dbres = db_query('SELECT t1.* FROM SC_divisions t1 LEFT JOIN SC_divisions t2 ON t1.xID=t2.xParentID WHERE t2.xID='.intval($division_id));
	return db_fetch_array($dbres);
}

function sc_getDefaultChildDivisionID($division_id){
	
	$dbres = db_query('SELECT t1.xID FROM SC_divisions t1 LEFT JOIN SC_divisions t2 ON t1.xUnicKey=t2.xLinkDivisionUKey WHERE t2.xID='.intval($division_id));
	$row = db_fetch_array($dbres);
	return $row['xID'];
}

function sc_initURObjects(&$__ur_SCApp){
	
	global $language;

	$screen_divisions = sc_getAdminDivs();
	foreach ($screen_divisions as $division){
		
		if(is_array($division['sub_divs'])){
			
			$__ur_cont = new UR_RO_Container( UR_FUNCTIONS, Localization::get($division['xName'], 'SC', $language));
			$__ur_cont = &$__ur_cont;
			$__ur_SCApp->AddChild( $__ur_cont );
			
			foreach ($division['sub_divs'] as $_division){
				$__ur_cont->AddChild( new SC__UR_RO_Bool( 'SC__'.$_division['xID'], Localization::get($_division['xName'], 'SC', $language), 'FM' ) );
			}
			unset($__ur_cont);
		}else{
			$__ur_SCApp->AddChild( new SC__UR_RO_Bool( 'SC__'.$division['xID'], Localization::get($division['xName'], 'SC', $language), 'FM' ));
		}
	}
}
	function sc_onSuccessPageUserAuthorization( $params){
		
		$_SESSION['WBS_ACCESS_SC'] = true;
		global $currentUser;
		sc_setSessionData('U_ID', $currentUser);
		return EVENT_APPROVED;
	}
	
	function sc_onFailurePageUserAuthorization($params){
		
		$_SESSION['WBS_ACCESS_SC'] = false;
		global $currentUser;
		sc_setSessionData('U_ID', $currentUser);
		return EVENT_APPROVED;
	}

	function sc_setSessionData($key, $val){
		
		$_SESSION['__WBS_SC_DATA'][$key] = $val;
	}

	function sc_getSessionData($key){
		
		return isset($_SESSION['__WBS_SC_DATA'][$key])?$_SESSION['__WBS_SC_DATA'][$key]:'';
	}
?>