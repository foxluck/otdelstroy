<?php
// Purpose	encrypts cc_number field ( see ORDERS_TABLE in database_structure.xml )
function cryptCCNumberCrypt( $cc_number, $key )
{
	return base64_encode($cc_number);
}

// Purpose	decrypts cc_number field ( see ORDERS_TABLE in database_structure.xml )
function cryptCCNumberDeCrypt( $cifer, $key )
{
	return base64_decode($cifer);
}

// Purpose	encrypts cc_holdername field ( see ORDERS_TABLE in database_structure.xml )
function cryptCCHoldernameCrypt( $cc_holdername, $key )
{
	return base64_encode( $cc_holdername );
}

// Purpose	decrypts cc_holdername field ( see ORDERS_TABLE in database_structure.xml )
function cryptCCHoldernameDeCrypt( $cifer, $key )
{
	return base64_decode( $cifer );
}

// Purpose	encrypts cc_expires field ( see ORDERS_TABLE in database_structure.xml )
function cryptCCExpiresCrypt( $cc_expires, $key )
{
	return base64_encode( $cc_expires );
}

// Purpose	decrypts cc_expires field ( see ORDERS_TABLE in database_structure.xml )
function cryptCCExpiresDeCrypt( $cifer, $key )
{
	return base64_decode( $cifer );
}

// Purpose	encrypts customer ( and admin ) password field 
//					( see ORDERS_TABLE in database_structure.xml )
function cryptPasswordCrypt( $password, $key )
{
	return base64_encode( $password );
}

// Purpose	decrypts customer ( and admin ) password field ( see ORDERS_TABLE in database_structure.xml )
function cryptPasswordDeCrypt( $cifer, $key )
{
	return base64_decode( $cifer );
}

// Purpose	encrypts getFileParam
// Remarks	see also get_file.php
function cryptFileParamCrypt( $getFileParam, $key )
{
	return base64_encode( $getFileParam );
}

// Purpose	decrypt getFileParam
// Remarks	see also get_file.php
function cryptFileParamDeCrypt( $cifer, $key )
{
	return base64_decode( $cifer );
}
?>