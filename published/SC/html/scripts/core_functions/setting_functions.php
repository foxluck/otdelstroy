<?php
// Purpose  see settingInstall() function
// Returns  group ID
function settingGetFreeGroupId()
{
	return 1;
}

function settingGetConstNameByID($_SettingID){

	$ReturnVal = '';
	$sql = '
		SELECT settings_constant_name FROM '.SETTINGS_TABLE.' WHERE settingsID='.intval($_SettingID).'
	';
	@list($ReturnVal) = db_fetch_row(db_query($sql));
	return $ReturnVal;
}

function settingGetAllSettingGroup()
{
	$q = db_query( "select settings_groupID, settings_group_name, sort_order from ".
	SETTINGS_GROUPS_TABLE.
	" where settings_groupID != ".settingGetFreeGroupId()." AND settings_groupID != 7".
	" order by sort_order, settings_group_name " );
	$res = array();
	while( $row = db_fetch_row($q) )
	$res[] = $row;
	return $res;
}


function settingGetSetting( $constantName )
{
	$q = db_query("select settingsID, settings_groupID, settings_constant_name, ".
	" settings_value, settings_title, settings_description, ".
	" settings_html_function, sort_order ".
	" from ".SETTINGS_TABLE.
	" where settings_constant_name='$constantName' " );
	$row = db_fetch_row($q);
	if(defined($row['settings_title'])){
		$row['settings_title'] = constant($row['settings_title']);
	}
	if(defined($row['settings_description'])){
		$row['settings_description'] = constant($row['settings_description']);
	}
	return $row;
}


