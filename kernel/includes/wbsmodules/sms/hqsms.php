<?php
class hqsms
{
	var $params;

	function setParams( $parameters )
	{
		$this->params = $parameters;
	}

	// Function is_spec_char returns 1 or 0 depending if the message text contains any of specials characters.
	function is_spec_char($_message) {
		mb_regex_encoding('UTF-8');
		if(mb_eregi("[^\@\£\$\¥\è\é\ù\ì\ò\Ç\Ø\ø\Å\å\_\^\{\}\\[\~\]\|\€\Æ\æ\ß\É\!\\\"\#\¤\%\&\\\'\(\)\*\+\,\-\.\/0123456789:;<=>?\¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà \r\n]",$_message)) 
			return 1;
		else 
			return 0;
	}
	
	function sendSMS( $to, $message, $sender="" )
	{
		$user = $this->params->get( "user" );
		$password = md5($this->params->get( "password" ));
		$baseurl = 'https://' . $this->params->get( "host" ).'/send.do';
		
		$fromtype = $this->params->get( "fromtype" );
		$from = urlencode( substr( trim( ( $fromtype==1 )? $sender : $this->params->get( "from" ) ), 0, 11 ) );
		//$from = '';
		
		$unicode = $this->is_spec_char($message);

		$text = urlencode($message);

		$to = trim( $to );
		if ( $to{0} == "+" )
			$to = substr( $to, 1 );

		if ( strpos($to, ' ' ) )
			return PEAR::raiseError( "Phone number must contain no spaces. " );

		$uri = "username=$user&password=$password&to=$to&message=$text";

		if ( count( $from ) != 0 )
			$uri .= "&from=$from";

		if ($this->params->get("debug")) {
			if (($f=fopen(WBS_DIR.'temp/log/sms.log', 'a+'))) {
				@fwrite($f, date('Y-m-d H:i:s')." to=$to&from=$from&message=$message\n");
				@fclose($f);
			}
		}
		if ( $unicode )
			$uri .= "&encoding=utf-8";
			
		// Use CURL an POST method instead of GET
		if ( !($ch = curl_init()) ){
			return PEAR::raiseError('Error initialization curl');
		}
		if ( curl_errno($ch) != 0 ){
			return PEAR::raiseError('Error initialization curl');
		}

		@curl_setopt( $ch, CURLOPT_URL, $baseurl );
		@curl_setopt( $ch, CURLOPT_POST, 1);
		@curl_setopt( $ch, CURLOPT_POSTFIELDS, $uri );
		@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		@curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
		$ret = curl_exec($ch);
		if (curl_errno($ch))
			PEAR::raiseError( curl_error($ch));
		curl_close($ch);
		
		if (is_array($ret))
			$send = split(":",$ret[0]);
		else 
			$send = split(":",$ret);
		
		if (strrpos($send[0], "OK") !== false) {
			return array(trim($send[1]), trim($send[2]*$this->getSMSCost()));
		} else {
			return PEAR::raiseError( "SMS sending failed: ".$this->getStatusDescription($send[1]) );
		}
	}

	function chargeSMS( $msgId )
	{ 
		$user = $this->params->get( "user" );
		$password = md5($this->params->get( "password" ));
		$baseurl = "http://" . $this->params->get( "host" );

		$url = "$baseurl/send.do?username=$user&password=$password&status=".trim($msgId);
		$ret = file($url);
		$result = array(
					"COST"=>$this->getSMSCost(),
					"RESERVE"=>$this->getSMSReserve(),
				);

		$send = split(":",$ret[0]);
		
		$result["CHARGE"] = 0;
		if (strrpos($send[0], "OK") === false) {
			$result["CHARGED"] = 0;
			$result["STATUS"] = SMS_STATUS_PENDING;

			return $result;
		}
		$result["STATUS"] = $this->getStatusSubstitution( $send[1] );
		
		$result["CHARGED"] = (int)($result["STATUS"] == SMS_STATUS_DELIVERED);
		$result["STATUS_TEXT"] = $send[0];
		return $result;
	}

	function getSMSLength( )
	{
		$unicode = $this->params->get( "unicode" );

		//return ( $unicode == 1 ) ? 210 : 480;
		return 210;
	}

	function getSMSPartLength( )
	{
		$unicode = $this->params->get( "unicode" );

		//return ( $unicode == 1 ) ? 70 : 160;
		return 70;
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
			"11" => "Message length too long, greater than 160 characters",
			"13" => "Invalid recipient mobile number",
			"14" => "Invalid recipient name",
			"15" => "Unsupported sender number",
			"16" => "Unsupported sender name",
			"17" => "Flash messages with special characters are not allowed",
			"18" => "Invalid number of parameters",
			"19" => "To many messages to send in one request(only 100 allowed)",
			"20" => "Invalid amount of indexes (idx parameter)",
			"101" => "Invalid authorization info",
			"102" => "Invalid account ID/password combination",
			"103" => "Insufficient SMS credits on your account",
			"200" => "Unsuccessful message submission",
			"300" => "Invalid value of field points (value 1 is required when this parameter is used)",
			"400" => "Invalid message ID of a status response",
			"999" => "Internal error (please contact HQsms.com)"		
		);
		return in_array( $id, array_keys( $statusDescr ) ) ? $statusDescr[$id] : "Unknown status ".$id;
	}

	function getStatusSubstitution( $id )
	{	
		// 401 NOT_FOUND Invalid message ID or report expired
		// 402 EXPIRED Message expired
		// 403 SENT Message has been sent
		// 404 DELIVERED Message delivered
		// 405 UNDELIVERED Message undelivered (roaming error)
		// 406 FAILED Message cannot be delivered
		// 407 REJECTED Message rejected (unavailable recipient)
		// 408 UNKNOWN Mobile operator Carnot be identified
		// 409 QUEUED Message is queued for delivery	
		switch ( $id ) {
			case "404": 
				return( SMS_STATUS_DELIVERED );
				break;
			case "401":
			case "402":
			case "405":
			case "406":
			case "407":
				return( SMS_STATUS_CANCELED );
				break;
			case "409":
			default:

				return( SMS_STATUS_PENDING );
				break;
				
		}
		
	}
}

?>
