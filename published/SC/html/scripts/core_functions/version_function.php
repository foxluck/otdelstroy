<?php
//NOT USED: any more
function verInstall()
{
	db_query("insert into ".SYSTEM_TABLE.
			" ( varName, value ) ".
			" values( 'version_number', '".STRING_VERSION."' ) ");

	db_query("insert into ".SYSTEM_TABLE.
			" ( varName, value ) ".
			" values( 'version_name', '".STRING_PRODUCT_NAME."' ) ");
}

function verGetPackageVersion()
{
	$q = db_query("select varName, value from ".SYSTEM_TABLE);
	$row = array("");
	while ( $row && strcmp($row[0], "version_number") )
	{
		$row = db_fetch_row($q);
	}
	return (float) $row[1];
}

function verUpdatePackageVersion()
{
	db_query("update ".SYSTEM_TABLE." set value = '".STRING_VERSION."' where varName = 'version_number'");
}

function verUpdatePackageName(){
	
	db_query("update ".SYSTEM_TABLE." set value = '".STRING_PRODUCT_NAME."' where varName = 'version_name'");
}
?>