function settingGetSettings( $settings_groupID )
{
	$q = db_phquery('
		SELECT settingsID, settings_groupID, settings_constant_name, settings_value, settings_title, settings_description, settings_html_function, sort_order
		FROM ?#SETTINGS_TABLE WHERE settings_groupID=?
		ORDER BY sort_order, settings_title',$settings_groupID);
	$res = array();
	while( $row = db_fetch_row($q) ){
		if(defined($row['settings_title'])){
			$row['settings_title'] = constant($row['settings_title']);
		}
		if(defined($row['settings_description'])){
			$row['settings_description'] = constant($row['settings_description']);
		}
		$res[] = $row;
	}
	return $res;
}

function _setSettingOptionValue( $settings_constant_name, $value ){
	$res = _getSettingOptionValue($settings_constant_name);
	if($res === false){

		$dbq = '
			INSERT INTO ?#SETTINGS_TABLE (settings_value, settings_constant_name)
			VALUES(?, ?)
		';
	}else{

		$dbq = '
			UPDATE ?#SETTINGS_TABLE SET settings_value=? WHERE settings_constant_name=?
		';
	}
	db_phquery($dbq, $value,$settings_constant_name);
}

function _getSettingOptionValue( $settings_constant_name ){
	$sql = 'SELECT `settings_value` FROM `?#SETTINGS_TABLE` WHERE `settings_constant_name`=?';
	$q = db_phquery($sql,$settings_constant_name);
	$result = false;
	if ( $row = db_fetch_assoc( $q ) ){
		$result = $row['settings_value'];
	}
	return $result;
}

function _setSettingOptionValueByID( $settings_constant_id, $value )
{
	$dbq = 'UPDATE '.SETTINGS_TABLE.' SET settings_value=? WHERE settingsID=?';

	db_phquery($dbq,$value,(int)$settings_constant_id);
}

function _getSettingOptionValueByID( $settings_constant_id ){

	$SQL = '
		SELECT settings_value FROM ?#SETTINGS_TABLE WHERE settingsID=?
	';
	$q = db_phquery($SQL, $settings_constant_id);
	if ( $row = db_fetch_row( $q ) )
	return $row["settings_value"];
	return null;
}


function settingCallHtmlFunction( $constantName ){

	$SQL = '
		SELECT settings_html_function, settingsID, settings_constant_name FROM ?#SETTINGS_TABLE WHERE settings_constant_name=?
	';
	$q = db_phquery($SQL, $constantName);
	if( $row = db_fetch_row($q) )
	{
		$function 	=  $row["settings_html_function"];
		$settingsID	=  $row["settingsID"];
		return __callHtmlFunction($function,$settingsID);
	}
	return false;
}

function __callHtmlFunction($function,$settingsID = 0)
{
	$code = "";
	if ( preg_match('/,[ ]*$|\([ ]*$/',$function)){
		$code = '$str='.$function.$settingsID.');';
	}else{
		$code = '$str='.$function.';';
	}
	$str = '';
	eval($code);
	return $str;
}

function settingCallHtmlFunctions( $settings_groupID )
{
	$q = db_phquery('SELECT settings_html_function, settingsID FROM ?#SETTINGS_TABLE WHERE settings_groupID=? ORDER BY sort_order, settings_title', $settings_groupID );
	$controls = array();
	while( $row = db_fetch_row($q) )
	{
		$function 	=  $row["settings_html_function"];
		$settingsID	=  $row["settingsID"];
		$str = "";
		if ( substr($function, -1, 1) != ')' )
		eval( "\$str=".$function."$settingsID);" );
		else
		eval( "\$str=".$function.";" );
			
		$controls[] = $str;
	}
	return $controls;
}

// Purpose	generate define directive withhelp eval function
function settingDefineConstants(){
	$Register = &Register::getInstance();	
	$q = db_phquery('SELECT `settingsID`, `settings_constant_name`, `settings_value` FROM `?#SETTINGS_TABLE`');
	while( $row = db_fetch_assoc($q) ){
		if($row['settings_constant_name']=='CONF_CURRENT_THEME'
			&&SystemSettings::is_hosted()
		&&((SystemSettings::get('DB_KEY')=='DEMO')||(SystemSettings::get('DB_KEY')=='DU2949'))){
			$demo_theme_id = isset($_GET['demo_theme_id'])?$_GET['demo_theme_id']:sc_getSessionData('demo_theme_id');
				
			if(isset($_GET['demo_theme_id'])){
				unset($_GET['demo_theme_id']);
			}

			if (preg_match('/[a-z0-9\-_\+]+/ui', $demo_theme_id, $matches)){
				$demo_theme_id = strtolower($matches[0]);
			}else{
				$demo_theme_id = '';
			}
				
			if(strlen($demo_theme_id)){
				sc_setSessionData('demo_theme_id',$demo_theme_id);
			}else{
				$demo_theme_id = $row['settings_value'];
			}
			define($row['settings_constant_name'],$demo_theme_id);

		}elseif($row['settings_constant_name']=='CONF_SHOP_URL'){
			$value = $row['settings_value'];
			$value = preg_replace('(^.*://)','',$value);
			$value = preg_replace('(/$)','',$value);
			$value = preg_replace('([/]{2,})','/',$value);
			define($row['settings_constant_name'],$value);
		}elseif(in_array($row['settings_constant_name'],array('CONF_DEFAULT_TITLE','CONF_SHOP_NAME','CONF_HOMEPAGE_META_DESCRIPTION','CONF_HOMEPAGE_META_KEYWORDS'))){
			//NOTICE this settings will be defined at abstract::head interface
			$value = unserialize( $row['settings_value']);
			$Register->set($row['settings_constant_name'],$value);	
			unset($value);
		}else{
			if(!defined($row['settings_constant_name'])){
				$value = $row['settings_value'];
				//$value = (preg_match('/^\d+\.{0,1}\d*$/u', $value)&&(strlen(floatval($value))==strlen($value)))? floatval($value) : $value;
				define($row['settings_constant_name'],$value);
			}else{
				//db_phquery('DELETE FROM `?#SETTINGS_TABLE` WHERE `settingsID`=?',$row['settingsID']);
				//DEBUG:
				//log_error(1024,"Duplicate constant name {$row['settings_constant_name']} in table ".SETTINGS_TABLE,__FILE__,__LINE__);
			}
		}
	}
	db_free_result($q['resource']);
}

function settingDefineMLConstants()
{
	$Register = &Register::getInstance();
	$ml_settings = array('CONF_DEFAULT_TITLE','CONF_SHOP_NAME','CONF_HOMEPAGE_META_DESCRIPTION','CONF_HOMEPAGE_META_KEYWORDS');
	$iso2 = LanguagesManager::getCurrentLanguage()->iso2;
	$default_iso2 = LanguagesManager::getDefaultLanguage()->iso2;
	foreach($ml_settings as $setting){
		if(!defined($setting)){
			if($values = $Register->get($setting)){
				$value = '';
				if(isset($values[$iso2])&&$values[$iso2]){
					$value = $values[$iso2];
				}elseif(($default_iso2 != $iso2)&&isset($values[$default_iso2])&&$values[$default_iso2]){
					$value = $values[$default_iso2];
				}
				define($setting,$value);
			}
			unset($values);
		}
	}
}

function setting_CHECK_BOX($settingsID){

	$SQL = '
		SELECT settings_constant_name FROM ?#SETTINGS_TABLE WHERE settingsID=?
	';
	$q = db_phquery($SQL, $settingsID);
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) )
	_setSettingOptionValue( $settings_constant_name,
	isset($_POST["setting".$settings_constant_name])?1:0 );
	$res = '<input type="checkbox" name="setting'.$settings_constant_name.'" value="1" ';
	if ( _getSettingOptionValue($settings_constant_name) )
	$res .= ' checked="checked" ';
	$res .= " />";
	return $res;
}



