<?php
class clickatell
{
	var $params;
	var $sess_id = false;


	function setParams( $parameters )
	{
		$this->params = $parameters;
	}

	function sendSMS( $to, $message, $sender="" )
	{
		$this->sess_id = false;

		$user = $this->params->get( "user" );
		$password = $this->params->get( "password" );
		$api_id = $this->params->get( "api_id" );
		$baseurl = "http://" . $this->params->get( "host" );
		$debug = $this->params->get( "debug" );

		$fromtype = $this->params->get( "fromtype" );
		$from = urlencode( substr( trim( ( $fromtype==1 )? $sender : $this->params->get( "from" ) ), 0, 11 ) );

		$unicode = $this->params->get( "unicode" );
		
		if ( $unicode)
		{
			$text = mb_convert_encoding( $message, "UCS-2", "UTF-8");

			$codes = "";

			for( $i=0; $i < strlen( $text ); $i++ )
				$codes .= sprintf( "%02X", ord( substr($text, $i, 1 ) ) );

			$message = $codes;
		}

		$text = urlencode("$message");

		$to = trim( $to );
		if ( $to{0} == "+" )
			$to = substr( $to, 1 );

		if ( strpos($to, ' ' ) )
			return PEAR::raiseError( "Phone number must contain no spaces. " );

		// auth call
		$url = "$baseurl/http/auth?user=$user&password=$password&api_id=$api_id";

		// do auth call
		$ret = file($url);
		// split our response. return string is on first line of the data returned
		$sess = split(":",$ret[0]);

		if ($sess[0] == "OK")
		{
			$this->sess_id = $sess_id = trim($sess[1]); // remove any whitespace
			$url = "$baseurl/http/sendmsg?session_id=$sess_id&concat=3&to=$to&text=$text";

			if ( count( $from ) != 0 )
				$url .= "&from=$from";

			if ( $unicode )
				$url .= "&unicode=1";

			// do sendmsg call
			$ret = file($url);
			$send = split(":",$ret[0]);

			if ($send[0] == "ID")
				return trim($send[1]);
			else
				return PEAR::raiseError( "SMS sending failed. ".$send[1] );
		}
		else
			return PEAR::raiseError( "Error connecting to SMS Gateway provider. ".$ret[0] );
	}


	function chargeSMS( $msgId )
	{
		$user = $this->params->get( "user" );
		$password = $this->params->get( "password" );
		$api_id = $this->params->get( "api_id" );
		$baseurl = "http://" . $this->params->get( "host" );
		$cost = $this->params->get( "cost" );

		$url = "$baseurl/http/getmsgcharge?user=$user&password=$password&api_id=$api_id&apimsgid=".trim($msgId);

		$ret = file($url);

		$result = array(
					"COST"=>$this->getSMSCost(),
					"RESERVE"=>$this->getSMSReserve(),
				);

		if ( strncmp( $ret[0], "ERR", 3 ) == 0 )
		{
			$result["CHARGED"] = 0;
			$result["CHARGE"] = $result["RESERVE"];
			$result["STATUS"] = SMS_STATUS_PENDING;

			return $result;
		}

		$send = split(" ",$ret[0]);

		$result["CHARGED"] = 1;
		$result["CHARGE"] = $result["COST"]*floatval( $send[3] );
		$result["STATUS"] = $this->getStatusSubstitution( $send[5] );
		$result["STATUS_TEXT"] = $send[5] .": ". $this->getStatusDescription( $send[5] );

		if ( $result["CHARGE"] == 0 && $result["STATUS"] != SMS_STATUS_CANCELED )
		{
			$result["CHARGED"] = 0;
			$result["CHARGE"] = $result["RESERVE"];
			$result["STATUS"] = SMS_STATUS_PENDING;

			return $result;
		}

		return $result;
	}

	function getSMSLength( )
	{
		$unicode = $this->params->get( "unicode" );

		return ( $unicode == 1 ) ? 189 : 405;
	}

	function getSMSPartLength( )
	{
		$unicode = $this->params->get( "unicode" );

		return ( $unicode == 1 ) ? 63 : 135;
	}

	function getSMSCost( )
	{
		return $this->params->get( "cost" );
	}

	function getSMSReserve( )
	{
		return $this->params->get( "reserve" );
	}

	function getStatusDescription( $id )
	{
		$statusDescr = array(
			"001" => "Message unknown",
			"002" => "Message queued",
			"003" => "Delivered",
			"004" => "Received by recipient",
			"005" => "Error with message",
			"006" => "User cancelled message delivery",
			"007" => "Error delivering message",
			"008" => "Message received by gateway",
			"009" => "Routing error",
			"010" => "Message expired",
			"011" => "Message queued",
			"012" => "Out of credit"
		);

		return in_array( $id, array_keys( $statusDescr ) ) ? $statusDescr[$id] : "Unknown status";
	}

	function getStatusSubstitution( $id )
	{
		switch ( $id )
		{
			case "002":
			case "003":
			case "004":
			case "008":

				return( SMS_STATUS_DELIVERED );
				break;

			case "005":
			case "006":
			case "007":
			case "009":
			case "010":
			case "012":

				return( SMS_STATUS_CANCELED );
				break;

			case "001":
			case "011":

			default:

				return( SMS_STATUS_PENDING );
				break;
		}
	}
}

?>
