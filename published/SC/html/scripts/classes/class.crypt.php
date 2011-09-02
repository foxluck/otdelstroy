<?php
class Crypt
{
	static $key;

	//CC number
	// Purpose	encrypts cc_number field ( see ORDERS_TABLE in database_structure.xml )
	static function CCNumberCrypt($cc_number)
	{
		return base64_encode($cc_number);
	}

	// Purpose	decrypts cc_number field ( see ORDERS_TABLE in database_structure.xml )
	static function CCNumberDeCrypt( $cifer, $key )
	{
		return base64_decode($cifer);
	}

	//Holder name
	// Purpose	encrypts cc_holdername field ( see ORDERS_TABLE in database_structure.xml )
	static function CCHoldernameCrypt( $cc_holdername, $key )
	{
		return base64_encode( $cc_holdername );
	}
	// Purpose	decrypts cc_holdername field ( see ORDERS_TABLE in database_structure.xml )
	static function CCHoldernameDeCrypt( $cifer, $key )
	{
		return base64_decode( $cifer );
	}
	//CCExpires
	static function CCExpiresCrypt( $cc_expires, $key )
	{
		return base64_encode( $cc_expires );
	}

	// Purpose	decrypts cc_expires field ( see ORDERS_TABLE in database_structure.xml )
	static function CCExpiresDeCrypt( $cifer, $key )
	{
		return base64_decode( $cifer );
	}

	//Password

	// Purpose	encrypts customer ( and admin ) password field
	//					( see ORDERS_TABLE in database_structure.xml )
	static function PasswordCrypt( $password, $key )
	{
		return base64_encode( $password );
	}

	// Purpose	decrypts customer ( and admin ) password field ( see ORDERS_TABLE in database_structure.xml )
	static function PasswordDeCrypt( $cifer, $key )
	{
		return base64_decode( $cifer );
	}

	//FileParam

	// Purpose	encrypts getFileParam
	// Remarks	see also get_file.php
	static function FileParamCrypt( $getFileParam, $key )
	{
		return base64_encode( $getFileParam );
	}

	// Purpose	decrypt getFileParam
	// Remarks	see also get_file.php
	static function FileParamDeCrypt( $cifer, $key )
	{
		return base64_decode( $cifer );
	}
}
?>