// *****************************************************************************
// Purpose
// Inputs
//			$dataType = 0	- string
//			$dataType = 1	- float
//			$dataType = 2	- int
// Remarks
// Returns
function setting_TEXT_BOX($dataType, $settingsID, $BlockInSafeMode = null){

	if(isset($BlockInSafeMode)){

		if($settingsID && CONF_BACKEND_SAFEMODE)return translate("msg_safemode_info_blocked");
		else{
			$settingsID = $BlockInSafeMode;
		}
	}
	$SQL = '
		SELECT settings_constant_name FROM ?#SETTINGS_TABLE WHERE settingsID=?
	';
	$q = db_phquery($SQL,$settingsID);
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) && isset($_POST["setting".$settings_constant_name]) )
	{
		if ( $dataType == 0 )
		$value = (string)$_POST["setting".$settings_constant_name];
		else if ( $dataType == 1 )
		$value = (float)$_POST["setting".$settings_constant_name];
		else if ( $dataType == 2 )
		$value = (int)$_POST["setting".$settings_constant_name];
		_setSettingOptionValue( $settings_constant_name, $value );
	}
	return "<input type='text' value='".xHtmlSpecialChars(_getSettingOptionValue( $settings_constant_name )).
	"' name='setting".$settings_constant_name."' size='40' />";
}



// *****************************************************************************
// Purpose
// Inputs
//			$dataType = 0	- string
//			$dataType = 1	- float
//			$dataType = 2	- int
// Remarks
// Returns
function setting_TEXT_BOX_ML($dataType, $settingsID, $BlockInSafeMode = null){

	if(isset($BlockInSafeMode)){

		if($settingsID && CONF_BACKEND_SAFEMODE)return translate("msg_safemode_info_blocked");
		else{
			$settingsID = $BlockInSafeMode;
		}
	}
	$SQL = '
		SELECT settings_constant_name FROM ?#SETTINGS_TABLE WHERE settingsID=?
	';
	$q = db_phquery($SQL,$settingsID);
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];
	
	$languages = LanguagesManager::getInstance()->getLanguages();
	$ml_value = array();
	if( isset($_POST["save"])){
		foreach($languages as $language){
			$field_name = "setting".$settings_constant_name.'_'.$language->iso2;
			if (isset($_POST[$field_name]) )
			{
				if ( $dataType == 0 )
				$value = (string)$_POST[$field_name];
				else if ( $dataType == 1 )
				$value = (float)$_POST[$field_name];
				else if ( $dataType == 2 )
				$value = (int)$_POST[$field_name];
				$ml_value[$language->iso2] = $value;
			}
		}
		_setSettingOptionValue( $settings_constant_name, serialize($ml_value) );
	}
	$ml_value = (array)unserialize(_getSettingOptionValue( $settings_constant_name ));
	$input = '';
	foreach($languages as $language){
		$value = isset($ml_value[$language->iso2])?$ml_value[$language->iso2]:'';
		$field_name = "setting".$settings_constant_name.'_'.$language->iso2;
		$input .= "<input type='text' value='".xHtmlSpecialChars($value).
		"' name='".$field_name."' size='40' />";
		if($lang_img = $language->getThumbnailURL()){
		$input .= '<img style="margin: auto 4px;" src="'.xHtmlSpecialChars($lang_img).'" alt="'.xHtmlSpecialChars($language->getName()).'">';
		}else{
			$input .=  xHtmlSpecialChars($language->getName());
		}
		$input .= "<br/>";
	}
	return $input;
}

