<?php
	require_once( "robotinit.php" );

	if ( PEAR::isError( $ret = chargeUsersSMS( ) ) )
		die( "[ERROR] ".$ret->getMessage );

	echo "[OK] Database: $DB_KEY. $ret sms charged.\n"
?>