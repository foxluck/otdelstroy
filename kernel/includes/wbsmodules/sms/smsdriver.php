<?php

class smsdriver
{
	var $params;

	var $conn;

	function xConnect( )
	{
		$host = $this->params->get( "host" );

		$port = 26;
		$debug = false;

		$this->conn = fsockopen( $host, $port, $errno, $errstr, 10 );

		if ( !$this->conn )
			return PEAR::raiseError( "Error happend on ($host:$port): $errstr ($errno)" );

		$str = $this->wrap( $this->pwd( ) );

		if( $debug )
		{
			print "$str<br>\n";
		}

		fputs( $this->conn, $str );

		if($debug)
		{
			print "<br>After pwd: ". fgets( $conn,128 ) ."<br>\n";
		}

		return true;
	}

	function xDisconnect()
	{
		fclose( $this->conn);
	}

	function wrap( $data )
	{
		$chk = 0;

		$len = strlen( $data );

		for( $i=0; $i<$len; $i++ )
		{
			$chk+=ord(substr($data,$i,1));
		}

		$chk = sprintf("%02X",$chk%256);

		return "\x0B$data$chk\x03";
	}

/*
	# $POSSIBLE VALUES:
	# file.sms
	# file.nol	( 72x14 )
	# file.gif	( 72x14 )
	# file.bmp (72x14 )
	# file.rtttl
	# file.jpg	( 72x14 )
	# file.midi	using channel 0
*/
	function msg( $text, $dest, $sender, $TYPE )
	{
		$GATEWAY = $this->params->get( "gateway" );

		return "\x05$dest\x04$sender\x04$text\x04N\x04$TYPE\x04$GATEWAY\x04";
	}

	function pwd( )
	{
		$userid = $this->params->get( "user" );
		$passwd = $this->params->get( "password" );

		return "\x06$userid\x04$passwd\x04";
	}

	function BinToAscii( $data )
	{
		$char = 0;
		$mydata="";
		$len = strlen($data);

		for( $i=0; $i<$len; $i++)
		{
			$char=ord(substr($data,$i,1));
			$charhex = sprintf("%02X",$char%256);
			$mydata = $mydata.$charhex;
		}

		return $mydata;
	}

	function SendBMP($text,$dest,$sender)
	{
		if( xConnect() )
		{
			$str = wrap(msg(BinToAscii($text),$dest,$sender,"myfile.bmp"));
			fputs($conn,$str);
			$str = fgets($conn,128);

			xDisconnect();
			return $str;
		}

		return "Failed: Could not connect";
	}

	function _sendSMS( $text, $dest, $sender="WebAsyst" )
	{
		$debug = $this->params->get( "debug" );

		$str = $this->wrap( $this->msg( $text, $dest, $sender, "myfile.sms" ) );

		if( $debug )
		{
			print "String to send: $str <br>\n";
		}

		fputs( $this->conn, $str );

		$str = fgets( $this->conn,128 );

		if( $debug )
		{
			print "Server response: $str - $errno  - $errstr <br>\n";
		}

		return $str." - ".$errno." - ".$errstr;
	}

	function setParams( $parameters )
	{
		 $this->params = $parameters;
	}

	function sendSMS( $to, $message, $sender="" )
	{
		$sent = 0;

		if ( PEAR::isError( $ret = $this->xConnect() ) )
			return $ret;

		$msg = $this->_sendSMS( $message, $to );

		$this->xDisconnect();

		if (strstr($msg,'+Ok'))
			return true;
		else
			return PEAR::raiseError( "SMS does not sent. $msg" );

	}
}

?>