// *****************************************************************************
// Purpose	same as setting_TEXT_BOX() except for it stores data in encrypted way
// Inputs
//			$dataType = 0	- string
//			$dataType = 1	- float
//			$dataType = 2	- int
// Remarks
// Returns
function setting_TEXT_BOX_SECURE($dataType, $settingsID)
{
	$q = db_phquery("select settings_constant_name from ".
	SETTINGS_TABLE." where settingsID=?",$settingsID);
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) && isset($_POST["setting".$settings_constant_name]) )
	{
		if ( $dataType == 0 )
		$value = $_POST["setting".$settings_constant_name];
		else if ( $dataType == 1 )
		$value = (float)$_POST["setting".$settings_constant_name];
		else if ( $dataType == 2 )
		$value = (int)$_POST["setting".$settings_constant_name];
		_setSettingOptionValue( $settings_constant_name, Crypt::CCNumberCrypt ( $value , NULL ) );
	}
	return "<input type=text value='".Crypt::CCNumberDeCrypt( _getSettingOptionValue( $settings_constant_name ) , NULL ).
	"' name='setting".$settings_constant_name."' size='40' >";
}


function setting_TEXT_AREA($settingsID)
{
	$q = db_phquery("select settings_constant_name from ".
	SETTINGS_TABLE." where settingsID=?",$settingsID);
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) && isset($_POST["setting".$settings_constant_name]) )
	{
		$value = $_POST["setting".$settings_constant_name];
		_setSettingOptionValue( $settings_constant_name, $value);
	}
	return "<TEXTAREA name=\"setting{$settings_constant_name}\" cols=\"60\" rows=\"6\">"// rows=\"15\"
	._getSettingOptionValue( $settings_constant_name )
	."</TEXTAREA>";
}


function setting_DATEFORMAT()
{
	if ( isset($_POST["save"]) )
	{
		if ( isset($_POST["setting_DATEFORMAT"]) )
		{
			_setSettingOptionValue( "CONF_DATE_FORMAT",
			$_POST["setting_DATEFORMAT"] );
		}
	}

	$res = "";
	$currencies = currGetAllCurrencies();
	$res = "<select name='setting_DATEFORMAT'>";
	$current_format = _getSettingOptionValue("CONF_DATE_FORMAT");
	if (!$current_format) $current_format = "MM/DD/YYYY";

	//first option  - MM/DD/YYYY - US style
	$res .= "<option value='MM/DD/YYYY'";
	if (!strcmp($current_format,"MM/DD/YYYY")) $res .= " selected";
	$res .= ">MM/DD/YYYY</option>";

	//second option - DD.MM.YYYY - European style
	$res .= "<option value='DD.MM.YYYY'";
	if (!strcmp($current_format,"DD.MM.YYYY")) $res .= " selected";
	$res .= ">DD.MM.YYYY</option>";

	$res .= "</select>";
	return $res;
}


