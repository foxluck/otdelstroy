<?php

/**
 *  Mail sockets functions for inbox
 *
 * @example 
 *
 */

class MailBox
{
	public static $params;
	protected static $connect;
	public static $log;
	public static $connectLimit = 60;

	public static function open()
	{
		self::$log = '';

		$prefix = self::$params['SECURE'] ? 'ssl://' : '';

		self::$connect = @fsockopen($prefix.self::$params['SERVER'], self::$params['PORT'], &$errno, &$errstr, 10);

		if(self::$connect) {

			if(self::$params['PROTOCOL'] == 'pop3') {
				self::$log = fgets(self::$connect);
				$cmd = "USER ".self::$params['LOGIN']."\r\n";
				fputs(self::$connect, $cmd);
				self::$log .= $cmd.fgets(self::$connect);
				$cmd = "PASS ".self::$params['PASSWORD']."\r\n";
				fputs(self::$connect, $cmd);
				$data = fgets(self::$connect);
				self::$log .= $cmd.$data;
				if(stripos($data, '+OK') === 0)
					return true;
			} else {
				self::$log = fread(self::$connect, 1024);
				$cmd = "O1 LOGIN ".self::$params['LOGIN']." ".self::$params['PASSWORD']."\r\n";
				fputs(self::$connect, $cmd);
				$data = fgets(self::$connect);
				self::$log .= $cmd . $data;
				if(stripos($data, 'O1 OK LOGIN') === 0)
					return true;
			}
			fclose(self::$connect);
			return array('error'=>'login');
		}
		return array('error'=>'connect');
	}

	public static function close()
	{
		if(self::$params['PROTOCOL'] == 'pop3')
			$cmd = "QUIT\r\n";
		else
			$cmd = "C1 LOGOUT\r\n";

		fputs(self::$connect, $cmd);
		self::$log .= $cmd . fread(self::$connect, 1024);

		fclose(self::$connect);
	}

	public static function getCount()
	{
		$count = 0;
		if(self::$params['PROTOCOL'] == 'pop3') {
			$cmd = "STAT\r\n";
			fputs(self::$connect, $cmd);
			$data = fgets(self::$connect);
			self::$log .= $cmd.$data;
			if(preg_match('/^\+OK (\d+)/i', $data, $match)) {
				$count = $match[1];
			}
		} else {
			$cmd = "B1 SELECT INBOX\r\n";
			fputs(self::$connect, $cmd);
			self::$log .= $cmd;
			while(($data = fgets(self::$connect)) && (stripos($data, 'B1 OK') !== 0)) {
				if(preg_match('/^\* (\d+) EXISTS/i', $data, $match)) {
					$count = $match[1];
				}
				self::$log .= $data;
			}
		}
		return $count;
	}

	// "delete" flag must be set if message will be deleted after save
	public function get($id, $delete=false)
	{
		if(self::$params['PROTOCOL'] == 'pop3') {
			$cmd = "RETR $id\r\n";
			fputs(self::$connect, $cmd);
			$data = fgets(self::$connect);
			self::$log .= $cmd.$data;

			if(stripos($data, '+OK') === 0) {
				$message = '';
				while(rtrim($data = fgets(self::$connect)) != '.') {
					$message .= $data;
				}
				self::decode($message);
				return $message;
			}
		} else {
			if($delete) {
				$id = 1;
			}
			$cmd = "B2 FETCH $id:$id BODY[]\r\n";
			fputs(self::$connect, $cmd);
			$data = fgets(self::$connect);
			self::$log .= $cmd.$data;

			if(stripos($data, "* $id FETCH") === 0) {
				$message = array();
				while(($data = fgets(self::$connect)) && (stripos($data, 'B2 OK FETCH') !== 0))
					$message[] = $data;

				unset($message[count($message)-1]);
				$message = join('', $message);

				self::decode($message);
				return $message;
			}
		}
	}

	// "delete" flag must has the same value as in get() function
	public function delete($id, $delete=false)
	{
		if(self::$params['PROTOCOL'] == 'pop3') {
			$cmd = "DELE $id\r\n";
			fputs(self::$connect, $cmd);
			$data = fgets(self::$connect);
			self::$log .= $cmd . $data;
			return true;
		} else {
			if($delete) {
				$id = 1;
			}
			$cmd = "D1 STORE $id:$id +FLAGS (\Deleted)\r\n";
			fputs(self::$connect, $cmd);
			$data = fread(self::$connect, 1024);
			self::$log .= $cmd . $data; // preg_match('/D2 OK STORE/i', $data)

			$cmd = "D2 EXPUNGE\r\n";
			fputs(self::$connect, $cmd);
			$data = fread(self::$connect, 1024);
			self::$log .= $cmd . $data;
			if(stripos($data, 'D3 OK EXPUNGE') === 0) {
				return true;
			}
			return false;
		}
	}

	public function decode(&$message)
	{
		$decoder = new MailDecode($message);
		$message = $decoder->decode();

		$charset = false;
		if(preg_match('/^\s*"?([a-z\/]+)"?;\s* charset="?([a-z0-9-]+)"?/i',
			$obj->headers['content-type'], $match)) {
			$charset = $match[2];
		}

		$message->headers['from'] = MailDecode::decodeHeaderLine($message->headers['from'], $charset);
		$message->headers['to'] = MailDecode::decodeHeaderLine($message->headers['to'], $charset );
		$message->headers['subject'] = MailDecode::decodeHeaderLine($message->headers['subject'], $charset);

		$message->parsed = MailDecode::parseBody($message);

		if(!empty($message->parsed['msg_text'])) {
			$out = array();
			if(!empty($message->parsed['text'])) {
				$out[] = $message->parsed['text'];
			}
			$out = array_merge($out, $message->parsed['msg_text']);
			$message->parsed['text'] = join("\n<hr size=1 noshade>\n", $out);

			// TODO: add subparts
//			for($i=0; $i<count($message->parsed['msg_text']); $i++) {
//				$t = iconv($message->parsed['msg_charset'][$i], 'UTF-8', $message->parsed['msg_text'][$i]);
//				$t = MailDecode::format_msgbody($message->parsed['msg_text'][$i], $message->parsed['msg_type'][$i]);
//				$message->parsed['text'] .= "\n<hr size=1 noshade>\n".$message->parsed['msg_text'][$i];
//			}
		}


	}

}

?>