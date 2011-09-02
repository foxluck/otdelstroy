<?php
global $smarty;
if($page<1)$page=1;

if ( isset($_GET['deleteCustomerID']) ){

	regDeleteCustomer( $_GET['deleteCustomerID'] );
	RedirectSQ('deleteCustomerID=');
}

if(isset($_GET['activateID'])){

	regActivateCustomer($_GET['activateID']);
	RedirectSQ('activateID=');
}

$CustomerGroups = GetAllCustGroups();
$_CGroups = array();
foreach ($CustomerGroups as $f_Ind=>$f_Group){
	$_CGroups[$f_Group['custgroupID']] = $f_Group;
}
$CustomerGroups = &$_CGroups;

if(!isset($_GET['search'])&& !isset($_GET['export_to_excel'])){
	$_GET['search'] = '';
}

if(isset($_GET['search'])||isset($_GET['export_to_excel'])){


	$Users = array();
	$ActiveState = '';
	if (isset($_GET["ActState"]) ){
		switch ($_GET["ActState"]){
			case 1://#activated
				$ActiveState = "AND (ActivationCode='' OR ActivationCode IS NULL)";
				break;
			case 0://#not activated
				$ActiveState = "AND ActivationCode<>''";
				break;
		}
	}

	$gridEntry = new Grid();

	$gridEntry->query_total_rows_num = 'SELECT COUNT(*) FROM ?#TBL_USERS'.
	' WHERE 1 '.
	(isset($_GET['login'])?' AND Login LIKE "%'.xEscapeSQLstring($_GET['login']).'%"':'').
	( ( isset($_GET['custgroupID']) and $_GET['custgroupID'] > 0 ) ? ' AND custgroupID = '.xEscapeSQLstring($_GET['custgroupID']) : '').
	(isset($_GET['email'])?' AND Email LIKE "%'.xEscapeSQLstring($_GET['email']).'%"':'').
	(isset($_GET['last_name'])?' AND last_name LIKE "%'.xEscapeSQLstring($_GET['last_name']).'%"':'').
	(isset($_GET['first_name'])?' AND first_name LIKE "%'.xEscapeSQLstring($_GET['first_name']).'%"':'').
	$ActiveState;

	$gridEntry->query_select_rows = 'SELECT * FROM ?#TBL_USERS'.
	' WHERE 1 '.
	(isset($_GET['login'])?' AND Login LIKE "%'.xEscapeSQLstring($_GET['login']).'%"':'').
	( ( isset($_GET['custgroupID']) and $_GET['custgroupID'] > 0 ) ? ' AND custgroupID = '.xEscapeSQLstring($_GET['custgroupID']) : '').
	(isset($_GET['email'])?' AND Email LIKE "%'.xEscapeSQLstring($_GET['email']).'%"':'').
	(isset($_GET['last_name'])?' AND last_name LIKE "%'.xEscapeSQLstring($_GET['last_name']).'%"':'').
	(isset($_GET['first_name'])?' AND first_name LIKE "%'.xEscapeSQLstring($_GET['first_name']).'%"':'').
	$ActiveState;

	$gridEntry->setRowHandler('$row[\'reg_datetime\'] = Time::standartTime($row[\'reg_datetime\']);return $row;');

	$gridEntry->registerHeader(translate("usr_custinfo_login"), 'Login', true);
	$gridEntry->registerHeader(translate("usr_custinfo_first_name"), 'first_name');
	$gridEntry->registerHeader(translate("usr_custinfo_last_name"), 'last_name');
	$gridEntry->registerHeader(translate("usr_custinfo_email"), 'Email');
	$gridEntry->registerHeader(translate("str_group"));
	$gridEntry->registerHeader(translate("usr_custinfo_regtime"), 'reg_datetime');
	$gridEntry->registerHeader(translate("usr_account_state"));
	$gridEntry->prepare();

	if ( isset($_GET['export_to_excel']) ){

		serExportCustomersToExcel( $gridEntry->exportRows(), $_GET['charset'] );
		$smarty->assign( 'customers_has_been_exported_succefully', 1 );
		$smarty->assign('MessageBlock',"<div class='success_block' ><span class='success_message'>".translate('msg_customers_exported_to_file').'<br><br>'.
		'<a href="get_file.php?getFileParam='.Crypt::FileParamCrypt( 'GetCustomerExcelSqlScript', null ).'">'.translate('btn_download').'</a>'.sprintf(' (%3.2f Kb)',filesize(DIR_TEMP.'/customers.csv')/1024).'</span></div>');
	}

}
$smarty->assign('TotalFound',str_replace('{N}',$gridEntry->total_rows_num,translate('msg_n_customers_found')));
if(SystemSettings::is_hosted()){
	$session_id = session_id();
	session_write_close();

	$messageClient = new WbsHttpMessageClient($db_key, 'wbs_msgserver.php');
	$messageClient->putData('action', 'ALLOW_VIEW_ORDER_DETAILS');
	$messageClient->putData('language',(LanguagesManager::getCurrentLanguage()->iso2));
	$res=$messageClient->send();

	session_id($session_id);
	session_start();

	if($res&&$messageClient->getResult('msg')!=''){
		$msg_type=$messageClient->getResult('msg_type');
		if($msg_type=='error'){
			$smarty->assign('MessageBlock',"<div class='error_block' ><span class='error_message'>".$messageClient->getResult('msg').'</span></div>');
		}else{
			$smarty->assign('MessageBlock',"<div class='comment_block' ><span class='success_message'>".$messageClient->getResult('msg').'</span></div>');
		}
	}
}else{
	$res = false;
}