function setting_WEIGHT_UNIT($settingsID)
{
	if ( isset($_POST["save"]) )
	_setSettingOptionValue( "CONF_WEIGHT_UNIT",
	$_POST["setting_WEIGHT_UNIT"] );
	$res = "<select name='setting_WEIGHT_UNIT'>";

	$units = array(
	"lbs" => translate("str_lbs"),
	"kg" => translate("str_kg"),
	"g" => translate("str_gram")
	);

	foreach( $units as $key => $val )
	{
		$res .= "<option value='".$key."'";
		if ( !strcmp(_getSettingOptionValue("CONF_WEIGHT_UNIT"),$key) )$res .= " selected ";
		$res .= ">";
		$res .= "	".$val;
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function settingCONF_DEFAULT_CURRENCY()
{
	if ( isset($_POST["save"]) )
	{
		if ( isset($_POST["settingCONF_DEFAULT_CURRENCY"]) )
		{
			_setSettingOptionValue( "CONF_DEFAULT_CURRENCY",
			$_POST["settingCONF_DEFAULT_CURRENCY"] );
		}
	}

	$res = "";
	$currencies = currGetAllCurrencies();
	$res = "<select name='settingCONF_DEFAULT_CURRENCY'>";
	$res .= "<option value='0'>".translate("str_not_defined")."</option>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_CURRENCY");
	foreach( $currencies as $currency )
	{
		$res .= "<option value='".$currency["CID"]."' ";
		if ( $selectedID == $currency["CID"] )
		$res .= " selected ";
		$res .= ">";
		$res .= $currency["Name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function settingCONF_DEFAULT_COUNTRY()
{
	if ( isset($_POST["save"]) )
	_setSettingOptionValue( "CONF_DEFAULT_COUNTRY",
	$_POST["settingCONF_DEFAULT_COUNTRY"] );
	$res = "<select name='settingCONF_DEFAULT_COUNTRY'>";
	$res .= "<option value='0'>".translate("str_not_defined")."</option>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_COUNTRY");
	$count_row = 0;
	$countries = cnGetCountries( array(), $count_row );

	foreach( $countries as $country )
	{
		$res .= "<option value='".$country["countryID"]."'";
		if ( $selectedID == $country["countryID"] )
		$res .= " selected ";
		$res .= ">";
		$res .= "	".xHtmlSpecialChars($country["country_name"]);
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function settingCONF_DEFAULT_TAX_CLASS()
{
	if ( isset($_POST["save"]) )
	_setSettingOptionValue( "CONF_DEFAULT_TAX_CLASS",
	$_POST["settingCONF_DEFAULT_TAX_CLASS"] );
	$res  = "<select name='settingCONF_DEFAULT_TAX_CLASS'>";
	$res .= "	<option value='0'>".translate("str_not_defined")."</option>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_TAX_CLASS");
	$count_row = 0;
	$taxClasses = taxGetTaxClasses();
	foreach( $taxClasses as $taxClass )
	{
		$res .= "	<option value='".$taxClass["classID"]."'";
		if ( $selectedID == $taxClass["classID"] )
		$res .= " selected ";
		$res .= ">";
		$res .= "	".$taxClass["name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}

function settingCONF_DEFAULT_CUSTOMER_GROUP()
{
	if ( isset($_POST["save"]) )
	_setSettingOptionValue( "CONF_DEFAULT_CUSTOMER_GROUP",
	$_POST["settingCONF_DEFAULT_CUSTOMER_GROUP"] );

	$res = "<select name='settingCONF_DEFAULT_CUSTOMER_GROUP'>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_CUSTOMER_GROUP");

	$res .= "<option value='0'>".translate("str_not_defined")."</option>";

	$custGroups = GetAllCustGroups();
	foreach( $custGroups as $custGroup )
	{
		$res .= "<option value='".$custGroup["custgroupID"]."'";
		if ( $selectedID == $custGroup["custgroupID"] )
		$res .= " selected ";
		$res .= ">";
		$res .= "	".$custGroup["custgroup_name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function _CONF_DISCOUNT_TYPE_radio_button( $value, $caption, $checked, $href )
{
	if ( $checked == 1 )
	$checked = "checked";
	else
	$checked = "";
	if ( $href )
	{
		$href1 = "<a href='".set_query('ukey=admin_custgroups')."'>";
		$href2 = "</a>";
	}
	else
	{
		$href1 = "";
		$href2 = "";
	}
	$res  = "";
	$res .= "	<tr>";
	$res .= "		<td>";
	$res .= "			<input name='settingCONF_DISCOUNT_TYPE' type=radio $checked value='$value' >";
	$res .= "		</td>";
	$res .= "		<td>";
	$res .= "			$href1".$caption.$href2;
	$res .= "		</td>";
	$res .= "	</tr>";
	return $res;
}


function settingCONF_DISCOUNT_TYPE()
{
	if ( isset($_POST["save"]) )
	_setSettingOptionValue( "CONF_DISCOUNT_TYPE", $_POST["settingCONF_DISCOUNT_TYPE"] );
	$value = _getSettingOptionValue("CONF_DISCOUNT_TYPE");
	$res = "";
	$res .= "<table border=0 cellspacing=1 cellpadding=5>";
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "1", translate("dscnt_disabled"),  $value=="1"?1:0, 0 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "2", translate("dscnt_method_cust_group"), 	$value=="2"?1:0, 1 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "3", translate("dscnt_method_order_amount"), $value=="3"?1:0, 0 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "4", translate("dscnt_method_cust_group_plus_order_amount"), 	$value=="4"?1:0, 0 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "5", translate("dscnt_method_max_of_cust_group_and_order_amount"), 	$value=="5"?1:0, 0 );
	$res .= "</table>";
	return $res;
}

function settingCONF_COLOR( $settingsID )
{
	$q = db_phquery("select settingsID, settings_constant_name from ".
	SETTINGS_TABLE." where settingsID=?",$settingsID);
	$row = db_fetch_row($q);
	$constant_name = $row["settings_constant_name"];


	if ( isset($_POST["save"]) && isset($_POST["settingCONF_COLOR_".$settingsID])  )
	_setSettingOptionValue( $constant_name,
	$_POST["settingCONF_COLOR_".$settingsID]  );

	$value = _getSettingOptionValue( $constant_name );
	$value = strtoupper($value);
	$res = "<table><tr><td><table bgcolor=black cellspacing=1><tr><td bgcolor=#".$value.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td>";
	$res .= "<td><input type=text value='".$value."' name='settingCONF_COLOR_$settingsID' ></td></tr></table>";
	return $res;
}

function setting_CURRENCY_SELECT($Options, $_SettingID = null){

	if(is_null($_SettingID)){
		$_SettingID = $Options;
		$Options = array(array('title'=>translate("str_not_defined"), 'value'=>0,));
	}
	$Currencies = currGetAllCurrencies();
	foreach ($Currencies as $_Currency){

		$Options[] = array(
		'title' 		=> $_Currency['Name'],
		'value' 	=> $_Currency['CID'],
		);
	}

	return setting_SELECT_BOX($Options, $_SettingID);
}

function setting_ORDERSTATUS_SELECT($options, $setting_id){

	$order_statuses = ostGetOrderStatues();
	foreach ($order_statuses as $order_status){

		$options[] = array("title"=>$order_status['status_name'],"value"=>$order_status['statusID']);
	}

	return setting_SELECT_BOX($options, $setting_id);
}

function settingCONF_COUNTRY()
{
	if ( isset($_POST["save"]) )
	_setSettingOptionValue( "CONF_COUNTRY",
	$_POST["settingCONF_COUNTRY"] );

	$count_row = 0;
	$countries = cnGetCountries( array(), $count_row );

	$res = "";

	$selectedID = _getSettingOptionValue("CONF_COUNTRY");
	if ( isset( $_GET["countryID"] ) )
	$selectedID = $_GET["countryID"];
	//if ( $selectedID == "0" )
	//	$res .= "<b>".translate("cfg_country_undefined")."</b>&nbsp;";
	$onChange = "JavaScript:window.location=\"admin.php?dpt=conf&sub=setting&settings_groupID=".$_GET["settings_groupID"]."&countryID=\" + document.MainForm.settingCONF_COUNTRY.value";
	// onchange='$onChange'
	$res .= "<select name='settingCONF_COUNTRY' >\n";
	$res .= "	<option value='0'>".translate("str_not_defined")."</option>";
	foreach( $countries as $country )
	{
		$res .= "<option value='".$country["countryID"]."' ";
		if ( $selectedID == $country["countryID"] )
		$res .= "selected";
		$res .= ">\n";
		$res .= "		".xHtmlSpecialChars($country["country_name"])."\n";
		$res .= "</option>\n";
	}
	$res .= "</select>";
	return $res;
}

function settingCONF_ZONE()
{
	if ( isset($_POST["save"]) )
	if ( isset($_POST["settingCONF_ZONE"]) )
	_setSettingOptionValue( "CONF_ZONE", $_POST["settingCONF_ZONE"] );

	$countries = cnGetCountries( array(), $count_row );
	if ( count($countries) != 0 )
	{

		$countryID = _getSettingOptionValue("CONF_COUNTRY");
		$zones = znGetZones( _getSettingOptionValue("CONF_COUNTRY") );

		$selectedID = _getSettingOptionValue("CONF_ZONE");
		$res = "";
		if ( (CONF_ADDRESSFORM_STATE == 0) && !ZoneBelongsToCountry($selectedID, $countryID) )
		$res .= translate("err_region_does_not_belong_to_country")."<br>";
		if ( count($zones) > 0 )
		{
			$res .= "<select name='settingCONF_ZONE'>\n";
			foreach( $zones as $zone )
			{
				$res .= "<option value='".$zone["zoneID"]."' ";
				if ( $selectedID == $zone["zoneID"] )
				$res .= "selected";
				$res .= ">\n";
				$res .= "		".xHtmlSpecialChars($zone["zone_name"])."\n";
				$res .= "</option>\n";
			}
			$res .= "</select>";
		}
		else
		{
			if ( trim($selectedID) != (string)((int)$selectedID) )
			$res .= "<input type=text name='settingCONF_ZONE' value='$selectedID'>";
			else
			$res .= "<input type=text name='settingCONF_ZONE' value=''>";
		}
		return $res;
	}
	else
	return "-";
}

function settingCONF_CALCULATE_TAX_ON_SHIPPING()
{

	if ( isset($_POST["save"]) )
	_setSettingOptionValue( "CONF_CALCULATE_TAX_ON_SHIPPING", $_POST["settingCONF_CALCULATE_TAX_ON_SHIPPING"] );

	$res = "<select name='settingCONF_CALCULATE_TAX_ON_SHIPPING'>";
	$res .= "	<option value='0'>".translate("str_not_defined")."</option>";
	$selectedID = _getSettingOptionValue("CONF_CALCULATE_TAX_ON_SHIPPING");
	$count_row = 0;
	$taxClasses = taxGetTaxClasses();
	foreach( $taxClasses as $taxClass )
	{
		$res .= "<option value='".$taxClass["classID"]."'";
		if ( $selectedID == $taxClass["classID"] )
		$res .= " selected ";
		$res .= ">";
		$res .= "	".$taxClass["name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}

function setting_SELECT_BOX($_Options, $_SettingID){

	if(!is_array($_Options)){

		$_Options = explode(',',$_Options);
		$TC = count($_Options)-1;
		for(;$TC>=0;$TC--){

			$_Options[$TC] = explode(':', $_Options[$TC]);
			$_Options[$TC]['title'] = $_Options[$TC][0];
			if(!isset($_Options[$TC][1])){
				$_Options[$TC]['value'] = '';
			}else{
				$_Options[$TC]['value'] = $_Options[$TC][1];
			}
		}
	}
	$sql = "
		SELECT settings_constant_name 
		FROM ".SETTINGS_TABLE." 
		WHERE settingsID=?
	";
	$row = db_fetch_row( db_phquery($sql,$_SettingID) );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) )
	_setSettingOptionValue( $settings_constant_name, 	$_POST["setting_".$settings_constant_name] );

	$html = '<select name="setting_'.$settings_constant_name.'">';
	$SettingConstantValue = _getSettingOptionValue($settings_constant_name);
	foreach ($_Options as $_Option){
		$_Option['title'] = str_replace('&amp;','&',$_Option['title']);
		$_Option['value'] = str_replace('&amp;','&',$_Option['value']);
		$html .= '<option value="'.xHtmlSpecialChars($_Option['value']).'"'.($SettingConstantValue==$_Option['value']?' selected="selected"':'').'>'.htmlspecialchars($_Option['title']).'</option>';
	}
	$html .= '</select>';
	return $html;
}

function setting_CHECKBOX_LIST($_boxDescriptions, $_SettingID){

	$sql = "
		SELECT settings_constant_name 
		FROM ".SETTINGS_TABLE." 
		WHERE settingsID=?
	";
	$row = db_fetch_row( db_phquery($sql,$_SettingID) );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) ){

		$newValues = '';
		$_POST['setting_'.$settings_constant_name] = isset($_POST['setting_'.$settings_constant_name])?$_POST['setting_'.$settings_constant_name]:array();

		$maxOffset = max(array_keys($_boxDescriptions));

		for(; $maxOffset>=0; $maxOffset-- ){
				
			$newValues .= (int)in_array($maxOffset, $_POST['setting_'.$settings_constant_name]);
		}
		_setSettingOptionValue( $settings_constant_name, 	bindec($newValues) );
	}

	$Value = _getSettingOptionValue($settings_constant_name);
	$html = '';


	foreach ($_boxDescriptions as $_offset=>$_boxDescr){

		$html .= '<div style="padding:2px;"><input'.($Value&pow(2, $_offset)?' checked="checked"':'').' name="setting_'.$settings_constant_name.'[]" value="'.$_offset.'" type="checkbox" style="margin:0px;padding:0px;" />&nbsp;'.$_boxDescr.'</div>';
	}
	return $html;
}

function setting_COUNTRY_SELECT($_ShowButton, $_SettingID = null){

	if(!isset($_SettingID)){

		$_SettingID = $_ShowButton;
		$_ShowButton = false;
	}

	$Options = array(
	array("title"=>'-', "value"=>0)
	);
	$CountriesNum = 0;
	$Countries = cnGetCountries(array('raw data'=>true), $CountriesNum );
	foreach ($Countries as $_Country){

		$Options[] = array("title"=>$_Country['country_name'], "value"=>$_Country['countryID']);
	}
	return '<nobr>'.setting_SELECT_BOX($Options, $_SettingID).($_ShowButton?'&nbsp;&nbsp;<input type="submit" name="save" value="'.translate("btn_select").'" />':'').'</nobr>';
}

function setting_ZONE_SELECT($_CountryID, $_Params ,$_SettingID = null){

	$Mode = '';
	if(!isset($_SettingID)){

		$_SettingID = $_Params;
		$Mode = 'simple';
	}elseif(isset($_Params['mode'])) {

		$Mode = $_Params['mode'];
	}
	$Zones = znGetZones($_CountryID);
	$Options = array(
	array("title"=>'-', "value"=>0)
	);
	switch ($Mode){
		default:
		case 'simple':
			break;
		case 'notdef':
			if(!count($Zones))return translate("str_regions_notdefined");
			break;
	}
	foreach ($Zones as $_Zone){

		$Options[] = array("title"=>$_Zone['zone_name'], "value"=>$_Zone['zoneID']);
	}
	return setting_SELECT_BOX($Options, $_SettingID);
}

function setting_RADIOGROUP($_Options, $_SettingID){

	if(!is_array($_Options)){

		$_Options = explode(',',$_Options);
		$TC = count($_Options)-1;
		for(;$TC>=0;$TC--){

			$_Options[$TC] = explode(':', $_Options[$TC]);
			$_Options[$TC]['title'] = $_Options[$TC][0];
			if(!isset($_Options[$TC][1])){
				$_Options[$TC]['value'] = '';
			}else{
				$_Options[$TC]['value'] = $_Options[$TC][1];
			}
		}
	}
	$sql = "
		SELECT settings_constant_name 
		FROM ".SETTINGS_TABLE." 
		WHERE settingsID=?
	";
	$row = db_fetch_row( db_phquery($sql,$_SettingID) );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) )
	_setSettingOptionValue( $settings_constant_name, 	$_POST["setting_".$settings_constant_name] );

	$html = '';
	$TC = 0;
	$SettingConstantValue = _getSettingOptionValue($settings_constant_name);
	foreach ($_Options as $_Option){

		$html .= '<input class="inlradio" type="radio" name="setting_'.$settings_constant_name.'" value="'.htmlspecialchars($_Option['value']).'"'.($SettingConstantValue==$_Option['value']?' checked="checked"':'').' id="id_'.$settings_constant_name.$TC.'" />&nbsp;<label for="id_'.$settings_constant_name.$TC.'">'.htmlspecialchars($_Option['title']).'</label><br />';
		$TC++;
	}
	return $html;
}

function setting_SINGLE_FILE($_Path, $_SettingID){

	$Error = 0;
	$ConstantName = settingGetConstNameByID($_SettingID);
	if(isset($_POST['save']) && isset($_FILES['setting_'.$ConstantName])){

		if($_FILES['setting_'.$ConstantName]['name']){
			if((strpos($_Path,DIR_IMG)!==false)&&!is_image($_FILES['setting_'.$ConstantName]['name'])){
				$Error = 'imm_notimage';
			}elseif(preg_match('/(\.(php[\d]?)($|\.)|^\.htaccess$|\.\.\/)/i', $_FILES['setting_'.$ConstantName]['name'])){
				$Error = 'loc_notsupported_filetype';
			}elseif(@copy($_FILES['setting_'.$ConstantName]['tmp_name'], $_Path.'/'.$_FILES['setting_'.$ConstantName]['name'])){
				_setSettingOptionValue($ConstantName, $_FILES['setting_'.$ConstantName]['name']);
			}else{
				$Error = 'err_failed_to_upload_file';
			}
		}
	}

	$ConstantValue = _getSettingOptionValue($ConstantName);
	return ($Error?'<div>'.translate($Error).'</div>':'').'<input type="file" name="setting_'.$ConstantName.'" /><br />'.($ConstantValue?$ConstantValue:'&nbsp;');
}
?>