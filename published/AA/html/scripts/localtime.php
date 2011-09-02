<?php


	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $MW_APP_ID, true );
	$kernelStrings = $loc_str[$language];


	require_once( "../../../common/html/includes/modules/JsHttpRequest/config.php" );
	require_once( "../../../common/html/includes/modules/JsHttpRequest/Php.php" );

	$JsHttpRequest =& new Subsys_JsHttpRequest_Php('utf-8');

	$TIMEZONE = $_POST['TIMEZONE'];
	$DST = $_POST['DST'];

	function displayDateTimeLocal( $timestamp )
	{
		global $kernelStrings;
		global $monthShortNames;

		$month = $monthShortNames[date( "n", $timestamp ) - 1];

		return sprintf( "<b>%s</b> (%s'%s)", date( "H:i", $timestamp ), $kernelStrings[$month], date( "d Y", $timestamp ) );
	}

	function convertTimestamp2Local2( $timestamp, $TIMEZONE, $DST )
	{
		if ( SERVER_TZ )
		{
			$dt = new Date();

			$dt->setTZ( new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
			$dt->setDate($timestamp);

			$dt->convertTZ( new Date_TimeZone( $TIMEZONE, $DST ) );
			$timestamp = $dt->getDate(DATE_FORMAT_UNIXTIME);
		}

		return $timestamp;
	}

	switch( true )
	{
		case true:

			$_RESULT = array( "state"=>'OK', "error"=>'', 'timestamp'=> displayDateTimeLocal( convertTimestamp2Local2( time(), $TIMEZONE, $DST ) ) );
	}

?>