if(!$res||$messageClient->getResult('success')===true){
	$smarty->assign('page_enabled','1');
}

$smarty->assign( 'customer_groups', $CustomerGroups );
global $file_encoding_charsets;
$smarty->assign('charsets', $file_encoding_charsets);
$smarty->assign('default_charset', translate('prdine_default_charset'));

$smarty->assign( 'sub_template', $this->getTemplatePath('backend/users_list.html'));

function serExportCustomersToExcel( $customers, $charset = '' ){

	$maxCountAddress = 0;
	$sql = "SELECT COUNT(addressID) FROM ?#CUSTOMER_ADDRESSES_TABLE WHERE customerID=?";
	foreach( $customers as $customer ) {
		$countAddress = db_phquery_fetch( DBRFETCH_FIRST, $sql, $customer["customerID"]);
		if($maxCountAddress < $countAddress) {
			$maxCountAddress = $countAddress;
		}
	}

	// open file to write
	$f = fopen( DIR_TEMP."/customers.csv", "w" );

	// head table generate
	$headLine = "Login;First name;Last name;Email;Group;Registered;Newsletter subscription;";

	$q = db_query( "select reg_field_ID, ".LanguagesManager::sql_prepareField('reg_field_name')." AS reg_field_name from ".CUSTOMER_REG_FIELDS_TABLE." order by sort_order " );
	while( $row = db_fetch_row($q) ) {
		$headLine .= _filterBadSymbolsToExcel( $row["reg_field_name"] ).";";
	}

	for( $i=1; $i<=$maxCountAddress; $i++ ){
		$headLine .= "Address ".$i.";";
	}

	fputs( $f, $headLine."\n" );

	foreach( $customers as $customer ) {

		if($customer["custgroupID"]) {
			$customer['custgroup_name'] = db_phquery_fetch(DBRFETCH_FIRST, "SELECT ".LanguagesManager::sql_prepareField('custgroup_name')." AS custgroup_name FROM ?#CUSTGROUPS_TABLE WHERE custgroupID=?", $customer["custgroupID"]);
		}else {
			$customer["custgroup_name"] = "";
		}

		$customer['subscribed4news'] = $customer['subscribed4news']?'+':'';

		$line = "";
		$line .= _filterBadSymbolsToExcel( $customer["Login"] ).";";
		$line .= _filterBadSymbolsToExcel( $customer["first_name"] ).";";
		$line .= _filterBadSymbolsToExcel( $customer["last_name"] ).";";
		$line .= _filterBadSymbolsToExcel( $customer["Email"] ).";";
		$line .= _filterBadSymbolsToExcel( $customer["custgroup_name"] ).";";
		$line .= _filterBadSymbolsToExcel( $customer["reg_datetime"] ).";";
		$line .= $customer["subscribed4news"].";";

		$q_reg_param = db_query( "select reg_field_ID, ".LanguagesManager::sql_prepareField('reg_field_name')." AS reg_field_name from ".CUSTOMER_REG_FIELDS_TABLE.
			" order by sort_order " );
		while( $row = db_fetch_row($q_reg_param) ) {

			$line .= _filterBadSymbolsToExcel( db_phquery_fetch(DBRFETCH_FIRST, "SELECT reg_field_value FROM ?#CUSTOMER_REG_FIELDS_VALUES_TABLE WHERE reg_field_ID=? AND customerID=?", $row["reg_field_ID"], $customer["customerID"])).";";
		}

		$countAddress = 0;
		$addresses = regGetAllAddressesByID($customer["customerID"]);
		foreach( $addresses as $address ) {
			$line .= _filterBadSymbolsToExcel( regGetAddressStr($address["addressID"], true) ).";";
			$countAddress ++;
		}

		for( $i=1; $i<=$maxCountAddress-$countAddress; $i++ ) {
			$line .= ";";
		}

		fputs( $f, $line."\n" );
	}

	fclose($f);

	if($charset && $charset != DEFAULT_CHARSET){

		iconv_file(DEFAULT_CHARSET, $charset, DIR_TEMP."/customers.csv");
	}
}

function _filterBadSymbolsToExcel( $str )
{
	$str = str_replace( "\r\n", "", $str );
	$str = str_replace( "<br>", " ", $str );

	$semicolonFlag = false;
	for( $i=0; $i<strlen($str); $i++ )
	{
		if ( $str[$i] == ";" )
		{
			$semicolonFlag = true;
			break;
		}
	}

	if ( !$semicolonFlag )
	return $str;
	else
	{
		$res = "";
		for( $i=0; $i<strlen($str); $i++  )
		{
			if ( $str[$i] == "\"" )
			$res .= "\"\"";
			else
			$res .= $str[$i];
		}
		return "\"".$res."\"";
	}
}


//EOF