<?php

	function socketSendOpen($host, $port, $user, $pass)
	{
		global $connect, $log;

		$connect = @fsockopen($host, $port, &$errno, &$errstr, 10);
		if($connect)
		{
			$log = fgets($connect);
			$cmd = 'EHLO '.$_SERVER['HTTP_HOST']."\n";
			fputs($connect, $cmd);
			$ret = smtp_get($connect);
//			$ret = fread($connect, 1024);
			$log .= $cmd.$ret;

			if($user && $pass)
			{
				$cmd = "AUTH LOGIN\n";
				fputs($connect, $cmd);
				$ret = fgets($connect);
				$log .= $cmd.$ret;
				if(strpos($ret, '334') === false)
				{
					socketSendClose();
					return $ret;
				}
				
				$cmd = base64_encode($user)."\n";
				fputs($connect, $cmd);
				$ret = fgets($connect);
				$log .= $cmd.$ret;
				if(strpos($ret, '334') === false)
				{
					socketSendClose();
					return $ret;
				}

				$cmd = base64_encode($pass)."\n";
				fputs($connect, $cmd);
				$ret = fgets($connect);
				$log .= $cmd.$ret;
				if(strpos($ret, '235') === false)
				{
					socketSendClose();
					return $ret;
				}
			}
			return '';
		}
		else
			return trim("$errno $errstr");
	}

	function socketSendClose()
	{
		global $connect, $log;

		if($connect)
		{
			$cmd = "QUIT\n";
			fputs($connect, $cmd);
			$log .= $cmd.fgets($connect);

			fclose($connect);
			$connect = false;
		}
	}

	function socketSendMail($fp, $from, $to, $message)
	{
		global $log;

		$ok = array();
		$error = false;

		$cmd = "MAIL FROM: $from\n";
		fputs($fp, $cmd);
		$ret = fgets($fp);
		$log .= $cmd.$ret;
		if(stripos($ret, '250') === false)
			return $ret;
		foreach($to as $addr)
		{
			$cmd = "RCPT TO: $addr\n";
			fputs($fp, $cmd);
			$ret = fgets($fp);
			$log .= $cmd.$ret;
			if(stripos($ret, '250') !== false)
				$ok[] = $addr;
			else
				$error = $ret;
		}
		if(count($ok))
		{
			$cmd = "DATA\n";
			fputs($fp, $cmd);
			$ret = fgets($fp);
			$log .= $cmd.$ret;
			if(stripos($ret, '354 ') === false)
				$error = $ret;
			else
			{
				fputs($fp, $message."\n.\n");
				$ret = fgets($fp);
				$log .= "* message data\n.\n".$ret;
				if(stripos($ret, '250') === false)
					$error = $ret;
			}
			return $ok;
		}
		return $error;
	}

	function smtp_get($fp)
	{
		$smtp_msg = '';
		while($line = fgets($fp, 515)) {
			$smtp_msg .= $line;
			if(substr($line, 3, 1) == ' ') {
				break;
			}
		}
		return $smtp_msg;
	}

?>
